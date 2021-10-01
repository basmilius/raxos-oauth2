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

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\Http\HttpCode;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Effect\RedirectEffect;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function urlencode;

/**
 * Class TokenResponseType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 2.0.0
 */
final class TokenResponseType extends AbstractResponseType
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function handle(Router $router, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): Effect|Response
    {
        $accessToken = $this->tokenFactory->generateAccessToken();

        $this->tokenFactory->saveAccessToken($client, $owner, $scope, $accessToken, 3600, null);

        $state = $state !== null ? '&state=' . urlencode($state) : '';

        return new RedirectEffect($router, "{$redirectUri}#code={$accessToken}&token_type=Bearer&expires_in=3600{$state}", HttpCode::SEE_OTHER);
    }

}
