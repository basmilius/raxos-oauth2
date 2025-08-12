<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;
use Raxos\OAuth2\Error\OAuth2Exception;
use Throwable;

/**
 * Class OAuth2ServerException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.17
 */
abstract class OAuth2ServerException extends OAuth2Exception
{

    /**
     * OAuth2ServerException constructor.
     *
     * @param HttpResponseCode $responeCode
     * @param string $error
     * @param string $errorDescription
     * @param Throwable|null $previous
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function __construct(
        public readonly HttpResponseCode $responeCode,
        string $error,
        string $errorDescription,
        ?Throwable $previous = null
    )
    {
        parent::__construct($responeCode, $error, $errorDescription, $previous);
    }

}
