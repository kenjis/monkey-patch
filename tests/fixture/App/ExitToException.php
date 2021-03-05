<?php

declare(strict_types=1);

namespace App;

class ExitToException
{
    public function callExit(): void
    {
        exit;
    }

    public function callDieInFunction(): void
    {
        die_test();
    }
}

function die_test(): void
{
    die('Bye!');
}
