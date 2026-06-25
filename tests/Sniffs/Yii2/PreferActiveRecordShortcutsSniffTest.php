<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Yii2\PreferActiveRecordShortcutsSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferActiveRecordShortcutsSniff.
 *
 * @internal
 */
#[CoversClass(PreferActiveRecordShortcutsSniff::class)]
final class PreferActiveRecordShortcutsSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Yii2.PreferActiveRecordShortcuts';

    /**
     * Test that find()->where()->one() triggers a warning.
     */
    #[Test]
    public function findWhereOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User extends \yii\db\ActiveRecord
{
    public static function getById($id)
    {
        return self::find()->where(["id" => $id])->one();
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Use findOne() shortcut method');
    }

    /**
     * Test that find()->where()->all() triggers a warning.
     */
    #[Test]
    public function findWhereAllTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User extends \yii\db\ActiveRecord
{
    public static function getByStatus($status)
    {
        return self::find()->where(["status" => $status])->all();
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Use findAll() shortcut method');
    }

    /**
     * Test that Model::find()->where()->one() triggers a warning.
     */
    #[Test]
    public function staticCallWithWhereOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->where(["email" => $email])->one();', self::SNIFF);

        $this->assertContainsWarning($result, 'Use findOne() shortcut method');
    }

    /**
     * Test that $model->find()->where()->all() triggers a warning.
     */
    #[Test]
    public function instanceCallWithWhereAllTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = $model->find()->where(["active" => true])->all();', self::SNIFF);

        $this->assertContainsWarning($result, 'Use findAll() shortcut method');
    }

    /**
     * Test that find()->where()->andWhere()->one() does NOT trigger a warning.
     * This is a complex query that cannot be replaced with findOne().
     */
    #[Test]
    public function complexChainWithOneDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()
    ->where(["status" => 1])
    ->andWhere(["role" => "admin"])
    ->one();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->orderBy()->all() does NOT trigger a warning.
     * This is a complex query that cannot be replaced with findAll().
     */
    #[Test]
    public function chainWithOrderByAndAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::find()
    ->where(["status" => 1])
    ->orderBy("created_at DESC")
    ->all();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that findOne() does not trigger a warning.
     */
    #[Test]
    public function findOneDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::findOne($id);', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that findAll() does not trigger a warning.
     */
    #[Test]
    public function findAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::findAll(["status" => 1]);', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->one() without where() does not trigger a warning.
     */
    #[Test]
    public function findOneWithoutWhereDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->one();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->all() without where() does not trigger a warning.
     */
    #[Test]
    public function findAllWithoutWhereDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::find()->all();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where() without one()/all() does not trigger a warning.
     */
    #[Test]
    public function findWhereWithoutTerminatorDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$query = User::find()->where(["status" => 1]);', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->count() does not trigger a warning.
     */
    #[Test]
    public function findWhereCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = User::find()->where(["status" => 1])->count();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->exists() does not trigger a warning.
     */
    #[Test]
    public function findWhereExistsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$exists = User::find()->where(["email" => $email])->exists();', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
