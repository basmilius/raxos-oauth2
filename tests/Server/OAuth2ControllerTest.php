<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\{HttpPostMap, HttpQueryMap};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\{InvalidRequestException};
use Raxos\OAuth2\Server\Token\{AuthorizationCodeInterface, TokenFactoryInterface};
use Raxos\Router\Mapper;
use RaxosTests\OAuth2\Controller;

function oauthControllerUnitContext(mixed $owner = 'owner'): array
{
    $client = test()->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $client->method('isSecretValid')->willReturnCallback(static fn (string $secret): bool => $secret === 'secret');
    $client->method('isRedirectUriAllowed')->willReturnCallback(static fn (string $uri): bool => str_starts_with($uri, 'https://example.org/callback'));
    $clients = test()->createMock(Raxos\OAuth2\Server\Client\ClientFactoryInterface::class);
    $clients->method('getClient')->willReturnCallback(static fn (string $id): ?ClientInterface => $id === 'client' ? $client : null);
    $tokens = test()->createMock(TokenFactoryInterface::class);
    $scopes = test()->createMock(Raxos\OAuth2\Server\Scope\ScopeFactoryInterface::class);
    $scopes->method('convertScopeString')->willReturnCallback(static fn (string $scope): array => explode(' ', $scope));
    $scope = test()->createMock(Raxos\OAuth2\Server\Scope\ScopeInterface::class);
    $scope->method('getKey')->willReturn('read');
    $scopes->method('convertScopes')->willReturn([$scope]);
    $server = new RaxosTests\OAuth2\UnitServer($clients, $scopes, $tokens, $owner);
    return [new RaxosTests\OAuth2\ContextController($server), $client, $clients, $tokens, $scopes, $scope];
}

function oauthControllerUnitQuery(): array
{
    return ['client_id' => 'client', 'redirect_uri' => 'https://example.org/callback', 'response_type' => 'code', 'scope' => 'read', 'state' => 'state', 'code_challenge' => str_repeat('a', 43), 'code_challenge_method' => 'S256'];
}

covers(Raxos\OAuth2\Server\OAuth2Controller::class);

it('exposes the four OAuth endpoints to controller mapping', function (): void {
    expect(count(iterator_to_array(Mapper::routes(new ReflectionClass(Controller::class)))))->toBe(4);
});

it('handles missing resource owners before validating either authorization endpoint', function (string $method): void {
    [$controller, , $clients, $tokens] = oauthControllerUnitContext(null);
    $clients->expects($this->never())->method('getClient');
    $tokens->expects($this->never())->method('generateAuthorizationCode');
    expect($controller->{$method}(HttpRequest::create())->responseCode)->toBe(Raxos\Http\HttpResponseCode::NO_CONTENT);
})->with(['getAuthorize', 'postAuthorize']);

it('renders authorization context after validating the client, redirect, scopes and PKCE', function (): void {
    [$controller, $client, , , $scopes, $scope] = oauthControllerUnitContext();
    $scopes->expects($this->once())->method('ensureValidScopes')->with(['read']);
    $query = oauthControllerUnitQuery();
    $context = $controller->getAuthorize(HttpRequest::create(query: new HttpQueryMap($query)))->result;
    expect($context)->toBe(['client' => $client, 'client_id' => 'client', 'redirect_uri' => $query['redirect_uri'], 'response_type' => 'code', 'scope' => 'read', 'scopes' => [$scope], 'state' => 'state', 'code_challenge' => str_repeat('a', 43), 'code_challenge_method' => 'S256']);
});

it('rejects missing authorization parameters before rendering consent', function (string $missing): void {
    [$controller] = oauthControllerUnitContext();
    $query = oauthControllerUnitQuery();
    unset($query[$missing]);
    expect(fn () => $controller->getAuthorize(HttpRequest::create(query: new HttpQueryMap($query))))->toThrow(InvalidRequestException::class);
})->with(['client_id', 'redirect_uri', 'response_type', 'scope', 'code_challenge', 'code_challenge_method']);

it('rejects unknown clients, disallowed redirects and unsupported response types', function (string $key, string $value, string $error): void {
    [$controller, , , $tokens] = oauthControllerUnitContext();
    $tokens->expects($this->never())->method('generateAuthorizationCode');
    $query = oauthControllerUnitQuery();
    $query[$key] = $value;
    expect(fn () => $controller->getAuthorize(HttpRequest::create(query: new HttpQueryMap($query))))->toThrow($error);
})->with([['client_id', 'missing', Raxos\OAuth2\Server\Error\InvalidClientException::class], ['redirect_uri', 'https://attacker.example', Raxos\OAuth2\Server\Error\RedirectUriMismatchException::class], ['response_type', 'unknown', Raxos\OAuth2\Server\Error\UnsupportedGrantTypeException::class]]);

it('propagates scope validation failures before rendering or generating tokens', function (): void {
    [$controller, , , $tokens, $scopes] = oauthControllerUnitContext();
    $scopes->method('ensureValidScopes')->willThrowException(new Raxos\OAuth2\Server\Error\InvalidScopeException('Invalid scope'));
    $tokens->expects($this->never())->method('generateAuthorizationCode');
    expect(fn () => $controller->getAuthorize(HttpRequest::create(query: new HttpQueryMap(oauthControllerUnitQuery()))))->toThrow(Raxos\OAuth2\Server\Error\InvalidScopeException::class);
});

