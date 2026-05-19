<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Arrays;

use VixPHPCS\Tests\BaseTest;

/**
 * @internal
 *
 * @coversNothing
 */
final class DisallowMixedArrayKeysSniffTest extends BaseTest
{
    public function testShortArrayWithMixedKeysTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "name" => "Anton",
    42,
];', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $this->assertContainsWarning($result, 'must not mix keyed and unkeyed elements');
    }

    public function testArraySyntaxWithMixedKeysTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = array(
    "name" => "Anton",
    42,
);', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $this->assertContainsWarning($result, 'must not mix keyed and unkeyed elements');
    }

    public function testAllKeyedElementsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "name" => "Anton",
    "age" => 42,
];', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $this->assertNoViolations($result);
    }

    public function testAllUnkeyedElementsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "Anton",
    42,
];', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $this->assertNoViolations($result);
    }

    public function testNestedArrayDoesNotTriggerFalsePositiveForOuterArray(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    ["name" => "Anton", 42],
    ["city" => "Berlin", 7],
];', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $warningCount = substr_count($result, 'must not mix keyed and unkeyed elements');
        $this->assertSame(2, $warningCount);
    }

    public function testArrowFunctionsDoNotMakeArrayLookKeyed(): void
    {
        $result = $this->runPhpcs('<?php
$callbacks = [
    fn (int $value) => $value * 2,
    fn (int $value) => $value + 1,
];', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $this->assertNoViolations($result);
    }

    public function testArrayUnpackIsTreatedAsUnkeyedElement(): void
    {
        $result = $this->runPhpcs('<?php
$extra = ["role" => "admin"];
$data = [
    "name" => "Anton",
    ...$extra,
];', 'VixPHPCS.Arrays.DisallowMixedArrayKeys');

        $this->assertContainsWarning($result, 'must not mix keyed and unkeyed elements');
    }
}
