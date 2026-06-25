<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Objects\DisallowReturnInSetterSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowReturnInSetterSniff.
 *
 * @internal
 */
#[CoversClass(DisallowReturnInSetterSniff::class)]
final class DisallowReturnInSetterSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Objects.DisallowReturnInSetter';

    #[Test]
    public function setterReturnTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function setName(string $name): void
    {
        return;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Return statements are not allowed inside setter methods');
    }

    #[Test]
    public function setterReturnWithValueTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function setName(string $name): self
    {
        return $this;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Return statements are not allowed inside setter methods');
    }

    #[Test]
    public function setterNameIsMatchedCaseInsensitively(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function SetName(string $name): void
    {
        return;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Return statements are not allowed inside setter methods');
    }

    #[Test]
    public function regularMethodReturnDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function getName(): string
    {
        return "value";
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function methodNamedSetDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function set(): bool
    {
        return true;
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function closureReturnInsideSetterDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function setName(string $name): void
    {
        $callback = function (): int {
            return 1;
        };
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function functionNamedSetterDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

function setName(string $name): string
{
    return $name;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
