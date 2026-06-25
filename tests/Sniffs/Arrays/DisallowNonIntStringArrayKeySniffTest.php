<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Arrays\DisallowNonIntStringArrayKeySniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowNonIntStringArrayKeySniff.
 *
 * @internal
 */
#[CoversClass(DisallowNonIntStringArrayKeySniff::class)]
final class DisallowNonIntStringArrayKeySniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey';

    #[Test]
    public function integerAndStringKeysAreAllowed(): void
    {
        $result = $this->runPhpcs('<?php
return [
    1 => "one",
    -2 => "two",
    "three" => 3,
    \'four\' => 4,
];
', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function integerAndStringKeysAreAllowedInArraySyntax(): void
    {
        $result = $this->runPhpcs('<?php
return array(
    1 => "one",
    "two" => 2,
);
', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function floatKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    1.5 => "value",
];
', self::SNIFF);

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    #[Test]
    public function booleanKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    true => "value",
    false => "other",
];
', self::SNIFF);

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    #[Test]
    public function nullKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    null => "value",
];
', self::SNIFF);

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    #[Test]
    public function expressionKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$key = "dynamic";

return [
    $key => "value",
];
', self::SNIFF);

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    #[Test]
    public function nestedArrayValuesDoNotTriggerFalsePositives(): void
    {
        $result = $this->runPhpcs('<?php
return [
    "items" => [
        true => "bad",
    ],
];
', self::SNIFF);

        $this->assertContainsError($result, 'Array keys must be int or string literals');
        $this->assertSame(1, mb_substr_count($result, 'Array keys must be int or string literals'));
    }
}
