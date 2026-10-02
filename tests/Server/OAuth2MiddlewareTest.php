<?php
declare(strict_types=1);

use Raxos\Http\{HttpRequest, HttpResponseCode};
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\Http\Structure\HttpHeadersMap;
use Raxos\OAuth2\Server\Client\{ClientFactoryInterface, ClientInterface};
use Raxos\OAuth2\Server\OAuth2Middleware;
use Raxos\OAuth2\Server\Scope\ScopeFactoryInterface;
use Raxos\OAuth2\Server\Token\{AccessTokenInterface, TokenFactoryInterface};
use RaxosTests\OAuth2\{UnitMiddleware, UnitServer};

covers(OAuth2Middleware::class);

it('accepts valid bearer tokens using the HTTP request parsing rules', function (string $authorization): void {
    $clients = $this->createMock(ClientFactoryInterface::class);
    $tokens = $this->createMock(TokenFactoryInterface::class);
    $token = $this->createMock(AccessTokenInterface::class);
    $token->method('getClientId')->willReturn('client');
    $tokens->expects($this->once())->method('getAccessToken')->with('access')->willReturn($token);
    $clients->expects($this->once())->method('getClient')->with('client')->willReturn($this->createMock(ClientInterface::class));
    $middleware = new UnitMiddleware(new UnitServer($clients, $this->createMock(ScopeFactoryInterface::class), $tokens));
    $request = HttpRequest::create(headers: new HttpHeadersMap(['authorization' => [$authorization]]));
    $response = new NoContentHttpResponse();
    expect($middleware->handle($request, static function (HttpRequest $actual) use ($request, $response): NoContentHttpResponse {
        expect($actual)->toBe($request);
        return $response;
    }))->toBe($response);
})->with(['Bearer access', 'bearer access', 'BEARER  access']);

it('rejects invalid credentials before running the protected handler', function (string $case, HttpResponseCode $status, string $error): void {
    $clients = $this->createMock(ClientFactoryInterface::class);
    $tokens = $this->createMock(TokenFactoryInterface::class);
    $token = $this->createMock(AccessTokenInterface::class);
    $token->method('getClientId')->willReturn('client');
    $token->method('isExpired')->willReturn($case === 'expired');
    $tokens->method('getAccessToken')->willReturn($case === 'unknown' ? null : $token);
    $clients->method('getClient')->willReturn($case === 'client' ? null : $this->createMock(ClientInterface::class));
    if (in_array($case, ['missing', 'malformed'], true)) {
        $tokens->expects($this->never())->method('getAccessToken');
    }
    $headers = $case === 'missing' ? [] : ['authorization' => [$case === 'malformed' ? 'Basic invalid' : 'Bearer access']];
    $middleware = new UnitMiddleware(new UnitServer($clients, $this->createMock(ScopeFactoryInterface::class), $tokens));
    $response = $middleware->handle(HttpRequest::create(headers: new HttpHeadersMap($headers)), static fn (): never => throw new LogicException('Must not run protected handler.'));
    expect($response->responseCode)->toBe($status)->and($response->body->jsonSerialize()['error'])->toBe($error);
})->with([['missing', HttpResponseCode::BAD_REQUEST, 'invalid_request'], ['malformed', HttpResponseCode::BAD_REQUEST, 'invalid_request'], ['unknown', HttpResponseCode::UNAUTHORIZED, 'invalid_token'], ['expired', HttpResponseCode::UNAUTHORIZED, 'invalid_token'], ['client', HttpResponseCode::UNAUTHORIZED, 'invalid_client']]);
