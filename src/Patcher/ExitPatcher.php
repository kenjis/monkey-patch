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
