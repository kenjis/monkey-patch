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

/*
 * Copyright for Original Code
 *
 * @link       http://antecedent.github.com/patchwork
 * @see        https://github.com/antecedent/patchwork/blob/1.3.5/src/Preprocessor/Stream.php
 */

namespace Kenjis\MonkeyPatch;

use Kenjis\MonkeyPatch\Exception\LogicException;

use function chgrp;
use function chmod;
use function chown;
use function closedir;
use function fclose;
use function feof;
use function fflush;
use function flock;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function ftruncate;
use function fwrite;
use function mkdir;
use function opendir;
use function readdir;
use function rename;
use function restore_error_handler;
use function rewinddir;
use function rmdir;
use function set_error_handler;
use function stat;
use function stream_set_blocking;
use function stream_set_read_buffer;
use function stream_set_timeout;
use function stream_set_write_buffer;
use function stream_wrapper_register;
use function stream_wrapper_restore;
use function stream_wrapper_unregister;
use function touch;
use function unlink;

use const SEEK_SET;
use const STREAM_META_ACCESS;
use const STREAM_META_GROUP;
use const STREAM_META_GROUP_NAME;
use const STREAM_META_OWNER;
use const STREAM_META_OWNER_NAME;
use const STREAM_META_TOUCH;
use const STREAM_OPTION_BLOCKING;
use const STREAM_OPTION_READ_BUFFER;
use const STREAM_OPTION_READ_TIMEOUT;
use const STREAM_OPTION_WRITE_BUFFER;
use const STREAM_URL_STAT_QUIET;

/**
 * @SuppressWarnings(PHPMD)
 */
class IncludeStream
{
    public const STREAM_OPEN_FOR_INCLUDE = 128;
    public const STAT_MTIME_NUMERIC_OFFSET = 9;
    public const STAT_MTIME_ASSOC_OFFSET = 'mtime';

    protected static $protocols = ['file', 'phar'];
    public $context;
    public $resource;

    public static function wrap(): void
    {
        foreach (static::$protocols as $protocol) {
            stream_wrapper_unregister($protocol);
            stream_wrapper_register($protocol, static::class);
        }
    }

    public static function unwrap(): void
    {
        foreach (static::$protocols as $protocol) {
            stream_wrapper_restore($protocol);
        }
    }

    protected function shouldPreprocess($path)
    {
        return PathChecker::check($path);
    }

    protected function preprocessAndOpen($path)
    {
        return MonkeyPatchManager::patch($path);
    }

    public function stream_open($path, $mode, $options, &$openedPath)
    {
        $this->unwrap();

        MonkeyPatchManager::log('stream_open: ' . $path);

        $including = (bool) ($options & self::STREAM_OPEN_FOR_INCLUDE);
        if ($including && $this->shouldPreprocess($path)) {
            $this->resource = $this->preprocessAndOpen($path);
            $this->wrap();

            return true;
        }

        if (isset($this->context)) {
            $this->resource = fopen($path, $mode, (bool) $options, $this->context);
        } else {
            $this->resource = fopen($path, $mode, (bool) $options);
        }

        $this->wrap();

        return $this->resource !== false;
    }

    public function stream_close()
    {
        return fclose($this->resource);
    }

    public function stream_eof()
    {
        return feof($this->resource);
    }

    public function stream_flush()
    {
        return fflush($this->resource);
    }

    public function stream_read($count)
    {
        return fread($this->resource, $count);
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        return fseek($this->resource, $offset, $whence) === 0;
    }

    public function stream_stat()
    {
        $result = fstat($this->resource);
        if ($result) {
            $result[self::STAT_MTIME_ASSOC_OFFSET]++;
            $result[self::STAT_MTIME_NUMERIC_OFFSET]++;
        }

        return $result;
    }

    public function stream_tell()
    {
        return ftell($this->resource);
    }

    public function url_stat($path, $flags)
    {
        $this->unwrap();
        if ($flags & STREAM_URL_STAT_QUIET) {
            set_error_handler(
                static function (
                    int $errno,
                    string $errstr,
                    string $errfile = null,
                    int $errline = null,
                    array $errcontext = null
                ): bool {
                    return true;
                }
            );
        }

        $result = stat($path);
        if ($flags & STREAM_URL_STAT_QUIET) {
            restore_error_handler();
        }

        $this->wrap();
        if ($result) {
            $result[self::STAT_MTIME_ASSOC_OFFSET]++;
            $result[self::STAT_MTIME_NUMERIC_OFFSET]++;
        }

        return $result;
    }

    public function dir_closedir()
    {
        closedir($this->resource);

        return true;
    }

    public function dir_opendir($path, $options)
    {
        $this->unwrap();
        if (isset($this->context)) {
            $this->resource = opendir($path, $this->context);
        } else {
            $this->resource = opendir($path);
        }

        $this->wrap();

        return $this->resource !== false;
    }

    public function dir_readdir()
    {
        return readdir($this->resource);
    }

    public function dir_rewinddir()
    {
        rewinddir($this->resource);

        return true;
    }

    public function mkdir($path, $mode, $options)
    {
        $this->unwrap();
        if (isset($this->context)) {
            $result = mkdir($path, $mode, (bool) $options, $this->context);
        } else {
            $result = mkdir($path, $mode, (bool) $options);
        }

        $this->wrap();

        return $result;
    }

    public function rename($path_from, $path_to)
    {
        $this->unwrap();
        if (isset($this->context)) {
            $result = rename($path_from, $path_to, $this->context);
        } else {
            $result = rename($path_from, $path_to);
        }

        $this->wrap();

        return $result;
    }

    public function rmdir($path, $options)
    {
        $this->unwrap();
        if (isset($this->context)) {
            $result = rmdir($path, $this->context);
        } else {
            $result = rmdir($path);
        }

        $this->wrap();

        return $result;
    }

    public function stream_cast($cast_as)
    {
        return $this->resource;
    }

    public function stream_lock($operation)
    {
        if ($operation === '0' || $operation === 0) {
            $operation = LOCK_EX;
        }

        return flock($this->resource, $operation);
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->resource, $arg1);

            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->resource, $arg1, $arg2);

            case STREAM_OPTION_WRITE_BUFFER:
                return stream_set_write_buffer($this->resource, $arg1);

            case STREAM_OPTION_READ_BUFFER:
                return stream_set_read_buffer($this->resource, $arg1);
        }
    }

    public function stream_write($data)
    {
        return fwrite($this->resource, $data);
    }

    public function unlink($path)
    {
        $this->unwrap();
        if (isset($this->context)) {
            $result = unlink($path, $this->context);
        } else {
            $result = unlink($path);
        }

        $this->wrap();

        return $result;
    }

    public function stream_metadata($path, $option, $value)
    {
        $this->unwrap();

        switch ($option) {
            case STREAM_META_TOUCH:
                if (empty($value)) {
                    $result = touch($path);
                } else {
                    $result = touch($path, $value[0], $value[1]);
                }

                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                $result = chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                $result = chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                $result = chmod($path, $value);
                break;
            default:
                throw new LogicException('$result is undefined.');
        }

        $this->wrap();

        return $result;
    }

    public function stream_truncate($new_size)
    {
        return ftruncate($this->resource, $new_size);
    }
}
