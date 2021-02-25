<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use Kenjis\MonkeyPatch\Patcher\ConstantPatcher\Proxy as ConstProxy;
use Kenjis\MonkeyPatch\Patcher\FunctionPatcher\Proxy;
use Kenjis\MonkeyPatch\Patcher\MethodPatcher\PatchManager;

use function strpos;

class MonkeyPatch
{
    /**
     * Patch on function
     *
     * @param string $function     function name
     * @param mixed  $return_value return value
     * @param string $class_name   class::method to apply this patch
     */
    public static function patchFunction(string $function, $return_value, $class_method = null): void
    {
        Proxy::patch__($function, $return_value, $class_method);
    }

    /**
     * Reset all patched fuctions
     */
    public static function resetFunctions(): void
    {
        Proxy::reset__();
    }

    /**
     * Patch on constant
     *
     * @param mixed $value
     */
    public static function patchConstant(string $constant, $value, ?string $class_method = null): void
    {
        ConstProxy::patch($constant, $value, $class_method);
    }

    /**
     * Reset all patched constants
     */
    public static function resetConstants(): void
    {
        ConstProxy::reset();
    }

    /**
     * Patch on class method
     *
     * @param array $params [method_name => return_value]
     */
    public static function patchMethod(string $class, array $params): void
    {
        PatchManager::set($class, $params);
    }

    /**
     * Reset all patched class method
     */
    public static function resetMethods(): void
    {
        PatchManager::clear();
    }

    protected static function getClassname($class_method)
    {
        if (strpos($class_method, '::') === false) {
            return 'Kenjis\MonkeyPatch\Patcher\FunctionPatcher\Proxy';
        }

        return 'Kenjis\MonkeyPatch\Patcher\MethodPatcher\PatchManager';
    }

    /**
     * @param string $class_method class::method or function name
     * @param int    $times        times
     * @param array  $params       parameters
     */
    public static function verifyInvokedMultipleTimes(
        string $class_method,
        int $times,
        ?array $params = null
    ): void {
        $classname = self::getClassname($class_method);
        $classname::setExpectedInvocations(
            $class_method,
            $times,
            $params
        );
    }

    /**
     * @param string $class_method class::method or function name
     * @param array  $params       parameters
     */
    public static function verifyInvoked(string $class_method, ?array $params = null): void
    {
        $classname = self::getClassname($class_method);
        $classname::setExpectedInvocations(
            $class_method,
            '+',
            $params
        );
    }

    /**
     * @param string $class_method class::method or function name
     * @param array  $params       parameters
     */
    public static function verifyInvokedOnce(string $class_method, ?array $params = null): void
    {
        $classname = self::getClassname($class_method);
        $classname::setExpectedInvocations(
            $class_method,
            1,
            $params
        );
    }

    /**
     * @param string $class_method class::method or function name
     * @param array  $params       parameters
     */
    public static function verifyNeverInvoked(string $class_method, ?array $params = null): void
    {
        $classname = self::getClassname($class_method);
        $classname::setExpectedInvocations(
            $class_method,
            0,
            $params
        );
    }

    /**
     * Run function verifcations
     */
    public static function verifyFunctionInvocations(): void
    {
        Proxy::verifyInvocations();
    }

    /**
     * Run method verifcations
     */
    public static function verifyMethodInvocations(): void
    {
        PatchManager::verifyInvocations();
    }
}
