<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Requires concrete non-private trait methods to be declared final.
 */
final class RequireFinalTraitMethodsSniff implements Sniff
{
    /**
     * {@inheritDoc}
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        if (!$this->isNamedTraitMethod($phpcsFile, $stackPtr)) {
            return;
        }

        if ($this->hasModifier($phpcsFile, $stackPtr, T_PRIVATE) || $this->hasModifier($phpcsFile, $stackPtr, T_ABSTRACT)) {
            return;
        }

        if ($this->hasModifier($phpcsFile, $stackPtr, T_FINAL)) {
            return;
        }

        $methodNamePtr = $this->findMethodNamePtr($phpcsFile, $stackPtr);

        if ($methodNamePtr === null) {
            return;
        }

        $phpcsFile->addWarning(
            'Non-private, non-abstract trait methods must be declared final.',
            $methodNamePtr,
            'MissingFinal',
        );
    }

    private function isNamedTraitMethod(File $phpcsFile, int $stackPtr): bool
    {
        if ($this->findMethodNamePtr($phpcsFile, $stackPtr) === null) {
            return false;
        }

        $tokens = $phpcsFile->getTokens();
        $conditions = $tokens[$stackPtr]['conditions'];

        foreach (array_reverse($conditions, true) as $conditionCode) {
            if (!isset(Tokens::OO_SCOPE_TOKENS[$conditionCode])) {
                continue;
            }

            return $conditionCode === T_TRAIT;
        }

        return false;
    }

    private function hasModifier(File $phpcsFile, int $stackPtr, int $modifier): bool
    {
        $tokens = $phpcsFile->getTokens();

        for ($ptr = $stackPtr - 1; $ptr >= 0; --$ptr) {
            $tokenCode = $tokens[$ptr]['code'];

            if (isset(Tokens::EMPTY_TOKENS[$tokenCode])) {
                continue;
            }

            if (in_array($tokenCode, [T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET, T_SEMICOLON, T_OPEN_TAG], true)) {
                return false;
            }

            if ($tokenCode === $modifier) {
                return true;
            }
        }

        return false;
    }

    private function findMethodNamePtr(File $phpcsFile, int $stackPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $namePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $stackPtr + 1, null, true);

        while ($namePtr !== false && $tokens[$namePtr]['code'] === T_BITWISE_AND) {
            $namePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $namePtr + 1, null, true);
        }

        if ($namePtr === false || $tokens[$namePtr]['code'] !== T_STRING) {
            return null;
        }

        return $namePtr;
    }
}
