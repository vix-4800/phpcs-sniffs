<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowUnusedTemplateSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowUnusedTemplateSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.PhpDoc.DisallowUnusedTemplate';

    public function testUnusedClassTemplateTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template TModel of ActiveRecord
 */
class Repository
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Template "TModel" is declared but never used.');
    }

    public function testClassTemplateUsedInSameDocblockDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template TModel of ActiveRecord
 * @extends BaseRepository<TModel>
 */
class Repository extends BaseRepository
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testClassTemplateUsedInMethodDocblockDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template TModel of ActiveRecord
 */
class Repository
{
    /**
     * @return TModel
     */
    public function find()
    {
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testUnusedTemplateAmongMultipleTemplatesTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey>
 */
class Collection implements IteratorAggregate
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Template "TValue" is declared but never used.');
        $this->assertStringNotContainsString('Template "TKey"', $result);
    }

    public function testFunctionTemplateUsedInSameDocblockDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template TModel of ActiveRecord
 * @param class-string<TModel> $className
 * @return TModel
 */
function make(string $className)
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testTemplateNameSubstringDoesNotCountAsUsage(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template TModel of ActiveRecord
 * @return TModelCollection
 */
class Repository
{
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Template "TModel" is declared but never used.');
    }

    public function testCovariantTemplateUsedInSameDocblockDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @template-covariant TModel of ActiveRecord
 * @extends BaseRepository<TModel>
 */
class Repository extends BaseRepository
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
