<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowReturnInConstructorDestructorSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowReturnInConstructorDestructorSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.Objects.DisallowReturnInConstructorDestructor';

    public function testConstructorReturnTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function __construct()
    {
        return;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Return statements are not allowed inside constructors or destructors');
    }

    public function testConstructorReturnWithValueTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function __construct()
    {
        return 1;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Return statements are not allowed inside constructors or destructors');
    }

    public function testDestructorReturnTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function __destruct()
    {
        return;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Return statements are not allowed inside constructors or destructors');
    }

    public function testMethodReturnDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(): int
    {
        return 1;
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testClosureReturnInsideConstructorDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function __construct()
    {
        $callback = function (): int {
            return 1;
        };
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testConstructorWithoutReturnDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function __construct()
    {
        $this->boot();
    }

    private function boot(): void
    {
    }
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
