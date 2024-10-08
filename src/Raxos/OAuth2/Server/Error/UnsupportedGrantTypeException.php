<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class UnsupportedGrantTypeException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class UnsupportedGrantTypeException extends OAuth2ServerException
{

    /**
     * UnsupportedGrantTypeException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(string $message = 'The authorization grant type is not supported by the authorization server.')
    {
        parent::__construct(HttpResponseCode::BAD_REQUEST, 'unsupported_grant_type', $message);
    }

}
