<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class UnsupportedGrantTypeException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 2.0.0
 */
final class UnsupportedGrantTypeException extends OAuth2ServerException
{

    /**
     * UnsupportedGrantTypeException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(string $message = 'The authorization grant type is not supported by the authorization server.')
    {
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getError(): string
    {
        return 'unsupported_grant_type';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getResponseCode(): HttpResponseCode
    {
        return HttpResponseCode::BAD_REQUEST;
    }

}
