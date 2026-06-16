<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for StaticInFinalClassSniff.
 *
 * @internal
 */
#[CoversNothing]
final class StaticInFinalClassSniffTest extends BaseTest
{
    public function testStaticReturnTypeInFinalClassTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

final class UserFactory
{
    public function make(): static
    {
        return $this;
    }
}
', 'VixPHPCS.Objects.StaticInFinalClass');

        $this->assertContainsWarning($result, 'Use "self" instead of "static" as the return type inside final classes.');
    }

    public function testStaticReturnTypeInFinalReadonlyClassTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

final readonly class UserFactory
{
    public static function make(): static
    {
        return new self();
    }
}
', 'VixPHPCS.Objects.StaticInFinalClass');

        $this->assertContainsWarning($result, 'Use "self" instead of "static" as the return type inside final classes.');
    }

    public function testSelfReturnTypeInFinalClassIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

final class UserFactory
{
    public static function make(): self
    {
        return new self();
    }
}
', 'VixPHPCS.Objects.StaticInFinalClass');

        $this->assertNoViolations($result);
    }

    public function testStaticReturnTypeInNonFinalClassIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class UserFactory
{
    public function make(): static
    {
        return $this;
    }
}
', 'VixPHPCS.Objects.StaticInFinalClass');

        $this->assertNoViolations($result);
    }
}
