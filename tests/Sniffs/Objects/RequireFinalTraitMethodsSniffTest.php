<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for RequireFinalTraitMethodsSniff.
 *
 * @internal
 */
#[CoversNothing]
final class RequireFinalTraitMethodsSniffTest extends BaseTest
{
    public function testPublicTraitMethodTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    public function run(): void
    {
    }
}
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertContainsWarning($result, 'must be declared final');
    }

    public function testImplicitlyPublicTraitMethodTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    function boot(): void
    {
    }
}
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertContainsWarning($result, 'must be declared final');
    }

    public function testFinalTraitMethodIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    final protected static function hydrate(): void
    {
    }
}
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertNoViolations($result);
    }

    public function testPrivateTraitMethodIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    private function helper(): void
    {
    }
}
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertNoViolations($result);
    }

    public function testAbstractTraitMethodIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

trait ExampleTrait
{
    abstract protected function configure(): void;
}
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertNoViolations($result);
    }

    public function testClassMethodOutsideTraitIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(): void
    {
    }
}
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertNoViolations($result);
    }

    public function testAnonymousClassMethodInsideTraitIsIgnored(): void
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
', 'VixPHPCS.Objects.RequireFinalTraitMethods');

        $this->assertNoViolations($result);
    }
}
