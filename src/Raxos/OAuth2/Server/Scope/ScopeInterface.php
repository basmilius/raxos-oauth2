<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Scope;

/**
 * Interface ScopeInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Scope
 * @since 2.0.0
 */
interface ScopeInterface
{

    /**
     * Gets the key of the scope.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getKey(): string;

    /**
     * Gets the name of the scope.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getName(): string;

    /**
     * Gets the description of the scope.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getDescription(): string;

}
