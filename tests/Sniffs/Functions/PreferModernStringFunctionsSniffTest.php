<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Functions;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferModernStringFunctionsSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferModernStringFunctionsSniffTest extends BaseTest
{
    /**
     * Test that strpos() !== false triggers a warning for str_contains().
     */
    public function testStrposNotEqualsFalseSuggestsStrContains(): void
    {
        $result = $this->runPhpcs('<?php
$haystack = "hello world";
$needle = "world";
if (strpos($haystack, $needle) !== false) {
    echo "Found";
}', 'VixPHPCS.Functions.PreferModernStringFunctions');

        $this->assertContainsWarning($result, 'strpos()');
        $this->assertContainsWarning($result, 'str_contains()');
    }

    /**
     * Test that strpos() === 0 triggers a warning for str_starts_with().
     */
    public function testStrposEqualsZeroSuggestsStrStartsWith(): void
    {
        $result = $this->runPhpcs('<?php
$haystack = "hello world";
$needle = "hello";
if (strpos($haystack, $needle) === 0) {
    echo "Starts with";
}', 'VixPHPCS.Functions.PreferModernStringFunctions');

        $this->assertContainsWarning($result, 'strpos()');
        $this->assertContainsWarning($result, 'str_starts_with()');
    }

    /**
     * Test that strpos() without comparison does not trigger warning.
     */
    public function testStrposWithoutComparisonDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$haystack = "hello world";
$needle = "world";
$position = strpos($haystack, $needle);', 'VixPHPCS.Functions.PreferModernStringFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test that method calls are ignored.
     */
    public function testMethodCallsAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php
class Foo {
    public function strpos($a, $b) {
        return 0;
    }
}

$foo = new Foo();
if ($foo->strpos("hello", "h") !== false) {
    echo "test";
}', 'VixPHPCS.Functions.PreferModernStringFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test that static method calls are ignored.
     */
    public function testStaticMethodCallsAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php
class Foo {
    public static function strpos($a, $b) {
        return 0;
    }
}

if (Foo::strpos("hello", "h") !== false) {
    echo "test";
}', 'VixPHPCS.Functions.PreferModernStringFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test that mb_strpos() !== false also triggers warning.
     */
    public function testMbStrposNotEqualsFalseSuggestsStrContains(): void
    {
        $result = $this->runPhpcs('<?php
$haystack = "hello world";
$needle = "world";
if (mb_strpos($haystack, $needle) !== false) {
    echo "Found";
}', 'VixPHPCS.Functions.PreferModernStringFunctions');

        $this->assertContainsWarning($result, 'mb_strpos()');
        $this->assertContainsWarning($result, 'str_contains()');
    }
}
