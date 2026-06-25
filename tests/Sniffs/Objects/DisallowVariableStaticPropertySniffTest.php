<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Objects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Objects\DisallowVariableStaticPropertySniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowVariableStaticPropertySniff.
 *
 * @internal
 */
#[CoversClass(DisallowVariableStaticPropertySniff::class)]
final class DisallowVariableStaticPropertySniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Objects.DisallowVariableStaticProperty';

    #[Test]
    public function variableStaticPropertyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

$toast = $model::$toast_array[$model->toast];
', self::SNIFF);

        $this->assertContainsError($result, 'Static properties must be accessed via a class name');
    }

    #[Test]
    public function parenthesizedVariableStaticPropertyTriggersError(): void
    {
        $result = $this->runPhpcs('<?php

$toast = ($model)::$toast_array[$model->toast];
', self::SNIFF);

        $this->assertContainsError($result, 'Static properties must be accessed via a class name');
    }

    #[Test]
    public function classNameAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

$toast = User::$toast_array[$id];
', self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function selfAccessIsAllowed(): void
    {
        $result = $this->runPhpcs('<?php

class Example
{
    public function getFoo(): int
    {
        return self::$foo;
    }
}
', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
