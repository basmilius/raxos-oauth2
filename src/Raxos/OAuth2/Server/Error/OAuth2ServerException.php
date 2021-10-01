<?php
/*
 * Copyright (c) 2017 - 2021 - Bas Milius <bas@mili.us>
 *
 * This file is part of the Latte Framework package.
 *
 * For the full copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Raxos\OAuth2\Server\Error;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\ExpectedValues;
use Raxos\Http\HttpCode;
use Raxos\OAuth2\Error\OAuth2Exception;

/**
 * Class OAuth2ServerException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 2.0.0
 */
abstract class OAuth2ServerException extends OAuth2Exception
{

    /**
     * Gets the error string.
     *
     * @return string
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public abstract function getError(): string;

    /**
     * Gets the error response code.
     *
     * @return int
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    #[ExpectedValues(valuesFromClass: HttpCode::class)]
    public abstract function getResponseCode(): int;

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
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
