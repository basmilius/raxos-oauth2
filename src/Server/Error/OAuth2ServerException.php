<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Error\Exception;
use Raxos\Http\HttpResponseCode;
use Throwable;

/**
 * Class OAuth2ServerException
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.17
 */
abstract class OAuth2ServerException extends Exception
{

    /**
     * OAuth2ServerException constructor.
     *
     * @param HttpResponseCode $responseCode
     * @param string $error
     * @param string $errorDescription
     * @param Throwable|null $previous
     *
     * @author Bas Milius <bas@mili.us>
     * @since 1.0.17
     */
    public function __construct(
        public readonly HttpResponseCode $responseCode,
        string $error,
        string $errorDescription,
        ?Throwable $previous = null
    )
    {
        parent::__construct($error, $errorDescription, $responseCode, $previous);
    }

}
