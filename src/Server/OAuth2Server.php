<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Raxos\OAuth2\Server\Client\ClientFactoryInterface;
use Raxos\OAuth2\Server\GrantType\{AuthorizationCodeGrantType, RefreshTokenGrantType};
use Raxos\OAuth2\Server\ResponseType\{CodeResponseType, TokenResponseType};
use Raxos\OAuth2\Server\Scope\ScopeFactoryInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

/**
 * Class OAuth2Server
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server
 * @since 1.0.16
 */
abstract class OAuth2Server
{

    public const array GRANT_TYPES = [
        'authorization_code' => AuthorizationCodeGrantType::class,
        'refresh_token' => RefreshTokenGrantType::class
    ];

    public const array RESPONSE_TYPES = [
        'code' => CodeResponseType::class,
        'token' => TokenResponseType::class
    ];

    /**
     * OAuth2Server constructor.
     *
     * @param ClientFactoryInterface $clientFactory
     * @param ScopeFactoryInterface $scopeFactory
     * @param TokenFactoryInterface $tokenFactory
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(
        public readonly ClientFactoryInterface $clientFactory,
        public readonly ScopeFactoryInterface $scopeFactory,
        public readonly TokenFactoryInterface $tokenFactory
    ) {}

    /**
     * Gets the owner.
     *
     * @return mixed
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public abstract function getOwner(): mixed;

    /**
     * Returns TRUE if there is an owner available.
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public abstract function hasOwner(): bool;

}
