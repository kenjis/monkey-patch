<?php

declare(strict_types=1);

namespace Kenjis\MonkeyPatch\Traits;

use Kenjis\MonkeyPatch\MonkeyPatch;
use MonkeyPatchManager;
use PHPUnit\Framework\ExpectationFailedException;

use function class_exists;

/**
 * Trait for PHPUnit TestCase class
 */
trait MonkeyPatchTrait
{
    /**
     * @after
     */
    protected function tearDownMonkeyPatch(): void
    {
        if (! class_exists('MonkeyPatchManager', false)) {
            return;
        }

        if (MonkeyPatchManager::isEnabled('FunctionPatcher')) {
            try {
                MonkeyPatch::verifyFunctionInvocations();
            } catch (ExpectationFailedException $e) {
                MonkeyPatch::resetFunctions();

                throw $e;
            }

            MonkeyPatch::resetFunctions();
        }

        if (MonkeyPatchManager::isEnabled('ConstantPatcher')) {
            MonkeyPatch::resetConstants();
        }

        if (MonkeyPatchManager::isEnabled('MethodPatcher')) {
            try {
                MonkeyPatch::verifyMethodInvocations();
            } catch (ExpectationFailedException $e) {
                MonkeyPatch::resetMethods();

                throw $e;
            }

            MonkeyPatch::resetMethods();
        }
    }
}
