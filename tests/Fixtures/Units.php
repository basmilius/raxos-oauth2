<?php
declare(strict_types=1);

namespace RaxosTests\OAuth2;

use Raxos\Http\HttpResponse;
use Raxos\Http\Response\{NoContentHttpResponse, ResultHttpResponse};
use Raxos\OAuth2\Server\Client\ClientFactoryInterface;
use Raxos\OAuth2\Server\{OAuth2Controller, OAuth2Middleware, OAuth2Server};
use Raxos\OAuth2\Server\Scope\ScopeFactoryInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

final class UnitServer extends OAuth2Server
{
    public function __construct(ClientFactoryInterface $clients, ScopeFactoryInterface $scopes, TokenFactoryInterface $tokens, public mixed $owner = 'owner')
    {
        parent::__construct($clients, $scopes, $tokens);
    }

    public function getOwner(): mixed
    {
        return $this->owner;
    }

    public function hasOwner(): bool
    {
        return $this->owner !== null;
    }
}

final readonly class ContextController extends OAuth2Controller
{
    protected function onAuthorizeMissingOwner(): HttpResponse
    {
        return new NoContentHttpResponse();
    }

    protected function renderAuthorize(array $context): HttpResponse
    {
        return new ResultHttpResponse($context);
    }
}

final readonly class UnitMiddleware extends OAuth2Middleware {}
