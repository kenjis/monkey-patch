<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021 Kenji Suzuki
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/kenjis/monkey-patch
 */

namespace Kenjis\MonkeyPatch;

use PHPUnit\Framework\TestCase;

use function count;

class InvocationVerifier
{
    public static function verify(array $expected_invocations, array $invocations): void
    {
        if ($expected_invocations === []) {
            return;
        }

        foreach ($expected_invocations as $class_method => $data) {
            foreach ($data as $params_times) {
                [$expected_params, $expected_times] = $params_times;

                $invoked = isset($invocations[$class_method]);
                if ($invoked === false) {
                    $actual_times = 0;
                } elseif ($expected_params === null) {
                    $actual_times = count($invocations[$class_method]);
                } else {
                    $count = 0;
                    foreach ($invocations[$class_method] as $actual_params) {
                        if ($actual_params === $expected_params) {
                            $count++;
                        }
                    }

                    $actual_times = $count;
                }

                if ($expected_times === 0) {
                    TestCase::assertEquals(
                        $expected_times,
                        $actual_times,
                        $class_method . '() expected to be not invoked, but invoked ' . $actual_times . ' times.'
                    );
                } elseif ($expected_times === '+') {
                    TestCase::assertGreaterThanOrEqual(
                        1,
                        $actual_times,
                        $class_method . '() expected to be invoked at least one time, but invoked ' . $actual_times . ' times.'
                    );
                } else {
                    TestCase::assertEquals(
                        $expected_times,
                        $actual_times,
                        $class_method . '() expected to be invoked ' . $expected_times . ' times, but invoked ' . $actual_times . ' times.'
                    );
                }
            }
        }
    }
}
