<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Token;

/**
 * Interface TokenInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Token
 * @since 2.0.0
 */
interface TokenInterface
{

    /**
     * Gets the client id associated with this token.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getClientId(): string;

    /**
     * Gets the owner of the token.
     *
     * @return mixed
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getOwner(): mixed;

    /**
     * Gets the scope for this token.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getScope(): string;

    /**
     * Gets the token value.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getToken(): string;

    /**
     * Returns TRUE if the token is expired.
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function isExpired(): bool;

    /**
     * Returns TRUE if the given scope is allowed by the token.
     *
     * @param string $scope
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function isScopeAllowed(string $scope): bool;

}
