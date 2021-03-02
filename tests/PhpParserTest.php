<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use Kenjis\MonkeyPatch\Patcher\MethodPatcher;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

use function array_unshift;

class PhpParserTest extends TestCase
{
    public function test_prettyPrintFile(): void
    {
        $code = <<<'CODE'
<?php
class Abc {
    public function test(string $foo) {
        var_dump($foo);
    }
}
CODE;
        $expected = <<<'CODE'
<?php

class Abc
{
    public function test(string $foo)
    {
        var_dump($foo);
    }
}
CODE;

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);

        $dumper = new NodeDumper();
//        echo $dumper->dump($ast) . "\n";

        $prettyPrinter = new PrettyPrinter\Standard();
        $newCode = $prettyPrinter->prettyPrintFile($ast);

        $this->assertSame($expected, $newCode);
    }

    public function test_insert_code_in_the_first_line_of_method(): void
    {
        $code = <<<'CODE'
<?php
class Abc {
    public function test(string $foo) {
        var_dump($foo);
    }
}
CODE;
        $expected = <<<'CODE'
<?php

class Abc
{
    public function test(string $foo)
    {
        if (($__ret__ = \__PatchManager__::getReturn(__CLASS__, __FUNCTION__, func_get_args())) !== __GO_TO_ORIG__) {
            return $__ret__;
        }
        var_dump($foo);
    }
}
CODE;

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract {
            public function leaveNode(Node $node): void
            {
                if ($node instanceof ClassMethod) {
                    $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
                    $ast = $parser->parse('<?php ' . MethodPatcher::CODE);

                    array_unshift(
                        $node->stmts,
                        $ast[0]
                    );
                }
            }
        });
        $ast = $traverser->traverse($ast);

        $prettyPrinter = new PrettyPrinter\Standard();
        $newCode = $prettyPrinter->prettyPrintFile($ast);

        $this->assertSame($expected, $newCode);
    }
}
