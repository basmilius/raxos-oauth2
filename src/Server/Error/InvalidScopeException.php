<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class InvalidScopeException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class InvalidScopeException extends OAuth2ServerException
{

    /**
     * InvalidScopeException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function __construct(string $message)
    {
        parent::__construct(HttpResponseCode::BAD_REQUEST, 'invalid_scope', $message);
    }

}
