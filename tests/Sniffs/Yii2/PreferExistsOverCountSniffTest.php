<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferExistsOverCountSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferExistsOverCountSniffTest extends BaseTest
{
    /**
     * Test that count() > 0 triggers a warning.
     */
    public function testCountGreaterThanZeroTriggersWarning(): void
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
    public function testCountGreaterOrEqualOneTriggersWarning(): void
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
    public function testCountNotEqualZeroTriggersWarning(): void
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
    public function testCountNotIdenticalZeroTriggersWarning(): void
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
    public function testCountEqualZeroTriggersWarning(): void
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
    public function testCountIdenticalZeroTriggersWarning(): void
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
    public function testCountLessThanOneTriggersWarning(): void
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
    public function testCountLessOrEqualZeroTriggersWarning(): void
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
    public function testFullQueryChainTriggersWarning(): void
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
    public function testInTernaryOperatorTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$hasUsers = $query->count() > 0 ? "yes" : "no";', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ...->exists() instead of count() > 0');
    }

    /**
     * Test that count() with arguments does NOT trigger a warning.
     */
    public function testCountWithArgumentsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = $query->count("*");', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() with different comparison does NOT trigger a warning.
     */
    public function testCountWithDifferentComparisonDoesNotTriggerWarning(): void
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
    public function testCountWithoutComparisonDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = $query->count();
echo $count;', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() function (not method) does NOT trigger a warning.
     */
    public function testCountFunctionDoesNotTriggerWarning(): void
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
    public function testExistsDoesNotTriggerWarning(): void
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
    public function testStaticMethodCallDoesNotTriggerWarning(): void
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
    public function testMultipleViolations(): void
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
    public function testNegationPatterns(): void
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
    public function testOneInIfConditionTriggersWarning(): void
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
    public function testOneInWhileConditionTriggersWarning(): void
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
    public function testOneInTernaryTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$result = $query->one() ? "found" : "not found";', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertContainsWarning($result, 'Use ->exists() instead of ->one()');
    }

    /**
     * Test that ->one() in logical AND triggers a warning.
     */
    public function testOneInLogicalAndTriggersWarning(): void
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
    public function testOneForAssignmentDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->where(["id" => 1])->one();
return $query->one();', 'VixPHPCS.Yii2.PreferExistsOverCount');

        $this->assertNoViolations($result);
    }
}
