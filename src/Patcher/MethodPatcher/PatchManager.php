<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher\MethodPatcher;

class_alias('Kenjis\MonkeyPatch\Patcher\MethodPatcher\PatchManager', '__PatchManager__');

use Kenjis\MonkeyPatch\InvocationVerifier;
use Kenjis\MonkeyPatch\MonkeyPatchManager;
use Kenjis\MonkeyPatch\Patcher\Backtrace;

use function array_key_exists;
use function call_user_func_array;
use function class_alias;
use function debug_backtrace;
use function is_callable;
use function rtrim;
use function var_export;

class PatchManager
{
    private static $patches = [];
    private static $expected_invocations = [];
    private static $invocations = [];

    /**
     * Set a method patch
     *
     * @param array $params [method_name => return_value]
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

    public static function getReturn($class, $method, $params)
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

    public static function setExpectedInvocations($class_method, $times, $params): void
    {
        self::$expected_invocations[$class_method][] = [$params, $times];
    }

    public static function verifyInvocations(): void
    {
        InvocationVerifier::verify(self::$expected_invocations, self::$invocations);
    }
}
