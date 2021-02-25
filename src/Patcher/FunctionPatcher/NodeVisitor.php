<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

/**
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
    public function leaveNode(Node $node): void
    {
        if (! ($node instanceof FuncCall)) {
            return;
        }

        if (! ($node->name instanceof Name)) {
            return;
        }

        if (! $node->name->isUnqualified()) {
            return;
        }

        if (
            FunctionPatcher::isWhitelisted((string) $node->name)
            && ! FunctionPatcher::isBlacklisted((string) $node->name)
        ) {
            $replacement = new FullyQualified('\__FuncProxy__::' . (string) $node->name);

            $pos = $node->getAttribute('startTokenPos');
            FunctionPatcher::$replacement[$pos] =
                '\__FuncProxy__::' . (string) $node->name;

            $node->name = $replacement;
        }
    }
}
