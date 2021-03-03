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

require __DIR__ . '/../vendor/autoload.php';

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

require __DIR__ . '/functions/exit__.php';

const __GO_TO_ORIG__ = '__GO_TO_ORIG__';

class_alias(MonkeyPatchManager::class, 'MonkeyPatchManager');

// And you have to configure for your application
MonkeyPatchManager::init([
    // If you want debug log, set `debug` true, and optionally you can set the log file path
    'debug' => true,
    'log_file' => __DIR__ . '/../tmp/monkey-patch-debug.log',
    // PHP Parser: PREFER_PHP7, PREFER_PHP5, ONLY_PHP7, ONLY_PHP5
    'php_parser' => 'PREFER_PHP7',
    // Project root directory
    'root_dir' => __DIR__ . '/../',
    'cache_dir' => __DIR__ . '/../tmp/cache',
    // Directories to patch source files
    'include_paths' => [
        __DIR__,
        __DIR__ . '/../tests/fixture/App',
    ],
    // Excluding directories to patch
    // If you want to patch files inside paths below, you must add the directory starting with '-'
    'exclude_paths' => [],
    // All patchers you use
    'patcher_list' => [
        'ExitPatcher',
        'FunctionPatcher',
        'MethodPatcher',
        'ConstantPatcher',
    ],
    // Additional functions to patch
    'functions_to_patch' => [
        //'random_string',
    ],
    'exit_exception_classname' => ExitException::class,
]);
