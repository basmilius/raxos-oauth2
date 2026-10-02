<?php
declare(strict_types=1);

use Raxos\OAuth2\Server\Error\{InvalidRequestException};
use Raxos\OAuth2\Server\Pkce;

covers(Pkce::class);

it('matches the RFC7636 S256 test vector and rejects missing or invalid proof', function (): void {
    $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
    $challenge = 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM';
    expect(Pkce::verify($verifier, $challenge))->toBeTrue();
    expect(Pkce::verify('incorrect', $challenge))->toBeFalse();
    expect(Pkce::verify($verifier, null))->toBeFalse();
    expect(fn () => Pkce::challenge($challenge, 'plain'))->toThrow(InvalidRequestException::class);
});

it('rejects malformed PKCE challenges', function (mixed $challenge, mixed $method): void {
    expect(fn (): string => Pkce::challenge($challenge, $method))->toThrow(InvalidRequestException::class);
})->with([[null, 'S256'], ['', 'S256'], [str_repeat('x', 42), 'S256'], [str_repeat('!', 43), 'S256'], [[], 'S256'], [str_repeat('x', 43), null]]);

it('accepts verifier boundary lengths and rejects invalid verifier input', function (mixed $verifier, bool $valid): void {
    $challenge = is_string($verifier) ? Raxos\Security\Base64::encodeUrlSafe(hash('sha256', $verifier, true)) : str_repeat('x', 43);
    expect(Pkce::verify($verifier, $challenge))->toBe($valid);
})->with([[str_repeat('a', 42), false], [str_repeat('a', 43), true], [str_repeat('a', 128), true], [str_repeat('a', 129), false], [str_repeat('!', 43), false], [null, false], [[], false]]);
