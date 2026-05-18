<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests using json_validate() instead of json_decode() for validation purposes.
 */
final class PreferJsonValidateSniff implements Sniff
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
        $functionName = mb_strtolower((string) $tokens[$stackPtr]['content']);

        if (!in_array($functionName, ['json_decode', 'json_last_error'], true)) {
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

        if ($functionName === 'json_last_error') {
            $this->addJsonValidateWarning($phpcsFile, $stackPtr);

            return;
        }

        if (!$this->isUsedOnlyForValidation($phpcsFile, $stackPtr) && !$this->hasJsonThrowOnErrorFlag($phpcsFile, $stackPtr)) {
            return;
        }

        $this->addJsonValidateWarning($phpcsFile, $stackPtr);
    }

    /**
     * Checks if json_decode call has JSON_THROW_ON_ERROR flag.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function hasJsonThrowOnErrorFlag(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParen === false || !isset($tokens[$openParen]['parenthesis_closer'])) {
            return false;
        }

        $closeParen = $tokens[$openParen]['parenthesis_closer'];

        for ($i = $openParen + 1; $i < $closeParen; ++$i) {
            if ($tokens[$i]['code'] === T_STRING && $tokens[$i]['content'] === 'JSON_THROW_ON_ERROR') {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if json_decode is used only for validation (result is not used).
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function isUsedOnlyForValidation(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParen === false || !isset($tokens[$openParen]['parenthesis_closer'])) {
            return false;
        }

        $closeParen = $tokens[$openParen]['parenthesis_closer'];

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $closeParen + 1, null, true);

        if ($nextToken === false) {
            return false;
        }

        if ($tokens[$nextToken]['code'] === T_SEMICOLON) {
            $searchEnd = $nextToken + 20;
            $foundJsonLastError = false;
            $tokensCount = count($tokens);

            for ($i = $nextToken + 1; $i < $searchEnd && $i < $tokensCount; ++$i) {
                if ($tokens[$i]['code'] === T_STRING && mb_strtolower((string) $tokens[$i]['content']) === 'json_last_error') {
                    $foundJsonLastError = true;

                    break;
                }
            }

            return $foundJsonLastError;
        }

        return false;
    }

    /**
     * Adds warning about using json_validate().
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function addJsonValidateWarning(File $phpcsFile, int $stackPtr): void
    {
        $warning = 'Consider using json_validate() for JSON validation instead of json_decode()/json_last_error() (PHP 8.3+)';
        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }
}
