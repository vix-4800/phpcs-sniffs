<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Operators;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferBooleanCastOverDoubleNegationSniff.
 *
 * @internal
 */
#[CoversNothing]
final class PreferBooleanCastOverDoubleNegationSniffTest extends BaseTest
{
    public function testDoubleNegationTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = !!$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertContainsWarning($result, '(bool)');
        $this->assertContainsWarning($result, 'double negation');
    }

    public function testSpacedDoubleNegationTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = ! !$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertContainsWarning($result, '(bool)');
    }

    public function testTripleNegationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isInactive = !!!$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertNoViolations($result);
    }

    public function testSingleNegationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isInactive = !$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertNoViolations($result);
    }

    public function testBooleanCastDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = (bool) $user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertNoViolations($result);
    }

    public function testMultipleDoubleNegationsTriggerMultipleWarnings(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = !!$user->active;
$hasItems = !!count($items);', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertContainsWarning($result, 'double negation');
        $this->assertGreaterThanOrEqual(2, mb_substr_count($result, 'WARNING'));
    }
}
