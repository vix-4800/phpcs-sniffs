<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferIdentityOverFindOneSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class PreferIdentityOverFindOneSniffTest extends BaseTest
{
    /**
     * Test that User::findOne(Yii::$app->user->id) triggers a warning.
     */
    public function testFindOneWithUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
class TestController
{
    public function actionProfile()
    {
        $user = User::findOne(Yii::$app->user->id);
        return $this->render("profile", ["user" => $user]);
    }
}', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(['id' => Yii::$app->user->id]) triggers a warning.
     */
    public function testFindOneWithArrayUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(["id" => Yii::$app->user->id]);', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::find()->where(['id' => Yii::$app->user->id])->one() triggers a warning.
     */
    public function testFindWhereWithUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["id" => Yii::$app->user->id])->one();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that different model names trigger warnings too.
     */
    public function testDifferentModelNamesTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$admin = Admin::findOne(Yii::$app->user->id);
$customer = Customer::findOne(Yii::$app->user->id);', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that findOne with different parameter does not trigger warning.
     */
    public function testFindOneWithDifferentParameterDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne($id);
$user = User::findOne(123);
$user = User::findOne(["email" => $email]);', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where() with different condition does not trigger warning.
     */
    public function testFindWhereWithDifferentConditionDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["email" => $email])->one();
$user = User::find()->where(["status" => 1])->one();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->all() does not trigger warning (not one()).
     */
    public function testFindWhereAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$users = User::find()->where(["id" => Yii::$app->user->id])->all();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertNoViolations($result);
    }

    /**
     * Test that other methods accessing Yii::$app->user->id don't trigger warning.
     */
    public function testOtherMethodsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$posts = Post::find()->where(["user_id" => Yii::$app->user->id])->all();
$count = Comment::find()->where(["author_id" => Yii::$app->user->id])->count();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertNoViolations($result);
    }

    /**
     * Test complex chain with andWhere.
     */
    public function testFindWhereAndWhereOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()
    ->where(["id" => Yii::$app->user->id])
    ->andWhere(["status" => 1])
    ->one();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that using single quotes for array key works.
     */
    public function testFindOneWithSingleQuotesTriggersWarning(): void
    {
        $result = $this->runPhpcs("<?php
\$user = User::findOne(['id' => Yii::\$app->user->id]);", 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(['id' => Yii::$app->user->identity->id]) triggers a warning.
     */
    public function testFindOneWithIdentityIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(["id" => Yii::$app->user->identity->id]);', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::find()->where(['id' => Yii::$app->user->identity->id])->one() triggers a warning.
     */
    public function testFindWhereWithIdentityIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["id" => Yii::$app->user->identity->id])->one();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test direct User::findOne(Yii::$app->user->identity->id).
     */
    public function testFindOneDirectIdentityIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(Yii::$app->user->identity->id);', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(Yii::$app->user->getId()) triggers a warning.
     */
    public function testFindOneWithGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(Yii::$app->user->getId());', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(['id' => Yii::$app->user->getId()]) triggers a warning.
     */
    public function testFindOneWithArrayGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(["id" => Yii::$app->user->getId()]);', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::find()->where(['id' => Yii::$app->user->getId()])->one() triggers a warning.
     */
    public function testFindWhereWithGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["id" => Yii::$app->user->getId()])->one();', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(Yii::$app->user->identity->getId()) triggers a warning.
     */
    public function testFindOneWithIdentityGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(Yii::$app->user->identity->getId());', 'VixPHPCS.Yii2.PreferIdentityOverFindOne');

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }
}
