<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use JetBrains\PhpStorm\Pure;
use Raxos\Http\HttpResponseCode;

/**
 * Class InvalidClientException
 *
 * @author Bas Milius <bas@glybe.nl>
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
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public function __construct(string $message = 'The client authentication failed.')
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
        return 'invalid_client';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[Pure]
    public function getResponseCode(): HttpResponseCode
    {
        return HttpResponseCode::UNAUTHORIZED;
    }

}
