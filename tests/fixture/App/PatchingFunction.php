<?php

declare(strict_types=1);

namespace App;

use App\Library\CheckMicrotime;

use function bin2hex;
use function function_exists;
use function header;
use function is_string;
use function mt_rand;
use function openssl_random_pseudo_bytes;
use function setcookie;

class PatchingFunction
{
    /** @var CheckMicrotime */
    private $checkMicrotime;

    public function index(): int
    {
        return mt_rand(100, 999);
    }

    public function another(): int
    {
        return mt_rand(1, 9);
    }

    public function openssl_random_pseudo_bytes(): string
    {
        $bytes = openssl_random_pseudo_bytes(4, $cstrong);

        $hex = '';
        if (is_string($bytes)) {
            $hex = bin2hex($bytes);
        }

        $output = "$hex\n";
        $output .= "$cstrong\n";

        return $output;
    }

    public function openssl_random_pseudo_bytes_without_2nd_arg(): string
    {
        $bytes = openssl_random_pseudo_bytes(4);

        $hex = '';
        if (is_string($bytes)) {
            $hex = bin2hex($bytes);
        }

        return "$hex\n";
    }

    public function function_exists(): string
    {
        if (function_exists('random_bytes')) {
            $output = 'I use random_bytes().';
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $output = 'I use openssl_random_pseudo_bytes().';
        } elseif (function_exists('mcrypt_create_iv')) {
            $output = 'I use mcrypt_create_iv().';
        } else {
            $output = 'I use mt_rand().';
        }

        if (! function_exists('exit')) {
            $output .= ' Do you know? There is no exit() function in PHP.';
        }

        return $output;
    }

    public function scope_limitation_method(): string
    {
        if (function_exists('microtime')) {
            $output = 'I have microtime().';
        } else {
            $output = 'I don\'t have microtime().';
        }

        $output .= ' ';

        $output .= $this->checkMicrotime();

        $this->checkMicrotime = new CheckMicrotime();
        $output .= ' ';

        if ($this->checkMicrotime->exists()) {
            $output .= 'I have microtime().';
        } else {
            $output .= 'I don\'t have microtime().';
        }

        return $output;
    }

    public function scope_limitation_class(): string
    {
        if (function_exists('microtime')) {
            $output = 'I have microtime().';
        } else {
            $output = 'I don\'t have microtime().';
        }

        $output .= ' ';

        $output .= $this->checkMicrotime();

        $this->checkMicrotime = new CheckMicrotime();
        $output .= ' ';

        if ($this->checkMicrotime->exists()) {
            $output .= 'I have microtime().';
        } else {
            $output .= 'I don\'t have microtime().';
        }

        return $output;
    }

    protected function checkMicrotime(): string
    {
        if (function_exists('microtime')) {
            return 'I have microtime().';
        }

        return 'I don\'t have microtime().';
    }

    public function header(): string
    {
        header('Location: http://www.example.com/');

        return 'call header()';
    }

    public function setcookie(): string
    {
        setcookie('TestCookie', 'something from somewhere');

        return 'call setcookie()';
    }
}
