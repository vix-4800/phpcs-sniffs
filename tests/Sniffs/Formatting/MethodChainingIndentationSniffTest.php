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
    protected const string SNIFF = 'VixPHPCS.Formatting.MethodChainingIndentation';

    #[Test]
    public function firstChainedCallMustBeIndented(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
->where(["id" => $model->user_id])
    ->select(["id"])
    ->all();
');

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
');

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
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function inlineChainIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
');

        $this->assertNoViolations($result);
    }
}
