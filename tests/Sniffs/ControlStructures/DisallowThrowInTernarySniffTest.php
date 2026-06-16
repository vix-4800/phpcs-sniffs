<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowThrowInTernarySniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowThrowInTernarySniffTest extends BaseTest
{
    /**
     * Test that throw in ternary operator triggers an error.
     */
    public function testThrowInTernaryTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $condition ? throw new Exception("error") : "default";', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }

    /**
     * Test that throw in second part of ternary triggers an error.
     */
    public function testThrowInSecondPartOfTernaryTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $condition ? "value" : throw new Exception("error");', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }

    /**
     * Test that throw in null coalescing operator triggers an error.
     */
    public function testThrowInNullCoalescingTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $model ?? throw new Exception("error");', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'null coalescing');
    }

    /**
     * Test that throw in regular statement does not trigger error.
     */
    public function testThrowInRegularStatementDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
if ($condition) {
    throw new Exception("error");
}', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test that standalone throw does not trigger error.
     */
    public function testStandaloneThrowDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
throw new Exception("error");', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test that throw in function body does not trigger error.
     */
    public function testThrowInFunctionBodyDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
function test() {
    throw new Exception("error");
}', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test that null coalescing without throw does not trigger error.
     */
    public function testNullCoalescingWithoutThrowDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $model ?? $default;', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test nested ternary with throw.
     */
    public function testNestedTernaryWithThrow(): void
    {
        $result = $this->runPhpcs('<?php
$result = $cond1 ? ($cond2 ? throw new Exception() : "b") : "c";', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }
}
