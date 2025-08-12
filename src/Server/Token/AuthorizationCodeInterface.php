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

}
