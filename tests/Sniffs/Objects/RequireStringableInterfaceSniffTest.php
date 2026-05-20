<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for RequireStringableInterfaceSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class RequireStringableInterfaceSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.Objects.RequireStringableInterface';

    public function testClassWithToStringAndWithoutStringableTriggersWarning(): void
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

    public function testClassWithToStringAndStringableDoesNotTriggerWarning(): void
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

    public function testClassWithToStringAndFullyQualifiedStringableDoesNotTriggerWarning(): void
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

    public function testClassWithoutToStringDoesNotTriggerWarning(): void
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

    public function testAnonymousClassWithToStringAndWithoutStringableTriggersWarning(): void
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

    public function testNestedAnonymousClassDoesNotAffectOuterClass(): void
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
