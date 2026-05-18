<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferIsGuestOverUserIdCheckSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferIsGuestOverUserIdCheckSniffTest extends BaseTest
{
    /**
     * Test that empty(Yii::$app->user->id) triggers a warning.
     */
    public function testEmptyUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (empty(Yii::$app->user->id)) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result, 'Use Yii::$app->user->isGuest instead of empty(Yii::$app->user->id)');
    }

    /**
     * Test that !empty(Yii::$app->user->id) triggers a warning.
     */
    public function testNotEmptyUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (!empty(Yii::$app->user->id)) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result, 'Use !Yii::$app->user->isGuest instead of !empty(Yii::$app->user->id)');
    }

    /**
     * Test that Yii::$app->user->id === null triggers a warning.
     */
    public function testUserIdIdenticalToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id === null) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result, 'Use Yii::$app->user->isGuest instead of Yii::$app->user->id === null');
    }

    /**
     * Test that Yii::$app->user->id == null triggers a warning.
     */
    public function testUserIdEqualToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id == null) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result, 'Use Yii::$app->user->isGuest instead of Yii::$app->user->id == null');
    }

    /**
     * Test that Yii::$app->user->id !== null triggers a warning.
     */
    public function testUserIdNotIdenticalToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id !== null) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result, 'Use !Yii::$app->user->isGuest instead of Yii::$app->user->id !== null');
    }

    /**
     * Test that Yii::$app->user->id != null triggers a warning.
     */
    public function testUserIdNotEqualToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id != null) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result, 'Use !Yii::$app->user->isGuest instead of Yii::$app->user->id != null');
    }

    /**
     * Test with whitespace variations.
     */
    public function testWithWhitespaceVariations(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii:: $app -> user -> id === null) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result);
    }

    /**
     * Test that empty() on other variables doesn't trigger.
     */
    public function testEmptyOnOtherVariablesDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

if (empty($userId)) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertNoViolations($result);
    }

    /**
     * Test that comparisons with other properties don't trigger.
     */
    public function testOtherPropertyComparisonsDoNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

if ($user->id === null) {
    // do something
}

if (Yii::$app->user->name === null) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertNoViolations($result);
    }

    /**
     * Test that using isGuest correctly doesn't trigger.
     */
    public function testCorrectUsageDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->isGuest) {
    // do something
}

if (!Yii::$app->user->isGuest) {
    // do something
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple violations in one file.
     */
    public function testMultipleViolations(): void
    {
        $result = $this->runPhpcs('<?php

if (empty(Yii::$app->user->id)) {
    // do something
}

if (Yii::$app->user->id === null) {
    // do something else
}

if (!empty(Yii::$app->user->id)) {
    // authenticated user
}', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertContainsWarning($result);
        // Should have 3 warnings
        $warningCount = substr_count($result, 'WARNING');
        $this->assertGreaterThanOrEqual(3, $warningCount);
    }

    /**
     * Test that user->id in arithmetic doesn't trigger.
     */
    public function testUserIdInArithmeticDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

$total = Yii::$app->user->id + 10;', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertNoViolations($result);
    }

    /**
     * Test that user->id in assignments doesn't trigger.
     */
    public function testUserIdAssignmentDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

$userId = Yii::$app->user->id;', 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck');

        $this->assertNoViolations($result);
    }
}
