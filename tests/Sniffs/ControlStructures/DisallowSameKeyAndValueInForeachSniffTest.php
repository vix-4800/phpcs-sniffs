<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\ControlStructures\DisallowSameKeyAndValueInForeachSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowSameKeyAndValueInForeachSniff.
 *
 * @internal
 */
#[CoversClass(DisallowSameKeyAndValueInForeachSniff::class)]
final class DisallowSameKeyAndValueInForeachSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.ControlStructures.DisallowSameKeyAndValueInForeach';

    #[Test]
    public function sameKeyAndValueVariableTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => $item) {
    echo $item;
}');

        $this->assertContainsWarning($result, 'must be different variables');
    }

    #[Test]
    public function sameKeyAndReferencedValueVariableTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => &$item) {
    $item = trim($item);
}');

        $this->assertContainsWarning($result, 'must be different variables');
    }

    #[Test]
    public function differentKeyAndValueVariablesAreAllowed(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $key => $item) {
    echo $key . $item;
}');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function foreachWithoutKeyIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item) {
    echo $item;
}');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function complexValueExpressionIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => $item["value"]) {
    echo $item["value"];
}');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function destructuredValueIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php
foreach ($items as $item => [$first, $second]) {
    echo $first . $second;
}');

        $this->assertNoViolations($result);
    }
}
