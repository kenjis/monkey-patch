<?php

declare(strict_types=1);

/**
 * Part of ci-phpunit-test
 *
 * @link       https://github.com/kenjis/ci-phpunit-test
 */

namespace Kenjis\MonkeyPatch\Patcher;

require __DIR__ . '/ConstantPatcher/NodeVisitor.php';
require __DIR__ . '/ConstantPatcher/Proxy.php';

use Kenjis\MonkeyPatch\Patcher\ConstantPatcher\NodeVisitor;

use function in_array;
use function strtolower;

class ConstantPatcher extends AbstractPatcher
{
    /** @var string[] special constant names which we don't patch */
    private static $blacklist = [
        'true',
        'false',
        'null',
    ];

    public function __construct()
    {
        $this->node_visitor = new NodeVisitor();
    }

    /**
     * @param string $name constant name
     */
    public static function isBlacklisted(string $name): bool
    {
        if (in_array(strtolower($name), self::$blacklist)) {
            return true;
        }

        return false;
    }
}
