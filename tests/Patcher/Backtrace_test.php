<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\Patcher;

use Kenjis\MonkeyPatch\TestCase;
use PHPUnit\Runner\Version;
use stdClass;

use function class_exists;
use function debug_backtrace;
use function version_compare;

use const PHP_VERSION;

/**
 * @group ci-phpunit-test
 * @group patcher
 */
class Backtrace_test extends TestCase
{
    public function test_getInfo_FunctionPatcher(): void
    {
        $trace = debug_backtrace();
        $info = Backtrace::getInfo('FunctionPatcher', $trace);

        if ($this->is_phpunit_71_and_greater()) {
            // PHPUnit 7.1
            $this->assertEquals('PHPUnit\Framework\TestResult', $info['class']);
            $this->assertEquals(
                'PHPUnit\Framework\TestResult::run',
                $info['class_method']
            );
            $this->assertEquals('run', $info['method']);
        } elseif ($this->is_phpunit_60_and_greater()) {
            // PHPUnit 6.0
            $this->assertEquals('PHPUnit\Framework\TestCase', $info['class']);
            $this->assertEquals(
                'PHPUnit\Framework\TestCase::runBare',
                $info['class_method']
            );
            $this->assertEquals('runBare', $info['method']);
        } else {
            // PHPUnit 5.7 and before
            $this->assertEquals('PHPUnit_Framework_TestCase', $info['class']);
            $this->assertEquals(
                'PHPUnit_Framework_TestCase::runBare',
                $info['class_method']
            );
            $this->assertEquals('runBare', $info['method']);
        }

        $this->assertNull($info['function']);
    }

    private function is_phpunit_71_and_greater()
    {
        if (class_exists('PHPUnit\Runner\Version')) {
            if (version_compare(Version::series(), '7.1') >= 0) {
                return true;
            }
        }

        return false;
    }

    private function is_phpunit_60_and_greater()
    {
        if (class_exists('PHPUnit\Runner\Version')) {
            if (version_compare(Version::series(), '6.0') >= 0) {
                return true;
            }
        }

        return false;
    }

    public function provide_PHP5_trace()
    {
        return [
            0 =>
            [
                'file' => '/vagrant/debug_backtrace/Proxy.php',
                'line' => 7,
                'function' => 'checkCalledMethod',
                'class' => 'Proxy',
                'type' => '::',
                'args' =>
                [0 => 'bar'],
            ],
            1 =>
            [
                'file' => '/vagrant/debug_backtrace/Test.php',
                'line' => 7,
                'function' => '__callStatic',
                'class' => 'Proxy',
                'type' => '::',
                'args' =>
                [
                    0 => 'bar',
                    1 =>
                    [],
                ],
            ],
            2 =>
            [
                'file' => '/vagrant/debug_backtrace/Test.php',
                'line' => 7,
                'function' => 'bar',
                'class' => 'Proxy',
                'type' => '::',
                'args' =>
                [],
            ],
            3 =>
            [
                'file' => '/vagrant/debug_backtrace/app.php',
                'line' => 7,
                'function' => 'run',
                'class' => 'Test',
                'object' =>
                    new stdClass(),  // dummy
                'type' => '->',
                'args' =>
                [],
            ],
        ];
    }

    public function provide_PHP7_trace()
    {
        return [
            0 =>
            [
                'file' => '/vagrant/debug_backtrace/Proxy.php',
                'line' => 7,
                'function' => 'checkCalledMethod',
                'class' => 'Proxy',
                'type' => '::',
                'args' =>
                [0 => 'bar'],
            ],
            1 =>
            [
                'file' => '/vagrant/debug_backtrace/Test.php',
                'line' => 7,
                'function' => '__callStatic',
                'class' => 'Proxy',
                'type' => '::',
                'args' =>
                [
                    0 => 'bar',
                    1 =>
                    [],
                ],
            ],
            2 =>
            [
                'file' => '/vagrant/debug_backtrace/app.php',
                'line' => 7,
                'function' => 'run',
                'class' => 'Test',
                'object' =>
                    new stdClass(),  // dummy
                'type' => '->',
                'args' =>
                [],
            ],
        ];
    }

    public function test_getInfo_FunctionPatcher_callStatic(): void
    {
        if (version_compare(PHP_VERSION, '6.0.0', '>=')) {
            $trace = $this->provide_PHP7_trace();
        } else {
            $trace = $this->provide_PHP5_trace();
        }

        $info = Backtrace::getInfo('FunctionPatcher', $trace);

        $this->assertEquals('Test', $info['class']);
        $this->assertEquals('run', $info['method']);
        $this->assertEquals(
            'Test::run',
            $info['class_method']
        );
        $this->assertNull($info['function']);
    }

    public function test_getInfo_MethodPatcher(): void
    {
        $trace = debug_backtrace();
        $info = Backtrace::getInfo('MethodPatcher', $trace);

        if ($this->is_phpunit_71_and_greater()) {
            // PHPUnit 7.1
            $this->assertEquals('PHPUnit\Framework\TestCase', $info['class']);
            $this->assertEquals(
                'PHPUnit\Framework\TestCase::runBare',
                $info['class_method']
            );
            $this->assertEquals('runBare', $info['method']);
        } elseif ($this->is_phpunit_60_and_greater()) {
            // PHPUnit 6.0
            $this->assertEquals('PHPUnit\Framework\TestCase', $info['class']);
            $this->assertEquals(
                'PHPUnit\Framework\TestCase::runTest',
                $info['class_method']
            );
            $this->assertEquals('runTest', $info['method']);
        } else {
            // PHPUnit 5.7 and before
            $this->assertEquals('PHPUnit_Framework_TestCase', $info['class']);
            $this->assertEquals(
                'PHPUnit_Framework_TestCase::runTest',
                $info['class_method']
            );
            $this->assertEquals('runTest', $info['method']);
        }

        $this->assertNull($info['function']);
    }
}
