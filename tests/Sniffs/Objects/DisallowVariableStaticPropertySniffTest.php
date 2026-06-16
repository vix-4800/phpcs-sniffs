<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversNothing;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowVariableStaticPropertySniff.
 *
 * @internal
 */
#[CoversNothing]
final class DisallowVariableStaticPropertySniffTest extends BaseTest
{
    public function testVariableStaticPropertyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

$toast = $model::$toast_array[$model->toast];
', 'VixPHPCS.Objects.DisallowVariableStaticProperty');

        $this->assertContainsError($result, 'Static properties must be accessed via a class name');
    }

    public function testParenthesizedVariableStaticPropertyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

$toast = ($model)::$toast_array[$model->toast];
', 'VixPHPCS.Objects.DisallowVariableStaticProperty');

        $this->assertContainsError($result, 'Static properties must be accessed via a class name');
    }

    public function testClassNameAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

$toast = User::$toast_array[$id];
', 'VixPHPCS.Objects.DisallowVariableStaticProperty');

        $this->assertNoViolations($result);
    }

    public function testSelfAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function getFoo(): int
    {
        return self::$foo;
    }
}
', 'VixPHPCS.Objects.DisallowVariableStaticProperty');

        $this->assertNoViolations($result);
    }
}
