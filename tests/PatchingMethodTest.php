<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use App\ConstantClass1;
use App\Library\Input;
use App\Model\AuthyModel;
use App\Model\CategoryModel;
use App\PatchingMethod;
use Kenjis\MonkeyPatch\Traits\MonkeyPatchTrait;

class PatchingMethodTest extends TestCase
{
    use MonkeyPatchTrait;

    public function test_index_return_is_array(): void
    {
        MonkeyPatch::resetMethods();

        MonkeyPatch::patchMethod(
            CategoryModel::class,
            [
                'getCategoryList' => [(object) ['name' => 'Nothing']],
            ]
        );
        MonkeyPatch::verifyInvoked(
            CategoryModel::class . '::getCategoryList'
        );
        MonkeyPatch::verifyNeverInvoked(
            ConstantClass1::class . '::getConst1'
        );

        $obj = new PatchingMethod();
        $output = $obj->index();

        $this->assertStringContainsString('Nothing', $output);
    }

    public function test_index_return_is_clouser(): void
    {
        MonkeyPatch::patchMethod(
            CategoryModel::class,
            [
                'getCategoryList' =>
                static function ($arg = null) {
                    return $arg === null ? [(object) ['name' => 'Nothing']]
                        : [(object) ['name' => 'Everything']];
                },
            ]
        );
        MonkeyPatch::verifyInvokedOnce(
            CategoryModel::class . '::getCategoryList'
        );

        $obj = new PatchingMethod();
        $output = $obj->index();

        $this->assertStringContainsString('Nothing', $output);
    }

    public function test_index_return_empty_string(): void
    {
        MonkeyPatch::patchMethod(
            PatchingMethod::class,
            ['index' => '']
        );
        MonkeyPatch::verifyInvokedOnce(
            PatchingMethod::class . '::index'
        );

        $obj = new PatchingMethod();
        $output = $obj->index();

        $this->assertEquals('', $output);
    }

    public function test_auth(): void
    {
        MonkeyPatch::patchMethod(
            AuthyModel::class,
            ['login' => true]
        );
        MonkeyPatch::verifyInvoked(
            AuthyModel::class . '::login',
            ['foo', 'bar']
        );
        MonkeyPatch::verifyInvokedOnce(
            AuthyModel::class . '::login',
            ['foo', 'bar']
        );
        MonkeyPatch::verifyNeverInvoked(
            AuthyModel::class . '::login',
            ['username', 'PHS/DL1m6OMYg']
        );
        MonkeyPatch::verifyInvokedOnce(
            Input::class . '::post',
            ['id']
        );
        MonkeyPatch::verifyInvokedOnce(
            Input::class . '::post',
            ['password']
        );
        MonkeyPatch::verifyInvokedMultipleTimes(
            Input::class . '::post',
            2
        );

        $obj = new PatchingMethod();
        $output = $obj->auth();

        $this->assertStringContainsString('Okay!', $output);
    }
}
