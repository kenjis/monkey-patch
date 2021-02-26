<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher\ConstantPatcher;

class_alias('Kenjis\MonkeyPatch\Patcher\ConstantPatcher\Proxy', '__ConstProxy__');

use Kenjis\MonkeyPatch\MonkeyPatchManager;
use Kenjis\MonkeyPatch\Patcher\Backtrace;
use LogicException;

use function array_key_exists;
use function class_alias;
use function constant;
use function debug_backtrace;
use function strpos;
use function strtolower;

class Proxy
{
    private static $patches = [];
    private static $patches_to_apply = [];

    /**
     * Set a constant patch
     *
     * @param string $constant     constant name
     * @param mixed  $value        value
     * @param string $class_method class::method to apply this patch
     *
     * @throws LogicException
     */
    public static function patch(string $constant, $value, string $class_method = ''): void
    {
        self::$patches[$constant] = $value;
        self::$patches_to_apply[$constant] = strtolower($class_method);
    }

    /**
     * Clear all patches and invocation data
     */
    public static function reset(): void
    {
        self::$patches = [];
        self::$patches_to_apply = [];
    }

    protected static function logInvocation($constant): void
    {
        if (MonkeyPatchManager::$debug) {
            $trace = debug_backtrace();
            $info = Backtrace::getInfo('ConstantPatcher', $trace);

            $file = $info['file'];
            $line = $info['line'];
            $method = $info['class_method'] ?? $info['function'];

            MonkeyPatchManager::log(
                'invoke_const: ' . $constant . ') on line ' . $line . ' in ' . $file . ' by ' . $method . '()'
            );
        }
    }

    protected static function checkCalledMethod($constant)
    {
        $trace = debug_backtrace();
        $info = Backtrace::getInfo('ConstantPatcher', $trace);

        $class = strtolower($info['class']);
        $class_method = strtolower($info['class_method']);

        // Patches the constants only in the class
        if (strpos(self::$patches_to_apply[$constant], '::') === false) {
            return self::$patches_to_apply[$constant] === $class;
        }

        return self::$patches_to_apply[$constant] === $class_method;
    }

    /**
     * Get patched constant value
     *
     * @return mixed
     */
    public static function get(string $constant)
    {
        self::logInvocation($constant);

        if (! empty(self::$patches_to_apply[$constant])) {
            if (! self::checkCalledMethod($constant)) {
                MonkeyPatchManager::log(
                    'invoke_const: ' . $constant . ' not patched (out of scope)'
                );

                return constant($constant);
            }
        }

        if (array_key_exists($constant, self::$patches)) {
            MonkeyPatchManager::log('invoke_const: ' . $constant . ' patched');

            return self::$patches[$constant];
        }

        MonkeyPatchManager::log(
            'invoke_const: ' . $constant . ' not patched (no patch)'
        );

        return constant($constant);
    }
}
