<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowReturnInConstructorDestructorSniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowReturnInConstructorDestructorSniffTest extends BaseTest
{
    private const string SNIFF = 'VixPHPCS.Objects.DisallowReturnInConstructorDestructor';

    #[Test]
    public function constructorReturnTriggersError(): void
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

    #[Test]
    public function constructorReturnWithValueTriggersError(): void
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

    #[Test]
    public function destructorReturnTriggersError(): void
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

    #[Test]
    public function methodReturnDoesNotTriggerError(): void
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

    #[Test]
    public function closureReturnInsideConstructorDoesNotTriggerError(): void
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

    #[Test]
    public function constructorWithoutReturnDoesNotTriggerError(): void
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
