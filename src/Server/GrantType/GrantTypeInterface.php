<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Http\{HttpRequest, HttpResponse};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\OAuth2ServerException;

/**
 * Interface GrantTypeInterface
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 1.0.16
 */
interface GrantTypeInterface
{

    /**
     * Handles the token request.
     *
     * @param HttpRequest $request
     * @param ClientInterface $client
     *
     * @return HttpResponse
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(HttpRequest $request, ClientInterface $client): HttpResponse;

}
