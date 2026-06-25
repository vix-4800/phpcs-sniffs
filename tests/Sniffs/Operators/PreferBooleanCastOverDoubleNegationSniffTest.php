<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Operators;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Operators\PreferBooleanCastOverDoubleNegationSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferBooleanCastOverDoubleNegationSniff.
 *
 * @internal
 */
#[CoversClass(PreferBooleanCastOverDoubleNegationSniff::class)]
final class PreferBooleanCastOverDoubleNegationSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Operators.PreferBooleanCastOverDoubleNegation';

    #[Test]
    public function doubleNegationTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = !!$user->active;');

        $this->assertContainsWarning($result, '(bool)');
        $this->assertContainsWarning($result, 'double negation');
    }

    #[Test]
    public function spacedDoubleNegationTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = ! !$user->active;');

        $this->assertContainsWarning($result, '(bool)');
    }

    #[Test]
    public function tripleNegationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isInactive = !!!$user->active;');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function singleNegationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isInactive = !$user->active;');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function booleanCastDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = (bool) $user->active;');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function multipleDoubleNegationsTriggerMultipleWarnings(): void
    {
        $result = $this->runPhpcs('<?php
$isActive = !!$user->active;
$hasItems = !!count($items);');

        $this->assertContainsWarning($result, 'double negation');
        $this->assertGreaterThanOrEqual(2, mb_substr_count($result, 'WARNING'));
    }
}
