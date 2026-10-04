<?php
declare(strict_types=1);

namespace RaxosTests\OAuth2;

use Raxos\Http\HttpResponse;
use Raxos\Http\Response\NoContentHttpResponse;
use Raxos\OAuth2\Server\OAuth2Controller;

final readonly class Controller extends OAuth2Controller
{

    protected function onAuthorizeMissingOwner(): HttpResponse
    {
        return new NoContentHttpResponse();
    }

    protected function renderAuthorize(array $context): HttpResponse
    {
        return new NoContentHttpResponse();
    }

}
