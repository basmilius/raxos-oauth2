<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Token;

use Raxos\OAuth2\Server\Client\ClientInterface;

/**
 * Interface RotatingTokenFactoryInterface
 *
 * The persistence adapter owns one atomic consume-and-issue transaction.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Token
 * @since 3.3.0
 */
interface RotatingTokenFactoryInterface extends TokenFactoryInterface
{
    /**
     * Atomically rechecks client, expiry and active family, consumes the previous token and persists both replacements. On reuse, revokes the entire family before returning false. Retain consumed tokens for replay detection; never implement this as separate read/delete/save operations.
     *
     * @param ClientInterface $client
     * @param RefreshTokenInterface $previous
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $scope
     * @param int $expiresIn
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function rotateRefreshToken(
        ClientInterface $client,
        RefreshTokenInterface $previous,
        string $accessToken,
        string $refreshToken,
        string $scope,
        int $expiresIn
    ): bool;
}
