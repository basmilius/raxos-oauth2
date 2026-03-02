<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\ResponseType;

use Raxos\Http\{HttpRequest, HttpResponse, HttpResponseCode};
use Raxos\OAuth2\Server\Client\ClientInterface;
use Raxos\Router\Responds;
use function urlencode;

/**
 * Class TokenResponseType
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\ResponseType
 * @since 1.0.16
 */
final class TokenResponseType extends AbstractResponseType
{

    use Responds;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function handle(HttpRequest $request, ClientInterface $client, mixed $owner, string $redirectUri, string $scope, ?string $state = null): HttpResponse
    {
        $accessToken = $this->tokenFactory->generateAccessToken();

        $this->tokenFactory->saveAccessToken($client, $owner, $scope, $accessToken, 3600, null);

        $state = $state !== null ? '&state=' . urlencode($state) : '';

        return $this->redirect(
            destination: "{$redirectUri}#code={$accessToken}&token_type=Bearer&expires_in=3600{$state}",
            responseCode: HttpResponseCode::SEE_OTHER
        );
    }

}
