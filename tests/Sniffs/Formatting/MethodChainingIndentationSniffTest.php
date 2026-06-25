<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Formatting\MethodChainingIndentationSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MethodChainingIndentationSniff.
 *
 * @internal
 */
#[CoversClass(MethodChainingIndentationSniff::class)]
final class MethodChainingIndentationSniffTest extends BaseTest
{
    #[Test]
    public function firstChainedCallMustBeIndented(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
->where(["id" => $model->user_id])
    ->select(["id"])
    ->all();
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertContainsError($result, 'First chained call must be indented');
    }

    #[Test]
    public function subsequentCallsMustAlign(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
      ->select(["id"])
    ->all();
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertContainsError($result, 'Chained call indentation must match');
    }

    #[Test]
    public function nestedStructuresKeepIndentation(): void
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

    #[Test]
    public function inlineChainIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
', 'VixPHPCS.Formatting.MethodChainingIndentation');

        $this->assertNoViolations($result);
    }
}
