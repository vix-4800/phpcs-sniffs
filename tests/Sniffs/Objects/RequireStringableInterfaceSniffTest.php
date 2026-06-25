<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Objects\RequireStringableInterfaceSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for RequireStringableInterfaceSniff.
 *
 * @internal
 */
#[CoversClass(RequireStringableInterfaceSniff::class)]
final class RequireStringableInterfaceSniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.Objects.RequireStringableInterface';

    #[Test]
    public function classWithToStringAndWithoutStringableTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function __toString(): string
    {
        return \'example\';
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Classes declaring __toString() must implement Stringable');
    }

    #[Test]
    public function classWithToStringAndStringableDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Example implements Stringable
{
    public function __toString(): string
    {
        return \'example\';
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function classWithToStringAndFullyQualifiedStringableDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Example implements \Stringable
{
    public function __toString(): string
    {
        return \'example\';
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function classWithoutToStringDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function render(): string
    {
        return \'example\';
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function anonymousClassWithToStringAndWithoutStringableTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

$object = new class {
    public function __toString(): string
    {
        return \'example\';
    }
};', self::SNIFF);

        $this->assertContainsWarning($result, 'Classes declaring __toString() must implement Stringable');
    }

    #[Test]
    public function nestedAnonymousClassDoesNotAffectOuterClass(): void
    {
        $result = $this->runPhpcs('<?php

class Example implements Stringable
{
    public function __toString(): string
    {
        return (string) new class {
            public function __toString(): string
            {
                return \'nested\';
            }
        };
    }
}', self::SNIFF);

        $this->assertContainsWarning($result, 'Classes declaring __toString() must implement Stringable');
    }
}
