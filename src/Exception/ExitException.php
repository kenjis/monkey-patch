<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
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
