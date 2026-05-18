<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferActiveRecordShortcutsSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferActiveRecordShortcutsSniffTest extends BaseTest
{
    /**
     * Test that find()->where()->one() triggers a warning.
     */
    public function testFindWhereOneTrigersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User extends \yii\db\ActiveRecord
{
    public static function getById($id)
    {
        return self::find()->where(["id" => $id])->one();
    }
}', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findOne() shortcut method');
    }

    /**
     * Test that find()->where()->all() triggers a warning.
     */
    public function testFindWhereAllTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class User extends \yii\db\ActiveRecord
{
    public static function getByStatus($status)
    {
        return self::find()->where(["status" => $status])->all();
    }
}', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findAll() shortcut method');
    }

    /**
     * Test that Model::find()->where()->one() triggers a warning.
     */
    public function testStaticCallWithWhereOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->where(["email" => $email])->one();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findOne() shortcut method');
    }

    /**
     * Test that $model->find()->where()->all() triggers a warning.
     */
    public function testInstanceCallWithWhereAllTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = $model->find()->where(["active" => true])->all();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertContainsWarning($result, 'Use findAll() shortcut method');
    }

    /**
     * Test that find()->where()->andWhere()->one() does NOT trigger a warning.
     * This is a complex query that cannot be replaced with findOne().
     */
    public function testComplexChainWithOneDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()
    ->where(["status" => 1])
    ->andWhere(["role" => "admin"])
    ->one();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->orderBy()->all() does NOT trigger a warning.
     * This is a complex query that cannot be replaced with findAll().
     */
    public function testChainWithOrderByAndAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::find()
    ->where(["status" => 1])
    ->orderBy("created_at DESC")
    ->all();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that findOne() does not trigger a warning.
     */
    public function testFindOneDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::findOne($id);', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that findAll() does not trigger a warning.
     */
    public function testFindAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::findAll(["status" => 1]);', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->one() without where() does not trigger a warning.
     */
    public function testFindOneWithoutWhereDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$user = User::find()->one();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->all() without where() does not trigger a warning.
     */
    public function testFindAllWithoutWhereDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$users = User::find()->all();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where() without one()/all() does not trigger a warning.
     */
    public function testFindWhereWithoutTerminatorDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$query = User::find()->where(["status" => 1]);', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->count() does not trigger a warning.
     */
    public function testFindWhereCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$count = User::find()->where(["status" => 1])->count();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->exists() does not trigger a warning.
     */
    public function testFindWhereExistsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

$exists = User::find()->where(["email" => $email])->exists();', 'VixPHPCS.Yii2.PreferActiveRecordShortcuts');

        $this->assertNoViolations($result);
    }
}
