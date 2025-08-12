<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;

/**
 * Interface ResponseTypeInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 1.0.16
 */
interface ResponseTypeInterface
{

    /**
     * Handles the authorization request.
     *
     * @param Request $request
     * @param ClientInterface $client
     * @param mixed $owner
     * @param string $redirectUri
     * @param string $scope
     * @param string|null $state
     *
     * @return Response
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(Request $request, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): Response;

}
