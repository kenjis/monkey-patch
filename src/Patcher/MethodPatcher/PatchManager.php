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

namespace Kenjis\MonkeyPatch\Patcher\MethodPatcher;

use Kenjis\MonkeyPatch\InvocationVerifier;
use Kenjis\MonkeyPatch\MonkeyPatchManager;
use Kenjis\MonkeyPatch\Patcher\Backtrace;

use function array_key_exists;
use function call_user_func_array;
use function debug_backtrace;
use function is_callable;
use function rtrim;
use function var_export;

class PatchManager
{
    /** @var array<string, mixed> */
    private static $patches = [];

    /** @var array<string, array<int, array{0: mixed[], 1: int|string}>> */
    private static $expected_invocations = [];

    /** @var array<string, mixed> */
    private static $invocations = [];

    /**
     * Set a method patch
     *
     * @param array<string, mixed> $params [method_name => return_value]
     */
    public static function set(string $class, array $params): void
    {
        self::$patches[$class] = $params;
    }

    /**
     * Clear all patches and invocation data
     */
    public static function clear(): void
    {
        self::$patches = [];
        self::$expected_invocations = [];
        self::$invocations = [];
    }

    /**
     * @param mixed[] $params
     *
     * @return false|mixed|string
     */
    public static function getReturn(string $class, string $method, array $params)
    {
        if (MonkeyPatchManager::$debug) {
            $trace = debug_backtrace();
            $info = Backtrace::getInfo('MethodPatcher', $trace);

            $file = $info['file'];
            $line = $info['line'];

            if (isset($info['class_method'])) {
                $called_method = $info['class_method'];
            } elseif (isset($info['function'])) {
                $called_method = $info['function'];
            } else {
                $called_method = 'n/a';
            }

            $log_args = static function () use ($params) {
                $output = '';
                foreach ($params as $arg) {
                    $output .= var_export($arg, true) . ', ';
                }

                $output = rtrim($output, ', ');

                return $output;
            };
            MonkeyPatchManager::log(
                'invoke_method: ' . $class . '::' . $method . '(' . $log_args() . ') on line ' . $line . ' in ' . $file . ' by ' . $called_method
            );
//          var_dump($trace); exit;
        }

        self::$invocations[$class . '::' . $method][] = $params;

        if (
            isset(self::$patches[$class])
            && array_key_exists($method, self::$patches[$class])
        ) {
            $patch = self::$patches[$class][$method];
        } else {
            return __GO_TO_ORIG__;
        }

        if (is_callable($patch)) {
            return call_user_func_array($patch, $params);
        }

        return $patch;
    }

    /**
     * @param int|string $times
     * @param mixed[]    $params
     */
    public static function setExpectedInvocations(string $class_method, $times, array $params): void
    {
        self::$expected_invocations[$class_method][] = [$params, $times];
    }

    public static function verifyInvocations(): void
    {
        InvocationVerifier::verify(self::$expected_invocations, self::$invocations);
    }
}
