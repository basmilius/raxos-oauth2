<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\{InvalidGrantException, InvalidRequestException, RedirectUriMismatchException};
use Raxos\Router\Mixin\Responds;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use function urldecode;

/**
 * Class AuthorizationCodeGrantType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 1.0.16
 */
final class AuthorizationCodeGrantType extends AbstractGrantType
{

    use Responds;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(Request $request, ClientInterface $client): Response
    {
        $code = $request->post->get('code') ?? throw new InvalidRequestException('Missing parameter: "code" is required.');
        $redirectUri = $request->post->get('redirect_uri') ?? throw new InvalidRequestException('Missing parameter: "redirect_uri" is required.');

        $authorizationCode = $this->tokenFactory->getAuthorizationCode($client, $code) ?? throw new InvalidGrantException("Authorization code doesn't exist or is invalid for the client.");
        $redirectUri = urldecode($redirectUri);

        if ($authorizationCode->isExpired()) {
            throw new InvalidGrantException('The authorization code has expired.');
        }

        if ($authorizationCode->getRedirectUri() !== $redirectUri) {
            throw new RedirectUriMismatchException();
        }

        $accessToken = $this->tokenFactory->generateAccessToken();
        $refreshToken = $this->tokenFactory->generateRefreshToken();

        $this->tokenFactory->saveRefreshToken($client, $authorizationCode->getOwner(), $authorizationCode->getScope(), $refreshToken);
        $this->tokenFactory->saveAccessToken($client, $authorizationCode->getOwner(), $authorizationCode->getScope(), $accessToken, 3600, $refreshToken);
        $this->tokenFactory->revokeAuthorizationCode($client, $authorizationCode);

        return $this->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'scope' => $authorizationCode->getScope(),
            'expires_in' => 3600,
            'refresh_token' => $refreshToken
        ]);
    }

}
