<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpResponseCode;

/**
 * Class InvalidRequestException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
final class InvalidRequestException extends OAuth2ServerException
{

    /**
     * InvalidRequestException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(string $message = 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.')
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
        return 'invalid_request';
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
