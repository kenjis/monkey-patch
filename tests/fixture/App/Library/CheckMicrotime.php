<?php

declare(strict_types=1);

namespace App\Library;

use function function_exists;

class CheckMicrotime
{
    public static function exists(): bool
    {
        return function_exists('microtime');
    }
}
