<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;
use Raxos\Router\Effect\Effect;
use Raxos\Router\Effect\NotFoundEffect;
use Raxos\Router\Response\Response;
use Raxos\Router\Router;

/**
 * Class AbstractResponseType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 2.0.0
 */
abstract class AbstractResponseType implements ResponseTypeInterface
{

    /**
     * AbstractResponseType constructor.
     *
     * @param TokenFactoryInterface $tokenFactory
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(protected readonly TokenFactoryInterface $tokenFactory)
    {
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     *
     * @noinspection PhpPureAttributeCanBeAddedInspection
     */
    public function handle(Router $router, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): Effect|Response
    {
        return new NotFoundEffect($router);
    }

}
