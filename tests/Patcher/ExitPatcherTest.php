<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\Patcher;

use Kenjis\MonkeyPatch\TestCase;

class ExitPatcherTest extends TestCase
{
    /**
     * @dataProvider provide_source
     */
    public function test_die(string $source, string $expected): void
    {
        [$actual] = ExitPatcher::patch($source);
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
die();
EOL
,
                <<<'EOL'
<?php
exit__();
EOL
            ],
            [
                <<<'EOL'
<?php
die;
EOL
,
                <<<'EOL'
<?php
exit__();
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
exit__();
EOL
            ],
            [
                <<<'EOL'
<?php
exit;
EOL
,
                <<<'EOL'
<?php
exit__();
EOL
            ],
            [
                <<<'EOL'
<?php
exit('status');
EOL
,
                <<<'EOL'
<?php
exit__('status');
EOL
            ],
        ];
    }
}
