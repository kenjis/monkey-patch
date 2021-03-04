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

namespace Kenjis\MonkeyPatch\Patcher\MethodPatcher;

use Kenjis\MonkeyPatch\Patcher\MethodPatcher;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

use function array_unshift;
use function assert;

class NodeVisitor extends NodeVisitorAbstract
{
    /**
     * @return array<Node>|int|Node|null
     */
    public function leaveNode(Node $node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        // I don't know why CODE_NO_RET is used only when return type void is stated.
        // But this conforms to the specification of ci-phpunit-test.
        if (isset($node->returnType->name) && $node->returnType->name === 'void') {
            $ast = $parser->parse('<?php ' . MethodPatcher::CODE_NO_RET);
        } elseif (isset($node->returnType->parts) && $node->returnType->parts[0] === 'void') {
            $ast = $parser->parse('<?php ' . MethodPatcher::CODE_NO_RET);
        } else {
            $ast = $parser->parse('<?php ' . MethodPatcher::CODE);
        }

        assert($ast !== null);

        if ($node->stmts !== null) {
            array_unshift(
                $node->stmts,
                $ast[0]
            );
        }

        return null;
    }
}
