<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Constants;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Constants\UppercaseMagicConstantsSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for UppercaseMagicConstantsSniff.
 *
 * @internal
 */
#[CoversClass(UppercaseMagicConstantsSniff::class)]
final class UppercaseMagicConstantsSniffTest extends BaseTest
{
    /**
     * Test that lowercase and mixed-case magic constants trigger warnings.
     */
    #[Test]
    public function lowercaseMagicConstantsTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
namespace App;

trait ExampleTrait
{
    public function buildMessage(): string
    {
        return __file__ . __Dir__ . __line__ . __function__ . __method__ . __trait__ . __namespace__;
    }
}', 'VixPHPCS.Constants.UppercaseMagicConstants');

        $this->assertContainsWarning($result, '__FILE__');
        $this->assertContainsWarning($result, '__DIR__');
        $this->assertContainsWarning($result, '__LINE__');
        $this->assertContainsWarning($result, '__FUNCTION__');
        $this->assertContainsWarning($result, '__METHOD__');
        $this->assertContainsWarning($result, '__TRAIT__');
        $this->assertContainsWarning($result, '__NAMESPACE__');
    }

    /**
     * Test that class-related magic constants trigger warnings when not uppercase.
     */
    #[Test]
    public function classMagicConstantsTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
class Example
{
    public function buildMessage(): string
    {
        return __class__;
    }
}', 'VixPHPCS.Constants.UppercaseMagicConstants');

        $this->assertContainsWarning($result, '__CLASS__');
    }

    /**
     * Test that correctly cased magic constants do not trigger warnings.
     */
    #[Test]
    public function uppercaseMagicConstantsDoNotTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
namespace App;

trait ExampleTrait
{
    public function buildMessage(): string
    {
        return __FILE__ . __DIR__ . __LINE__ . __FUNCTION__ . __METHOD__ . __TRAIT__ . __NAMESPACE__;
    }
}

class Example
{
    public function getClassName(): string
    {
        return __CLASS__;
    }
}', 'VixPHPCS.Constants.UppercaseMagicConstants');

        $this->assertNoViolations($result);
    }
}
