<?php

declare(strict_types=1);

namespace App;

use function define;
use function defined;

class ConstantClass1
{
    public function __construct()
    {
        if (! defined('CONST1')) {
            define('CONST1', 'CONST1 value');
        }

        if (! defined('CONST2')) {
            define('CONST2', 'CONST2 value');
        }
    }

    public function getConst1(): string
    {
        return CONST1;
    }

    public function getConst2(): string
    {
        return CONST2;
    }

    public function getConst1_(): string
    {
        return CONST1;
    }
}
