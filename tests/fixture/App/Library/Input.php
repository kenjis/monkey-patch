<?php

declare(strict_types=1);

namespace App\Library;

class Input
{
    public function post(string $key): string
    {
        if ($key === 'id') {
            return 'foo';
        }

        if ($key === 'password') {
            return 'bar';
        }

        return '';
    }
}
