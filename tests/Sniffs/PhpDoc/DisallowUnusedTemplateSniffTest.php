<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\PhpDoc\DisallowUnusedTemplateSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowUnusedTemplateSniff.
 *
 * @internal
 */
#[CoversClass(DisallowUnusedTemplateSniff::class)]
final class DisallowUnusedTemplateSniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.PhpDoc.DisallowUnusedTemplate';

    #[Test]
    public function unusedClassTemplateTriggersWarning(): void
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

    #[Test]
    public function classTemplateUsedInSameDocblockDoesNotTriggerWarning(): void
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

    #[Test]
    public function classTemplateUsedInMethodDocblockDoesNotTriggerWarning(): void
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

    #[Test]
    public function unusedTemplateAmongMultipleTemplatesTriggersWarning(): void
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

    #[Test]
    public function functionTemplateUsedInSameDocblockDoesNotTriggerWarning(): void
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

    #[Test]
    public function templateNameSubstringDoesNotCountAsUsage(): void
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

    #[Test]
    public function covariantTemplateUsedInSameDocblockDoesNotTriggerWarning(): void
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
