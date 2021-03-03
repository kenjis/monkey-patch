<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\Patcher;

use Kenjis\MonkeyPatch\TestCase;

class ConstantPatcherTest extends TestCase
{
    /** @var ConstantPatcher */
    private $obj;

    public function setUp(): void
    {
        $this->obj = new ConstantPatcher();
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
     * @dataProvider provide_source_cannot_patch
     */
    public function test_cannot_patch(string $source, string $expected): void
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
echo ENVIRONMENT;
EOL
,
                <<<'EOL'
<?php

echo \__ConstProxy__::get('ENVIRONMENT');
EOL
            ],
        ];
    }

    /**
     * @return string[][]
     */
    public function provide_source_cannot_patch(): array
    {
        return [
            [
                <<<'EOL'
<?php
function test($a = ENVIRONMENT)
{
}
EOL
,
                <<<'EOL'
<?php

function test($a = ENVIRONMENT)
{
}
EOL
            ],
        ];
    }
}
