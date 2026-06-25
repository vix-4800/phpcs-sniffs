<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Yii2\PreferExistsOverCountSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferExistsOverCountSniff.
 *
 * @internal
 */
#[CoversClass(PreferExistsOverCountSniff::class)]
final class PreferExistsOverCountSniffTest extends BaseTest
{
    /**
     * Test that count() > 0 triggers a warning.
     */
    #[Test]
    public function countGreaterThanZeroTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() > 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() > 0');
    }

    /**
     * Test that count() >= 1 triggers a warning.
     */
    #[Test]
    public function countGreaterOrEqualOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() >= 1) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() >= 1');
    }

    /**
     * Test that count() != 0 triggers a warning.
     */
    #[Test]
    public function countNotEqualZeroTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() != 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() != 0');
    }

    /**
     * Test that count() !== 0 triggers a warning.
     */
    #[Test]
    public function countNotIdenticalZeroTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() !== 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() !== 0');
    }

    /**
     * Test that count() == 0 triggers a warning with negation.
     */
    #[Test]
    public function countEqualZeroTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() == 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use !...->exists() instead of count() == 0');
    }

    /**
     * Test that count() === 0 triggers a warning with negation.
     */
    #[Test]
    public function countIdenticalZeroTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() === 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use !...->exists() instead of count() === 0');
    }

    /**
     * Test that count() < 1 triggers a warning with negation.
     */
    #[Test]
    public function countLessThanOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() < 1) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use !...->exists() instead of count() < 1');
    }

    /**
     * Test that count() <= 0 triggers a warning with negation.
     */
    #[Test]
    public function countLessOrEqualZeroTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() <= 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use !...->exists() instead of count() <= 0');
    }

    /**
     * Test with full query chain.
     */
    #[Test]
    public function fullQueryChainTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (User::find()->where(["status" => 1])->count() > 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() > 0');
    }

    /**
     * Test in ternary operator.
     */
    #[Test]
    public function inTernaryOperatorTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$hasUsers = $query->count() > 0 ? "yes" : "no";', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() > 0');
    }

    /**
     * Test that count() with arguments does NOT trigger a warning.
     */
    #[Test]
    public function countWithArgumentsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = $query->count("*");', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() with different comparison does NOT trigger a warning.
     */
    #[Test]
    public function countWithDifferentComparisonDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->count() > 5) {
    // do something
}

if ($query->count() >= 10) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() without comparison does NOT trigger a warning.
     */
    #[Test]
    public function countWithoutComparisonDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = $query->count();
echo $count;', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() function (not method) does NOT trigger a warning.
     */
    #[Test]
    public function countFunctionDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (count($array) > 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test that exists() does NOT trigger a warning.
     */
    #[Test]
    public function existsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($query->exists()) {
    // do something
}

if (!$query->exists()) {
    // do something else
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test static method call does NOT trigger a warning.
     */
    #[Test]
    public function staticMethodCallDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (SomeClass::count() > 0) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple violations in one file.
     */
    #[Test]
    public function multipleViolations(): void
    {
        $result = $this->runPhpcs('<?php

if ($query1->count() > 0) {
    // do something
}

if ($query2->count() === 0) {
    // do something else
}

$result = $query3->count() >= 1 ? "found" : "not found";', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'count() > 0');
        $this->assertContainsWarning($result, 'count() === 0');
        $this->assertContainsWarning($result, 'count() >= 1');
    }

    /**
     * Test negation patterns.
     */
    #[Test]
    public function negationPatterns(): void
    {
        $result = $this->runPhpcs('<?php

// Should suggest !exists()
if ($query->count() == 0) {
    echo "no records";
}

// Should suggest exists()
if ($query->count() > 0) {
    echo "has records";
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, '!...->exists()');
        $this->assertContainsWarning($result, '...->exists()');
    }

    /**
     * Test that ->one() in if condition triggers a warning.
     */
    #[Test]
    public function oneInIfConditionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (TimeTracker::find()->where(["datetime_end" => null, "user_id" => $user->id])->one()) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ->exists() instead of ->one()');
    }

    /**
     * Test that ->one() in while condition triggers a warning.
     */
    #[Test]
    public function oneInWhileConditionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

while ($query->one()) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ->exists() instead of ->one()');
    }

    /**
     * Test that ->one() in ternary condition triggers a warning.
     */
    #[Test]
    public function oneInTernaryTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$result = $query->one() ? "found" : "not found";', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ->exists() instead of ->one()');
    }

    /**
     * Test that ->one() in logical AND triggers a warning.
     */
    #[Test]
    public function oneInLogicalAndTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if ($condition && $query->one()) {
    // do something
}', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ->exists() instead of ->one()');
    }

    /**
     * Test that ->one() used for assignment does not trigger warning.
     */
    #[Test]
    public function oneForAssignmentDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->where(["id" => 1])->one();
return $query->one();', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }
}
