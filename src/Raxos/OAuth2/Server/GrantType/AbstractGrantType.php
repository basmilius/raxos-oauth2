<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Http\HttpRequest;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;
use Raxos\Router\Effect\{Effect, NotFoundEffect};
use Raxos\Router\Response\Response;
use Raxos\Router\Router;

/**
 * Class AbstractGrantType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\GrantType
 * @since 1.0.16
 */
class AbstractGrantType implements GrantTypeInterface
{

    /**
     * AbstractGrantType constructor.
     *
     * @param TokenFactoryInterface $tokenFactory
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(
        protected readonly TokenFactoryInterface $tokenFactory
    )
    {
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function handle(Router $router, HttpRequest $request, ClientInterface $client): Effect|Response
    {
        return new NotFoundEffect($router);
    }

}
