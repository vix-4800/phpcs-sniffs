<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\ControlStructures\DisallowThrowInTernarySniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowThrowInTernarySniff.
 *
 * @internal
 */
#[CoversClass(DisallowThrowInTernarySniff::class)]
final class DisallowThrowInTernarySniffTest extends BaseTest
{
    /**
     * Test that throw in ternary operator triggers an error.
     */
    #[Test]
    public function throwInTernaryTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $condition ? throw new Exception("error") : "default";', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }

    /**
     * Test that throw in second part of ternary triggers an error.
     */
    #[Test]
    public function throwInSecondPartOfTernaryTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $condition ? "value" : throw new Exception("error");', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }

    /**
     * Test that throw in null coalescing operator triggers an error.
     */
    #[Test]
    public function throwInNullCoalescingTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $model ?? throw new Exception("error");', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'null coalescing');
    }

    /**
     * Test that throw in regular statement does not trigger error.
     */
    #[Test]
    public function throwInRegularStatementDoesNotTriggerError(): void
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
    #[Test]
    public function standaloneThrowDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
throw new Exception("error");', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test that throw in function body does not trigger error.
     */
    #[Test]
    public function throwInFunctionBodyDoesNotTriggerError(): void
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
    #[Test]
    public function nullCoalescingWithoutThrowDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
$result = $model ?? $default;', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertNoViolations($result);
    }

    /**
     * Test nested ternary with throw.
     */
    #[Test]
    public function nestedTernaryWithThrow(): void
    {
        $result = $this->runPhpcs('<?php
$result = $cond1 ? ($cond2 ? throw new Exception() : "b") : "c";', 'VixPHPCS.ControlStructures.DisallowThrowInTernary');

        $this->assertContainsError($result, 'ternary');
    }
}
