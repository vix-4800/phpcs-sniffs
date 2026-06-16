<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\PhpDoc;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DeprecatedTagSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DeprecatedTagSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.PhpDoc.DeprecatedTag';

    public function testFunctionWithDeprecatedTagTriggersWarning(): void
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

    public function testMethodWithDeprecatedTagTriggersWarning(): void
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

    public function testClassConstantWithDeprecatedTagTriggersWarning(): void
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

    public function testDeprecatedTagWithExistingAttributeTriggersWarning(): void
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

    public function testClassWithDeprecatedTagDoesNotTriggerWarning(): void
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

    public function testInterfaceWithDeprecatedTagDoesNotTriggerWarning(): void
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

    public function testFileDocblockWithDeprecatedTagDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @deprecated
 */

$foo = 1;', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testEnumCaseWithDeprecatedTagTriggersWarning(): void
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

    public function testEnumDeclarationWithDeprecatedTagDoesNotTriggerWarning(): void
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

    public function testOtherTagsDoNotTriggerWarning(): void
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
