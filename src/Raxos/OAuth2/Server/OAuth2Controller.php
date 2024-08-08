<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use JetBrains\PhpStorm\{ArrayShape, Pure};
use Raxos\Foundation\Util\Base64;
use Raxos\Http\{HttpRequest, HttpResponseCode};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\{InvalidClientException, InvalidRequestException, OAuth2ServerException, RedirectUriMismatchException, UnsupportedGrantTypeException};
use Raxos\OAuth2\Server\GrantType\AbstractGrantType;
use Raxos\OAuth2\Server\ResponseType\AbstractResponseType;
use Raxos\Router\Attribute\{Get, Post};
use Raxos\Router\Controller\Controller;
use Raxos\Router\Effect\{Effect, VoidEffect};
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
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
abstract class OAuth2Controller extends Controller
{

    /**
     * OAuth2Controller constructor.
     *
     * @param Router $router
     * @param OAuth2Server $oAuth2
     * @param HttpRequest $request
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Pure]
    public function __construct(
        Router $router,
        protected readonly OAuth2Server $oAuth2,
        protected readonly HttpRequest $request
    )
    {
        parent::__construct($router);
    }

    /**
     * Invoked when GET /authorize is requested.
     *
     * @return Effect|Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Get('/authorize')]
    protected final function getAuthorize(): Effect|Response
    {
        if (!$this->oAuth2->hasOwner()) {
            return $this->onAuthorizeMissingOwner();
        }

        [$client, $clientId, $redirectUri, $responseType, $scope, $state] = $this->ensureClientForAuthorize();

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
     * @return Effect|Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Post('/authorize')]
    protected final function postAuthorize(): Effect|Response
    {
        if (!$this->oAuth2->hasOwner()) {
            return $this->onAuthorizeMissingOwner();
        }

        [$client, , $redirectUri, $responseType, $scope, $state] = $this->ensureClientForAuthorize();

        if (!$this->request->post->has('authorize')) {
            $join = str_contains($redirectUri, '?') ? '&' : '?';
            $state = $state !== null ? '&state=' . urlencode($state) : '';

            return $this->redirect("{$redirectUri}{$join}error=access_denied{$state}", HttpResponseCode::SEE_OTHER);
        }

        $responseType = OAuth2Server::RESPONSE_TYPES[$responseType] ?? throw new UnsupportedGrantTypeException();
        /** @var AbstractResponseType $responseType */
        $responseType = new $responseType($this->oAuth2->tokenFactory);

        return $responseType->handle($this->router, $client, $this->oAuth2->getOwner(), $redirectUri, $scope, $state);
    }

    /**
     * Invoked when POST /revoke is requested.
     *
     * @return Effect
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Post('/revoke')]
    protected final function postRevoke(): Effect
    {
        $client = $this->ensureClientFromHeader();
        $token = $this->request->post->get('token');
        $tokenTypeHint = $this->request->post->get('token_type_hint');
        $tokenFactory = $this->oAuth2->tokenFactory;

        if ($token !== null) {
            if ($tokenTypeHint === 'access_token' && ($accessToken = $tokenFactory->getAccessToken($token)) !== null) {
                $tokenFactory->revokeAccessToken($client, $accessToken);
            } else if ($tokenTypeHint === 'authorization_code' && ($authorizationCode = $tokenFactory->getAuthorizationCode($client, $token)) !== null) {
                $tokenFactory->revokeAuthorizationCode($client, $authorizationCode);
            } else if ($tokenTypeHint === 'refresh_token' && ($refreshToken = $tokenFactory->getRefreshToken($client, $token)) !== null) {
                $tokenFactory->revokeRefreshToken($client, $refreshToken);
            } else if (($accessToken = $tokenFactory->getAccessToken($token)) !== null) {
                $tokenFactory->revokeAccessToken($client, $accessToken);
            } else if (($authorizationCode = $tokenFactory->getAuthorizationCode($client, $token)) !== null) {
                $tokenFactory->revokeAuthorizationCode($client, $authorizationCode);
            } else if (($refreshToken = $tokenFactory->getRefreshToken($client, $token)) !== null) {
                $tokenFactory->revokeRefreshToken($client, $refreshToken);
            }
        }

        $this->router
            ->responseRegistry
            ->responseCode(HttpResponseCode::OK);

        return new VoidEffect($this->router);
    }

    /**
     * Invoked when POST /token is requested.
     *
     * @return Effect|Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Post('/token')]
    protected final function postToken(): Effect|Response
    {
        [$client, $grantType] = $this->ensureClientForToken();

        $grantType = OAuth2Server::GRANT_TYPES[$grantType] ?? throw new UnsupportedGrantTypeException();
        /** @var AbstractGrantType $grantType */
        $grantType = new $grantType($this->oAuth2->tokenFactory);

        return $grantType->handle($this->router, $this->request, $client);
    }

    /**
     * Invoked when an owner is missing when it's required.
     *
     * @return Effect|Response
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    protected abstract function onAuthorizeMissingOwner(): Effect|Response;

    /**
     * Renders the authorize screen.
     *
     * @param array $context
     *
     * @return Response
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    protected abstract function renderAuthorize(array $context): Response;

    /**
     * Ensures a client for the authorize request.
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
    private function ensureClientForAuthorize(): array
    {
        $clientId = $this->request->queryString->get('client_id') ?? throw new InvalidRequestException('Missing parameter: "client_id" is required.');
        $redirectUri = $this->request->queryString->get('redirect_uri') ?? throw new InvalidRequestException('Missing parameter: "redirect_uri" is required.');
        $responseType = $this->request->queryString->get('response_type') ?? throw new InvalidRequestException('Missing parameter: "response_type" is required.');
        $scope = $this->request->queryString->get('scope') ?? throw new InvalidRequestException('Missing parameter: "scope" is required.');

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

        return [$client, $clientId, $redirectUri, $responseType, $scope, $this->request->queryString->get('state')];
    }

    /**
     * Ensures a client from the authorization header.
     *
     * @return ClientInterface
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    private function ensureClientFromHeader(): ClientInterface
    {
        $authorization = $this->request->headers->get('authorization') ?? throw new InvalidRequestException('Missing header: "Authorization" is required.');

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
     * @return array
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[ArrayShape([
        ClientInterface::class,
        'string'
    ])]
    private function ensureClientForToken(): array
    {
        $client = $this->ensureClientFromHeader();
        $grantType = $this->request->post->get('grant_type') ?? throw new InvalidRequestException('Missing parameter: "grant_type" is required.');

        if (!array_key_exists($grantType, OAuth2Server::GRANT_TYPES)) {
            throw new UnsupportedGrantTypeException();
        }

        return [$client, $grantType];
    }

}
