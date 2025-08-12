<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Client;

/**
 * Interface ClientInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Client
 * @since 1.0.16
 */
interface ClientInterface
{

    /**
     * Gets the client id.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function getClientId(): string;

    /**
     * Returns TRUE if the given redirect uri is allowed.
     *
     * @param string $redirectUri
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function isRedirectUriAllowed(string $redirectUri): bool;

    /**
     * Returns TRUE if the given secret is valid.
     *
     * @param string $clientSecret
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function isSecretValid(string $clientSecret): bool;

}
