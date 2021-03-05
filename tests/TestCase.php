<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use Kenjis\PhpUnitHelper\DebugHelper;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    use DebugHelper;
}
