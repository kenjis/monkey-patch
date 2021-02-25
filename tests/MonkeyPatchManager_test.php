<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use CIPHPUnitTest;
use CIPHPUnitTestReflection;
use Kenjis\PhpUnitHelper\ReflectionHelper;

use function file_exists;
use function unlink;

/**
 * @group ci-phpunit-test
 * @group patcher
 */
class MonkeyPatchManager_test extends TestCase
{
    use ReflectionHelper;

    private static $debug;
    private static $log_file;

    public static function setUpBeforeClass(): void
    {
        self::$debug = self::getPrivateProperty(MonkeyPatchManager::class, 'debug');
        self::$log_file = self::getPrivateProperty(MonkeyPatchManager::class, 'log_file');
    }

    public static function tearDownAfterClass(): void
    {
        Cache::clearCache();
        CIPHPUnitTest::setPatcherCacheDir();
        self::setPrivateProperty(MonkeyPatchManager::class, 'debug', self::$debug);
        self::setPrivateProperty(MonkeyPatchManager::class, 'log_file', self::$log_file);

        unlink(__DIR__ . '/monkey-patch-debug.log');
    }

    public function test_setCacheDir_error(): void
    {
        $this->expectException(TypeError::class);

        MonkeyPatchManager::setCacheDir(null);
    }

    public function test_patch_error_cannot_read_file(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Can't read 'dummy'");

        MonkeyPatchManager::patch('dummy');
    }

    public function test_patch_miss_cache(): void
    {
        $cache_dir = APPPATH . 'tests/_ci_phpunit_test/tmp/cache_test';
        CIPHPUnitTest::setPatcherCacheDir($cache_dir);

        $cache_file = Cache::getSrcCacheFilePath(__FILE__);
        $this->assertFalse(file_exists($cache_file));

        MonkeyPatchManager::patch(__FILE__);

        $this->assertTrue(file_exists($cache_file));
    }

    public function test_log_file_path_configurable(): void
    {
        $debug_method = self::getPrivateMethodInvoker(MonkeyPatchManager::class, 'setDebug');
        $debug_method(['debug' => true, 'log_file' => __DIR__ . '/monkey-patch-debug.log']);

        $actual_debug = self::getPrivateProperty(MonkeyPatchManager::class, 'debug');
        $actual_log_file = self::getPrivateProperty(MonkeyPatchManager::class, 'log_file');
        $this->assertTrue($actual_debug);
        $this->assertEquals(__DIR__ . '/monkey-patch-debug.log', $actual_log_file);
    }
}
