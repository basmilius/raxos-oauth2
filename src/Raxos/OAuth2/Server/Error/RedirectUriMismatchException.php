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

use JetBrains\PhpStorm\ExpectedValues;
use Raxos\Http\HttpCode;

/**
 * Class RedirectUriMismatchException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 2.0.0
 */
final class RedirectUriMismatchException extends OAuth2ServerException
{

    /**
     * RedirectUriMismatchException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(string $message = 'The redirect uri is missing or does not match.')
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
        return 'redirect_uri_mismatch';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    #[ExpectedValues(valuesFromClass: HttpCode::class)]
    public final function getResponseCode(): int
    {
        return HttpCode::BAD_REQUEST;
    }

}
