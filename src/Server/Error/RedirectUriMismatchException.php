<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class RedirectUriMismatchException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class RedirectUriMismatchException extends OAuth2ServerException
{

    /**
     * RedirectUriMismatchException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.16
     */
    public function __construct(string $message = 'The redirect uri is missing or does not match.')
    {
        parent::__construct(HttpResponseCode::BAD_REQUEST, 'redirect_uri_mismatch', $message);
    }

}
