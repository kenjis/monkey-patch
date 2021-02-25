<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher;

require __DIR__ . '/MethodPatcher/NodeVisitor.php';
require __DIR__ . '/MethodPatcher/PatchManager.php';

use Kenjis\MonkeyPatch\Patcher\MethodPatcher\NodeVisitor;

use function current;
use function is_array;
use function is_string;
use function key;
use function ksort;
use function next;
use function reset;
use function strpos;
use function token_get_all;

class MethodPatcher extends AbstractPatcher
{
    public const CODE = <<<'EOL'
if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) return $__ret__;
EOL;
    public const CODENORET = <<<'EOL'
if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) return;
EOL;

    public static $replacement;

    public function __construct()
    {
        $this->node_visitor = new NodeVisitor();
    }

    protected static function generateNewSource($source)
    {
        $tokens = token_get_all($source);
        $new_source = '';
        $i = -1;

        ksort(self::$replacement);
        reset(self::$replacement);
        $replacement['key'] = key(self::$replacement);
        $replacement['value'] = current(self::$replacement);
        next(self::$replacement);
        if ($replacement['key'] === null) {
            $replacement = false;
        }

        $start_method = false;

        foreach ($tokens as $key => $token) {
            $i++;

            if (isset($replacement['key']) && $i === $replacement['key']) {
                $start_method = true;
            }

            if (is_string($token)) {
                if ($start_method && $token === '{') {
                    if (self::isVoidFunction($tokens, $key)) {
                        $new_source .= '{ ' . self::CODENORET;
                    } else {
                        $new_source .= '{ ' . self::CODE;
                    }

                    $start_method = false;
                    $replacement['key'] = key(self::$replacement);
                    $replacement['value'] = current(self::$replacement);
                    next(self::$replacement);
                    if ($replacement['key'] === null) {
                        $replacement = false;
                    }
                } else {
                    $new_source .= $token;
                }
            } else {
                $new_source .= $token[1];
            }
        }

        return $new_source;
    }

    /**
     * Checks if a function has a void return type
     *
     * @param $tokens
     * @param $key
     */
    protected static function isVoidFunction($tokens, $key): bool
    {
        if ($key - 1 <= 0) {
            return false;
        }

        $token = $tokens[$key - 1];
        if (is_array($token)) {
            $token = $token[1];
        }

        // Loop backwards though the start of the function block till you either find "void"
        // or the end of the parameters declaration.
        while ($token !== ')') {
            if (strpos($token, 'void') !== false) {
                return true;
            }

            $token = $tokens[$key - 1];
            if (is_array($token)) {
                $token = $token[1];
            }

            $key--;
        }

        return false;
    }
}
