<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Yii2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Yii2\DisallowResponseFormatAssignmentSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowResponseFormatAssignmentSniff.
 *
 * @internal
 */
#[CoversClass(DisallowResponseFormatAssignmentSniff::class)]
final class DisallowResponseFormatAssignmentSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Yii2.DisallowResponseFormatAssignment';

    /**
     * Test that direct assignment to Yii::$app->response->format with JSON format triggers a warning.
     */
    #[Test]
    public function responseFormatAssignmentTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

class TestController
{
    public function actionTest()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ["status" => "ok"];
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that direct assignment with XML constant triggers a warning.
     */
    #[Test]
    public function responseFormatWithDifferentConstantTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

Yii::$app->response->format = Response::FORMAT_XML;', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that direct assignment with string literal triggers a warning.
     */
    #[Test]
    public function responseFormatWithStringLiteralTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
Yii::$app->response->format = "json";', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that using $this->asJson() does not trigger warning.
     */
    #[Test]
    public function asJsonMethodDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
class TestController
{
    public function actionTest()
    {
        return $this->asJson(["status" => "ok"]);
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that reading Yii::$app->response->format does not trigger warning.
     */
    #[Test]
    public function readingResponseFormatDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$format = Yii::$app->response->format;
if (Yii::$app->response->format === "json") {
    echo "JSON format";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that other Yii::$app->response properties do not trigger warning.
     */
    #[Test]
    public function otherResponsePropertiesDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
Yii::$app->response->statusCode = 404;
Yii::$app->response->headers->set("X-Custom", "value");', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that assignment with whitespace and newlines triggers warning.
     */
    #[Test]
    public function responseFormatWithWhitespaceTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

Yii :: $app -> response -> format = Response::FORMAT_JSON;', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
    }

    /**
     * Test that variable or method named Yii does not trigger warning.
     */
    #[Test]
    public function variableNamedYiiDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$Yii = new stdClass();
$Yii->app->response->format = "json";

class Test {
    public function Yii() {
        return null;
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple violations in the same file.
     */
    #[Test]
    public function multipleViolations(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

class TestController
{
    public function action1()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return ["status" => "ok"];
    }

    public function action2()
    {
        Yii::$app->response->format = Response::FORMAT_XML;
        return ["status" => "ok"];
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Yii::$app->response->format');
        // Should contain warnings for both assignments
        $warningCount = mb_substr_count($result, 'Yii::$app->response->format');
        $this->assertGreaterThanOrEqual(2, $warningCount);
    }

    /**
     * Test that other formats (non-JSON/XML) do not trigger warning.
     */
    #[Test]
    public function otherFormatsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
use yii\web\Response;

class TestController
{
    public function actionRaw()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        return "plain text";
    }

    public function actionHtml()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return "<html></html>";
    }

    public function actionCustom()
    {
        Yii::$app->response->format = "custom";
        return $data;
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
