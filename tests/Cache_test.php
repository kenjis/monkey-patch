<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use Kenjis\PhpUnitHelper\ReflectionHelper;

use function file_exists;
use function realpath;

class Cache_test extends TestCase
{
    use ReflectionHelper;

    public static function tearDownAfterClass(): void
    {
        Cache::clearCache();

        $dir = __DIR__ . '/../tmp/cache';
        MonkeyPatchManager::setCacheDir($dir);
    }

    public function test_setCacheDir(): void
    {
        $cache_dir = __DIR__ . '/../tmp/cache_test';
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
            __DIR__ . '/../src/Exception',
            __DIR__ . '/../src/Patcher',
        ];
        Cache::writeTmpIncludePaths($list);

        $actual = Cache::getTmpIncludePaths();
        $this->assertEquals($list, $actual);
    }

    public function test_writeTmpExcludePaths(): void
    {
        $list = [
            __DIR__ . '/tmp/test',
        ];
        Cache::writeTmpExcludePaths($list);

        $actual = Cache::getTmpExcludePaths();
        $this->assertEquals($list, $actual);
    }

    public function test_clearSrcCache(): void
    {
        Cache::clearSrcCache();
        $this->assertFalse(file_exists(
            self::getPrivateProperty(
                __NAMESPACE__ . '\Cache',
                'src_cache_dir'
            )
        ));
    }

    public function test_clearCache(): void
    {
        Cache::clearCache();
        $this->assertFalse(file_exists(
            self::getPrivateProperty(
                __NAMESPACE__ . '\Cache',
                'cache_dir'
            )
        ));
    }
}
