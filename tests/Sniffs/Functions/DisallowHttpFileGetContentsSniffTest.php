<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Common\Sniffs\Functions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Functions\DisallowHttpFileGetContentsSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * Tests for DisallowHttpFileGetContentsSniff.
 *
 * @internal
 */
#[CoversClass(DisallowHttpFileGetContentsSniff::class)]
final class DisallowHttpFileGetContentsSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Functions.DisallowHttpFileGetContents';

    /**
     * Test that HTTP URL triggers a warning.
     */
    #[Test]
    public function httpUrlTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents("http://example.com/api");', self::SNIFF);

        $this->assertContainsWarning($result, 'HTTP requests');
        $this->assertContainsWarning($result, 'HTTP client');
    }

    /**
     * Test that HTTPS URL triggers a warning.
     */
    #[Test]
    public function httpsUrlTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents(\'https://example.com/api\');', self::SNIFF);

        $this->assertContainsWarning($result, 'file_get_contents()');
    }

    /**
     * Test that URL literals are detected case-insensitively.
     */
    #[Test]
    public function urlSchemeIsCaseInsensitive(): void
    {
        $result = $this->runPhpcs('<?php
$response = FILE_GET_CONTENTS("HTTPS://example.com/api");', self::SNIFF);

        $this->assertContainsWarning($result, 'HTTP requests');
    }

    /**
     * Test that interpolated HTTP URL triggers a warning.
     */
    #[Test]
    public function interpolatedHttpUrlTriggersWarning(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents("https://$host/api");', self::SNIFF);

        $this->assertContainsWarning($result, 'HTTP requests');
    }

    /**
     * Test that local paths do not trigger warnings.
     */
    #[Test]
    public function localPathDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$contents = file_get_contents(__DIR__ . "/file.txt");
$contents = file_get_contents("php://input");', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that dynamic URLs do not trigger warnings.
     */
    #[Test]
    public function dynamicUrlDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents($url);', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that methods named file_get_contents do not trigger warnings.
     */
    #[Test]
    public function methodNamedFileGetContentsDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$response = $client->file_get_contents("https://example.com/api");
$response = Client::file_get_contents("https://example.com/api");', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that function declarations do not trigger warnings.
     */
    #[Test]
    public function functionDeclarationDoesNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
function file_get_contents(string $url): string {
    return $url;
}', self::SNIFF);

        $this->assertNoViolations($result);
    }

    /**
     * Test that later HTTP string arguments do not trigger warnings.
     */
    #[Test]
    public function laterHttpStringArgumentsDoNotTriggerWarning(): void
    {
        $result = $this->runPhpcs('<?php
$response = file_get_contents($path, false, stream_context_create([
    "http" => [
        "header" => "Referer: https://example.com",
    ],
]));', self::SNIFF);

        $this->assertNoViolations($result);
    }
}
