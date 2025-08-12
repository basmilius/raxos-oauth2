<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class InvalidClientException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class InvalidClientException extends OAuth2ServerException
{

    /**
     * InvalidClientException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(string $message = 'The client authentication failed.')
    {
        parent::__construct(HttpResponseCode::UNAUTHORIZED, 'invalid_client', $message);
    }

}
