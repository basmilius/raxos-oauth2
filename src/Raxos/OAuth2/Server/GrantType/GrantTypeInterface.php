<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Http\HttpRequest;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\OAuth2ServerException;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;

/**
 * Interface GrantTypeInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 2.0.0
 */
interface GrantTypeInterface
{

    /**
     * Handles the token request.
     *
     * @param Router $router
     * @param HttpRequest $request
     * @param ClientInterface $client
     *
     * @return Effect|Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function handle(Router $router, HttpRequest $request, ClientInterface $client): Effect|Response;

}
