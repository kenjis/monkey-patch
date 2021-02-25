<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\Patcher;

use Kenjis\MonkeyPatch\TestCase;

class ExitPatcher_test extends TestCase
{
    /**
     * @dataProvider provide_source
     */
    public function test_die($source, $expected): void
    {
        [$actual] = ExitPatcher::patch($source);
        $this->assertEquals($expected, $actual);
    }

    public function provide_source()
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
