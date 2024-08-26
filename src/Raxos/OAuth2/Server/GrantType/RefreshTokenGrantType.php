<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\{InvalidGrantException, InvalidRequestException};
use Raxos\Router\Mixin\Responds;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;

/**
 * Class RefreshTokenGrantType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 1.0.16
 */
final class RefreshTokenGrantType extends AbstractGrantType
{

    use Responds;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function handle(Request $request, ClientInterface $client): Response
    {
        $refreshToken = $request->post->get('refresh_token') ?? throw new InvalidRequestException('Missing parameter: "refresh_token" is required.');
        $refreshToken = $this->tokenFactory->getRefreshToken($client, $refreshToken);

        if ($refreshToken === null || $refreshToken->isExpired()) {
            throw new InvalidGrantException('The refresh token has expired.');
        }

        $accessToken = $this->tokenFactory->generateAccessToken();
        $oldAccessToken = $this->tokenFactory->getAccessTokenByAssociatedToken($client, $refreshToken->getToken());

        $this->tokenFactory->saveAccessToken($client, $refreshToken->getOwner(), $refreshToken->getScope(), $accessToken, 3600, $refreshToken->getToken());

        if ($oldAccessToken !== null) {
            $this->tokenFactory->revokeAccessToken($client, $oldAccessToken);
        }

        return $this->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'scope' => $refreshToken->getScope(),
            'expires_in' => 3600
        ]);
    }

}
