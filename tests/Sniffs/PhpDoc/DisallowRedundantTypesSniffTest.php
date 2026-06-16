<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowRedundantTypesSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowRedundantTypesSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.PhpDoc.DisallowRedundantTypes';

    public function testDuplicateUnionTypesTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var string|string $name
 * @return null|null
 */
function example()
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Duplicate PHPDoc union type "string" in @var.');
        $this->assertContainsWarning($result, 'Duplicate PHPDoc union type "null" in @return.');
    }

    public function testBroadTypesSwallowingNarrowerTypesTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var mixed|string $value
 * @var bool|true|false $flag
 * @var object|stdClass $object
 * @var iterable|array $items
 * @var Throwable|Exception $e
 */
function example(): void
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'PHPDoc union type "mixed|string" contains redundant narrower types.');
        $this->assertContainsWarning($result, 'PHPDoc union type "bool|true|false" contains redundant narrower types.');
        $this->assertContainsWarning($result, 'PHPDoc union type "object|stdClass" contains redundant narrower types.');
        $this->assertContainsWarning($result, 'PHPDoc union type "iterable|array" contains redundant narrower types.');
        $this->assertContainsWarning($result, 'PHPDoc union type "Throwable|Exception" contains redundant narrower types.');
    }

    public function testNestedRedundantTypesTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @var array<string|string> $duplicate
 * @var array<mixed|string> $redundant
 * @return array<bool|false>
 */
function example()
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Duplicate PHPDoc union type "string" in nested PHPDoc value types.');
        $this->assertContainsWarning(
            $result,
            'Nested PHPDoc union type "mixed|string" contains redundant narrower types.',
        );
        $this->assertContainsWarning(
            $result,
            'Nested PHPDoc union type "bool|false" contains redundant narrower types.',
        );
    }

    public function testDistinctUsefulUnionsDoNotTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param string|int|null $value
 * @return array<int, string>|Traversable<int, string>
 */
function example($value)
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
