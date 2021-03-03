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

use Kenjis\MonkeyPatch\MonkeyPatchManager;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

abstract class AbstractPatcher
{
    protected $node_visitor;
    public static $replacement;

    public function patch(string $source): array
    {
        $patched = false;

        $parser = (new ParserFactory())
            ->create(
                MonkeyPatchManager::getPhpParser(),
                new Lexer(
                    ['usedAttributes' => ['startTokenPos', 'endTokenPos']]
                )
            );
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->node_visitor);

        $ast_orig = $parser->parse($source);
        $prettyPrinter = new PrettyPrinter\Standard();
        $source_ = $prettyPrinter->prettyPrintFile($ast_orig);

        $ast = $parser->parse($source);
        $traverser->traverse($ast);

        $new_source = $prettyPrinter->prettyPrintFile($ast);

        if ($source_ !== $new_source) {
            $patched = true;
        }

        return [
            $new_source,
            $patched,
        ];
    }
}
