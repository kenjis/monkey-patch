<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher\MethodPatcher;

use Kenjis\MonkeyPatch\Patcher\MethodPatcher;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

class NodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): void
    {
        if (! ($node instanceof ClassMethod)) {
            return;
        }

        $pos = $node->getAttribute('startTokenPos');
        MethodPatcher::$replacement[$pos] = true;
    }
}
