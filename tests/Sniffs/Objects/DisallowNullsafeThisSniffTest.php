<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Objects\DisallowNullsafeThisSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowNullsafeThisSniff.
 *
 * @internal
 */
#[CoversClass(DisallowNullsafeThisSniff::class)]
final class DisallowNullsafeThisSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Objects.DisallowNullsafeThis';

    #[Test]
    public function nullsafeThisMethodCallTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(): void
    {
        $this?->foo();
    }
}
');

        $this->assertContainsWarning($result, 'Nullsafe operator is redundant on $this');
    }

    #[Test]
    public function nullsafeThisPropertyAccessTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(): string
    {
        return $this?->name;
    }
}
');

        $this->assertContainsWarning($result, 'Nullsafe operator is redundant on $this');
    }

    #[Test]
    public function regularThisAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(): void
    {
        $this->foo();
    }
}
');

        $this->assertNoViolations($result);
    }

    #[Test]
    public function nullsafeOtherVariableIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function run(?object $model): void
    {
        $model?->foo();
    }
}
');

        $this->assertNoViolations($result);
    }
}
