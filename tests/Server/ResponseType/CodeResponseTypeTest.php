<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\{HttpQueryMap};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\ResponseType\CodeResponseType;
use Raxos\OAuth2\Server\Token\{TokenFactoryInterface};

covers(CodeResponseType::class);

it('stores the S256 challenge with each authorization code', function (): void {
    $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
    $client = $this->createMock(ClientInterface::class);
    $factory = $this->createMock(TokenFactoryInterface::class);
    $factory->method('generateAuthorizationCode')->willReturn('code');
    $factory->expects($this->once())->method('saveAuthorizationCode')->with($client, 'owner', 'https://example.org', 'read', 'code', 'state', $challenge);
    $request = HttpRequest::create(query: new HttpQueryMap(['code_challenge' => $challenge, 'code_challenge_method' => 'S256']));
    new CodeResponseType($factory)->handle($request, $client, 'owner', 'https://example.org', 'read', 'state');
});

it('encodes code and state values and preserves an existing redirect query', function (string $redirect, ?string $state): void {
    $challenge = str_repeat('a', 43);
    $client = $this->createMock(ClientInterface::class);
    $factory = $this->createMock(TokenFactoryInterface::class);
    $factory->method('generateAuthorizationCode')->willReturn('code+&value');
    $factory->expects($this->once())->method('saveAuthorizationCode')->with($client, 'owner', $redirect, 'read', 'code+&value', $state, $challenge);
    $request = HttpRequest::create(query: new HttpQueryMap(['code_challenge' => $challenge, 'code_challenge_method' => 'S256']));
    $response = new CodeResponseType($factory)->handle($request, $client, 'owner', $redirect, 'read', $state);
    parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
    expect($query['code'])->toBe('code+&value')->and($query['state'] ?? null)->toBe($state);
    if (str_contains($redirect, '?')) {
        expect($query['existing'])->toBe('keep');
    }
})->with([['https://example.org/callback', null], ['https://example.org/callback?existing=keep', 'state & plus+']]);
