<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Token;

/**
 * Interface AuthorizationCodeInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Token
 * @since 1.0.16
 */
interface AuthorizationCodeInterface extends TokenInterface
{

    /**
     * Gets the redirect uri.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function getRedirectUri(): string;

    /**
     * Returns the S256 challenge bound to this authorization code, or null when no challenge was stored.
     *
     * @return string|null S256 challenge; null marks a legacy code that cannot be redeemed.
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public function getCodeChallenge(): ?string;

}
