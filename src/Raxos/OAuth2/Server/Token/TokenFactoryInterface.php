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
     * @return AccessTokenInterface
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getAccessToken(string $token): AccessTokenInterface;

    /**
     * Gets an authorization code instance.
     *
     * @param string $code
     *
     * @return AuthorizationCodeInterface
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getAuthorizationCode(string $code): AuthorizationCodeInterface;

    /**
     * Gets a refresh token instance.
     *
     * @param string $token
     *
     * @return RefreshTokenInterface
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function getRefreshToken(string $token): RefreshTokenInterface;

    /**
     * Saves a new access token for the given client and owner with
     * access to the given scope.
     *
     * @param string $clientId
     * @param mixed $owner
     * @param string $scope
     * @param string $accessToken
     * @param int $expiresIn
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function saveAccessToken(string $clientId, mixed $owner, string $scope, string $accessToken, int $expiresIn): void;

    /**
     * Saves a new authorization code for the given client and owner
     * with access to the given scope and bound to the given redirect
     * uri and state.
     *
     * @param string $clientId
     * @param mixed $owner
     * @param string $redirectUri
     * @param string $scope
     * @param string $authorizationCode
     * @param string|null $state
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function saveAuthorizationCode(string $clientId, mixed $owner, string $redirectUri, string $scope, string $authorizationCode, ?string $state = null): void;

}
