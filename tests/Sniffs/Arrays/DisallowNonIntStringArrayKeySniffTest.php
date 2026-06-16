<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowNonIntStringArrayKeySniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowNonIntStringArrayKeySniffTest extends BaseTest
{
    public function testIntegerAndStringKeysAreAllowed(): void
    {
        $result = $this->runPhpcs('<?php
return [
    1 => "one",
    -2 => "two",
    "three" => 3,
    \'four\' => 4,
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertNoViolations($result);
    }

    public function testIntegerAndStringKeysAreAllowedInArraySyntax(): void
    {
        $result = $this->runPhpcs('<?php
return array(
    1 => "one",
    "two" => 2,
);
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertNoViolations($result);
    }

    public function testFloatKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    1.5 => "value",
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    public function testBooleanKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    true => "value",
    false => "other",
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    public function testNullKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    null => "value",
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    public function testExpressionKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$key = "dynamic";

return [
    $key => "value",
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    public function testNestedArrayValuesDoNotTriggerFalsePositives(): void
    {
        $result = $this->runPhpcs('<?php
return [
    "items" => [
        true => "bad",
    ],
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
        $this->assertSame(1, mb_substr_count($result, 'Array keys must be int or string literals'));
    }
}
