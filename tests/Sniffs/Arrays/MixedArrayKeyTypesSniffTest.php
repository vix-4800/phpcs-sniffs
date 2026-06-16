<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MixedArrayKeyTypesSniff.
 *
 * @internal
 */
#[CoversNothing]
final class MixedArrayKeyTypesSniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.Arrays.MixedArrayKeyTypes';

    #[Test]
    public function stringAndIntegerKeysTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    2 => "name",
];', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    #[Test]
    public function implicitIntegerAndStringKeysTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    "name",
];', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    #[Test]
    public function stringKeysOnlyDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    "name" => "Anton",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function integerKeysOnlyDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    0 => "first",
    1 => "second",
    "3",
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function arrowFunctionItemCountsAsImplicitIntegerKey(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "id" => 1,
    fn (): int => 2,
];', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    #[Test]
    public function nestedArraysDoNotAffectParentArrayKeyTypes(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    ["id" => 1],
    ["name" => 2],
];', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function oldArraySyntaxTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = array(
    "id" => 1,
    0 => 2,
);', self::SNIFF);

        $this->assertContainsWarning($result, 'Do not mix integer and string keys');
    }

    #[Test]
    public function dynamicKeysDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    $dynamicKey => 1,
    "name" => 2,
];', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
