<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\Http\HttpResponseCode;
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\Router\Effect\{Effect, RedirectEffect};
use Raxos\Router\Response\Response;
use Raxos\Router\Router;
use function str_contains;
use function urlencode;

/**
 * Class CodeResponseType
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 1.0.16
 */
final class CodeResponseType extends AbstractResponseType
{

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function handle(Router $router, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): Effect|Response
    {
        $authorizationCode = $this->tokenFactory->generateAuthorizationCode();

        $this->tokenFactory->saveAuthorizationCode($client, $owner, $redirectUri, $scope, $authorizationCode, $state);

        $join = str_contains($redirectUri, '?') ? '&' : '?';
        $state = $state !== null ? '&state=' . urlencode($state) : '';

        return new RedirectEffect($router, "{$redirectUri}{$join}code={$authorizationCode}{$state}", HttpResponseCode::SEE_OTHER);
    }

}
