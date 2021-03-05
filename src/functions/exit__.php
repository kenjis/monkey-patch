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

use Kenjis\MonkeyPatch\Exception\ExitException;
use Kenjis\MonkeyPatch\MonkeyPatchManager;

/**
 * @param string|int|null $status
 */
function exit__($status = null): void
{
    $trace = debug_backtrace();
    $file = $trace[0]['file'];
    $line = $trace[0]['line'];
    $class = $trace[1]['class'] ?? null;
    $method = $trace[1]['function'];

    if ($class === null) {
        $message = 'exit() called in ' . $method . '() function';
    } else {
        $message = 'exit() called in ' . $class . '::' . $method . '()';
    }

    $exception_name = MonkeyPatchManager::getExitExceptionClassname();

    /**
     * @var ExitException
     */
    $exception = new $exception_name($message);
    $exception->file = $file;
    $exception->line = $line;
    $exception->class = $class;
    $exception->method = $method;
    $exception->exit_status = $status;

    throw $exception;
}
