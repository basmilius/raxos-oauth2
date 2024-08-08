<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpResponseCode;

/**
 * Class RedirectUriMismatchException
 *
 * @author Bas Milius <bas@glybe.nl>
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
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(string $message = 'The redirect uri is missing or does not match.')
    {
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Pure]
    public function getError(): string
    {
        return 'redirect_uri_mismatch';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Pure]
    public function getResponseCode(): HttpResponseCode
    {
        return HttpResponseCode::BAD_REQUEST;
    }

}
