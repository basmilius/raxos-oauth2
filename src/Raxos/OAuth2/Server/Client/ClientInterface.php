<?php
/*
 * Copyright (c) 2017 - 2021 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Raxos\OAuth2\Server\Client;

/**
 * Interface ClientInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Client
 * @since 2.0.0
 */
interface ClientInterface
{

    /**
     * Gets the client id.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getClientId(): string;

    /**
     * Returns TRUE if the given redirect uri is allowed.
     *
     * @param string $redirectUri
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function isRedirectUriAllowed(string $redirectUri): bool;

    /**
     * Returns TRUE if the given secret is valid.
     *
     * @param string $clientSecret
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function isSecretValid(string $clientSecret): bool;

}
