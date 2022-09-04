<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Token;

/**
 * Interface AuthorizationCodeInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Token
 * @since 2.0.0
 */
interface AuthorizationCodeInterface extends TokenInterface
{

    /**
     * Gets the redirect uri.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getRedirectUri(): string;

}
