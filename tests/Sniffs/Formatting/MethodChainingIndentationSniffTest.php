<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MethodChainingIndentationSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class MethodChainingIndentationSniffTest extends BaseTest
{
    public function testFirstChainedCallMustBeIndented(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
->where(["id" => $model->user_id])
    ->select(["id"])
    ->all();
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertContainsError($result, 'First chained call must be indented');
    }

    public function testSubsequentCallsMustAlign(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
      ->select(["id"])
    ->all();
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertContainsError($result, 'Chained call indentation must match');
    }

    public function testNestedStructuresKeepIndentation(): void
    {
        $result = $this->runPhpcs('<?php

function example(): array
{
    return User::find()
        ->where([
            "id" => $id,
        ])
        ->limit(10)
        ->all();
}
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertNoViolations($result);
    }

    public function testInlineChainIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertNoViolations($result);
    }
}
