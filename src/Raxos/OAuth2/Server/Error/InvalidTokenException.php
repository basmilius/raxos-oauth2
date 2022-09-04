<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use Raxos\Http\HttpResponseCode;

/**
 * Class InvalidTokenException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 2.0.0
 */
final class InvalidTokenException extends OAuth2ServerException
{

    /**
     * InvalidTokenException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(string $message = 'Invalid access token.')
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
        return 'invalid_token';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getResponseCode(): HttpResponseCode
    {
        return HttpResponseCode::UNAUTHORIZED;
    }

}
