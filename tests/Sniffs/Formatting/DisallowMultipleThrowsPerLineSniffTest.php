<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Formatting;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Formatting\DisallowMultipleThrowsPerLineSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowMultipleThrowsPerLineSniff.
 *
 * @internal
 */
#[CoversClass(DisallowMultipleThrowsPerLineSniff::class)]
final class DisallowMultipleThrowsPerLineSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Formatting.DisallowMultipleThrowsPerLine';

    #[Test]
    public function detectsMultipleExceptionsOnSingleLine(): void
    {
        $code = '<?php
        /**
         * @throws JsonException|Exception
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Each @throws annotation must contain only one exception type');
    }

    #[Test]
    public function allowsSingleExceptionPerThrows(): void
    {
        $code = '<?php
        /**
         * @throws JsonException
         * @throws Exception
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function detectsThreeExceptions(): void
    {
        $code = '<?php
        /**
         * @throws InvalidArgumentException|RuntimeException|LogicException
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'InvalidArgumentException, RuntimeException, LogicException');
    }

    #[Test]
    public function allowsSingleFullyQualifiedException(): void
    {
        $code = '<?php
        /**
         * @throws \App\Exception\CustomException
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function detectsMultipleFullyQualifiedExceptions(): void
    {
        $code = '<?php
        /**
         * @throws \App\Exception\FirstException|\App\Exception\SecondException
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Each @throws annotation must contain only one exception type');
    }

    #[Test]
    public function allowsThrowsWithDescription(): void
    {
        $code = '<?php
        /**
         * @throws JsonException When JSON parsing fails
         */
        function test(): void {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function ignoresOtherDocTags(): void
    {
        $code = '<?php
        /**
         * @param string|int $value
         * @return string|null
         */
        function test($value) {}
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertNoViolations($result);
    }

    #[Test]
    public function handlesClassDocBlock(): void
    {
        $code = '<?php
        class TestClass
        {
            /**
             * @throws Exception|RuntimeException
             */
            public function method(): void {}
        }
        ';
        $result = $this->runPhpcs($code, self::SNIFF);

        $this->assertContainsWarning($result, 'Each @throws annotation must contain only one exception type');
    }
}
