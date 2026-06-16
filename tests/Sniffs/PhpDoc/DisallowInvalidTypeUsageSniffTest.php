<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowInvalidTypeUsageSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowInvalidTypeUsageSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.PhpDoc.DisallowInvalidTypeUsage';

    public function testVoidAndNeverInValueTagsTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param void $input
 * @var never $value
 * @property-read void $name
 */
final class Example
{
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use "void" in @param value types.');
        $this->assertContainsError($result, 'Do not use "never" in @var value types.');
        $this->assertContainsError($result, 'Do not use "void" in @property-read value types.');
    }

    public function testInvalidThrowsAndMixinTypesTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @throws string
 * @throws stdClass
 * @throws Exception|false
 * @mixin int
 * @mixin void
 * @mixin null
 */
final class Example
{
}', self::SNIFF);

        $this->assertContainsError($result, '@throws must reference throwable class types.');
        $this->assertContainsError($result, '@mixin must reference class-like types.');
    }

    public function testInvalidCallableParameterTypesTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var callable(void): string $callback
 * @var callable(never): string $callback
 */
function example(): void
{
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use "void" in nested PHPDoc value types.');
        $this->assertContainsError($result, 'Do not use "never" in nested PHPDoc value types.');
    }

    public function testImpossibleScalarIntersectionsTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param string&int $value
 * @param array&callable $callback
 * @param null&string $name
 */
function example($value, $callback, $name): void
{
}', self::SNIFF);

        $this->assertContainsError($result, 'Impossible intersection type "string&int" in @param.');
        $this->assertContainsError($result, 'Impossible intersection type "array&callable" in @param.');
        $this->assertContainsError($result, 'Impossible intersection type "null&string" in @param.');
    }

    public function testInvalidNestedTypesAndDuplicateShapeKeysTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var array<void> $items
 * @var array<int, void> $map
 * @var array{foo: void} $shape
 * @var array{0: string, 0: int} $duplicate
 * @var array{foo: array{bar: int, bar: string}} $nestedDuplicate
 */
function example(): void
{
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use "void" in nested PHPDoc value types.');
        $this->assertContainsError($result, 'Duplicate array shape key "0".');
        $this->assertContainsError($result, 'Duplicate array shape key "bar".');
    }

    public function testInvalidReturnAndStaticAnalyzerTagsTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return array<void>
 * @phpstan-return list<never>
 * @param-out void $output
 * @phpstan-param void $value
 * @psalm-param never $other
 */
function example(&$output, $value, $other)
{
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use "void" in nested PHPDoc value types.');
        $this->assertContainsError($result, 'Do not use "never" in nested PHPDoc value types.');
        $this->assertContainsError($result, 'Do not use "void" in @param-out value types.');
        $this->assertContainsError($result, 'Do not use "void" in @phpstan-param value types.');
        $this->assertContainsError($result, 'Do not use "never" in @psalm-param value types.');
    }

    public function testGenericInheritanceTagsTriggerNestedErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @extends Collection<void>
 * @implements IteratorAggregate<never, string>
 * @use SomeTrait<void>
 */
final class Example extends Collection implements IteratorAggregate
{
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use "void" in nested PHPDoc value types.');
        $this->assertContainsError($result, 'Do not use "never" in nested PHPDoc value types.');
    }

    public function testThrowsClassStringTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @throws class-string<RuntimeException>
 */
function example(): void
{
}', self::SNIFF);

        $this->assertContainsError($result, '@throws must reference throwable class types.');
    }

    public function testValidClassLikeTypesDoNotTriggerErrors(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param array<int, string> $items
 * @param Foo&Bar $value
 * @throws RuntimeException
 * @mixin SomeClass
 * @return void
 * @var callable(): void $voidCallback
 * @var callable(): never $neverCallback
 */
function example(array $items, $value): void
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
