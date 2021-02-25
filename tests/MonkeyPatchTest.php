<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use PHPUnit\Framework\TestCase;

class MonkeyPatchTest extends TestCase
{
    /** @var MonkeyPatch */
    protected $monkeyPatch;

    protected function setUp(): void
    {
        $this->monkeyPatch = new MonkeyPatch();
    }

    public function testIsInstanceOfMonkeyPatch(): void
    {
        $actual = $this->monkeyPatch;
        $this->assertInstanceOf(MonkeyPatch::class, $actual);
    }
}
