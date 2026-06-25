<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\PhpDoc\DisallowVoidMixedWithOtherTypesSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowVoidMixedWithOtherTypesSniff.
 *
 * @internal
 */
#[CoversClass(DisallowVoidMixedWithOtherTypesSniff::class)]
final class DisallowVoidMixedWithOtherTypesSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.PhpDoc.DisallowVoidMixedWithOtherTypes';

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

    #[Test]
    public function varCallableReturnVoidMixedWithNullTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var callable(): void|null $callback
 */
$callback = static function (): void {
};', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in callable PHPDoc.');
    }

    #[Test]
    public function paramCallableReturnVoidMixedWithTypesTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param callable(): void|int|string $callback
 */
function foo(callable $callback): void
{
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in callable PHPDoc.');
    }

    #[Test]
    public function returnCallableReturnVoidMixedWithNullTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return callable(): void|null
 */
function foo(): callable
{
    return static function (): void {
    };
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in callable PHPDoc.');
    }

    #[Test]
    public function propertyCallableReturnVoidMixedWithTypesTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @property callable(): void|int $callback
 */
final class Foo
{
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in callable PHPDoc.');
    }

    #[Test]
    public function nestedCallableReturnVoidMixedWithNullTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return array<callable(): void|null>
 */
function foo(): array
{
    return [];
}', self::SNIFF);

        $this->assertContainsError($result, '"void" cannot be combined with other return types in callable PHPDoc.');
    }

    #[Test]
    public function callableReturnVoidAloneDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var callable(): void $callback
 * @param callable(): void $handler
 * @property callable(): void $property
 * @return array<callable(): void>
 */
function foo(callable $handler): void
{
    $callback = $handler;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
