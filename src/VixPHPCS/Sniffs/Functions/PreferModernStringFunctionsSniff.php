<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests using modern string functions (str_contains, str_starts_with, str_ends_with) instead of strpos.
 *
 * PHP 8.0 introduced dedicated string search functions that are more readable and semantic.
 *
 * Bad patterns:
 * strpos($haystack, $needle) !== false  // Use str_contains()
 * strpos($haystack, $needle) === 0      // Use str_starts_with()
 * strpos($haystack, $needle) === (strlen($haystack) - strlen($needle))  // Use str_ends_with()
 *
 * Good:
 * str_contains($haystack, $needle)
 * str_starts_with($haystack, $needle)
 * str_ends_with($haystack, $needle)
 */
final class PreferModernStringFunctionsSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Processes this test when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $functionName = mb_strtolower((string) $token['content']);

        if (!in_array($functionName, ['strpos', 'stripos', 'mb_strpos'], true)) {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken !== false && in_array($tokens[$prevToken]['code'], [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true)) {
            return;
        }

        if ($prevToken !== false && $tokens[$prevToken]['code'] === T_FUNCTION) {
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $openParen = $nextToken;
        $closeParen = $tokens[$openParen]['parenthesis_closer'] ?? null;

        if ($closeParen === null) {
            return;
        }

        $comparisonToken = $phpcsFile->findNext(T_WHITESPACE, $closeParen + 1, null, true);

        if ($comparisonToken === false) {
            return;
        }

        $modernFunction = $this->getModernFunctionSuggestion($phpcsFile, $comparisonToken, $functionName);

        if ($modernFunction === null) {
            return;
        }

        $warning = sprintf(
            'Use of %s() with comparison is discouraged; use %s() for better readability (PHP 8.0+)',
            $functionName,
            $modernFunction,
        );

        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }

    /**
     * Determines which modern function to suggest based on the comparison.
     *
     * @param File   $phpcsFile
     * @param int    $comparisonPtr
     * @param string $originalFunction
     */
    private function getModernFunctionSuggestion(File $phpcsFile, int $comparisonPtr, string $originalFunction): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $comparisonCode = $tokens[$comparisonPtr]['code'];

        if (in_array($comparisonCode, [T_IS_NOT_IDENTICAL, T_IS_IDENTICAL], true)) {
            $valueToken = $phpcsFile->findNext(T_WHITESPACE, $comparisonPtr + 1, null, true);

            if ($valueToken !== false && $tokens[$valueToken]['code'] === T_FALSE) {
                return 'str_contains';
            }
        }

        if ($comparisonCode === T_IS_IDENTICAL) {
            $valueToken = $phpcsFile->findNext(T_WHITESPACE, $comparisonPtr + 1, null, true);

            if ($valueToken !== false && $tokens[$valueToken]['code'] === T_LNUMBER && $tokens[$valueToken]['content'] === '0') {
                return 'str_starts_with';
            }
        }

        return null;
    }
}
