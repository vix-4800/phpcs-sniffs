<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Operators;

use PHPUnit\Framework\Attributes\Test;
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
    #[Test]
    public function doubleNegationTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = !!$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertContainsWarning($result, '(bool)');
        $this->assertContainsWarning($result, 'double negation');
    }

    #[Test]
    public function spacedDoubleNegationTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = ! !$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertContainsWarning($result, '(bool)');
    }

    #[Test]
    public function tripleNegationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isInactive = !!!$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function singleNegationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isInactive = !$user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function booleanCastDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = (bool) $user->active;', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function multipleDoubleNegationsTriggerMultipleWarnings(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = !!$user->active;
$hasItems = !!count($items);', 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation');

        $this->assertContainsWarning($result, 'double negation');
        $this->assertGreaterThanOrEqual(2, mb_substr_count($result, 'WARNING'));
    }
}
