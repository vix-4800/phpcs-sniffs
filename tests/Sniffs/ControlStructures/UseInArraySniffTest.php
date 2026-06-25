<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\ControlStructures\UseInArraySniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for UseInArraySniff.
 *
 * @internal
 */
#[CoversClass(UseInArraySniff::class)]
final class UseInArraySniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.ControlStructures.UseInArray';

    /**
     * Test that multiple OR comparisons with === trigger a warning.
     */
    #[Test]
    public function multipleOrComparisonsWithIdenticalTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$site_id = 1;
if ($site_id === 1 || $site_id === 2 || $site_id === 3) {
    echo "match";
}', self::SNIFF);

        $this->assertContainsWarning($result, 'in_array()');
    }

    /**
     * Test that multiple AND comparisons with !== trigger a warning.
     */
    #[Test]
    public function multipleAndComparisonsWithNotIdenticalTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$site_id = 1;
if ($site_id !== 1 && $site_id !== 2 && $site_id !== 3) {
    echo "no match";
}', self::SNIFF);

        $this->assertContainsWarning($result, '!in_array()');
    }

    /**
     * Test enum comparisons.
     */
    #[Test]
    public function enumComparisonsTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($site_id === SiteId::Chaturbate->value
    || $site_id === SiteId::StripChat->value
    || $site_id === SiteId::AdultWork->value) {
    echo "match";
}', self::SNIFF);

        $this->assertContainsWarning($result, 'in_array()');
    }

    /**
     * Test that two comparisons don't trigger warning (minimum is 3).
     */
    #[Test]
    public function twoComparisonsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($site_id === 1 || $site_id === 2) {
    echo "match";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that single comparison doesn't trigger warning.
     */
    #[Test]
    public function singleComparisonDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($site_id === 1) {
    echo "match";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that in_array usage doesn't trigger warning.
     */
    #[Test]
    public function inArrayDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
if (in_array($site_id, [1, 2, 3], true)) {
    echo "match";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that comparisons of different variables don't trigger warning.
     */
    #[Test]
    public function differentVariablesDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($var1 === 1 || $var2 === 2 || $var3 === 3) {
    echo "match";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that mixed operators don't trigger warning.
     */
    #[Test]
    public function mixedOperatorsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
if ($var === 1 || $var === 2 && $var === 3) {
    echo "match";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple comparison groups in one file.
     */
    #[Test]
    public function multipleComparisonGroups(): void
    {
        $result = $this->runPhpcs('<?php
if ($a === 1 || $a === 2 || $a === 3) {
    echo "first";
}

if ($b !== 4 && $b !== 5 && $b !== 6) {
    echo "second";
}', self::SNIFF);

        $this->assertContainsWarning($result, 'in_array()');
        $this->assertContainsWarning($result, '!in_array()');
    }

    /**
     * Test comparison with object properties.
     */
    #[Test]
    public function objectPropertyComparisons(): void
    {
        $result = $this->runPhpcs('<?php
if ($obj->prop === 1 || $obj->prop === 2 || $obj->prop === 3) {
    echo "match";
}', self::SNIFF);

        $this->assertContainsWarning($result, 'in_array()');
    }

    /**
     * Test that sequential if statements with same variable don't trigger false positives.
     * This was a bug where the sniff would continue scanning beyond statement boundaries.
     */
    #[Test]
    public function sequentialIfStatementsDoNotTriggerFalsePositives(): void
    {
        $result = $this->runPhpcs('<?php
$next = 1;
if ($next === false || $next === 1) {
    return;
}
if ($next === false || $next === 2) {
    return;
}
if ($next === false || $next === 3) {
    return;
}', self::SNIFF);

        // Each if statement has only 2 comparisons, so no warnings should be triggered
        $this->assertNoViolations($result);
    }

    /**
     * Test that sequential if statements with 3+ comparisons each do trigger warnings.
     */
    #[Test]
    public function sequentialIfStatementsWithMultipleComparisonsEach(): void
    {
        $result = $this->runPhpcs('<?php
if ($var === 1 || $var === 2 || $var === 3) {
    return;
}
if ($var === 4 || $var === 5 || $var === 6) {
    return;
}', self::SNIFF);

        // Both if statements should trigger warnings
        $this->assertContainsWarning($result, 'in_array()');
        $warningCount = mb_substr_count($result, 'in_array()');
        $this->assertGreaterThanOrEqual(2, $warningCount, 'Expected at least 2 warnings for 2 separate if statements');
    }
}
