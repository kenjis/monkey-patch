<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\Patcher;

use Kenjis\MonkeyPatch\MonkeyPatch;
use Kenjis\MonkeyPatch\Patcher\FunctionPatcher\Proxy;
use Kenjis\MonkeyPatch\TestCase;
use Kenjis\PhpUnitHelper\ReflectionHelper;
use LogicException;

use function ob_end_clean;
use function ob_start;

class FunctionPatcherTest extends TestCase
{
    use ReflectionHelper;

    /** @var FunctionPatcher */
    private $obj;

    public function setUp(): void
    {
        $this->obj = new FunctionPatcher();
    }

    /**
     * @dataProvider provide_source
     */
    public function test_patch(string $source, string $expected): void
    {
        [$actual] = $this->obj->patch($source);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return string[][]
     */
    public function provide_source(): array
    {
        return [
            [
                <<<'EOL'
<?php
mt_rand(1, 100);
EOL
,
                <<<'EOL'
<?php

\__FuncProxy__::mt_rand(1, 100);
EOL
            ],

            [
                <<<'EOL'
<?php
exit();
EOL
,
                <<<'EOL'
<?php

exit;
EOL
            ],

            [
                <<<'EOL'
<?php
namespace Foo;
mt_rand(1, 100);
EOL
,
                <<<'EOL'
<?php

namespace Foo;

\__FuncProxy__::mt_rand(1, 100);
EOL
            ],

            [
                <<<'EOL'
<?php
namespace Foo;
Bar\mt_rand(1, 100);
EOL
,
                <<<'EOL'
<?php

namespace Foo;

Bar\mt_rand(1, 100);
EOL
            ],
        ];
    }

    /**
     * @dataProvider provide_source_blacklist
     */
    public function test_addBlacklist(string $source, string $expected): void
    {
        self::setPrivateProperty(
            'Kenjis\MonkeyPatch\Patcher\FunctionPatcher',
            'lock_function_list',
            false
        );

        FunctionPatcher::addBlacklist('mt_rand');

        [$actual] = $this->obj->patch($source);
        $this->assertEquals($expected, $actual);

        FunctionPatcher::removeBlacklist('mt_rand');

        self::setPrivateProperty(
            'Kenjis\MonkeyPatch\Patcher\FunctionPatcher',
            'lock_function_list',
            true
        );
    }

    /**
     * @return string[][]
     */
    public function provide_source_blacklist(): array
    {
        return [
            [
                <<<'EOL'
<?php
mt_rand(1, 100);
time();
EOL
,
                <<<'EOL'
<?php

mt_rand(1, 100);
\__FuncProxy__::time();
EOL
            ],
        ];
    }

    /**
     * @dataProvider provide_source_not_loaded
     */
    public function test_not_loaded_function(string $source, string $expected): void
    {
        [$actual] = $this->obj->patch($source);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return string[][]
     */
    public function provide_source_not_loaded(): array
    {
        return [
            [
                <<<'EOL'
<?php
not_loaded_func();
date(DATE_ATOM);
EOL
,
                <<<'EOL'
<?php

not_loaded_func();
\__FuncProxy__::date(DATE_ATOM);
EOL
            ],
        ];
    }

    public function test_patch_on_blacklisted_func(): void
    {
        ob_start();
        try {
            MonkeyPatch::patchFunction('redirect', null);
        } catch (LogicException $e) {
            $this->assertStringContainsString(
                "Can't patch on 'redirect'. It is in blacklist.",
                $e->getMessage()
            );
        }

        ob_end_clean();
    }

    public function test_patch_on_not_whitelisted_func(): void
    {
        ob_start();
        try {
            MonkeyPatch::patchFunction('htmlspecialchars', null);
        } catch (LogicException $e) {
            $this->assertStringContainsString(
                "Can't patch on 'htmlspecialchars'. It is not in whitelist.",
                $e->getMessage()
            );
        }

        ob_end_clean();
    }

    public function test_Proxy_checkPassedByReference(): void
    {
        ob_start();
        try {
            $array = [];
            Proxy::ksort($array);
        } catch (LogicException $e) {
            $this->assertStringContainsString(
                "Can't patch on function 'ksort'.",
                $e->getMessage()
            );
        }

        ob_end_clean();
    }

    public function test_addWhitelists(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("You can't add to whitelist after initialization");

        FunctionPatcher::addWhitelists(['mt_rand']);
    }
}
