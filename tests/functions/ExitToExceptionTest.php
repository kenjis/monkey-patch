<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\functions;

use App\ExitToException;
use Kenjis\MonkeyPatch\Exception\ExitException;
use Kenjis\MonkeyPatch\TestCase;

class ExitToExceptionTest extends TestCase
{
    public function test_callExit(): void
    {
        try {
            $obj = new ExitToException();
            $obj->callExit();
        } catch (ExitException $e) {
            $this->assertEquals(ExitToException::class, $e->class);
            $this->assertEquals('callExit', $e->method);
            $this->assertNull($e->exit_status);
        }
    }

    public function test_callDieInFunction(): void
    {
        try {
            $obj = new ExitToException();
            $obj->callDieInFunction();
        } catch (ExitException $e) {
            $this->assertEquals('App\die_test', $e->method);
            $this->assertEquals('Bye!', $e->exit_status);
        }
    }
}
