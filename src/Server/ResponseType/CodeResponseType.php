<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\Http\{HttpRequest, HttpResponse, HttpResponseCode};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\Router\Responds;
use function str_contains;
use function urlencode;

/**
 * Class CodeResponseType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 1.0.16
 */
final class CodeResponseType extends AbstractResponseType
{

    use Responds;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(HttpRequest $request, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): HttpResponse
    {
        $authorizationCode = $this->tokenFactory->generateAuthorizationCode();

        $this->tokenFactory->saveAuthorizationCode($client, $owner, $redirectUri, $scope, $authorizationCode, $state);

        $join = str_contains($redirectUri, '?') ? '&' : '?';
        $state = $state !== null ? '&state=' . urlencode($state) : '';

        return $this->redirect(
            destination: "{$redirectUri}{$join}code={$authorizationCode}{$state}",
            responseCode: HttpResponseCode::SEE_OTHER
        );
    }

}
