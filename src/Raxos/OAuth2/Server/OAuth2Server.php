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

namespace Raxos\OAuth2\Server;

use Raxos\OAuth2\Server\Client\ClientFactoryInterface;
use Raxos\OAuth2\Server\ResponseType\CodeResponseType;
use Raxos\OAuth2\Server\ResponseType\TokenResponseType;
use Raxos\OAuth2\Server\Scope\ScopeFactoryInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

/**
 * Class OAuth2Server
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server
 * @since 2.0.0
 */
abstract class OAuth2Server
{

    public const GRANT_TYPES = [
        'authorization_code' => null,
        'refresh_token' => null
    ];

    public const RESPONSE_TYPES = [
        'code' => CodeResponseType::class,
        'token' => TokenResponseType::class
    ];

    /**
     * OAuth2Server constructor.
     *
     * @param ClientFactoryInterface $clientFactory
     * @param ScopeFactoryInterface $scopeFactory
     * @param TokenFactoryInterface $tokenFactory
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(
        protected ClientFactoryInterface $clientFactory,
        protected ScopeFactoryInterface $scopeFactory,
        protected TokenFactoryInterface $tokenFactory
    )
    {
    }

    /**
     * Gets the client factory instance.
     *
     * @return ClientFactoryInterface
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getClientFactory(): ClientFactoryInterface
    {
        return $this->clientFactory;
    }

    /**
     * Gets the scope factory instance.
     *
     * @return ScopeFactoryInterface
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getScopeFactory(): ScopeFactoryInterface
    {
        return $this->scopeFactory;
    }

    /**
     * Gets the token factory instance.
     *
     * @return TokenFactoryInterface
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getTokenFactory(): TokenFactoryInterface
    {
        return $this->tokenFactory;
    }

    /**
     * Gets the owner.
     *
     * @return mixed
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public abstract function getOwner(): mixed;

    /**
     * Returns TRUE if there is an owner available.
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public abstract function hasOwner(): bool;

}
