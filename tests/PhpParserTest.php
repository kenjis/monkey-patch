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
use function assert;
use function is_array;

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
        assert($ast !== null);

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
        assert($ast !== null);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new class extends NodeVisitorAbstract {
            /**
             * @return array<Node>|int|Node|null
             */
            public function leaveNode(Node $node)
            {
                if ($node instanceof ClassMethod) {
                    assert(is_array($node->stmts));

                    $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
                    $ast = $parser->parse('<?php ' . MethodPatcher::CODE);
                    assert($ast !== null);

                    array_unshift(
                        $node->stmts,
                        $ast[0]
                    );
                }

                return null;
            }
        });
        $ast = $traverser->traverse($ast);

        $prettyPrinter = new PrettyPrinter\Standard();
        $newCode = $prettyPrinter->prettyPrintFile($ast);

        $this->assertSame($expected, $newCode);
    }
}
