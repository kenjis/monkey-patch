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

namespace Kenjis\MonkeyPatch\Patcher\ConstantPatcher;

use Kenjis\MonkeyPatch\Patcher\ConstantPatcher;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\NodeVisitorAbstract;

use function call_user_func_array;
use function is_callable;
use function ucfirst;

class NodeVisitor extends NodeVisitorAbstract
{
    /** @var int */
    private $disable_const_rewrite_level = 0;

    /**
     * @return int|Node|null
     */
    public function enterNode(Node $node)
    {
        $callback = [$this, 'before' . ucfirst($node->getType())];
        if (is_callable($callback)) {
            call_user_func_array($callback, [$node]);
        }

        return null;
    }

    /**
     * @return array<Node>|int|Node|null
     */
    public function leaveNode(Node $node)
    {
        if (! ($node instanceof ConstFetch)) {
            $callback = [$this, 'rewrite' . ucfirst($node->getType())];
            if (is_callable($callback)) {
                call_user_func_array($callback, [$node]);
            }

            return null;
        }

        if ($this->disable_const_rewrite_level > 0) {
            return null;
        }

        if (! ($node->name instanceof Name)) {
            return null;
        }

        if (! $node->name->isUnqualified()) {
            return null;
        }

        if (! ConstantPatcher::isBlacklisted((string) $node->name)) {
            $replacement = new FullyQualified('__ConstProxy__::get(\'' . (string) $node->name . '\')');
            $node->name = $replacement;
        }

        return null;
    }

    /**
     * The following logic is from:
     * <https://github.com/badoo/soft-mocks/blob/06fe26a2c9ab4cae17b88648439952ab0586438f/src/QA/SoftMocks.php#L1572>
     * Thank you.
     *
     * The MIT License (MIT)
     * Copyright (c) 2016 Badoo Development
     */
    public function beforeParam(): void
    {
        // Cannot rewrite constants that are used as default values in function arguments
        $this->disable_const_rewrite_level++;
    }

    public function rewriteParam(): void
    {
        $this->disable_const_rewrite_level--;
    }

    public function beforeConst(): void
    {
        // Cannot rewrite constants that are used as default values in constant declarations
        $this->disable_const_rewrite_level++;
    }

    public function rewriteConst(): void
    {
        $this->disable_const_rewrite_level--;
    }

    public function beforeStmt_PropertyProperty(): void
    {
        // Cannot rewrite constants that are used as default values in property declarations
        $this->disable_const_rewrite_level++;
    }

    public function rewriteStmt_PropertyProperty(): void
    {
        $this->disable_const_rewrite_level--;
    }

    public function beforeStmt_StaticVar(): void
    {
        // Cannot rewrite constants that are used as default values in static variable declarations
        $this->disable_const_rewrite_level++;
    }

    public function rewriteStmt_StaticVar(): void
    {
        $this->disable_const_rewrite_level--;
    }
}
