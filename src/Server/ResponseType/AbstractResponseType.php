<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\Http\HttpRequest;
use Raxos\Http\HttpResponse;
use Raxos\Http\Response\NotFoundHttpResponse;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\OAuth2\Server\SecurityProfile;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

/**
 * Class AbstractResponseType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 1.0.16
 */
abstract class AbstractResponseType implements ResponseTypeInterface
{
    /**
     * AbstractResponseType constructor.
     *
     * @param TokenFactoryInterface $tokenFactory
     * @param SecurityProfile $profile
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(
        protected readonly TokenFactoryInterface $tokenFactory,
        protected readonly SecurityProfile $profile = new SecurityProfile()
    )
    {
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(
        HttpRequest $request,
        ClientInterface $client,
        mixed $owner,
        string $redirectUri,
        string $scope,
        ?string $state = null
    ): HttpResponse
    {
        return new NotFoundHttpResponse();
    }
}
