<?php
declare(strict_types=1);

use Raxos\Http\HttpRequest;
use Raxos\Http\Structure\HttpPostMap;
use Raxos\Http\Structure\HttpQueryMap;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\Error\InvalidRequestException;
use Raxos\OAuth2\Server\GrantType\AuthorizationCodeGrantType;
use Raxos\OAuth2\Server\Pkce;
use Raxos\OAuth2\Server\ResponseType\CodeResponseType;
use Raxos\OAuth2\Server\Token\AuthorizationCodeInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;
use Raxos\Router\Mapper;
use RaxosTests\OAuth2\Controller;

it('matches the RFC7636 S256 test vector and rejects missing or invalid proof', function (): void {
    $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
    $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
    expect(Pkce::verify($verifier, $challenge))->toBeTrue();
    expect(Pkce::verify('incorrect', $challenge))->toBeFalse();
    expect(Pkce::verify($verifier, null))->toBeFalse();
    expect(fn() => Pkce::challenge($challenge, 'plain'))->toThrow(InvalidRequestException::class);
});

it('consumes a code atomically before issuing tokens and rejects a concurrent redemption', function (): void {
    $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
    $client = $this->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $code = $this->createMock(AuthorizationCodeInterface::class);
    $code->method('getClientId')->willReturn('client');
    $code->method('getRedirectUri')->willReturn('https://example.org/callback');
    $code->method('getCodeChallenge')->willReturn('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM');
    $code->method('getScope')->willReturn('read');
    $code->method('isExpired')->willReturn(false);
    $factory = $this->createMock(TokenFactoryInterface::class);
    // Both requests see the same snapshot; only the conditional DELETE can claim it.
    $factory->method('getAuthorizationCode')->willReturn($code);
    $storage = new PDO('sqlite::memory:');
    $storage->exec("CREATE TABLE codes (token TEXT PRIMARY KEY, client TEXT); INSERT INTO codes VALUES ('one-use', 'client')");
    $factory->method('consumeAuthorizationCode')->willReturnCallback(static function () use ($storage): bool {
        $claim = $storage->prepare('DELETE FROM codes WHERE token = ? AND client = ?');
        $claim->execute(['one-use', 'client']);
        return $claim->rowCount() === 1;
    });
    $factory->expects($this->once())->method('generateAccessToken')->willReturn('access');
    $factory->expects($this->once())->method('generateRefreshToken')->willReturn('refresh');
    $factory->expects($this->once())->method('saveAccessToken');
    $factory->expects($this->once())->method('saveRefreshToken');
    $request = HttpRequest::create(post: new HttpPostMap(['code' => 'one-use', 'redirect_uri' => 'https://example.org/callback', 'code_verifier' => $verifier]));
    $grant = new AuthorizationCodeGrantType($factory);
    expect($grant->handle($request, $client)->body['access_token'])->toBe('access');
    expect(fn() => $grant->handle($request, $client))->toThrow(InvalidGrantException::class);
});

it('stores the S256 challenge with each authorization code', function (): void {
    $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
    $client = $this->createMock(ClientInterface::class);
    $factory = $this->createMock(TokenFactoryInterface::class);
    $factory->method('generateAuthorizationCode')->willReturn('code');
    $factory->expects($this->once())->method('saveAuthorizationCode')->with($client, 'owner', 'https://example.org', 'read', 'code', 'state', $challenge);
    $request = HttpRequest::create(query: new HttpQueryMap(['code_challenge' => $challenge, 'code_challenge_method' => 'S256']));
    new CodeResponseType($factory)->handle($request, $client, 'owner', 'https://example.org', 'read', 'state');
});

it('exposes the four OAuth endpoints to controller mapping', function (): void {
    expect(count(iterator_to_array(Mapper::routes(new ReflectionClass(Controller::class)))))->toBe(4);
});

it('rejects invalid code redemption before consuming a code or issuing a token', function (string $case, string $exception): void {
    $client = $this->createMock(ClientInterface::class);
    $client->method('getClientId')->willReturn('client');
    $code = $this->createMock(AuthorizationCodeInterface::class);
    $code->method('getClientId')->willReturn($case === 'other-client' ? 'other' : 'client');
    $code->method('getRedirectUri')->willReturn('https://example.org/callback');
    $code->method('getCodeChallenge')->willReturn('E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM');
    $code->method('isExpired')->willReturn($case === 'expired');
    $factory = $this->createMock(TokenFactoryInterface::class);
    $factory->method('getAuthorizationCode')->willReturn($case === 'missing-code' ? null : $code);
    $factory->expects($this->never())->method('consumeAuthorizationCode');
    $factory->expects($this->never())->method('generateAccessToken');
    $post = ['code' => 'one-use', 'redirect_uri' => 'https://example.org/callback', 'code_verifier' => 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk'];
    if ($case === 'redirect') {
        $post['redirect_uri'] = 'https://attacker.example/callback';
    } elseif ($case === 'verifier') {
        $post['code_verifier'] = str_repeat('x', 43);
    }
    $request = HttpRequest::create(post: new HttpPostMap($post));
    expect(fn(): mixed => new AuthorizationCodeGrantType($factory)->handle($request, $client))->toThrow($exception);
})->with([
    ['expired', InvalidGrantException::class], ['other-client', InvalidGrantException::class],
    ['missing-code', InvalidGrantException::class], ['redirect', Raxos\OAuth2\Server\Error\RedirectUriMismatchException::class], ['verifier', InvalidGrantException::class],
]);

it('rejects malformed PKCE challenges', function (mixed $challenge, mixed $method): void {
    expect(fn(): string => Pkce::challenge($challenge, $method))->toThrow(InvalidRequestException::class);
})->with([[null, 'S256'], ['', 'S256'], [str_repeat('x', 42), 'S256'], [str_repeat('!', 43), 'S256'], [[], 'S256'], [str_repeat('x', 43), null]]);
