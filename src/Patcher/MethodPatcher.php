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

class MethodPatcher extends AbstractPatcher
{
    public const CODE = <<<'EOL'
if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) return $__ret__;
EOL;

    public const CODE_NO_RET = <<<'EOL'
if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) return;
EOL;

    public static $replacement;

    public function __construct()
    {
        $this->node_visitor = new NodeVisitor();
    }
}
