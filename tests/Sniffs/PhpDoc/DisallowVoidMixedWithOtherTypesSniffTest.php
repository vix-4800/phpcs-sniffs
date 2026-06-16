<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowVoidMixedWithOtherTypesSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowVoidMixedWithOtherTypesSniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes';

    #[Test]
    public function voidMixedWithNullTriggersError(): void
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

    #[Test]
    public function voidMixedWithArrayAndNullTriggersError(): void
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

    #[Test]
    public function voidMixedWithSingleTypeTriggersError(): void
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

    #[Test]
    public function voidAloneDoesNotTriggerError(): void
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

    #[Test]
    public function noReturnTagDoesNotTriggerError(): void
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

    #[Test]
    public function returnWithMultipleNonVoidTypesDoesNotTriggerError(): void
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

    #[Test]
    public function returnWithNeverDoesNotTriggerError(): void
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
