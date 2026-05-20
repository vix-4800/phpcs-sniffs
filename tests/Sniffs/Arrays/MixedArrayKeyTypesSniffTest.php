<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Arrays;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MixedArrayKeyTypesSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class MixedArrayKeyTypesSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.Arrays.MixedArrayKeyTypes';

    public function testStringAndIntegerKeysTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    2 => "name",
];', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    public function testImplicitIntegerAndStringKeysTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    "name",
];', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    public function testStringKeysOnlyDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    "name" => "Anton",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testIntegerKeysOnlyDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    0 => "first",
    1 => "second",
    "3",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testArrowFunctionItemCountsAsImplicitIntegerKey(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    fn (): int => 2,
];', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    public function testNestedArraysDoNotAffectParentArrayKeyTypes(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    ["id" => 1],
    ["name" => 2],
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testOldArraySyntaxTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = array(
    "id" => 1,
    0 => 2,
);', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    public function testDynamicKeysDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    $dynamicKey => 1,
    "name" => 2,
];', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
