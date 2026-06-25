<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Functions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Functions\DisallowNullableBoolReturnTypeSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowNullableBoolReturnTypeSniff.
 *
 * @internal
 */
#[CoversClass(DisallowNullableBoolReturnTypeSniff::class)]
final class DisallowNullableBoolReturnTypeSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Functions.DisallowNullableBoolReturnType';

    #[Test]
    public function nullableBoolNativeReturnTypeTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

function isValid(): ?bool
{
    return null;
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use nullable "bool" as a return type; return true/false instead of null.');
    }

    #[Test]
    public function boolAndNullUnionReturnTypeTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

final class Checker
{
    public function isValid(): int|bool|null
    {
        return null;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use nullable "bool" as a return type; return true/false instead of null.');
    }

    #[Test]
    public function nullableBoolDocblockTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return bool|null
 */
function isValid()
{
    return null;
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use nullable "bool" in @return; return true/false instead of null.');
    }

    #[Test]
    public function nullableBoolDocblockOnMethodTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

final class Checker
{
    /**
     * @return ?bool
     */
    public function isValid()
    {
        return null;
    }
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use nullable "bool" in @return; return true/false instead of null.');
    }

    #[Test]
    public function nullableBoolDocblockWithSpacesTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return bool | null Explanation
 */
function isVisible()
{
    return null;
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use nullable "bool" in @return; return true/false instead of null.');
    }

    #[Test]
    public function plainBoolReturnTypeIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

function isValid(): bool
{
    return true;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function plainBoolDocblockIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return bool
 */
function isValid()
{
    return true;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function arrayOfNullableBoolDoesNotTriggerError(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return array<bool|null>
 */
function states(): array
{
    return [true, null];
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function classDocblockIsIgnored(): void
    {
        $result = $this->runPhpcs('<?php

/**
 * @return bool|null
 */
final class Checker
{
}', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
