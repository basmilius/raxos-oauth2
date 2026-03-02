<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\Http\{HttpRequest, HttpResponse};
use Raxos\OAuth2\Server\Client\ClientInterface;

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
     * @param HttpRequest $request
     * @param ClientInterface $client
     * @param mixed $owner
     * @param string $redirectUri
     * @param string $scope
     * @param string|null $state
     *
     * @return HttpResponse
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(HttpRequest $request, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): HttpResponse;

}
