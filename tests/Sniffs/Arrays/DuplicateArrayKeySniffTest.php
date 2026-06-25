<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Arrays\DuplicateArrayKeySniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DuplicateArrayKeySniff.
 *
 * @internal
 */
#[CoversClass(DuplicateArrayKeySniff::class)]
final class DuplicateArrayKeySniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.Arrays.DuplicateArrayKey';

    #[Test]
    public function duplicateStringKeysTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "host" => "primary",
    "host" => "secondary",
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key');
        $this->assertContainsError($result, "'host'");
    }

    #[Test]
    public function duplicateKeysInLongArraySyntaxTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = array(
    "host" => "primary",
    "host" => "secondary",
);', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key');
    }

    #[Test]
    public function numericStringAndIntegerKeysAreTreatedAsDuplicates(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "1" => "first",
    1 => "second",
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key 1');
    }

    #[Test]
    public function booleanAndIntegerKeysAreTreatedAsDuplicates(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    true => "first",
    1 => "second",
];', self::SNIFF);

        $this->assertContainsError($result, 'Duplicate array key 1');
    }

    #[Test]
    public function nestedArraysAreCheckedIndependently(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "outer" => [
        "inner" => 1,
        "inner" => 2,
    ],
    "inner" => 3,
];', self::SNIFF);

        $this->assertContainsError($result, "Duplicate array key 'inner'");
        $this->assertStringContainsString(' 5 | ERROR |', $result);
        $this->assertContainsError($result, 'line 4');
        $this->assertSame(1, mb_substr_count($result, 'Duplicate array key'));
    }

    #[Test]
    public function implicitKeysDoNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    "first",
    "second",
    "third",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function dynamicKeysDoNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$config = [
    $key => "first",
    $key => "second",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function distinctKeysDoNotTriggerError(): void
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
