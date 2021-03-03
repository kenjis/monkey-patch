<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021 Kenji Suzuki
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/kenjis/monkey-patch
 */

namespace Kenjis\MonkeyPatch\Exception;

use RuntimeException;

class ExitException extends RuntimeException
{
    /** @var string */
    public $file;

    /** @var int */
    public $line;

    /** @var string */
    public $class;

    /** @var string */
    public $method;

    /** @var string|int|null */
    public $exit_status;
}
