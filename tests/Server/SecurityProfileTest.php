<?php
declare(strict_types=1);

use Raxos\Error\InvalidArgumentException;
use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\Http\Structure\HttpPostMap;
use Raxos\Http\Structure\HttpQueryMap;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\Error\InvalidScopeException;
use Raxos\OAuth2\Server\Error\UnsupportedGrantTypeException;
use Raxos\OAuth2\Server\GrantType\RefreshTokenGrantType;
use Raxos\OAuth2\Server\SecurityProfile;
use Raxos\OAuth2\Server\Token\RefreshTokenInterface;
use Raxos\OAuth2\Server\Token\RotatingTokenFactoryInterface;

covers(SecurityProfile::class);

it('rejects invalid protocol selections and non-positive lifetimes', function (array $options): void {
    expect(fn() => new SecurityProfile(...$options))->toThrow(InvalidArgumentException::class);
})->with([[['accessTokenLifetime' => 0]], [['grantTypes' => ['password']]], [['responseTypes' => ['unsupported']]]]);

it('refuses modern configuration when the storage factory cannot rotate atomically', function (): void {
    [$controller] = oauthControllerUnitContext();
    expect(fn() => $controller->oAuth2->securityProfile(SecurityProfile::modern()))->toThrow(InvalidArgumentException::class);
});

it('disables implicit authorization and can restrict supported grants', function (): void {
    [$controller] = oauthControllerUnitContext();
    $controller->oAuth2->securityProfile(new SecurityProfile(grantTypes: ['authorization_code'], responseTypes: ['code']));
    $query = oauthControllerUnitQuery();
    $query['response_type'] = 'token';
    expect(fn() => $controller->getAuthorize(HttpRequest::create(query: new HttpQueryMap($query))))->toThrow(UnsupportedGrantTypeException::class);
    $request = HttpRequest::create(headers: new HttpHeadersMap(['authorization' => ['Basic ' . base64_encode('client:secret')]]), post: new HttpPostMap(['grant_type' => 'refresh_token']));
    expect(fn() => $controller->postToken($request))->toThrow(UnsupportedGrantTypeException::class);
});

it('rotates both tokens with one storage operation and rejects a concurrent reuse', function (): void {
    $client = test()->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $refresh = test()->createMock(RefreshTokenInterface::class);
    $refresh->method('getClientId')->willReturn('client');
    $refresh->method('getScope')->willReturn('read write');
    $refresh->method('isScopeAllowed')->willReturnCallback(static fn(string $scope): bool => in_array($scope, ['read', 'write'], true));
    $tokens = test()->createMock(RotatingTokenFactoryInterface::class);
    $tokens->method('getRefreshToken')->willReturn($refresh);
    $tokens->method('generateAccessToken')->willReturn('access');
    $tokens->method('generateRefreshToken')->willReturn('replacement');
    $tokens->expects(test()->exactly(2))->method('rotateRefreshToken')->with($client, $refresh, 'access', 'replacement', 'read', 120)->willReturnOnConsecutiveCalls(true, false);
    $tokens->expects(test()->never())->method('saveAccessToken');
    $tokens->expects(test()->never())->method('saveRefreshToken');
    $tokens->expects(test()->never())->method('revokeAccessToken');
    $grant = new RefreshTokenGrantType($tokens, SecurityProfile::modern(120));
    $request = HttpRequest::create(post: new HttpPostMap(['refresh_token' => 'old', 'scope' => 'read']));
    expect($grant->handle($request, $client)->body)->toBe(['access_token' => 'access', 'token_type' => 'Bearer', 'scope' => 'read', 'expires_in' => 120, 'refresh_token' => 'replacement']);
    expect(fn() => $grant->handle($request, $client))->toThrow(InvalidGrantException::class);
});

it('rejects scope escalation before consuming or generating any token', function (): void {
    $client = test()->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $refresh = test()->createMock(RefreshTokenInterface::class);
    $refresh->method('getClientId')->willReturn('client');
    $refresh->method('getScope')->willReturn('read');
    $refresh->method('isScopeAllowed')->willReturn(false);
    $tokens = test()->createMock(RotatingTokenFactoryInterface::class);
    $tokens->method('getRefreshToken')->willReturn($refresh);
    $tokens->expects(test()->never())->method('rotateRefreshToken');
    $tokens->expects(test()->never())->method('generateAccessToken');
    expect(fn() => new RefreshTokenGrantType($tokens, SecurityProfile::modern())->handle(HttpRequest::create(post: new HttpPostMap(['refresh_token' => 'old', 'scope' => 'admin'])), $client))->toThrow(InvalidScopeException::class);
});
