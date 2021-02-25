<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher;

use function is_string;
use function token_get_all;

use const T_EXIT;

class ExitPatcher
{
    public static function patch($source)
    {
        $tokens = token_get_all($source);

        $patched = false;
        $new_source = '';
        $i = -1;

        foreach ($tokens as $token) {
            $i++;
            if (is_string($token)) {
                $new_source .= $token;
            } elseif ($token[0] === T_EXIT) {
                if ($tokens[$i + 1] === ';') {
                    $new_source .= 'exit__()';
                } else {
                    $new_source .= 'exit__';
                }

                $patched = true;
            } else {
                $new_source .= $token[1];
            }
        }

        return [
            $new_source,
            $patched,
        ];
    }
}
