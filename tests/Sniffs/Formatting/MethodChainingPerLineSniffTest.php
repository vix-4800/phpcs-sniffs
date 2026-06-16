<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MethodChainingPerLineSniff.
 *
 * @internal
 */
#[CoversNothing]
final class MethodChainingPerLineSniffTest extends BaseTest
{
    #[Test]
    public function multipleCallsOnSameLineTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
    ->limit(10)->all();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertContainsError($result, 'Only one chained method call is allowed per line');
    }

    #[Test]
    public function properMultilineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
    ->select(["id"])
    ->limit(10)
    ->all();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function firstInlineCallBeforeMultilineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

$service->firstCall()
    ->secondCall()
    ->thirdCall();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function inlineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function nestedClosuresOnSameLineAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php

$names = User::find()
    ->map(function ($user) {
        return $user->profile->name;
    })
    ->all();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertNoViolations($result);
    }
}
