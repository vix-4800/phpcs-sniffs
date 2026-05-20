<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowLogicalOperatorsSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowLogicalOperatorsSniffTest extends BaseTest
{
    /**
     * Test that and triggers a warning.
     */
    public function testAndTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($isReady and $isValid) {
    runTask();
}', 'VixPHPCS.ControlStructures.DisallowLogicalOperators');

        $this->assertContainsWarning($result, '&&');
        $this->assertContainsWarning($result, 'and');
    }

    /**
     * Test that or triggers a warning.
     */
    public function testOrTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($isFallback or $isPreview) {
    renderPage();
}', 'VixPHPCS.ControlStructures.DisallowLogicalOperators');

        $this->assertContainsWarning($result, '||');
        $this->assertContainsWarning($result, 'or');
    }

    /**
     * Test that multiple logical operators trigger multiple warnings.
     */
    public function testMultipleLogicalOperatorsTriggerMultipleWarnings(): void
    {
        $result = $this->runPhpcs('<?php
$result = $first and $second;
$result = $third or $fourth;', 'VixPHPCS.ControlStructures.DisallowLogicalOperators');

        $this->assertContainsWarning($result, '&&');
        $this->assertContainsWarning($result, '||');

        $warningCount = substr_count($result, 'Use ');
        $this->assertGreaterThanOrEqual(2, $warningCount, 'Expected at least 2 warnings for logical operators');
    }

    /**
     * Test that && and || do not trigger warnings.
     */
    public function testBooleanOperatorsDoNotTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
if (($isReady && $isValid) || $isFallback) {
    runTask();
}', 'VixPHPCS.ControlStructures.DisallowLogicalOperators');

        $this->assertNoViolations($result);
    }

    /**
     * Test that xor is ignored.
     */
    public function testXorIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php
if ($left xor $right) {
    runTask();
}', 'VixPHPCS.ControlStructures.DisallowLogicalOperators');

        $this->assertNoViolations($result);
    }
}
