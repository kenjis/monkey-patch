<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use CIPHPUnitTest;
use ReflectionHelper;
use TestCase;

use function file_exists;
use function realpath;

/**
 * @group ci-phpunit-test
 * @group patcher
 */
class Cache_test extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        Cache::clearCache();
        CIPHPUnitTest::setPatcherCacheDir();
    }

    public function test_setCacheDir(): void
    {
        $cache_dir = APPPATH . 'tests/_ci_phpunit_test/tmp/cache_test';
        Cache::setCacheDir($cache_dir);
        $this->assertEquals(realpath($cache_dir), Cache::getCacheDir());
    }

    public function test_writeTmpFunctionWhitelist(): void
    {
        Cache::createTmpListDir();
        $list = [
            'file_exists',
            'file_get_contents',
            'file_put_contents',
        ];
        Cache::writeTmpFunctionWhitelist($list);

        $actual = Cache::getTmpFunctionWhitelist();
        $this->assertEquals($list, $actual);
    }

    public function test_writeTmpPatcherList(): void
    {
        $list = [
            'ExitPatcher',
            'FunctionPatcher',
            'MethodPatcher',
        ];
        Cache::writeTmpPatcherList($list);

        $actual = Cache::getTmpPatcherList();
        $this->assertEquals($list, $actual);
    }

    public function test_writeTmpIncludePaths(): void
    {
        $list = [
            APPPATH,
            BASEPATH,
        ];
        Cache::writeTmpIncludePaths($list);

        $actual = Cache::getTmpIncludePaths();
        $this->assertEquals($list, $actual);
    }

    public function test_writeTmpExcludePaths(): void
    {
        $list = [
            APPPATH . 'test',
        ];
        Cache::writeTmpExcludePaths($list);

        $actual = Cache::getTmpExcludePaths();
        $this->assertEquals($list, $actual);
    }

    public function test_clearSrcCache(): void
    {
        Cache::clearSrcCache();
        $this->assertFalse(file_exists(
            ReflectionHelper::getPrivateProperty(
                __NAMESPACE__ . '\Cache',
                'src_cache_dir'
            )
        ));
    }

    public function test_clearCache(): void
    {
        Cache::clearCache();
        $this->assertFalse(file_exists(
            ReflectionHelper::getPrivateProperty(
                __NAMESPACE__ . '\Cache',
                'cache_dir'
            )
        ));
    }
}
