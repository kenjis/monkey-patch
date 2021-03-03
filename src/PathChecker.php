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

use RuntimeException;

use function array_unique;
use function is_dir;
use function ltrim;
use function realpath;
use function sort;
use function str_replace;
use function strlen;
use function substr;

use const DIRECTORY_SEPARATOR;
use const SORT_STRING;

class PathChecker
{
    private static $include_paths = [];
    private static $exclude_paths = [];

    /**
     * @param array $paths directory or file path
     *
     * @return array
     *
     * @throws RuntimeException
     */
    protected static function normalizePaths(array $paths): array
    {
        $new_paths = [];
        $excluded = false;
        foreach ($paths as $path) {
            // Path starting with '-' has special meaning (excluding it)
            if (substr($path, 0, 1) === '-') {
                $excluded = true;
                $path = ltrim($path, '-');
            }

            $real_path = realpath($path);
            if ($real_path === false) {
                throw new RuntimeException($path . ' does not exist?');
            }

            if (is_dir($real_path)) {
                // Must use DIRECTORY_SEPARATOR for Windows
                $real_path .= DIRECTORY_SEPARATOR;
            }

            $new_paths[] = $excluded ? '-' . $real_path : $real_path;
        }

        $new_paths = array_unique($new_paths, SORT_STRING);
        sort($new_paths, SORT_STRING);

        return $new_paths;
    }

    public static function setIncludePaths(array $dir): void
    {
        self::$include_paths = self::normalizePaths($dir);
    }

    public static function setExcludePaths(array $dir): void
    {
        self::$exclude_paths = self::normalizePaths($dir);
    }

    public static function getIncludePaths()
    {
        return self::$include_paths;
    }

    public static function getExcludePaths()
    {
        return self::$exclude_paths;
    }

    public static function check(string $path)
    {
        $path = (string) realpath($path);

        // Windows
        if (DIRECTORY_SEPARATOR === '\\') {
            $path = str_replace('/', '\\', $path);
        }

        // Whitelist first
        $is_white = false;
        foreach (self::$include_paths as $white_dir) {
            $len = strlen($white_dir);
            if (substr($path, 0, $len) === $white_dir) {
                $is_white = true;
            }
        }

        if ($is_white === false) {
            return false;
        }

        // Then blacklist
        foreach (self::$exclude_paths as $black_dir) {
            // Check excluded path that starts with '-'.
            // '-' is smaller than '/', so this checking always comes first.
            if (substr($black_dir, 0, 1) === '-') {
                $black_dir = ltrim($black_dir, '-');
                $len = strlen($black_dir);
                if (substr($path, 0, $len) === $black_dir) {
                    return true;
                }
            }

            $len = strlen($black_dir);
            if (substr($path, 0, $len) === $black_dir) {
                return false;
            }
        }

        return true;
    }
}
