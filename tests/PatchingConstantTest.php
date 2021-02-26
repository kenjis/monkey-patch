<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use App\ConstantClass1;

class PatchingConstantTest extends TestCase
{
    public function test_patch_constant_without_class_and_method_name(): void
    {
        MonkeyPatch::patchConstant('CONST1', 'Can you see?');

        $obj = new ConstantClass1();

        $this->assertEquals('Can you see?', $obj->getConst1());
        $this->assertEquals('CONST2 value', $obj->getConst2());
    }

    public function test_patch_constant_with_class_and_method_name(): void
    {
        MonkeyPatch::patchConstant(
            'CONST1',
            'Can you see?',
            ConstantClass1::class . '::getConst1'
        );

        $obj = new ConstantClass1();

        $this->assertEquals('Can you see?', $obj->getConst1());
        $this->assertEquals('CONST1 value', $obj->getConst1_());
    }
}
