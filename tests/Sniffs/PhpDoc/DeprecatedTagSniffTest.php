<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\PhpDoc\DeprecatedTagSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DeprecatedTagSniff.
 *
 * @internal
 */
#[CoversClass(DeprecatedTagSniff::class)]
final class DeprecatedTagSniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.PhpDoc.DeprecatedTag';

    #[Test]
    public function functionWithDeprecatedTagTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @deprecated Use newFunction() instead.
 */
function oldFunction(): void
{
}', self::SNIFF);

        $this->assertContainsWarning(
            $result,
            'Use the #[\Deprecated] attribute instead of the @deprecated docblock tag.',
        );
    }

    #[Test]
    public function methodWithDeprecatedTagTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Foo
{
    /**
     * @deprecated
     */
    public function oldMethod(): void
    {
    }
}', self::SNIFF);

        $this->assertContainsWarning(
            $result,
            'Use the #[\Deprecated] attribute instead of the @deprecated docblock tag.',
        );
    }

    #[Test]
    public function classConstantWithDeprecatedTagTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Foo
{
    /**
     * @deprecated Use NEW_CONST instead.
     */
    public const OLD_CONST = 1;
}', self::SNIFF);

        $this->assertContainsWarning(
            $result,
            'Use the #[\Deprecated] attribute instead of the @deprecated docblock tag.',
        );
    }

    #[Test]
    public function deprecatedTagWithExistingAttributeTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Foo
{
    /**
     * @deprecated
     */
    #[\SomeOtherAttribute]
    public function oldMethod(): void
    {
    }
}', self::SNIFF);

        $this->assertContainsWarning(
            $result,
            'Use the #[\Deprecated] attribute instead of the @deprecated docblock tag.',
        );
    }

    #[Test]
    public function classWithDeprecatedTagDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @deprecated Use NewClass instead.
 */
class OldClass
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function interfaceWithDeprecatedTagDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @deprecated
 */
interface OldInterface
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function fileDocblockWithDeprecatedTagDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @deprecated
 */

$foo = 1;', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function enumCaseWithDeprecatedTagTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

enum Status
{
    /**
     * @deprecated Use Status::Active instead.
     */
    case Old;

    case Active;
}', self::SNIFF);

        $this->assertContainsWarning(
            $result,
            'Use the #[\Deprecated] attribute instead of the @deprecated docblock tag.',
        );
    }

    #[Test]
    public function enumDeclarationWithDeprecatedTagDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @deprecated
 */
enum OldStatus
{
    case Active;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function otherTagsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @param string $foo
 * @return void
 */
function someFunction(string $foo): void
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
