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

require_once __DIR__ . '/ConstantPatcher/NodeVisitor.php';
require_once __DIR__ . '/ConstantPatcher/Proxy.php';

use Kenjis\MonkeyPatch\Patcher\ConstantPatcher\NodeVisitor;

use function in_array;
use function strtolower;

class ConstantPatcher extends AbstractPatcher
{
    /** @var string[] special constant names which we don't patch */
    private static $blacklist = [
        'true',
        'false',
        'null',
    ];

    public function __construct()
    {
        $this->node_visitor = new NodeVisitor();
    }

    /**
     * @param string $name constant name
     */
    public static function isBlacklisted(string $name): bool
    {
        if (in_array(strtolower($name), self::$blacklist)) {
            return true;
        }

        return false;
    }
}
