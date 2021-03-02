<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher;

require __DIR__ . '/FunctionPatcher/NodeVisitor.php';
require __DIR__ . '/FunctionPatcher/Proxy.php';

use Kenjis\MonkeyPatch\Patcher\FunctionPatcher\NodeVisitor;
use LogicException;

use function array_search;
use function array_splice;
use function in_array;
use function strtolower;

class FunctionPatcher extends AbstractPatcher
{
    private static $lock_function_list = false;

    /** @var array list of function names (in lower case) which you patch */
    private static $whitelist = [
        'mt_rand',
        'rand',
        'uniqid',
        'hash_hmac',
        'md5',
        'sha1',
        'hash',
        'time',
        'microtime',
        'date',
        'function_exists',
        'header',
        'setcookie',
        // Functions that have param called by reference
        // Need to prepare method in FunctionPatcher\Proxy class
        'openssl_random_pseudo_bytes',
    ];

    /** @var array list of function names (in lower case) which can't be patched */
    private static $blacklist = [
        // Segmentation fault
        'call_user_func_array',
        'exit__',
        // Error: Only variables should be assigned by reference
        'get_instance',
        'get_config',
        'load_class',
        'get_mimes',
        '_get_validation_object',
        // has reference param
        'preg_replace',
        'preg_match',
        'preg_match_all',
        'array_unshift',
        'array_shift',
        'sscanf',
        'ksort',
        'krsort',
        'str_ireplace',
        'str_replace',
        'is_callable',
        'flock',
        'end',
        'idn_to_ascii',
        // Special functions for ci-phpunit-test
        'show_404',
        'show_error',
        'redirect',
    ];
    public static $replacement;

    public function __construct()
    {
        $this->node_visitor = new NodeVisitor();
    }

    protected static function checkLock($error_msg): void
    {
        if (self::$lock_function_list) {
            throw new LogicException($error_msg);
        }
    }

    public static function addWhitelists(array $function_list): void
    {
        self::checkLock("You can't add to whitelist after initialization");

        foreach ($function_list as $function_name) {
            self::$whitelist[] = strtolower($function_name);
        }
    }

    /**
     * @return array
     */
    public static function getFunctionWhitelist(): array
    {
        return self::$whitelist;
    }

    public static function addBlacklist($function_name): void
    {
        self::checkLock("You can't add to blacklist after initialization");

        self::$blacklist[] = strtolower($function_name);
    }

    public static function removeBlacklist($function_name): void
    {
        self::checkLock("You can't remove from blacklist after initialization");

        $key = array_search(strtolower($function_name), self::$blacklist);
        array_splice(self::$blacklist, $key, 1);
    }

    public static function lockFunctionList(): void
    {
        self::$lock_function_list = true;
    }

    /**
     * @param string $name function name
     */
    public static function isWhitelisted(string $name): bool
    {
        if (in_array(strtolower($name), self::$whitelist)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name function name
     */
    public static function isBlacklisted(string $name): bool
    {
        if (in_array(strtolower($name), self::$blacklist)) {
            return true;
        }

        return false;
    }
}
