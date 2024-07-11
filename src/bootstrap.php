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

use Kenjis\MonkeyPatch\Exception\ExitException;
use Kenjis\MonkeyPatch\MonkeyPatchManager;
use Kenjis\MonkeyPatch\Patcher\ConstantPatcher\Proxy as ConstProxy;
use Kenjis\MonkeyPatch\Patcher\FunctionPatcher\Proxy as FuncProxy;
use Kenjis\MonkeyPatch\Patcher\MethodPatcher\PatchManager;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/functions/exit__.php';

const __GO_TO_ORIG__ = '__GO_TO_ORIG__';

class_alias(MonkeyPatchManager::class, 'MonkeyPatchManager');
class_alias(PatchManager::class, '__PatchManager__');
class_alias(ConstProxy::class, '__ConstProxy__');
class_alias(FuncProxy::class, '__FuncProxy__');

// And you have to configure for your application
MonkeyPatchManager::init([
    // If you want debug log, set `debug` true, and optionally you can set the log file path
    'debug' => true,
    'log_file' => __DIR__ . '/../tmp/monkey-patch-debug.log',
    // PHP version
    'php_version_major' => 7,
    'php_version_minor' => 0,
    // Project root directory
    'root_dir' => __DIR__ . '/../',
    'cache_dir' => __DIR__ . '/../tmp/cache',
    // Directories to patch source files
    'include_paths' => [
        __DIR__ . '/../tests/fixture/App',
    ],
    // Excluding directories to patch
    // If you want to patch files inside paths below, you must add the directory starting with '-'
    'exclude_paths' => [
        __DIR__,
    ],
    // All patchers you use
    'patcher_list' => [
        'ExitPatcher',
        'ConstantPatcher',
        'FunctionPatcher',
        'MethodPatcher',
    ],
    // Additional functions to patch
    'functions_to_patch' => [
        //'random_string',
    ],
    'exit_exception_classname' => ExitException::class,
]);
