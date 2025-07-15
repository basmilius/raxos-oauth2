<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Client;

/**
 * Interface ClientFactoryInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Client
 * @since 1.0.16
 */
interface ClientFactoryInterface
{

    /**
     * Gets a client with the given id.
     *
     * @param string $clientId
     *
     * @return ClientInterface|null
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function getClient(string $clientId): ?ClientInterface;

}
