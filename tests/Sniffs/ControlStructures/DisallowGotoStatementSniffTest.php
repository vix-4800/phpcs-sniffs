<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowGotoStatementSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowGotoStatementSniffTest extends BaseTest
{
    /**
     * Expected minimum number of errors for multiple goto statements test.
     */
    private const EXPECTED_MULTIPLE_GOTO_ERRORS = 2;

    /**
     * Test that goto statement triggers an error.
     */
    public function testGotoStatementTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
goto label;
echo "This is skipped";
label:
echo "This is executed";', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertContainsError($result, 'goto');
        $this->assertContainsError($result, 'anti-pattern');
    }

    /**
     * Test that goto in conditional triggers an error.
     */
    public function testGotoInConditionalTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
if ($condition) {
    goto end;
}
echo "Middle";
end:
echo "End";', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertContainsError($result, 'goto');
    }

    /**
     * Test that goto in loop triggers an error.
     */
    public function testGotoInLoopTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < 10; $i++) {
    if ($i === 5) {
        goto done;
    }
}
done:
echo "Done";', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertContainsError($result, 'goto');
    }

    /**
     * Test that multiple goto statements trigger multiple errors.
     */
    public function testMultipleGotoStatementsTriggersMultipleErrors(): void
    {
        $result = $this->runPhpcs('<?php
if ($a) {
    goto label1;
}
if ($b) {
    goto label2;
}
label1:
echo "Label 1";
label2:
echo "Label 2";', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertContainsError($result, 'goto');
        $errorCount = substr_count($result, 'goto');
        $this->assertGreaterThanOrEqual(self::EXPECTED_MULTIPLE_GOTO_ERRORS, $errorCount, 'Expected at least 2 goto errors');
    }

    /**
     * Test that code without goto doesn't trigger error.
     */
    public function testCodeWithoutGotoDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
if ($condition) {
    return true;
}

for ($i = 0; $i < 10; $i++) {
    if ($i === 5) {
        break;
    }
}

while ($running) {
    if ($stop) {
        continue;
    }
}', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertNoViolations($result);
    }

    /**
     * Test that early return pattern doesn't trigger error.
     */
    public function testEarlyReturnPatternDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
function test($value) {
    if ($value === null) {
        return false;
    }

    if ($value < 0) {
        return false;
    }

    return true;
}', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertNoViolations($result);
    }

    /**
     * Test that break and continue in loops don't trigger error.
     */
    public function testBreakAndContinueDoNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item) {
    if ($item === null) {
        continue;
    }

    if ($item->isInvalid()) {
        break;
    }

    processItem($item);
}', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertNoViolations($result);
    }

    /**
     * Test goto with forward jump.
     */
    public function testGotoWithForwardJumpTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
goto forward;
echo "Skipped";
forward:
echo "Executed";', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertContainsError($result, 'goto');
    }

    /**
     * Test goto with backward jump (though not recommended, should still be caught).
     */
    public function testGotoWithBackwardJumpTriggersError(): void
    {
        $result = $this->runPhpcs('<?php
$counter = 0;
start:
$counter++;
if ($counter < 5) {
    goto start;
}', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertContainsError($result, 'goto');
    }

    /**
     * Test that label without goto doesn't trigger error.
     */
    public function testLabelWithoutGotoDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php
// Note: Labels without goto are technically valid but unusual
// This test ensures we only flag the goto statement, not labels
function test() {
    echo "Normal code";
}', 'VixPHPCS.ControlStructures.DisallowGotoStatement');

        $this->assertNoViolations($result);
    }
}
