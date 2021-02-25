<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use TestCase;

/**
 * @group ci-phpunit-test
 * @group patcher
 */
class PathChecker_test extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        PathChecker::setIncludePaths(
            [APPPATH]
        );
        PathChecker::setExcludePaths(
            [
                APPPATH . 'tests/',
            ]
        );
    }

    public function test_check_true(): void
    {
        PathChecker::setIncludePaths(
            [
                APPPATH . 'controllers/',
            ]
        );
        $test = PathChecker::check(APPPATH . 'controllers/abc.php');
        $this->assertTrue($test);
    }

    public function test_check_false(): void
    {
        PathChecker::setExcludePaths(
            [
                APPPATH . 'controllers/sub/',
            ]
        );
        $test = PathChecker::check(
            APPPATH . '/controllers/sub/abc.php'
        );
        $this->assertFalse($test);
    }
}
