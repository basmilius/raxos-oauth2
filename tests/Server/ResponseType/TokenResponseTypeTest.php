<?php
declare(strict_types=1);

use Raxos\Http\{HttpRequest, HttpResponseCode};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\ResponseType\TokenResponseType;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

covers(TokenResponseType::class);

it('stores access tokens and encodes token and state values in the redirect fragment', function (?string $state): void {
    $client = $this->createMock(ClientInterface::class);
    $factory = $this->createMock(TokenFactoryInterface::class);
    $factory->method('generateAccessToken')->willReturn('access+&token');
    $factory->expects($this->once())->method('saveAccessToken')->with($client, 'owner', 'read', 'access+&token', 3600, null);
    $response = new TokenResponseType($factory)->handle(HttpRequest::create(), $client, 'owner', 'https://example.org/callback', 'read', $state);
    parse_str(parse_url($response->headers->get('Location'), PHP_URL_FRAGMENT), $fragment);
    $expected = ['access_token' => 'access+&token', 'token_type' => 'Bearer', 'expires_in' => '3600'];
    if ($state !== null) {
        $expected['state'] = $state;
    }
    expect($response->responseCode)->toBe(HttpResponseCode::SEE_OTHER)->and($fragment)->toBe($expected);
})->with([null, 'state & plus+']);
