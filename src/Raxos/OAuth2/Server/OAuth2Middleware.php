<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Raxos\Http\HttpRequest;
use Raxos\OAuth2\Server\Error\{InvalidClientException, InvalidRequestException, InvalidTokenException, OAuth2ServerException};
use Raxos\Router\Attribute\Injected;
use Raxos\Router\Effect\Effect;
use Raxos\Router\MiddlewareInterface;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function str_starts_with;
use function substr;

/**
 * Class OAuth2Middleware
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server
 * @since 1.0.16
 */
abstract readonly class OAuth2Middleware implements MiddlewareInterface
{

    #[Injected]
    public HttpRequest $request;

    #[Injected]
    public Router $router;

    /**
     * OAuth2Middleware constructor.
     *
     * @param OAuth2Server $oAuth2
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(
        protected OAuth2Server $oAuth2
    )
    {
    }

    /**
     * {@inheritdoc}
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function handle(): Effect|Response|bool|null
    {
        $authorization = $this->request->headers->get('authorization');

        if ($authorization === null || !str_starts_with($authorization, 'Bearer ')) {
            throw new InvalidRequestException('Missing required bearer token in "Authorization" header.');
        }

        $authorization = substr($authorization, 7);
        $clientFactory = $this->oAuth2->clientFactory;
        $tokenFactory = $this->oAuth2->tokenFactory;
        $token = $tokenFactory->getAccessToken($authorization);

        if ($token === null) {
            throw new InvalidTokenException();
        }

        $client = $clientFactory->getClient($token->getClientId());

        if ($token->isExpired()) {
            throw new InvalidTokenException('The access_token has expired.');
        }

        if ($client === null) {
            throw new InvalidClientException();
        }

        return true;
    }

}
