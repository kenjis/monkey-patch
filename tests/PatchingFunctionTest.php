<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch;

use App\PatchingFunction;
use TypeError;

use function intval;

class PatchingFunctionTest extends TestCase
{
    /** @var PatchingFunction */
    private $obj;

    public function setUp(): void
    {
        parent::setUp();

        $this->obj = new PatchingFunction();
    }

    public function test_index_patch_mt_rand(): void
    {
        MonkeyPatch::patchFunction(
            'mt_rand',
            100,
            PatchingFunction::class
        );

        $output = $this->obj->index();

        $this->assertSame(100, $output);
    }

    public function test_index_patch_mt_rand_return_null(): void
    {
        $this->expectException(TypeError::class);

        MonkeyPatch::patchFunction(
            'mt_rand',
            null,
            PatchingFunction::class
        );

        $this->obj->index();
    }

    public function test_another_patch_mt_rand(): void
    {
        MonkeyPatch::patchFunction(
            'mt_rand',
            static function ($a, $b) {
                return intval($a . $b);
            },
            PatchingFunction::class
        );

        $output = $this->obj->another();

        $this->assertSame(19, $output);
    }

    public function test_openssl_random_pseudo_bytes(): void
    {
        MonkeyPatch::patchFunction(
            'openssl_random_pseudo_bytes',
            'aaaa',
            PatchingFunction::class
        );

        $output = $this->obj->openssl_random_pseudo_bytes();

        $this->assertEquals("61616161\n1\n", $output);
    }

    public function test_openssl_random_pseudo_bytes_null(): void
    {
        $this->expectException(TypeError::class);

        MonkeyPatch::patchFunction(
            'openssl_random_pseudo_bytes',
            null,
            PatchingFunction::class
        );

        $this->obj->openssl_random_pseudo_bytes();
    }

    public function test_openssl_random_pseudo_bytes_callable(): void
    {
        MonkeyPatch::patchFunction(
            'openssl_random_pseudo_bytes',
            static function ($int, &$crypto_strong) {
                $crypto_strong = false;

                return 'bbbb';
            },
            PatchingFunction::class
        );

        $output = $this->obj->openssl_random_pseudo_bytes();

        $this->assertEquals("62626262\n\n", $output);
    }

    public function test_openssl_random_pseudo_bytes_without_2nd_arg(): void
    {
        MonkeyPatch::patchFunction(
            'openssl_random_pseudo_bytes',
            'aaaa',
            PatchingFunction::class
        );

        $output = $this->obj->openssl_random_pseudo_bytes_without_2nd_arg();

        $this->assertEquals("61616161\n", $output);
    }

    public function test_function_exists_use_random_bytes(): void
    {
        MonkeyPatch::patchFunction(
            'function_exists',
            static function ($function) {
                if ($function === 'random_bytes') {
                    return true;
                }

                if ($function === 'openssl_random_pseudo_bytes') {
                    return false;
                }

                if ($function === 'mcrypt_create_iv') {
                    return false;
                }

                return __GO_TO_ORIG__;
            },
            PatchingFunction::class
        );

        MonkeyPatch::verifyInvokedOnce('function_exists', ['random_bytes']);
        MonkeyPatch::verifyInvokedOnce('function_exists', ['exit']);
        MonkeyPatch::verifyInvokedMultipleTimes('function_exists', 2);
        MonkeyPatch::verifyNeverInvoked('function_exists', ['openssl_random_pseudo_bytes']);
        MonkeyPatch::verifyNeverInvoked('function_exists', ['mcrypt_create_iv']);

        $output = $this->obj->function_exists();

        $this->assertStringContainsString('I use random_bytes().', $output);
        $this->assertStringContainsString('Do you know? There is no exit() function in PHP.', $output);
    }

    public function test_function_exists_use_openssl_random_pseudo_bytes(): void
    {
        MonkeyPatch::patchFunction(
            'function_exists',
            static function ($function) {
                if ($function === 'random_bytes') {
                    return false;
                }

                if ($function === 'openssl_random_pseudo_bytes') {
                    return true;
                }

                if ($function === 'mcrypt_create_iv') {
                    return false;
                }

                return __GO_TO_ORIG__;
            },
            PatchingFunction::class
        );

        $output = $this->obj->function_exists();

        $this->assertStringContainsString('I use openssl_random_pseudo_bytes().', $output);
        $this->assertStringContainsString('Do you know? There is no exit() function in PHP.', $output);
    }

    public function test_function_exists_use_mcrypt_create_iv(): void
    {
        MonkeyPatch::patchFunction(
            'function_exists',
            static function ($function) {
                if ($function === 'random_bytes') {
                    return false;
                }

                if ($function === 'openssl_random_pseudo_bytes') {
                    return false;
                }

                if ($function === 'mcrypt_create_iv') {
                    return true;
                }

                return __GO_TO_ORIG__;
            },
            PatchingFunction::class
        );

        $output = $this->obj->function_exists();

        $this->assertStringContainsString('I use mcrypt_create_iv().', $output);
        $this->assertStringContainsString('Do you know? There is no exit() function in PHP.', $output);
    }

    public function test_scope_limitation_method(): void
    {
        MonkeyPatch::patchFunction(
            'function_exists',
            false,
            PatchingFunction::class . '::scope_limitation_method'
        );

        $output = $this->obj->scope_limitation_method();

        $this->assertEquals(
            "I don't have microtime(). I have microtime(). I have microtime().",
            $output
        );
    }

    public function test_scope_limitation_class(): void
    {
        MonkeyPatch::patchFunction(
            'function_exists',
            false,
            PatchingFunction::class
        );

        $output = $this->obj->scope_limitation_method();

        $this->assertEquals(
            "I don't have microtime(). I don't have microtime(). I have microtime().",
            $output
        );
    }

    public function test_header(): void
    {
        MonkeyPatch::patchFunction(
            'header',
            null,
            PatchingFunction::class . '::header'
        );

        $output = $this->obj->header();

        MonkeyPatch::verifyInvokedOnce(
            'header',
            ['Location: http://www.example.com/']
        );
        $this->assertEquals('call header()', $output);
    }

    public function test_setcookie(): void
    {
        MonkeyPatch::patchFunction(
            'setcookie',
            true,
            PatchingFunction::class . '::setcookie'
        );

        $output = $this->obj->setcookie();

        MonkeyPatch::verifyInvokedOnce(
            'setcookie',
            ['TestCookie', 'something from somewhere']
        );
        $this->assertEquals('call setcookie()', $output);
    }
}
