<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MethodChainingPerLineSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class MethodChainingPerLineSniffTest extends BaseTest
{
    public function testMultipleCallsOnSameLineTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
    ->limit(10)->all();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertContainsError($result, 'Only one chained method call is allowed per line');
    }

    public function testProperMultilineChainPasses(): void
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

    public function testFirstInlineCallBeforeMultilineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

$service->firstCall()
    ->secondCall()
    ->thirdCall();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertNoViolations($result);
    }

    public function testInlineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
', 'VixPHPCS.Formatting.MethodChainingPerLine');

        $this->assertNoViolations($result);
    }

    public function testNestedClosuresOnSameLineAreIgnored(): void
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
