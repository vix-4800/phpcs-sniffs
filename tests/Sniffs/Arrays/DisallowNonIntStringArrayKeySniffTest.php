<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Arrays;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowNonIntStringArrayKeySniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowNonIntStringArrayKeySniffTest extends BaseTest
{
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
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

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
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function floatKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    1.5 => "value",
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

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
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
    }

    #[Test]
    public function nullKeyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
return [
    null => "value",
];
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

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
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

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
', 'VixPHPCS.Arrays.DisallowNonIntStringArrayKey');

        $this->assertContainsError($result, 'Array keys must be int or string literals');
        $this->assertSame(1, mb_substr_count($result, 'Array keys must be int or string literals'));
    }
}
