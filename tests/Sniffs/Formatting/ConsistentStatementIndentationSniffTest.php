<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Formatting\ConsistentStatementIndentationSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for ConsistentStatementIndentationSniff.
 *
 * @internal
 */
#[CoversClass(ConsistentStatementIndentationSniff::class)]
final class ConsistentStatementIndentationSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Formatting.ConsistentStatementIndentation';

    #[Test]
    public function detectsInconsistentEchoIndentation(): void
    {
        $code = '<?php
function render() {
    Modal::begin([
        "id" => "modal",
    ]);
        echo "<img src=\"test.jpg\">";
    Modal::end();
}
';
        $result = $this->runPhpcs($code);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    #[Test]
    public function allowsConsistentIndentation(): void
    {
        $code = '<?php
function render() {
    Modal::begin([
        "id" => "modal",
    ]);
    echo "<img src=\"test.jpg\">";
    Modal::end();
}
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function detectsInconsistentIfIndentation(): void
    {
        $code = '<?php
function process() {
    $a = 1;
        if ($a > 0) {
        echo "positive";
    }
    return $a;
}
';
        $result = $this->runPhpcs($code);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    #[Test]
    public function allowsNestedBlocks(): void
    {
        $code = '<?php
function process() {
    if (true) {
        echo "nested";
        if (false) {
            echo "more nested";
        }
    }
    echo "back to base";
}
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function ignoresClosuresWithDifferentLevels(): void
    {
        $code = '<?php
$callback = function () {
    echo "inside closure";
};
echo "outside closure";
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function detectsInconsistentReturnIndentation(): void
    {
        $code = '<?php
function getValue() {
    $value = calculate();
        return $value;
}
';
        $result = $this->runPhpcs($code);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    #[Test]
    public function allowsProperlyIndentedLoops(): void
    {
        $code = '<?php
function iterate() {
    foreach ($items as $item) {
        echo $item;
    }
    for ($i = 0; $i < 10; $i++) {
        echo $i;
    }
    while (true) {
        break;
    }
}
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsVariablesInsideMultiLineArrays(): void
    {
        $code = '<?php
$result = array_map(
    static fn ($item) => $item->toArray(),
    $items,
);
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsVariablesInsideShortArrays(): void
    {
        $code = '<?php
$data = [
    "key" => $value,
    "another" => $other,
];
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsReturnInsideClosure(): void
    {
        $code = '<?php
$widget = DetailView::widget(
    [
        "model" => $model,
        "attributes" => [
            [
                "attribute" => "user_id",
                "value" => static function ($data) {
                    return isset($data->user) ? $data->user->name : null;
                },
            ],
        ],
    ]
);
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsTernaryExpressionInsideArray(): void
    {
        $code = '<?php
$config = [
    "attribute" => "created_by",
    "value" => isset($model->createdBy) ? Html::a(
        $model->createdBy->shortName,
        ["user/view", "id" => $model->created_by]
    ) : null,
    "format" => "raw",
];
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsMultiLineConditionWithVariables(): void
    {
        $code = '<?php
function check($user, $config) {
    if (
        $user->name === "test"
        && $config->isEnabled()
    ) {
        return true;
    }

    return false;
}
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsFunctionArgumentsOnMultipleLines(): void
    {
        $code = '<?php
$result = someFunction(
    $firstArg,
    $secondArg,
    $thirdArg
);
$otherVar = 1;
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsNestedMultiLineArrays(): void
    {
        $code = '<?php
$config = [
    "nested" => [
        "key" => $value,
        "other" => [
            "deep" => $deepValue,
        ],
    ],
];
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function allowsArrowFunctionInArrayMap(): void
    {
        $code = '<?php
function buildMenu() {
    $items = getItems();

    return array_map(
        static fn (Item $item): array => $item->toArray(),
        $items,
    );
}
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function stillDetectsActualInconsistentIndentation(): void
    {
        $code = '<?php
function process() {
    $a = 1;
    $b = 2;
        $c = 3;
    $d = 4;
}
';
        $result = $this->runPhpcs($code);

        $this->assertContainsWarning($result, 'Statement indentation is inconsistent');
    }

    #[Test]
    public function allowsYiiWidgetPattern(): void
    {
        $code = '<?php
echo Html::a(
    Yii::t("app", "Delete"),
    ["delete", "id" => $model->id],
    [
        "class" => "btn btn-danger",
        "data" => [
            "confirm" => Yii::t("app", "Are you sure?"),
            "method" => "post",
        ],
    ]
);
';
        $result = $this->runPhpcs($code);

        $this->assertNoViolations($result);
    }
}
