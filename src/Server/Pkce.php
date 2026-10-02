<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Raxos\OAuth2\Server\Error\InvalidRequestException;
use Raxos\Security\Base64;
use function hash;
use function hash_equals;
use function preg_match;

/**
 * S256 is required so authorization requests never expose the verifier.
 *
 * @author Bas Milius <bas@mili.us>
 * @since 3.2.0
 */
final class Pkce
{
    /**
     * @param mixed $challenge
     * @param mixed $method
     * @return string
     * @throws InvalidRequestException
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public static function challenge(mixed $challenge, mixed $method): string
    {
        if ($method !== 'S256' || !is_string($challenge) || !preg_match('/^[A-Za-z0-9_-]{43}$/D', $challenge)) {
            throw new InvalidRequestException('A valid S256 code_challenge is required.');
        }

        return $challenge;
    }

    /**
     * @param mixed $verifier
     * @param string|null $challenge
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public static function verify(mixed $verifier, ?string $challenge): bool
    {
        return $challenge !== null
            && is_string($verifier)
            && preg_match('/^[A-Za-z0-9._~-]{43,128}$/D', $verifier)
            && hash_equals($challenge, Base64::encodeUrlSafe(hash('sha256', $verifier, true)));
    }
}
