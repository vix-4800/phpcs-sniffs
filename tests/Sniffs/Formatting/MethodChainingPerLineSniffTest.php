<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Formatting\MethodChainingPerLineSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for MethodChainingPerLineSniff.
 *
 * @internal
 */
#[CoversClass(MethodChainingPerLineSniff::class)]
final class MethodChainingPerLineSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Formatting.MethodChainingPerLine';

    #[Test]
    public function multipleCallsOnSameLineTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

User::find()
    ->where(["id" => $model->user_id])
    ->limit(10)->all();
');

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
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function firstInlineCallBeforeMultilineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

$service->firstCall()
    ->secondCall()
    ->thirdCall();
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function inlineChainPasses(): void
    {
        $result = $this->runPhpcs('<?php

User::find()->where(["id" => $model->user_id])->all();
');

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
');

        $this->assertNoViolations($result);
    }
}
