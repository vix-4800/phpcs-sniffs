<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Yii2\PreferIdentityOverFindOneSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferIdentityOverFindOneSniff.
 *
 * @internal
 */
#[CoversClass(PreferIdentityOverFindOneSniff::class)]
final class PreferIdentityOverFindOneSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Yii2.PreferIdentityOverFindOne';

    /**
     * Test that User::findOne(Yii::$app->user->id) triggers a warning.
     */
    #[Test]
    public function findOneWithUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
class TestController
{
    public function actionProfile()
    {
        $user = User::findOne(Yii::$app->user->id);
        return $this->render("profile", ["user" => $user]);
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(['id' => Yii::$app->user->id]) triggers a warning.
     */
    #[Test]
    public function findOneWithArrayUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(["id" => Yii::$app->user->id]);', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::find()->where(['id' => Yii::$app->user->id])->one() triggers a warning.
     */
    #[Test]
    public function findWhereWithUserIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["id" => Yii::$app->user->id])->one();', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that different model names trigger warnings too.
     */
    #[Test]
    public function differentModelNamesTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$admin = Admin::findOne(Yii::$app->user->id);
$customer = Customer::findOne(Yii::$app->user->id);', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that findOne with different parameter does not trigger warning.
     */
    #[Test]
    public function findOneWithDifferentParameterDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne($id);
$user = User::findOne(123);
$user = User::findOne(["email" => $email]);', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where() with different condition does not trigger warning.
     */
    #[Test]
    public function findWhereWithDifferentConditionDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["email" => $email])->one();
$user = User::find()->where(["status" => 1])->one();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that find()->where()->all() does not trigger warning (not one()).
     */
    #[Test]
    public function findWhereAllDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$users = User::find()->where(["id" => Yii::$app->user->id])->all();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that other methods accessing Yii::$app->user->id don't trigger warning.
     */
    #[Test]
    public function otherMethodsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$posts = Post::find()->where(["user_id" => Yii::$app->user->id])->all();
$count = Comment::find()->where(["author_id" => Yii::$app->user->id])->count();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test complex chain with andWhere.
     */
    #[Test]
    public function findWhereAndWhereOneTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()
    ->where(["id" => Yii::$app->user->id])
    ->andWhere(["status" => 1])
    ->one();', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that using single quotes for array key works.
     */
    #[Test]
    public function findOneWithSingleQuotesTriggersWarning(): void
    {
        $result = $this->runPhpcs("<?php
\$user = User::findOne(['id' => Yii::\$app->user->id]);", self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(['id' => Yii::$app->user->identity->id]) triggers a warning.
     */
    #[Test]
    public function findOneWithIdentityIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(["id" => Yii::$app->user->identity->id]);', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::find()->where(['id' => Yii::$app->user->identity->id])->one() triggers a warning.
     */
    #[Test]
    public function findWhereWithIdentityIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["id" => Yii::$app->user->identity->id])->one();', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test direct User::findOne(Yii::$app->user->identity->id).
     */
    #[Test]
    public function findOneDirectIdentityIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(Yii::$app->user->identity->id);', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(Yii::$app->user->getId()) triggers a warning.
     */
    #[Test]
    public function findOneWithGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(Yii::$app->user->getId());', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(['id' => Yii::$app->user->getId()]) triggers a warning.
     */
    #[Test]
    public function findOneWithArrayGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(["id" => Yii::$app->user->getId()]);', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::find()->where(['id' => Yii::$app->user->getId()])->one() triggers a warning.
     */
    #[Test]
    public function findWhereWithGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::find()->where(["id" => Yii::$app->user->getId()])->one();', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }

    /**
     * Test that User::findOne(Yii::$app->user->identity->getId()) triggers a warning.
     */
    #[Test]
    public function findOneWithIdentityGetIdTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$user = User::findOne(Yii::$app->user->identity->getId());', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->user->identity');
    }
}
