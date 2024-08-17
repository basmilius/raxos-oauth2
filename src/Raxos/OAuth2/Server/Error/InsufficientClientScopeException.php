<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class InsufficientClientScopeException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class InsufficientClientScopeException extends OAuth2ServerException
{

    /**
     * InsufficientClientScopeException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(string $message = 'Insufficient client scope.')
    {
        parent::__construct(HttpResponseCode::FORBIDDEN, 'insufficient_client_scope', $message);
    }

}
