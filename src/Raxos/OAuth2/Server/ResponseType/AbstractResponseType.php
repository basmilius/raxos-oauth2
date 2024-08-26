<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;
use Raxos\Router\Request\Request;
use Raxos\Router\Response\{NotFoundResponse, Response};

/**
 * Class AbstractResponseType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 1.0.16
 */
abstract class AbstractResponseType implements ResponseTypeInterface
{

    /**
     * AbstractResponseType constructor.
     *
     * @param TokenFactoryInterface $tokenFactory
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(protected readonly TokenFactoryInterface $tokenFactory) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function handle(Request $request, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): Response
    {
        return new NotFoundResponse();
    }

}
