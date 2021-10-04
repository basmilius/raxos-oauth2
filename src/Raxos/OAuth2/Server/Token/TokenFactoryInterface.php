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

namespace Raxos\OAuth2\Server\Token;

use Raxos\OAuth2\Server\Client\ClientInterface;

/**
 * Interface TokenFactoryInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Token
 * @since 2.0.0
 */
interface TokenFactoryInterface
{

    /**
     * Generates a new access token.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function generateAccessToken(): string;

    /**
     * Generates a new authorization code.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function generateAuthorizationCode(): string;

    /**
     * Generates a new refresh token.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function generateRefreshToken(): string;

    /**
     * Gets an access token instance.
     *
     * @param string $token
     *
     * @return AccessTokenInterface|null
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getAccessToken(string $token): ?AccessTokenInterface;

    /**
     * Gets an access token by its associated (refresh) token.
     *
     * @param ClientInterface $client
     * @param string $token
     *
     * @return AccessTokenInterface|null
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getAccessTokenByAssociatedToken(ClientInterface $client, string $token): ?AccessTokenInterface;

    /**
     * Gets an authorization code instance.
     *
     * @param string $code
     *
     * @return AuthorizationCodeInterface|null
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getAuthorizationCode(string $code): ?AuthorizationCodeInterface;

    /**
     * Gets a refresh token instance.
     *
     * @param string $token
     *
     * @return RefreshTokenInterface|null
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getRefreshToken(string $token): ?RefreshTokenInterface;

    /**
     * Revokes the given access token.
     *
     * @param ClientInterface $client
     * @param AccessTokenInterface $accessToken
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function revokeAccessToken(ClientInterface $client, AccessTokenInterface $accessToken): void;

    /**
     * Revokes the given authorization code.
     *
     * @param ClientInterface $client
     * @param AuthorizationCodeInterface $authorizationCode
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function revokeAuthorizationCode(ClientInterface $client, AuthorizationCodeInterface $authorizationCode): void;

    /**
     * Revokes the given refresh token.
     *
     * @param ClientInterface $client
     * @param RefreshTokenInterface $refreshToken
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function revokeRefreshToken(ClientInterface $client, RefreshTokenInterface $refreshToken): void;

    /**
     * Saves a new access token for the given client and owner with
     * access to the given scope.
     *
     * @param ClientInterface $client
     * @param mixed $owner
     * @param string $scope
     * @param string $accessToken
     * @param int $expiresIn
     * @param string|null $refreshToken
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function saveAccessToken(ClientInterface $client, mixed $owner, string $scope, string $accessToken, int $expiresIn, ?string $refreshToken): void;

    /**
     * Saves a new authorization code for the given client and owner
     * with access to the given scope and bound to the given redirect
     * uri and state.
     *
     * @param ClientInterface $client
     * @param mixed $owner
     * @param string $redirectUri
     * @param string $scope
     * @param string $authorizationCode
     * @param string|null $state
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function saveAuthorizationCode(ClientInterface $client, mixed $owner, string $redirectUri, string $scope, string $authorizationCode, ?string $state = null): void;

    /**
     * Saves a new refresh token for the given client and owner with
     * access to the given scope.
     *
     * @param ClientInterface $client
     * @param mixed $owner
     * @param string $scope
     * @param string $refreshToken
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function saveRefreshToken(ClientInterface $client, mixed $owner, string $scope, string $refreshToken): void;

}
