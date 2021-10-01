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

use Raxos\Http\HttpCode;

/**
 * Class InvalidClientException
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Error
 * @since 2.0.0
 */
final class InvalidClientException extends OAuth2ServerException
{

    /**
     * InvalidClientException constructor.
     *
     * @param string $message
     *
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function __construct(string $message = 'The client authentication failed.')
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
        return 'invalid_client';
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public final function getResponseCode(): int
    {
        return HttpCode::UNAUTHORIZED;
    }

}
