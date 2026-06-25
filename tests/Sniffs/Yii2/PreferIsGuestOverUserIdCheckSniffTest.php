<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Yii2\PreferIsGuestOverUserIdCheckSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferIsGuestOverUserIdCheckSniff.
 *
 * @internal
 */
#[CoversClass(PreferIsGuestOverUserIdCheckSniff::class)]
final class PreferIsGuestOverUserIdCheckSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Yii2.PreferIsGuestOverUserIdCheck';

    /**
     * Test that empty(Yii::$app->user->id) triggers a warning.
     */
    #[Test]
    public function emptyUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (empty(Yii::$app->user->id)) {
    // do something
}');

        $this->assertContainsWarning($result, 'Use Yii::$app->user->isGuest instead of empty(Yii::$app->user->id)');
    }

    /**
     * Test that !empty(Yii::$app->user->id) triggers a warning.
     */
    #[Test]
    public function notEmptyUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (!empty(Yii::$app->user->id)) {
    // do something
}');

        $this->assertContainsWarning($result, 'Use !Yii::$app->user->isGuest instead of !empty(Yii::$app->user->id)');
    }

    /**
     * Test that Yii::$app->user->id === null triggers a warning.
     */
    #[Test]
    public function userIdIdenticalToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id === null) {
    // do something
}');

        $this->assertContainsWarning($result, 'Use Yii::$app->user->isGuest instead of Yii::$app->user->id === null');
    }

    /**
     * Test that Yii::$app->user->id == null triggers a warning.
     */
    #[Test]
    public function userIdEqualToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id == null) {
    // do something
}');

        $this->assertContainsWarning($result, 'Use Yii::$app->user->isGuest instead of Yii::$app->user->id == null');
    }

    /**
     * Test that Yii::$app->user->id !== null triggers a warning.
     */
    #[Test]
    public function userIdNotIdenticalToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id !== null) {
    // do something
}');

        $this->assertContainsWarning($result, 'Use !Yii::$app->user->isGuest instead of Yii::$app->user->id !== null');
    }

    /**
     * Test that Yii::$app->user->id != null triggers a warning.
     */
    #[Test]
    public function userIdNotEqualToNullTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->id != null) {
    // do something
}');

        $this->assertContainsWarning($result, 'Use !Yii::$app->user->isGuest instead of Yii::$app->user->id != null');
    }

    /**
     * Test with whitespace variations.
     */
    #[Test]
    public function withWhitespaceVariations(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii:: $app -> user -> id === null) {
    // do something
}');

        $this->assertContainsWarning($result);
    }

    /**
     * Test that empty() on other variables doesn't trigger.
     */
    #[Test]
    public function emptyOnOtherVariablesDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

if (empty($userId)) {
    // do something
}');

        $this->assertNoViolations($result);
    }

    /**
     * Test that comparisons with other properties don't trigger.
     */
    #[Test]
    public function otherPropertyComparisonsDoNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

if ($user->id === null) {
    // do something
}

if (Yii::$app->user->name === null) {
    // do something
}');

        $this->assertNoViolations($result);
    }

    /**
     * Test that using isGuest correctly doesn't trigger.
     */
    #[Test]
    public function correctUsageDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

if (Yii::$app->user->isGuest) {
    // do something
}

if (!Yii::$app->user->isGuest) {
    // do something
}');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple violations in one file.
     */
    #[Test]
    public function multipleViolations(): void
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
}');

        $this->assertContainsWarning($result);
        // Should have 3 warnings
        $warningCount = mb_substr_count($result, 'WARNING');
        $this->assertGreaterThanOrEqual(3, $warningCount);
    }

    /**
     * Test that user->id in arithmetic doesn't trigger.
     */
    #[Test]
    public function userIdInArithmeticDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

$total = Yii::$app->user->id + 10;');

        $this->assertNoViolations($result);
    }

    /**
     * Test that user->id in assignments doesn't trigger.
     */
    #[Test]
    public function userIdAssignmentDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php

$userId = Yii::$app->user->id;');

        $this->assertNoViolations($result);
    }
}
