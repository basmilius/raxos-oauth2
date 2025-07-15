<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Token;

/**
 * Interface TokenInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Token
 * @since 1.0.16
 */
interface TokenInterface
{

    /**
     * Gets the client id associated with this token.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function getClientId(): string;

    /**
     * Gets the owner of the token.
     *
     * @return mixed
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function getOwner(): mixed;

    /**
     * Gets the scope for this token.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function getScope(): string;

    /**
     * Gets the token value.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function getToken(): string;

    /**
     * Returns TRUE if the token is expired.
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function isExpired(): bool;

    /**
     * Returns TRUE if the token allows the given scope.
     *
     * @param string $scope
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function isScopeAllowed(string $scope): bool;

}
