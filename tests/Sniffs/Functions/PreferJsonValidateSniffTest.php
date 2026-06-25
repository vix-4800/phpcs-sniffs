<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Functions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Functions\PreferJsonValidateSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for PreferJsonValidateSniff.
 *
 * @internal
 */
#[CoversClass(PreferJsonValidateSniff::class)]
final class PreferJsonValidateSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Functions.PreferJsonValidate';

    /**
     * Test that json_last_error triggers warning.
     */
    #[Test]
    public function jsonLastErrorTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$data = json_decode($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Invalid JSON");
}', self::SNIFF);

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode followed by json_last_error triggers warning.
     */
    #[Test]
    public function jsonDecodeWithLastErrorCheckTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
json_decode($json);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Invalid JSON");
}', self::SNIFF);

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode with assignment does not trigger (data is used).
     */
    #[Test]
    public function jsonDecodeWithAssignmentAndUsageDoesNotTrigger(): void
    {
        $result = $this->runPhpcs('<?php
$data = json_decode($json);
echo $data->name;', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that method calls are ignored.
     */
    #[Test]
    public function methodCallsAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php
$obj->json_decode($json);
if ($obj->json_last_error() !== 0) {
    throw new Exception();
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that static method calls are ignored.
     */
    #[Test]
    public function staticMethodCallsAreIgnored(): void
    {
        $result = $this->runPhpcs('<?php
SomeClass::json_decode($json);
SomeClass::json_last_error();', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test multiple json_last_error calls trigger warnings.
     */
    #[Test]
    public function multipleJsonLastErrorCallsTriggerWarnings(): void
    {
        $result = $this->runPhpcs('<?php
json_decode($json1);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception();
}

json_decode($json2);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception();
}', self::SNIFF);

        $this->assertContainsWarning($result, 'json_validate()');
        // Should have multiple warnings
        $warningCount = mb_substr_count($result, 'json_validate()');
        $this->assertGreaterThanOrEqual(2, $warningCount);
    }

    /**
     * Test that json_decode with JSON_THROW_ON_ERROR triggers warning.
     */
    #[Test]
    public function jsonDecodeWithThrowOnErrorTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
json_decode($json, true, 512, JSON_THROW_ON_ERROR);', self::SNIFF);

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode with JSON_THROW_ON_ERROR in try-catch triggers warning.
     */
    #[Test]
    public function jsonDecodeWithThrowOnErrorInTryCatchTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
try {
    json_decode(\'{test}\', true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    throw new Exception("Invalid JSON");
}', self::SNIFF);

        $this->assertContainsWarning($result, 'json_validate()');
    }

    /**
     * Test that json_decode with JSON_THROW_ON_ERROR and assignment still triggers warning if value not used.
     */
    #[Test]
    public function jsonDecodeWithThrowOnErrorAndUnusedAssignmentTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
try {
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    // Just validating
}', self::SNIFF);

        $this->assertContainsWarning($result, 'json_validate()');
    }
}
