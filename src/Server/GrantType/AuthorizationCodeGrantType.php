<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\InvalidGrantException;
use Raxos\OAuth2\Server\Error\InvalidRequestException;
use Raxos\OAuth2\Server\Error\RedirectUriMismatchException;
use Raxos\OAuth2\Server\Pkce;
use Raxos\Router\Responds;

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
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(
        HttpRequest $request,
        ClientInterface $client
    ): HttpResponse
    {
        $code = $request->post->get('code') ?? throw new InvalidRequestException('Missing parameter: "code" is required.');
        $redirectUri = $request->post->get('redirect_uri') ?? throw new InvalidRequestException('Missing parameter: "redirect_uri" is required.');

        $authorizationCode = $this->tokenFactory->getAuthorizationCode($client, $code) ?? throw new InvalidGrantException("Authorization code doesn't exist or is invalid for the client.");

        if ($authorizationCode->isExpired() || $authorizationCode->getClientId() !== $client->getClientId()) {
            throw new InvalidGrantException('The authorization code has expired.');
        }

        if ($authorizationCode->getRedirectUri() !== $redirectUri) {
            throw new RedirectUriMismatchException();
        }

        if (!Pkce::verify($request->post->get('code_verifier'), $authorizationCode->getCodeChallenge())) {
            throw new InvalidGrantException('PKCE verification failed.');
        }

        if (!$this->tokenFactory->consumeAuthorizationCode($client, $authorizationCode)) {
            throw new InvalidGrantException('The authorization code has already been consumed or expired.');
        }

        $accessToken = $this->tokenFactory->generateAccessToken();
        $refreshToken = $this->tokenFactory->generateRefreshToken();

        $this->tokenFactory->saveRefreshToken($client, $authorizationCode->getOwner(), $authorizationCode->getScope(), $refreshToken);
        $this->tokenFactory->saveAccessToken($client, $authorizationCode->getOwner(), $authorizationCode->getScope(), $accessToken, $this->profile->accessTokenLifetime, $refreshToken);

        return $this->json([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'scope' => $authorizationCode->getScope(),
            'expires_in' => $this->profile->accessTokenLifetime,
            'refresh_token' => $refreshToken
        ]);
    }

}
