<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\ControlStructures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\ControlStructures\DisallowCountInLoopSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowCountInLoopSniff.
 *
 * @internal
 */
#[CoversClass(DisallowCountInLoopSniff::class)]
final class DisallowCountInLoopSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.ControlStructures.DisallowCountInLoop';

    /**
     * Expected minimum number of warnings for multiple for loops test.
     */
    private const int EXPECTED_MULTIPLE_WARNINGS = 2;

    /**
     * Test that count() in for loop condition triggers a warning.
     */
    #[Test]
    public function countInForLoopConditionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3, 4, 5];
for ($i = 0; $i < count($array); $i++) {
    echo $array[$i];
}', self::SNIFF);

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test that count() with complex expressions in for loop triggers a warning.
     */
    #[Test]
    public function countWithComplexExpressionTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < count($this->items); $i++) {
    echo "item";
}', self::SNIFF);

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test that count() with array access in for loop triggers a warning.
     */
    #[Test]
    public function countWithArrayAccessTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < count($data["items"]); $i++) {
    echo "item";
}', self::SNIFF);

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test that count stored in variable before loop doesn't trigger warning.
     */
    #[Test]
    public function countStoredInVariableDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3, 4, 5];
$count = count($array);
for ($i = 0; $i < $count; $i++) {
    echo $array[$i];
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that foreach doesn't trigger warning.
     */
    #[Test]
    public function foreachDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3, 4, 5];
foreach ($array as $item) {
    echo $item;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() used outside of loop doesn't trigger warning.
     */
    #[Test]
    public function countOutsideLoopDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$array = [1, 2, 3];
$count = count($array);
if ($count > 0) {
    echo "has items";
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() in for loop initialization doesn't trigger warning.
     */
    #[Test]
    public function countInForLoopInitializationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($count = count($array), $i = 0; $i < $count; $i++) {
    echo $i;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that count() in for loop increment part doesn't trigger warning.
     */
    #[Test]
    public function countInForLoopIncrementDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$count = 0;
for ($i = 0; $i < 10; $i++, $count = count($array)) {
    echo $i;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that method named count() doesn't trigger warning.
     */
    #[Test]
    public function methodNamedCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < $this->count(); $i++) {
    echo $i;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that static method named count() doesn't trigger warning.
     */
    #[Test]
    public function staticMethodNamedCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < MyClass::count(); $i++) {
    echo $i;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test while loop with count() doesn't trigger warning (we only check for loops).
     */
    #[Test]
    public function whileLoopWithCountDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$i = 0;
while ($i < count($array)) {
    echo $array[$i];
    $i++;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple for loops with count() each trigger warnings.
     */
    #[Test]
    public function multipleForLoopsWithCountTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i < count($array1); $i++) {
    echo $i;
}

for ($j = 0; $j < count($array2); $j++) {
    echo $j;
}', self::SNIFF);

        $this->assertContainsWarning($result, 'count()');
        $warningCount = mb_substr_count($result, 'count()');
        $this->assertGreaterThanOrEqual(self::EXPECTED_MULTIPLE_WARNINGS, $warningCount, 'Expected at least 2 warnings for 2 separate for loops');
    }

    /**
     * Test count() with greater than or equal operator.
     */
    #[Test]
    public function countWithGreaterThanOrEqualTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 10; $i >= count($array); $i--) {
    echo $i;
}', self::SNIFF);

        $this->assertContainsWarning($result, 'count()');
    }

    /**
     * Test count() with not equal operator.
     */
    #[Test]
    public function countWithNotEqualTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
for ($i = 0; $i != count($array); $i++) {
    echo $i;
}', self::SNIFF);

        $this->assertContainsWarning($result, 'count()');
    }
}
