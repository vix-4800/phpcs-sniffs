<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowVoidMixedWithOtherTypesSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowVoidMixedWithOtherTypesSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes';

    public function testVoidMixedWithNullTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return void|null
 */
function foo(): void
{
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in @return tag.');
    }

    public function testVoidMixedWithArrayAndNullTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return array|null|void
 */
function foo()
{
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in @return tag.');
    }

    public function testVoidMixedWithSingleTypeTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return string|void
 */
function foo()
{
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in @return tag.');
    }

    public function testVoidAloneDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return void
 */
function foo(): void
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testNoReturnTagDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param string $foo
 */
function foo(string $foo): string
{
    return $foo;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testReturnWithMultipleNonVoidTypesDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return string|int|null
 */
function foo(): string|int|null
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testReturnWithNeverDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return never
 */
function foo(): never
{
    throw new \Exception();
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
