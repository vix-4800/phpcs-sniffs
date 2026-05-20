<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Functions;

use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowNullableBoolReturnTypeSniff.
 *
 * @internal
 *
 * @coversNothing
 */
class DisallowNullableBoolReturnTypeSniffTest extends BaseTest
{
    private const SNIFF = 'VixPHPCS.Functions.DisallowNullableBoolReturnType';

    public function testNullableBoolNativeReturnTypeTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

function isValid(): ?bool
{
    return null;
}', self::SNIFF);

        $this->assertContainsError($result, 'Do not use nullable "bool" as a return type; return true/false instead of null.');
    }

    public function testBoolAndNullUnionReturnTypeTriggersError(): void
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

    public function testNullableBoolDocblockTriggersError(): void
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

    public function testNullableBoolDocblockOnMethodTriggersError(): void
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

    public function testNullableBoolDocblockWithSpacesTriggersError(): void
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

    public function testPlainBoolReturnTypeIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

function isValid(): bool
{
    return true;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    public function testPlainBoolDocblockIsAllowed(): void
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

    public function testArrayOfNullableBoolDoesNotTriggerError(): void
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

    public function testClassDocblockIsIgnored(): void
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
