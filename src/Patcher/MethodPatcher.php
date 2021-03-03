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

require_once __DIR__ . '/MethodPatcher/NodeVisitor.php';
require_once __DIR__ . '/MethodPatcher/PatchManager.php';

use Kenjis\MonkeyPatch\Patcher\MethodPatcher\NodeVisitor;

class MethodPatcher extends AbstractPatcher
{
    public const CODE = <<<'EOL'
if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) return $__ret__;
EOL;

    public const CODE_NO_RET = <<<'EOL'
if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) return;
EOL;

    public function __construct()
    {
        $this->node_visitor = new NodeVisitor();
    }
}
