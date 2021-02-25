<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

class PathCheckerTest extends TestCase
{
    private static $appPath = __DIR__ . '/../src/';

    public static function tearDownAfterClass(): void
    {
        PathChecker::setIncludePaths(
            [self::$appPath]
        );
        PathChecker::setExcludePaths(
            [__DIR__]
        );
    }

    public function test_check_true(): void
    {
        PathChecker::setIncludePaths(
            [
                self::$appPath . 'Patcher/',
            ]
        );
        $test = PathChecker::check(self::$appPath . 'Patcher/ExitPatcher.php');
        $this->assertTrue($test);
    }

    public function test_check_false(): void
    {
        PathChecker::setExcludePaths(
            [
                self::$appPath . 'Patcher/ConstantPatcher/',
            ]
        );
        $test = PathChecker::check(
            self::$appPath . '/Patcher/ConstantPatcher/Proxy.php'
        );
        $this->assertFalse($test);
    }
}
