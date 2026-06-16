<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowSuspiciousLiteralTypesSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowSuspiciousLiteralTypesSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.PhpDoc.DisallowSuspiciousLiteralTypes';

    public function testSoleNullFalseAndLiteralValueTypesTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param null $input
 * @param false $flag
 * @property-read false $enabled
 * @return 0
 * @param \'ok\' $status
 * @var \'\' $name
 */
function example($input, $flag)
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Suspicious single-value type "null" in @param.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "false" in @param.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "false" in @property-read.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "0" in @return.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "\'ok\'" in @param.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "\'\'" in @var.');
    }

    public function testSuspiciousNestedLiteralTypesTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var array<null> $items
 * @var array<false> $flags
 * @var array{foo: null} $shape
 * @return array<false>
 * @phpstan-return array<null>
 * @psalm-var array{foo: null} $psalmShape
 * @implements IteratorAggregate<null, string>
 */
function example(): void
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Suspicious single-value type "null" in nested PHPDoc value types.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "false" in nested PHPDoc value types.');
    }

    public function testSuspiciousTemplateNamesAndBoundsTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template void
 * @template TNull of null
 * @phpstan-template TVoid of void
 * @psalm-template TNever of never
 */
final class Example
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Template name "void" conflicts with a native PHPDoc type.');
        $this->assertContainsWarning($result, 'Suspicious template bound "null" in @template.');
        $this->assertContainsWarning($result, 'Suspicious template bound "void" in @template.');
        $this->assertContainsWarning($result, 'Suspicious template bound "never" in @template.');
    }

    public function testUsefulTypesDoNotTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template T of object
 * @param string|null $input
 * @param bool $flag
 * @return int
 * @var array<int, string> $items
 */
final class Example
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
