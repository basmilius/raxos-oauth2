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
 * Interface ClientFactoryInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Client
 * @since 2.0.0
 */
interface ClientFactoryInterface
{

    /**
     * Gets a client with the given id.
     *
     * @param string $clientId
     *
     * @return ClientInterface|null
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getClient(string $clientId): ?ClientInterface;

}
