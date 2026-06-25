<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Objects\RequireFinalTraitMethodsSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for RequireFinalTraitMethodsSniff.
 *
 * @internal
 */
#[CoversClass(RequireFinalTraitMethodsSniff::class)]
final class RequireFinalTraitMethodsSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Objects.RequireFinalTraitMethods';

    #[Test]
    public function publicTraitMethodTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    public function run(): void
    {
    }
}
');

        $this->assertContainsWarning($result, 'must be declared final');
    }

    #[Test]
    public function implicitlyPublicTraitMethodTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    function boot(): void
    {
    }
}
');

        $this->assertContainsWarning($result, 'must be declared final');
    }

    #[Test]
    public function finalTraitMethodIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    final protected static function hydrate(): void
    {
    }
}
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function privateTraitMethodIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    private function helper(): void
    {
    }
}
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function abstractTraitMethodIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    abstract protected function configure(): void;
}
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function classMethodOutsideTraitIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(): void
    {
    }
}
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function anonymousClassMethodInsideTraitIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    final public function makeFactory(): object
    {
        return new class {
            public function create(): object
            {
                return new stdClass();
            }
        };
    }
}
');

        $this->assertNoViolations($result);
    }
}
