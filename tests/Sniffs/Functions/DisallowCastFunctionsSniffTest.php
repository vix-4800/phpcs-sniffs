<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Functions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Functions\DisallowCastFunctionsSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowCastFunctionsSniff.
 *
 * @internal
 */
#[CoversClass(DisallowCastFunctionsSniff::class)]
final class DisallowCastFunctionsSniffTest extends BaseTest
{
    /**
     * Test that strval() triggers a warning.
     */
    #[Test]
    public function strvalTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$var = "123";
$str = strval($var);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'strval()');
        $this->assertContainsWarning($result, '(string)');
    }

    /**
     * Test that intval() triggers a warning.
     */
    #[Test]
    public function intvalTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$var = "123";
$int = intval($var);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'intval()');
        $this->assertContainsWarning($result, '(int)');
    }

    /**
     * Test that floatval() triggers a warning.
     */
    #[Test]
    public function floatvalTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$var = "123.45";
$float = floatval($var);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'floatval()');
        $this->assertContainsWarning($result, '(float)');
    }

    /**
     * Test that boolval() triggers a warning.
     */
    #[Test]
    public function boolvalTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$var = 1;
$bool = boolval($var);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'boolval()');
        $this->assertContainsWarning($result, '(bool)');
    }

    /**
     * Test that type casts don't trigger warnings.
     */
    #[Test]
    public function typeCastsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$str = (string) $var;
$int = (int) $var;
$float = (float) $var;
$bool = (bool) $var;', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test that methods named strval, intval, etc. don't trigger warnings.
     */
    #[Test]
    public function methodNamedStrvalDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
class MyClass {
    public function strval() {
        return "string";
    }
}
$obj = new MyClass();
$result = $obj->strval();', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test that static methods named intval don't trigger warnings.
     */
    #[Test]
    public function staticMethodNamedIntvalDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
class MyClass {
    public static function intval() {
        return 42;
    }
}
$result = MyClass::intval();', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test that function declarations don't trigger warnings.
     */
    #[Test]
    public function functionDeclarationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
function strval($value) {
    return (string) $value;
}', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertNoViolations($result);
    }

    /**
     * Test case sensitivity - STRVAL, InTvAl, etc.
     */
    #[Test]
    public function caseInsensitiveFunctionNames(): void
    {
        $result = $this->runPhpcs('<?php
$str = STRVAL($var);
$int = InTvAl($var);
$float = FLOATVAL($var);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'strval()');
        $this->assertContainsWarning($result, 'intval()');
        $this->assertContainsWarning($result, 'floatval()');
    }

    /**
     * Test multiple function calls in one file.
     */
    #[Test]
    public function multipleFunctionCalls(): void
    {
        $result = $this->runPhpcs('<?php
$str = strval($var1);
$int = intval($var2);
$float = floatval($var3);
$bool = boolval($var4);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'strval()');
        $this->assertContainsWarning($result, 'intval()');
        $this->assertContainsWarning($result, 'floatval()');
        $this->assertContainsWarning($result, 'boolval()');
    }

    /**
     * Test that functions with additional parameters still trigger warnings.
     */
    #[Test]
    public function functionsWithParametersTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$int = intval($var, 16);
$float = floatval($var);', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'intval()');
        $this->assertContainsWarning($result, 'floatval()');
    }

    /**
     * Test nested function calls.
     */
    #[Test]
    public function nestedFunctionCalls(): void
    {
        $result = $this->runPhpcs('<?php
$result = strval(intval($var));', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'strval()');
        $this->assertContainsWarning($result, 'intval()');
    }

    /**
     * Test in array context.
     */
    #[Test]
    public function inArrayContext(): void
    {
        $result = $this->runPhpcs('<?php
$data = [
    "string" => strval($var),
    "int" => intval($var),
    "float" => floatval($var),
];', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'strval()');
        $this->assertContainsWarning($result, 'intval()');
        $this->assertContainsWarning($result, 'floatval()');
    }

    /**
     * Test in function arguments.
     */
    #[Test]
    public function inFunctionArguments(): void
    {
        $result = $this->runPhpcs('<?php
function test($a, $b) {}
test(strval($x), intval($y));', 'VixPHPCS.Functions.DisallowCastFunctions');

        $this->assertContainsWarning($result, 'strval()');
        $this->assertContainsWarning($result, 'intval()');
    }
}
