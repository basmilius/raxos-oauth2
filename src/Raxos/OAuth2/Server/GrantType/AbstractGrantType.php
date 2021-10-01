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

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Http\HttpRequest;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Effect\NotFoundEffect;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;

/**
 * Class AbstractGrantType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 2.0.0
 */
class AbstractGrantType implements GrantTypeInterface
{

    /**
     * AbstractGrantType constructor.
     *
     * @param TokenFactoryInterface $tokenFactory
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(protected TokenFactoryInterface $tokenFactory)
    {
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function handle(Router $router, HttpRequest $request, ClientInterface $client): Effect|Response
    {
        return new NotFoundEffect($router);
    }

}
