<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Closure;
use Raxos\Contract\Router\MiddlewareInterface;
use Raxos\Http\{HttpRequest, HttpResponse};
use Raxos\OAuth2\Server\Error\{InvalidClientException, InvalidRequestException, InvalidTokenException};
use Raxos\Router\Responds;

/**
 * Class OAuth2Middleware
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server
 * @since 1.0.16
 */
abstract readonly class OAuth2Middleware implements MiddlewareInterface
{

    use Responds;

    /**
     * OAuth2Middleware constructor.
     *
     * @param OAuth2Server $oAuth2
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(
        protected OAuth2Server $oAuth2
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public function handle(HttpRequest $request, Closure $next): HttpResponse
    {
        $authorization = $request->bearerToken();

        if ($authorization === null) {
            return $this->error(new InvalidRequestException('Missing required bearer token in "Authorization" header.'));
        }

        $clientFactory = $this->oAuth2->clientFactory;
        $tokenFactory = $this->oAuth2->tokenFactory;
        $token = $tokenFactory->getAccessToken($authorization);

        if ($token === null) {
            return $this->error(new InvalidTokenException());
        }

        $client = $clientFactory->getClient($token->getClientId());

        if ($token->isExpired()) {
            return $this->error(new InvalidTokenException('The access_token has expired.'));
        }

        if ($client === null) {
            return $this->error(new InvalidClientException());
        }

        return $next($request);
    }

}
