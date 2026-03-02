<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\GrantType;

use Raxos\Http\{HttpRequest, HttpResponse};
use Raxos\Http\Response\NotFoundHttpResponse;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

/**
 * Class AbstractGrantType
 *
 * @author Bas Milius <bas@mili.us>
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
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(
        protected readonly TokenFactoryInterface $tokenFactory
    ) {}

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(HttpRequest $request, ClientInterface $client): HttpResponse
    {
        return new NotFoundHttpResponse();
    }

}
