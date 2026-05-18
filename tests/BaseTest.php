<?php

declare(strict_types=1);

namespace VixPHPCS\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base test class for PHPCS sniff testing.
 */
abstract class BaseTest extends TestCase
{
    /**
     * Run PHPCS on given content with specified sniff.
     *
     * @param string      $content PHP code to check
     * @param string|null $sniff   Specific sniff to run (e.g., 'VixPHPCS.Functions.DisallowIsNull')
     *                             If null, all VixPHPCS sniffs will be run
     *
     * @return string PHPCS output
     */
    protected function runPhpcs(string $content, ?string $sniff = null): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'phpcs_test_');
        file_put_contents($tempFile, $content);

        $phpcsPath = __DIR__ . '/../vendor/bin/phpcs';

        if ($sniff !== null) {
            $command = sprintf(
                '%s --standard=VixPHPCS --report-width=1000 --sniffs=%s %s 2>&1',
                escapeshellarg($phpcsPath),
                escapeshellarg($sniff),
                escapeshellarg($tempFile),
            );
        } else {
            $command = sprintf(
                '%s --standard=VixPHPCS --report-width=1000 %s 2>&1',
                escapeshellarg($phpcsPath),
                escapeshellarg($tempFile),
            );
        }

        $output = shell_exec($command);
        unlink($tempFile);

        return $output ?? '';
    }

    /**
     * Assert that PHPCS output contains a warning.
     *
     * @param string      $output  PHPCS output
     * @param string|null $message Optional message fragment to check for
     */
    protected function assertContainsWarning(string $output, ?string $message = null): void
    {
        $this->assertStringContainsString('WARNING', $output, 'Expected PHPCS to report a warning');

        if ($message !== null) {
            $this->assertStringContainsString($message, $output, "Expected warning message to contain: {$message}");
        }
    }

    /**
     * Assert that PHPCS output contains an error.
     *
     * @param string      $output  PHPCS output
     * @param string|null $message Optional message fragment to check for
     */
    protected function assertContainsError(string $output, ?string $message = null): void
    {
        $this->assertStringContainsString('ERROR', $output, 'Expected PHPCS to report an error');

        if ($message !== null) {
            $this->assertStringContainsString($message, $output, "Expected error message to contain: {$message}");
        }
    }

    /**
     * Assert that PHPCS output contains no errors or warnings.
     *
     * @param string $output PHPCS output
     */
    protected function assertNoViolations(string $output): void
    {
        $this->assertStringNotContainsString('ERROR', $output, 'Expected no errors');
        $this->assertStringNotContainsString('WARNING', $output, 'Expected no warnings');
    }

    /**
     * Assert that PHPCS found violations (either errors or warnings).
     *
     * @param string $output PHPCS output
     */
    protected function assertHasViolations(string $output): void
    {
        $hasViolation = str_contains($output, 'ERROR') || str_contains($output, 'WARNING');
        $this->assertTrue($hasViolation, 'Expected PHPCS to report at least one violation (error or warning)');
    }
}
