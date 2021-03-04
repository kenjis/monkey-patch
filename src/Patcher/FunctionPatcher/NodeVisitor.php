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

/*
 * Copyright for Original Code
 *
 * @link       https://github.com/adri/monkey
 * @see        https://github.com/adri/monkey/blob/dfbb93ae09a2c0712f43eab7ced76d3f49989fbe/testTest.php
 */

namespace Kenjis\MonkeyPatch\Patcher\FunctionPatcher;

use Kenjis\MonkeyPatch\Patcher\FunctionPatcher;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;

class NodeVisitor extends NodeVisitorAbstract
{
    /**
     * @return array<Node>|int|Node|null
     */
    public function leaveNode(Node $node)
    {
        if (! ($node instanceof FuncCall)) {
            return null;
        }

        if (! ($node->name instanceof Name)) {
            return null;
        }

        if (! $node->name->isUnqualified()) {
            return null;
        }

        if (
            FunctionPatcher::isWhitelisted((string) $node->name)
            && ! FunctionPatcher::isBlacklisted((string) $node->name)
        ) {
            $replacement = new FullyQualified('__FuncProxy__::' . (string) $node->name);
            $node->name = $replacement;
        }

        return null;
    }
}
