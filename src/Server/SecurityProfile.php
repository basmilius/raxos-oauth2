<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server;

use Raxos\Error\InvalidArgumentException;
use function in_array;

/**
 * Class SecurityProfile
 *
 * Explicit protocol selection and access-token lifetime.
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server
 * @since 3.3.0
 */
final readonly class SecurityProfile
{
    /**
     * Selects allowed grants and response types; refresh rotation requires an atomic token factory.
     *
     * @param list<string> $grantTypes
     * @param list<string> $responseTypes
     * @param int $accessTokenLifetime
     * @param bool $refreshRotation
     * @throws InvalidArgumentException
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public function __construct(
        public array $grantTypes = ['authorization_code', 'refresh_token'],
        public array $responseTypes = ['code', 'token'],
        public int $accessTokenLifetime = 3600,
        public bool $refreshRotation = false
    )
    {
        if ($accessTokenLifetime < 1) {
            throw new InvalidArgumentException('The access-token lifetime must be positive.');
        }

        foreach ($grantTypes as $grant) {
            if (!in_array($grant, ['authorization_code', 'refresh_token'], true)) {
                throw new InvalidArgumentException('Unsupported grant type.');
            }
        }

        foreach ($responseTypes as $response) {
            if (!in_array($response, ['code', 'token'], true)) {
                throw new InvalidArgumentException('Unsupported response type.');
            }
        }
    }

    /**
     * Disables implicit authorization and requires atomic refresh rotation.
     *
     * @param int $accessTokenLifetime
     * @return self
     * @author Bas Milius <bas@mili.us>
     * @since 3.3.0
     */
    public static function modern(int $accessTokenLifetime = 900): self
    {
        return new self(responseTypes: ['code'], accessTokenLifetime: $accessTokenLifetime, refreshRotation: true);
    }
}
