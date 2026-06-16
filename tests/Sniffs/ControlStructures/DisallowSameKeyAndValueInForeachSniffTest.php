<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowSameKeyAndValueInForeachSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowSameKeyAndValueInForeachSniffTest extends BaseTest
{
    #[Test]
    public function sameKeyAndValueVariableTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => $item) {
    echo $item;
}', 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach');

        $this->assertContainsWarning($result, 'must be different variables');
    }

    #[Test]
    public function sameKeyAndReferencedValueVariableTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => &$item) {
    $item = trim($item);
}', 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach');

        $this->assertContainsWarning($result, 'must be different variables');
    }

    #[Test]
    public function differentKeyAndValueVariablesAreAllowed(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $key => $item) {
    echo $key . $item;
}', 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function foreachWithoutKeyIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item) {
    echo $item;
}', 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function complexValueExpressionIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => $item["value"]) {
    echo $item["value"];
}', 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function destructuredValueIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => [$first, $second]) {
    echo $first . $second;
}', 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach');

        $this->assertNoViolations($result);
    }
}
