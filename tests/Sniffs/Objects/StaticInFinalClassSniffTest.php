<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Objects\StaticInFinalClassSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for StaticInFinalClassSniff.
 *
 * @internal
 */
#[CoversClass(StaticInFinalClassSniff::class)]
final class StaticInFinalClassSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Objects.StaticInFinalClass';

    #[Test]
    public function staticReturnTypeInFinalClassTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

final class UserFactory
{
    public function make(): static
    {
        return $this;
    }
}
');

        $this->assertContainsWarning($result, 'Use "self" instead of "static" as the return type inside final classes.');
    }

    #[Test]
    public function staticReturnTypeInFinalReadonlyClassTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

final readonly class UserFactory
{
    public static function make(): static
    {
        return new self();
    }
}
');

        $this->assertContainsWarning($result, 'Use "self" instead of "static" as the return type inside final classes.');
    }

    #[Test]
    public function selfReturnTypeInFinalClassIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

final class UserFactory
{
    public static function make(): self
    {
        return new self();
    }
}
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function staticReturnTypeInNonFinalClassIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class UserFactory
{
    public function make(): static
    {
        return $this;
    }
}
');

        $this->assertNoViolations($result);
    }
}
