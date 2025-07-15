<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Error\OAuth2ServerException;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\Response;

/**
 * Interface GrantTypeInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 1.0.16
 */
interface GrantTypeInterface
{

    /**
     * Handles the token request.
     *
     * @param Request $request
     * @param ClientInterface $client
     *
     * @return Response
     * @throws OAuth2ServerException
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function handle(Request $request, ClientInterface $client): Response;

}
