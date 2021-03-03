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
    public $file;
    public $line;
    public $class;
    public $method;
    public $exit_status;
}
