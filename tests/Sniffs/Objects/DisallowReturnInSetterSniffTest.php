<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowReturnInSetterSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowReturnInSetterSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.Objects.DisallowReturnInSetter';

    public function testSetterReturnTriggersError(): void
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

    public function testSetterReturnWithValueTriggersError(): void
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

    public function testSetterNameIsMatchedCaseInsensitively(): void
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

    public function testRegularMethodReturnDoesNotTriggerError(): void
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

    public function testMethodNamedSetDoesNotTriggerError(): void
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

    public function testClosureReturnInsideSetterDoesNotTriggerError(): void
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

    public function testFunctionNamedSetterDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

function setName(string $name): string
{
    return $name;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
