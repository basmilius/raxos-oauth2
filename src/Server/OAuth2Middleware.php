<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Closure;
use Raxos\OAuth2\Server\Error\{InvalidClientException, InvalidRequestException, InvalidTokenException};
use Raxos\Router\Contract\MiddlewareInterface;
use Raxos\Router\Mixin\Responds;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;
use function str_starts_with;
use function substr;

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
     * @since 1.0.16
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->headers->get('authorization');

        if ($authorization === null || !str_starts_with($authorization, 'Bearer ')) {
            return $this->error(new InvalidRequestException('Missing required bearer token in "Authorization" header.'));
        }

        $authorization = substr($authorization, 7);
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
