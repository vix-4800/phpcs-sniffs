<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Arrays\RequireAscendingIntegerArrayKeysSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for RequireAscendingIntegerArrayKeysSniff.
 *
 * @internal
 */
#[CoversClass(RequireAscendingIntegerArrayKeysSniff::class)]
final class RequireAscendingIntegerArrayKeysSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Arrays.RequireAscendingIntegerArrayKeys';

    #[Test]
    public function decreasingExplicitIntegerKeyTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$items = [
    10 => "first",
    5 => "second",
    7 => "third",
];');

        $this->assertContainsWarning($result, 'Integer array key 5 must be greater than the preceding integer key 10.');
        $this->assertSame(1, mb_substr_count($result, 'Integer array key'));
    }

    #[Test]
    public function nonIncreasingExplicitIntegerKeyTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$items = [
    -2 => "first",
    -2 => "second",
];');

        $this->assertContainsWarning($result, 'Integer array key -2 must be greater than the preceding integer key -2.');
    }

    #[Test]
    public function nestedArraysAreCheckedIndependently(): void
    {
        $result = $this->runPhpcs('<?php
$items = [
    1 => [
        10 => "first",
        5 => "second",
    ],
    2 => "third",
];');

        $this->assertContainsWarning($result, 'Integer array key 5 must be greater than the preceding integer key 10.');
        $this->assertStringContainsString(' 5 | WARNING |', $result);
    }

    #[Test]
    public function ascendingIntegerKeysAndOtherKeyKindsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$items = array(
    1 => "first",
    "10" => "numeric string",
    $key => "dynamic",
    "implicit",
    5 => "second",
);
');

        $this->assertNoViolations($result);
    }
}
