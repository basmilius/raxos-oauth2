<?php
declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use JetBrains\PhpStorm\ArrayShape;
use Raxos\Http\HttpResponseCode;
use Raxos\OAuth2\Error\OAuth2Exception;

/**
 * Class OAuth2ServerException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 1.0.16
 */
abstract class OAuth2ServerException extends OAuth2Exception
{

    /**
     * Gets the error string.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public abstract function getError(): string;

    /**
     * Gets the error response code.
     *
     * @return HttpResponseCode
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    public abstract function getResponseCode(): HttpResponseCode;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 1.0.16
     */
    #[ArrayShape([
        'code' => 'int',
        'error' => 'string',
        'error_description' => 'string'
    ])]
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->getResponseCode(),
            'error' => $this->getError(),
            'error_description' => $this->getMessage()
        ];
    }

}
