<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Error\InvalidArgumentException;
use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\Error\InvalidRequestException;
use Raxos\OAuth2\Server\Error\InvalidScopeException;
use Raxos\OAuth2\Server\Token\RotatingTokenFactoryInterface;
use Raxos\Router\Responds;
use function is_string;
use function preg_split;


/**
 * Class RefreshTokenGrantType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 1.0.16
 */
final class RefreshTokenGrantType extends AbstractGrantType
{

    use Responds;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(
        HttpRequest $request,
        ClientInterface $client
    ): HttpResponse
    {
        $refreshToken = $request->post->get('refresh_token') ?? throw new InvalidRequestException('Missing parameter: "refresh_token" is required.');
        $refreshToken = $this->tokenFactory->getRefreshToken($client, $refreshToken);

        if ($refreshToken === null || $refreshToken->isExpired() || $refreshToken->getClientId() !== $client->getClientId()) {
            throw new InvalidGrantException('The refresh token has expired.');
        }

        $scope = $refreshToken->getScope();
        $requestedScope = $request->post->get('scope');

        if ($requestedScope !== null) {
            if (!is_string($requestedScope) || $requestedScope === '') {
                throw new InvalidScopeException('Refresh scope must be a non-empty subset of the original grant.');
            }

            foreach (preg_split('/ +/', $requestedScope) as $permission) {
                if (!$refreshToken->isScopeAllowed($permission)) {
                    throw new InvalidScopeException('Refresh scope must be a non-empty subset of the original grant.');
                }
            }
            $scope = $requestedScope;
        }

        if ($this->profile->refreshRotation) {
            if (!$this->tokenFactory instanceof RotatingTokenFactoryInterface) {
                throw new InvalidArgumentException('Refresh rotation requires a RotatingTokenFactoryInterface implementation.');
            }
            $accessToken = $this->tokenFactory->generateAccessToken();
            $replacement = $this->tokenFactory->generateRefreshToken();

            if (!$this->tokenFactory->rotateRefreshToken($client, $refreshToken, $accessToken, $replacement, $scope, $this->profile->accessTokenLifetime)) {
                throw new InvalidGrantException('The refresh token has already been consumed, revoked or expired.');
            }

            return $this->json([
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'scope' => $scope,
                'expires_in' => $this->profile->accessTokenLifetime,
                'refresh_token' => $replacement
            ]);
        }

        $accessToken = $this->tokenFactory->generateAccessToken();
        $oldAccessToken = $this->tokenFactory->getAccessTokenByAssociatedToken($client, $refreshToken->getToken());

        $this->tokenFactory->saveAccessToken($client, $refreshToken->getOwner(), $scope, $accessToken, $this->profile->accessTokenLifetime, $refreshToken->getToken());

        if ($oldAccessToken !== null) {
            $this->tokenFactory->revokeAccessToken($client, $oldAccessToken);
        }

        return $this->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'scope' => $scope,
            'expires_in' => $this->profile->accessTokenLifetime
        ]);
    }
}
