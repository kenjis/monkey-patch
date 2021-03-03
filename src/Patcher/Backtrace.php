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

namespace Kenjis\MonkeyPatch\Patcher;

use LogicException;

use function version_compare;

use const PHP_VERSION;

class Backtrace
{
    private static $map = [
        'FunctionPatcher' => 1,
        'MethodPatcher'   => 0,
        'ConstantPatcher' => 0,
    ];

    public static function getInfo($patcher, $backtrace)
    {
        if (! isset(self::$map[$patcher])) {
            throw new LogicException("No such a patcher: $patcher");
        }

        $offset = self::$map[$patcher];

        // Supports PHP7 optimization
        if (version_compare(PHP_VERSION, '6.0.0', '>')) {
            if ($backtrace[$offset]['function'] === '__callStatic') {
                $offset--;
            }
        }

        $file = $backtrace[$offset]['file'] ?? null;
        $line = $backtrace[$offset]['line'] ?? null;

        if (isset($backtrace[$offset + 2])) {
            $class  = $backtrace[$offset + 2]['class'] ?? null;
            $function = $backtrace[$offset + 2]['function'];
        } else {
            $class = null;
            $function = null;
        }

        if (isset($class)) {
            $method = $function;
            $class_method = $class . '::' . $function;
            $function = null;
        } else {
            $method = null;
            $class_method = null;
        }

        return [
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'method' => $method,
            'class_method' => $class_method,
            'function' => $function,
        ];
    }
}