it('redirects denied consent with encoded state and no token side effects', function (): void {
    [$controller, , , $tokens] = oauthControllerUnitContext();
    $tokens->expects($this->never())->method('generateAuthorizationCode');
    $query = oauthControllerUnitQuery();
    $query['redirect_uri'] .= '?existing=keep';
    $query['state'] = 'state & plus+';
    $response = $controller->postAuthorize(HttpRequest::create(query: new HttpQueryMap($query), post: new HttpPostMap()));
    parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $redirect);
    expect($response->responseCode)->toBe(Raxos\Http\HttpResponseCode::SEE_OTHER)
        ->and($redirect)->toBe(['existing' => 'keep', 'error' => 'access_denied', 'state' => 'state & plus+']);
});

it('issues the selected response type after consent', function (string $responseType): void {
    [$controller, $client, , $tokens] = oauthControllerUnitContext();
    $query = oauthControllerUnitQuery();
    $query['response_type'] = $responseType;
    if ($responseType === 'code') {
        $tokens->expects($this->once())->method('generateAuthorizationCode')->willReturn('authorization-code');
        $tokens->expects($this->once())->method('saveAuthorizationCode')->with($client, 'owner', $query['redirect_uri'], 'read', 'authorization-code', 'state', str_repeat('a', 43));
    } else {
        $tokens->expects($this->once())->method('generateAccessToken')->willReturn('access');
        $tokens->expects($this->once())->method('saveAccessToken')->with($client, 'owner', 'read', 'access', 3600, null);
    }
    $response = $controller->postAuthorize(HttpRequest::create(query: new HttpQueryMap($query), post: new HttpPostMap(['authorize' => 'yes'])));
    expect($response->responseCode)->toBe(Raxos\Http\HttpResponseCode::SEE_OTHER)
        ->and($response->headers->get('Location'))->toContain($responseType === 'code' ? 'code=authorization-code' : '#access_token=access');
})->with(['code', 'token']);

it('authenticates token requests and rejects missing or unsupported grant types', function (?string $grant, string $error): void {
    [$controller] = oauthControllerUnitContext();
    $request = HttpRequest::create(headers: new Raxos\Http\Structure\HttpHeadersMap(['authorization' => ['Basic ' . base64_encode('client:secret')]]), post: new HttpPostMap($grant === null ? [] : ['grant_type' => $grant]));
    expect(fn () => $controller->postToken($request))->toThrow($error);
})->with([[null, InvalidRequestException::class], ['unsupported', Raxos\OAuth2\Server\Error\UnsupportedGrantTypeException::class], ['authorization_code', InvalidRequestException::class]]);

it('rejects malformed Basic authentication as an OAuth client error', function (?string $authorization, string $error): void {
    [$controller] = oauthControllerUnitContext();
    $request = HttpRequest::create(headers: new Raxos\Http\Structure\HttpHeadersMap($authorization === null ? [] : ['authorization' => [$authorization]]));
    expect(fn () => $controller->postRevoke($request))->toThrow($error);
})->with([[null, InvalidRequestException::class], ['Bearer access', Raxos\OAuth2\Server\Error\InvalidClientException::class], ['Basic', Raxos\OAuth2\Server\Error\InvalidClientException::class], ['Basic %%%', Raxos\OAuth2\Server\Error\InvalidClientException::class], ['Basic ' . base64_encode('without-colon'), Raxos\OAuth2\Server\Error\InvalidClientException::class], ['Basic ' . base64_encode('client:wrong'), Raxos\OAuth2\Server\Error\InvalidClientException::class], ['Basic ' . base64_encode('missing:secret'), Raxos\OAuth2\Server\Error\InvalidClientException::class]]);

it('accepts Basic scheme case and authenticates before a revocation acknowledgement', function (string $scheme): void {
    [$controller, , , $tokens] = oauthControllerUnitContext();
    $tokens->expects($this->never())->method('revokeAccessToken');
    $response = $controller->postRevoke(HttpRequest::create(headers: new Raxos\Http\Structure\HttpHeadersMap(['authorization' => [$scheme . ' ' . base64_encode('client:secret')]]), post: new HttpPostMap()));
    expect($response->responseCode)->toBe(Raxos\Http\HttpResponseCode::ACCEPTED)->and($response->body)->toBeTrue();
})->with(['Basic', 'basic', 'BASIC']);

it('revokes each token type with and without a type hint', function (string $kind, bool $hint): void {
    [$controller, $client, , $tokens] = oauthControllerUnitContext();
    [$interface, $getter, $revoker] = match ($kind) {
        'access_token' => [Raxos\OAuth2\Server\Token\AccessTokenInterface::class, 'getAccessToken', 'revokeAccessToken'],
        'authorization_code' => [AuthorizationCodeInterface::class, 'getAuthorizationCode', 'revokeAuthorizationCode'],
        'refresh_token' => [Raxos\OAuth2\Server\Token\RefreshTokenInterface::class, 'getRefreshToken', 'revokeRefreshToken']
    };
    $token = $this->createMock($interface);
    $token->method('getClientId')->willReturn('client');
    $tokens->method($getter)->willReturn($token);
    $tokens->expects($this->once())->method($revoker)->with($client, $token);
    $post = ['token' => 'token'];
    if ($hint) {
        $post['token_type_hint'] = $kind;
    }
    $response = $controller->postRevoke(HttpRequest::create(headers: new Raxos\Http\Structure\HttpHeadersMap(['authorization' => ['Basic ' . base64_encode('client:secret')]]), post: new HttpPostMap($post)));
    expect($response->responseCode)->toBe(Raxos\Http\HttpResponseCode::ACCEPTED)->and($response->body)->toBeTrue();
})->with([['access_token', true], ['access_token', false], ['authorization_code', true], ['authorization_code', false], ['refresh_token', true], ['refresh_token', false]]);
