<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

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
}
