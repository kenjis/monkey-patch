<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

class PathChecker_test extends TestCase
{
    private static $appPath = __DIR__ . '/../src/';

    public static function tearDownAfterClass(): void
    {
        PathChecker::setIncludePaths(
            [self::$appPath]
        );
        PathChecker::setExcludePaths(
            [
                self::$appPath . 'tests/',
            ]
        );
    }

    public function test_check_true(): void
    {
        PathChecker::setIncludePaths(
            [
                self::$appPath . 'controllers/',
            ]
        );
        $test = PathChecker::check(self::$appPath . 'controllers/abc.php');
        $this->assertTrue($test);
    }

    public function test_check_false(): void
    {
        PathChecker::setExcludePaths(
            [
                self::$appPath . 'controllers/sub/',
            ]
        );
        $test = PathChecker::check(
            self::$appPath . '/controllers/sub/abc.php'
        );
        $this->assertFalse($test);
    }
}
