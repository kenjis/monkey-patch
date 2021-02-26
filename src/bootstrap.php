<?php

declare(strict_types=1);

use Kenjis\MonkeyPatch\MonkeyPatchManager;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

// PHP-Parser 4.x
require __DIR__ . '/MonkeyPatchManager.php';

require __DIR__ . '/IncludeStream.php';
require __DIR__ . '/PathChecker.php';
require __DIR__ . '/MonkeyPatch.php';
require __DIR__ . '/Cache.php';
require __DIR__ . '/InvocationVerifier.php';

require __DIR__ . '/functions/exit__.php';

const __GO_TO_ORIG__ = '__GO_TO_ORIG__';

class_alias(MonkeyPatchManager::class, 'MonkeyPatchManager');

// And you have to configure for your application
MonkeyPatchManager::init([
  // PHP Parser: PREFER_PHP7, PREFER_PHP5, ONLY_PHP7, ONLY_PHP5
    'php_parser' => 'PREFER_PHP7',
  // Project root directory
    'root_dir' => __DIR__ . '/../',
  // Cache directory
    'cache_dir' => __DIR__ . '/../tmp/cache',
  // Directories to patch on source files
    'include_paths' => [__DIR__],
  // Excluding directories to patch
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
    // Debug log file
    'log_file' => __DIR__ . '/../tmp/monkey-patch-debug.log',
]);
