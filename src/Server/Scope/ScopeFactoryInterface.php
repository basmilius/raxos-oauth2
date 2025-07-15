<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Scope;

use Raxos\OAuth2\Server\Error\InvalidScopeException;

/**
 * Interface ScopeFactoryInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Scope
 * @since 1.0.16
 */
interface ScopeFactoryInterface
{

    /**
     * Converts a scope string to an array of scope keys.
     *
     * @param string $scopeString
     *
     * @return string[]
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function convertScopeString(string $scopeString): array;

    /**
     * Converts the given scope keys to scope instances.
     *
     * @param array $scopes
     *
     * @return ScopeInterface[]
     * @throws InvalidScopeException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function convertScopes(array $scopes): array;

    /**
     * Ensures that the given scopes are valid.
     *
     * @param array $scopes
     *
     * @throws InvalidScopeException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function ensureValidScopes(array $scopes): void;

    /**
     * Gets the details for the given scope key.
     *
     * @param string $key
     *
     * @return ScopeInterface
     * @throws InvalidScopeException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function getScope(string $key): ScopeInterface;

}
