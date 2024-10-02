<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use JetBrains\PhpStorm\{ArrayShape};
use Raxos\Foundation\Util\Base64;
use Raxos\Http\{HttpResponseCode};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\{InvalidClientException, InvalidRequestException, OAuth2ServerException, RedirectUriMismatchException, UnsupportedGrantTypeException};
use Raxos\OAuth2\Server\GrantType\AbstractGrantType;
use Raxos\OAuth2\Server\ResponseType\AbstractResponseType;
use Raxos\Router\Attribute\{Get, Post};
use Raxos\Router\Mixin\Responds;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use function array_key_exists;
use function count;
use function explode;
use function str_contains;
use function urlencode;

/**
 * Class OAuth2Controller
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server
 * @since 1.0.16
 */
abstract readonly class OAuth2Controller
{

    use Responds;

    /**
     * OAuth2Controller constructor.
     *
     * @param OAuth2Server $oAuth2
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(
        public OAuth2Server $oAuth2
    ) {}

    /**
     * Invoked when GET /authorize is requested.
     *
     * @param Request $request
     *
     * @return Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Get('/authorize')]
    protected final function getAuthorize(Request $request): Response
    {
        if (!$this->oAuth2->hasOwner()) {
            return $this->onAuthorizeMissingOwner();
        }

        [$client, $clientId, $redirectUri, $responseType, $scope, $state] = $this->ensureClientForAuthorize($request);

        return $this->renderAuthorize([
            'client' => $client,
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => $responseType,
            'scope' => $scope,
            'scopes' => $this->oAuth2
                ->scopeFactory
                ->convertScopes($this->oAuth2
                    ->scopeFactory
                    ->convertScopeString($scope)),
            'state' => $state
        ]);
    }

    /**
     * Invoked when POST /authorize is requested.
     *
     * @param Request $request
     *
     * @return Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Post('/authorize')]
    protected final function postAuthorize(Request $request): Response
    {
        if (!$this->oAuth2->hasOwner()) {
            return $this->onAuthorizeMissingOwner();
        }

        [$client, , $redirectUri, $responseType, $scope, $state] = $this->ensureClientForAuthorize($request);

        if (!$request->post->has('authorize')) {
            $join = str_contains($redirectUri, '?') ? '&' : '?';
            $state = $state !== null ? '&state=' . urlencode($state) : '';

            return $this->redirect(
                destination: "{$redirectUri}{$join}error=access_denied{$state}",
                responseCode: HttpResponseCode::SEE_OTHER
            );
        }

        $responseType = OAuth2Server::RESPONSE_TYPES[$responseType] ?? throw new UnsupportedGrantTypeException();
        /** @var AbstractResponseType $responseType */
        $responseType = new $responseType($this->oAuth2->tokenFactory);

        return $responseType->handle($request, $client, $this->oAuth2->getOwner(), $redirectUri, $scope, $state);
    }

    /**
     * Invoked when POST /revoke is requested.
     *
     * @param Request $request
     *
     * @return Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Post('/revoke')]
    protected final function postRevoke(Request $request): Response
    {
        $client = $this->ensureClientFromHeader($request);
        $token = $request->post->get('token');
        $tokenTypeHint = $request->post->get('token_type_hint');
        $tokenFactory = $this->oAuth2->tokenFactory;

        if ($token !== null) {
            if ($tokenTypeHint === 'access_token' && ($accessToken = $tokenFactory->getAccessToken($token)) !== null) {
                $tokenFactory->revokeAccessToken($client, $accessToken);
            } elseif ($tokenTypeHint === 'authorization_code' && ($authorizationCode = $tokenFactory->getAuthorizationCode($client, $token)) !== null) {
                $tokenFactory->revokeAuthorizationCode($client, $authorizationCode);
            } elseif ($tokenTypeHint === 'refresh_token' && ($refreshToken = $tokenFactory->getRefreshToken($client, $token)) !== null) {
                $tokenFactory->revokeRefreshToken($client, $refreshToken);
            } elseif (($accessToken = $tokenFactory->getAccessToken($token)) !== null) {
                $tokenFactory->revokeAccessToken($client, $accessToken);
            } elseif (($authorizationCode = $tokenFactory->getAuthorizationCode($client, $token)) !== null) {
                $tokenFactory->revokeAuthorizationCode($client, $authorizationCode);
            } elseif (($refreshToken = $tokenFactory->getRefreshToken($client, $token)) !== null) {
                $tokenFactory->revokeRefreshToken($client, $refreshToken);
            }
        }

        return $this->json(true, responseCode: HttpResponseCode::ACCEPTED);
    }

    /**
     * Invoked when POST /token is requested.
     *
     * @param Request $request
     *
     * @return Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Post('/token')]
    protected final function postToken(Request $request): Response
    {
        [$client, $grantType] = $this->ensureClientForToken($request);

        /** @var class-string<AbstractGrantType> $grantType */
        $grantType = OAuth2Server::GRANT_TYPES[$grantType] ?? throw new UnsupportedGrantTypeException();
        $grantType = new $grantType($this->oAuth2->tokenFactory);

        return $grantType->handle($request, $client);
    }

    /**
     * Invoked when an owner is missing when it's required.
     *
     * @return Response
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    protected abstract function onAuthorizeMissingOwner(): Response;

    /**
     * Renders the "authorize" screen.
     *
     * @param array $context
     *
     * @return Response
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    protected abstract function renderAuthorize(array $context): Response;

    /**
     * Ensures a client for the "authorize" request.
     *
     * @param Request $request
     *
     * @return array
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[ArrayShape([
        ClientInterface::class,
        'string',
        'string',
        'string',
        'string',
        'string|null'
    ])]
    private function ensureClientForAuthorize(Request $request): array
    {
        $clientId = $request->query->get('client_id') ?? throw new InvalidRequestException('Missing parameter: "client_id" is required.');
        $redirectUri = $request->query->get('redirect_uri') ?? throw new InvalidRequestException('Missing parameter: "redirect_uri" is required.');
        $responseType = $request->query->get('response_type') ?? throw new InvalidRequestException('Missing parameter: "response_type" is required.');
        $scope = $request->query->get('scope') ?? throw new InvalidRequestException('Missing parameter: "scope" is required.');

        $client = $this->oAuth2->clientFactory->getClient($clientId) ?? throw new InvalidClientException();

        if (!$client->isRedirectUriAllowed($redirectUri)) {
            throw new RedirectUriMismatchException();
        }

        if (!array_key_exists($responseType, OAuth2Server::RESPONSE_TYPES)) {
            throw new UnsupportedGrantTypeException();
        }

        $this->oAuth2->scopeFactory->ensureValidScopes(
            $this->oAuth2->scopeFactory->convertScopeString($scope)
        );

        return [$client, $clientId, $redirectUri, $responseType, $scope, $request->query->get('state')];
    }

    /**
     * Ensures a client from the authorization header.
     *
     * @param Request $request
     *
     * @return ClientInterface
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    private function ensureClientFromHeader(Request $request): ClientInterface
    {
        $authorization = $request->headers->get('authorization') ?? throw new InvalidRequestException('Missing header: "Authorization" is required.');

        [$authorizationType, $authorizationValue] = explode(' ', $authorization, 2);

        if ($authorizationType !== 'Basic') {
            throw new InvalidClientException();
        }

        $authorizationValue = Base64::decode($authorizationValue);
        $authorizationValue = explode(':', $authorizationValue, 2);

        if (count($authorizationValue) !== 2) {
            throw new InvalidClientException();
        }

        [
            $clientId,
            $clientSecret
        ] = $authorizationValue;

        $client = $this->oAuth2->clientFactory->getClient($clientId);

        if ($client === null || !$client->isSecretValid($clientSecret)) {
            throw new InvalidClientException();
        }

        return $client;
    }

    /**
     * Ensures a client for the token request.
     *
     * @param Request $request
     *
     * @return array
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[ArrayShape([
        ClientInterface::class,
        'string'
    ])]
    private function ensureClientForToken(Request $request): array
    {
        $client = $this->ensureClientFromHeader($request);
        $grantType = $request->post->get('grant_type') ?? throw new InvalidRequestException('Missing parameter: "grant_type" is required.');

        if (!array_key_exists($grantType, OAuth2Server::GRANT_TYPES)) {
            throw new UnsupportedGrantTypeException();
        }

        return [$client, $grantType];
    }

}
