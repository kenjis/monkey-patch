<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021 Kenji Suzuki
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/kenjis/monkey-patch
 */

namespace Kenjis\MonkeyPatch;

use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

use function dirname;
use function file;
use function file_put_contents;
use function filemtime;
use function implode;
use function is_dir;
use function is_readable;
use function mkdir;
use function realpath;
use function rmdir;
use function strlen;
use function substr;
use function touch;
use function unlink;

use const FILE_APPEND;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;

class Cache
{
    /** @var string */
    private static $project_root;

    /** @var string */
    private static $cache_dir;

    /** @var string */
    private static $src_cache_dir;

    /** @var string */
    private static $tmp_function_blacklist_file;

    /** @var string */
    private static $tmp_function_whitelist_file;

    /** @var string */
    private static $tmp_patcher_list_file;

    /** @var string */
    private static $tmp_include_paths_file;

    /** @var string */
    private static $tmp_exclude_paths_file;

    public static function setProjectRootDir(string $dir): void
    {
        self::$project_root = realpath($dir);
        if (self::$project_root === false) {
            throw new LogicException("No such directory: $dir");
        }
    }

    public static function setCacheDir(string $dir): void
    {
        self::createDir($dir);
        self::$cache_dir = realpath($dir);

        if (self::$cache_dir === false) {
            throw new LogicException("No such directory: $dir");
        }

        self::$src_cache_dir = self::$cache_dir . '/src';
        self::$tmp_function_whitelist_file =
            self::$cache_dir . '/conf/func_whiltelist.php';
        self::$tmp_function_blacklist_file =
            self::$cache_dir . '/conf/func_blacklist.php';
        self::$tmp_patcher_list_file =
            self::$cache_dir . '/conf/patcher_list.php';
        self::$tmp_include_paths_file =
            self::$cache_dir . '/conf/include_paths.php';
        self::$tmp_exclude_paths_file =
            self::$cache_dir . '/conf/exclude_paths.php';
    }

    public static function getCacheDir()
    {
        return self::$cache_dir;
    }

    public static function getSrcCacheFilePath(string $path)
    {
        $len = strlen(self::$project_root);
        $relative_path = substr($path, $len);

        if ($relative_path === false) {
            return false;
        }

        return self::$src_cache_dir . '/' . $relative_path;
    }

    protected static function createDir(string $dir): void
    {
        if (! is_dir($dir)) {
            if (! @mkdir($dir, 0777, true)) {
                throw new RuntimeException('Failed to create folder: ' . $dir);
            }
        }
    }

    /**
     * @param string $path original source file path
     *
     * @return string|false
     */
    public static function getValidSrcCachePath(string $path)
    {
        $cache_file = self::getSrcCacheFilePath($path);

        if (
            is_readable($cache_file) && filemtime($cache_file) > filemtime($path)
        ) {
            return $cache_file;
        }

        return false;
    }

    /**
     * Write to src cache file
     *
     * @param string $path   original source file path
     * @param string $source source code
     */
    public static function writeSrcCacheFile(string $path, string $source): void
    {
        $cache_file = self::getSrcCacheFilePath($path);
        if ($cache_file !== false) {
            self::writeCacheFile($cache_file, $source);
        }
    }

    /**
     * Write to cache file
     *
     * @param string $path     file path
     * @param string $contents file contents
     */
    public static function writeCacheFile(string $path, string $contents): void
    {
        $dir = dirname($path);
        self::createDir($dir);
        file_put_contents($path, $contents);
    }

    public static function getTmpFunctionBlacklistFile()
    {
        return self::$tmp_function_blacklist_file;
    }

    public static function createTmpListDir(): void
    {
        if (is_readable(self::$tmp_function_blacklist_file)) {
            return;
        }

        $dir = dirname(self::$tmp_function_blacklist_file);
        self::createDir($dir);

        touch(self::$tmp_function_blacklist_file);
    }

    public static function appendTmpFunctionBlacklist($function): void
    {
        file_put_contents(
            self::getTmpFunctionBlacklistFile(),
            $function . "\n",
            FILE_APPEND
        );
    }

    protected static function writeTmpConfFile($filename, array $list): void
    {
        $contents = implode("\n", $list);
        file_put_contents(
            self::$$filename,
            $contents
        );
    }

    public static function writeTmpFunctionWhitelist(array $functions): void
    {
        self::writeTmpConfFile(
            'tmp_function_whitelist_file',
            $functions
        );
    }

    public static function writeTmpPatcherList(array $patchers): void
    {
        self::writeTmpConfFile(
            'tmp_patcher_list_file',
            $patchers
        );
    }

    public static function writeTmpIncludePaths(array $paths): void
    {
        self::writeTmpConfFile(
            'tmp_include_paths_file',
            $paths
        );
    }

    /**
     * @param array $paths
     */
    public static function writeTmpExcludePaths(array $paths): void
    {
        self::writeTmpConfFile(
            'tmp_exclude_paths_file',
            $paths
        );
    }

    /**
     * @return string[]|false
     */
    protected static function getTmpConfFile(string $filename)
    {
        if (is_readable(self::$$filename)) {
            return file(
                self::$$filename,
                FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
            );
        }

        return [];
    }

    public static function getTmpFunctionWhitelist()
    {
        return self::getTmpConfFile('tmp_function_whitelist_file');
    }

    public static function getTmpPatcherList()
    {
        return self::getTmpConfFile('tmp_patcher_list_file');
    }

    public static function getTmpIncludePaths()
    {
        return self::getTmpConfFile('tmp_include_paths_file');
    }

    public static function getTmpExcludePaths()
    {
        return self::getTmpConfFile('tmp_exclude_paths_file');
    }

    /**
     * @param string $orig_file original source file
     *
     * @return string removed cache file
     */
    public static function removeSrcCacheFile(string $orig_file): string
    {
        $cache = self::getSrcCacheFilePath($orig_file);
        @unlink($cache);
        MonkeyPatchManager::log('remove_src_cache: ' . $cache);

        return $cache;
    }

    public static function clearSrcCache(): void
    {
        self::recursiveUnlink(self::$src_cache_dir);
        MonkeyPatchManager::log('clear_src_cache: cleared ' . self::$src_cache_dir);
    }

    public static function clearCache(): void
    {
        self::recursiveUnlink(self::$cache_dir);
        MonkeyPatchManager::log('clear_cache: cleared ' . self::$cache_dir);
    }

    /**
     * Recursive Unlink
     */
    protected static function recursiveUnlink(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir((string) $file);
            } else {
                unlink((string) $file);
            }
        }

        rmdir($dir);
    }
}
