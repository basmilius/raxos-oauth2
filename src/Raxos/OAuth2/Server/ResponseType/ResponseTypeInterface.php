<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;

/**
 * Interface ResponseTypeInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 2.0.0
 */
interface ResponseTypeInterface
{

    /**
     * Handles the authorize request.
     *
     * @param Router $router
     * @param ClientInterface $client
     * @param mixed $owner
     * @param string $redirectUri
     * @param string $scope
     * @param string|null $state
     *
     * @return Effect|Response
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function handle(Router $router, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): Effect|Response;

}
