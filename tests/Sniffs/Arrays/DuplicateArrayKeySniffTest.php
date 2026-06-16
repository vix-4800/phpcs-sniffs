<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DuplicateArrayKeySniff.
 *
 * @internal
 */
#[CoversNothing]
final class DuplicateArrayKeySniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.Arrays.DuplicateArrayKey';

    public function testDuplicateStringKeysTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "host" => "primary",
    "host" => "secondary",
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key');
        $this->assertContainsError($result, '\'host\'');
    }

    public function testDuplicateKeysInLongArraySyntaxTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = array(
    "host" => "primary",
    "host" => "secondary",
);', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key');
    }

    public function testNumericStringAndIntegerKeysAreTreatedAsDuplicates(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "1" => "first",
    1 => "second",
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key 1');
    }

    public function testBooleanAndIntegerKeysAreTreatedAsDuplicates(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    true => "first",
    1 => "second",
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key 1');
    }

    public function testNestedArraysAreCheckedIndependently(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "outer" => [
        "inner" => 1,
        "inner" => 2,
    ],
    "inner" => 3,
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key \'inner\'');
        $this->assertStringContainsString(' 5 | ERROR |', $result);
        $this->assertContainsError($result, 'line 4');
        $this->assertSame(1, mb_substr_count($result, 'Duplicate array key'));
    }

    public function testImplicitKeysDoNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "first",
    "second",
    "third",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDynamicKeysDoNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    $key => "first",
    $key => "second",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testDistinctKeysDoNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "host" => "primary",
    "port" => 443,
    "scheme" => "https",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
