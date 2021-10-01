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

namespace Raxos\OAuth2\Server\Token;

/**
 * Interface TokenInterface
 *
 * @author Bas Milius <bas@glybe.nl>
 * @package Raxos\OAuth2\Server\Token
 * @since 2.0.0
 */
interface TokenInterface
{

    /**
     * Returns TRUE if the token is expired.
     *
     * @return bool
     * @author Bas Milius <bas@glybe.nl>
     * @since 2.0.0
     */
    public function isExpired(): bool;

}
