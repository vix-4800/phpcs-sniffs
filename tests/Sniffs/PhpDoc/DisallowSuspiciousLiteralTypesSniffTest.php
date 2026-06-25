<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\PhpDoc\DisallowSuspiciousLiteralTypesSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowSuspiciousLiteralTypesSniff.
 *
 * @internal
 */
#[CoversClass(DisallowSuspiciousLiteralTypesSniff::class)]
final class DisallowSuspiciousLiteralTypesSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.PhpDoc.DisallowSuspiciousLiteralTypes';

    #[Test]
    public function soleNullFalseAndLiteralValueTypesTriggerWarnings(): void
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
}');

        $this->assertContainsWarning($result, 'Suspicious single-value type "null" in @param.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "false" in @param.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "false" in @property-read.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "0" in @return.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "\'ok\'" in @param.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "\'\'" in @var.');
    }

    #[Test]
    public function suspiciousNestedLiteralTypesTriggerWarnings(): void
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
}');

        $this->assertContainsWarning($result, 'Suspicious single-value type "null" in nested PHPDoc value types.');
        $this->assertContainsWarning($result, 'Suspicious single-value type "false" in nested PHPDoc value types.');
    }

    #[Test]
    public function suspiciousTemplateNamesAndBoundsTriggerWarnings(): void
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
}');

        $this->assertContainsWarning($result, 'Template name "void" conflicts with a native PHPDoc type.');
        $this->assertContainsWarning($result, 'Suspicious template bound "null" in @template.');
        $this->assertContainsWarning($result, 'Suspicious template bound "void" in @template.');
        $this->assertContainsWarning($result, 'Suspicious template bound "never" in @template.');
    }

    #[Test]
    public function usefulTypesDoNotTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template T of object
 * @param string|null $input
 * @param array<string, string|null> $item
 * @param bool $flag
 * @return int
 * @var array<int, string> $items
 * @var array{foo: string|null} $shape
 */
final class Example
{
}');

        $this->assertNoViolations($result);
    }
}
