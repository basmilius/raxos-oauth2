<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Raxos\Foundation\Util\Singleton;
use Raxos\Http\HttpRequest;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\InvalidClientException;
use Raxos\OAuth2\Server\Error\InvalidRequestException;
use Raxos\OAuth2\Server\Error\InvalidTokenException;
use Raxos\OAuth2\Server\Error\OAuth2ServerException;
use Raxos\OAuth2\Server\Token\TokenInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Middleware\Middleware;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function str_starts_with;
use function substr;

/**
 * Class OAuth2Middleware
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server
 * @since 2.0.0
 */
abstract class OAuth2Middleware extends Middleware
{

    protected ?ClientInterface $client;
    protected ?TokenInterface $token;

    /**
     * OAuth2Middleware constructor.
     *
     * @param Router $router
     * @param OAuth2Server $oAuth2
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(
        Router $router,
        protected readonly OAuth2Server $oAuth2
    )
    {
        parent::__construct($router);
    }

    /**
     * {@inheritdoc}
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.0
     */
    public function handle(): Effect|Response|bool|null
    {
        $request = Singleton::get(HttpRequest::class);

        $authorization = $request->headers()->get('authorization');

        if ($authorization === null || !str_starts_with($authorization, 'Bearer ')) {
            throw new InvalidRequestException('Missing required bearer token in "Authorization" header.');
        }

        $authorization = substr($authorization, 7);
        $clientFactory = $this->oAuth2->clientFactory;
        $tokenFactory = $this->oAuth2->tokenFactory;
        $this->token = $tokenFactory->getAccessToken($authorization);

        if ($this->token === null) {
            throw new InvalidTokenException();
        }

        $this->client = $clientFactory->getClient($this->token->getClientId());

        if ($this->token->isExpired()) {
            throw new InvalidTokenException('The access_token has expired.');
        }

        if ($this->client === null) {
            throw new InvalidClientException();
        }

        return true;
    }

}
