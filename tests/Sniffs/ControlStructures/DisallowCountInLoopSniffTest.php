<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowCountInLoopSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowCountInLoopSniffTest extends BaseTest
{
    /**
     * Expected minimum number of warnings for multiple for loops test.
     */
    private const EXPECTED_MULTIPLE_WARNINGS = 2;

    /**
     * Test that count() in for loop condition triggers a warning.
     */
    public function testCountInForLoopConditionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3, 4, 5];
for ($i = 0; $i < count($array); $i++) {
    echo $array[$i];
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test that count() with complex expressions in for loop triggers a warning.
     */
    public function testCountWithComplexExpressionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < count($this->items); $i++) {
    echo "item";
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test that count() with array access in for loop triggers a warning.
     */
    public function testCountWithArrayAccessTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < count($data["items"]); $i++) {
    echo "item";
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test that count stored in variable before loop doesn't trigger warning.
     */
    public function testCountStoredInVariableDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3, 4, 5];
$count = count($array);
for ($i = 0; $i < $count; $i++) {
    echo $array[$i];
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test that foreach doesn't trigger warning.
     */
    public function testForeachDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3, 4, 5];
foreach ($array as $item) {
    echo $item;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() used outside of loop doesn't trigger warning.
     */
    public function testCountOutsideLoopDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3];
$count = count($array);
if ($count > 0) {
    echo "has items";
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() in for loop initialization doesn't trigger warning.
     */
    public function testCountInForLoopInitializationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($count = count($array), $i = 0; $i < $count; $i++) {
    echo $i;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() in for loop increment part doesn't trigger warning.
     */
    public function testCountInForLoopIncrementDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$count = 0;
for ($i = 0; $i < 10; $i++, $count = count($array)) {
    echo $i;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test that method named count() doesn't trigger warning.
     */
    public function testMethodNamedCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < $this->count(); $i++) {
    echo $i;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test that static method named count() doesn't trigger warning.
     */
    public function testStaticMethodNamedCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < MyClass::count(); $i++) {
    echo $i;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test while loop with count() doesn't trigger warning (we only check for loops).
     */
    public function testWhileLoopWithCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$i = 0;
while ($i < count($array)) {
    echo $array[$i];
    $i++;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple for loops with count() each trigger warnings.
     */
    public function testMultipleForLoopsWithCountTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < count($array1); $i++) {
    echo $i;
}

for ($j = 0; $j < count($array2); $j++) {
    echo $j;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertContainsWarning($result, 'count()');
        $warningCount = substr_count($result, 'count()');
        $this->assertGreaterThanOrEqual(self::EXPECTED_MULTIPLE_WARNINGS, $warningCount, 'Expected at least 2 warnings for 2 separate for loops');
    }

    /**
     * Test count() with greater than or equal operator.
     */
    public function testCountWithGreaterThanOrEqualTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 10; $i >= count($array); $i--) {
    echo $i;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test count() with not equal operator.
     */
    public function testCountWithNotEqualTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i != count($array); $i++) {
    echo $i;
}', 'VixPHPCS.ControlStructures.DisallowCountInLoop');

        $this->assertContainsWarning($result, 'count()');
    }
}
