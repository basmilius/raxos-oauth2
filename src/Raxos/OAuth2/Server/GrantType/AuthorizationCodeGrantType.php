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
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\Error\InvalidRequestException;
use Raxos\OAuth2\Server\Error\RedirectUriMismatchException;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function urldecode;

/**
 * Class AuthorizationCodeGrantType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 2.0.0
 */
final class AuthorizationCodeGrantType extends AbstractGrantType
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function handle(Router $router, HttpRequest $request, ClientInterface $client): Effect|Response
    {
        $code = $request->post()->get('code')
            ?? throw new InvalidRequestException('Missing parameter: "code" is required.');

        $redirectUri = $request->post()->get('redirect_uri')
            ?? throw new InvalidRequestException('Missing parameter: "redirect_uri" is required.');

        $authorizationCode = $this->tokenFactory->getAuthorizationCode($code) ?? throw new InvalidGrantException('Authorization code doesn\'t exist or is invalid for the client.');
        $redirectUri = urldecode($redirectUri);

        if ($authorizationCode->isExpired()) {
            throw new InvalidGrantException('The authorization code has expired.');
        }

        if ($authorizationCode->getRedirectUri() !== $redirectUri) {
            throw new RedirectUriMismatchException();
        }
    }

}
