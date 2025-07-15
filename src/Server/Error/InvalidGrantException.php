<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class InvalidGrantException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class InvalidGrantException extends OAuth2ServerException
{

    /**
     * InvalidGrantException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function __construct(string $message)
    {
        parent::__construct(HttpResponseCode::BAD_REQUEST, 'invalid_grant', $message);
    }

}
