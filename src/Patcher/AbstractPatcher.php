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
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter;

use function assert;

abstract class AbstractPatcher
{
    /** @var NodeVisitorAbstract */
    protected $node_visitor;

    /** @var array<int, string> */
    public static $replacement;

    /**
     * @return array{0: string, 1: bool}
     */
    public function patch(string $source): array
    {
        $patched = false;

        $parser = (new ParserFactory())
            ->createForVersion(
                PhpVersion::fromComponents(
                    MonkeyPatchManager::getPhpVersionMajor(),
                    MonkeyPatchManager::getPhpVersionMinor()
                )
            );
        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->node_visitor);

        $ast_orig = $parser->parse($source);
        assert($ast_orig !== null);

        $prettyPrinter = new PrettyPrinter\Standard();
        $source_ = $prettyPrinter->prettyPrintFile($ast_orig);

        $ast = $parser->parse($source);
        assert($ast !== null);

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
