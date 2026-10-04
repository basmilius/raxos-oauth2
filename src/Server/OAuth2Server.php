<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Raxos\Error\InvalidArgumentException;
use Raxos\OAuth2\Server\Client\ClientFactoryInterface;
use Raxos\OAuth2\Server\GrantType\AuthorizationCodeGrantType;
use Raxos\OAuth2\Server\GrantType\RefreshTokenGrantType;
use Raxos\OAuth2\Server\ResponseType\CodeResponseType;
use Raxos\OAuth2\Server\ResponseType\TokenResponseType;
use Raxos\OAuth2\Server\Scope\ScopeFactoryInterface;
use Raxos\OAuth2\Server\Token\RotatingTokenFactoryInterface;
use Raxos\OAuth2\Server\Token\TokenFactoryInterface;

/**
 * Class OAuth2Server
 *
 * Connects OAuth request handling to application-owned client, scope and token persistence.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server
 * @since 1.0.16
 */
abstract class OAuth2Server
{

    /**
     * Controls enabled grants, response types and token lifetimes without changing legacy defaults.
     *
     * @var SecurityProfile
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public private(set) SecurityProfile $profile;

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
     * @param SecurityProfile $profile
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(
        public readonly ClientFactoryInterface $clientFactory,
        public readonly ScopeFactoryInterface $scopeFactory,
        public readonly TokenFactoryInterface $tokenFactory,
        SecurityProfile $profile = new SecurityProfile()
    )
    {
        $this->securityProfile($profile);
    }

    /**
     * Validates the persistence capability before enabling refresh-token rotation.
     *
     * @param SecurityProfile $profile
     *
     * @return static
     * @throws InvalidArgumentException
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function securityProfile(SecurityProfile $profile): static
    {
        if ($profile->refreshRotation && !$this->tokenFactory instanceof RotatingTokenFactoryInterface) {
            throw new InvalidArgumentException('Refresh rotation requires a RotatingTokenFactoryInterface implementation.');
        }
        $this->profile = $profile;

        return $this;
    }

    /**
     * Gets the owner.
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public abstract function getOwner(): mixed;

    /**
     * Returns TRUE if there is an owner available.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public abstract function hasOwner(): bool;

}
