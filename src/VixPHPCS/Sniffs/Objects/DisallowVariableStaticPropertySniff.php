<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Disallows accessing static properties via objects (e.g. $object::$property).
 */
final class DisallowVariableStaticPropertySniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_DOUBLE_COLON];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        if (!$this->isStaticPropertyAccess($phpcsFile, $stackPtr)) {
            return;
        }

        $variablePtr = $this->findLeadingVariablePtr($phpcsFile, $stackPtr);

        if ($variablePtr === null) {
            return;
        }

        $phpcsFile->addError(
            'Static properties must be accessed via a class name, not via an object variable',
            $variablePtr,
            'VariableStaticProperty',
        );
    }

    /**
     * Checks whether the token sequence represents a static property access.
     *
     * @param File $phpcsFile
     * @param int  $doubleColonPtr
     */
    private function isStaticPropertyAccess(File $phpcsFile, int $doubleColonPtr): bool
    {
        $nextPtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $doubleColonPtr + 1, null, true);

        if ($nextPtr === false) {
            return false;
        }

        $tokens = $phpcsFile->getTokens();
        $nextCode = $tokens[$nextPtr]['code'];

        return in_array($nextCode, [T_VARIABLE, T_DOLLAR], true);
    }

    /**
     * Finds the variable token that precedes the double colon.
     *
     * @param File $phpcsFile
     * @param int  $doubleColonPtr
     */
    private function findLeadingVariablePtr(File $phpcsFile, int $doubleColonPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $prevPtr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $doubleColonPtr - 1, null, true);

        if ($prevPtr === false) {
            return null;
        }

        if ($tokens[$prevPtr]['code'] === T_VARIABLE) {
            return $prevPtr;
        }

        if ($tokens[$prevPtr]['code'] !== T_CLOSE_PARENTHESIS) {
            return null;
        }

        $openPtr = $this->findMatchingOpener($tokens, $prevPtr);

        if ($openPtr === null) {
            return null;
        }

        $variablePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $openPtr + 1, $prevPtr, true);

        if ($variablePtr === false || $tokens[$variablePtr]['code'] !== T_VARIABLE) {
            return null;
        }

        $additionalCodePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $variablePtr + 1, $prevPtr, true);

        if ($additionalCodePtr !== false) {
            return null;
        }

        return $variablePtr;
    }

    /**
     * Simple matcher to locate the opening parenthesis for a closing parenthesis pointer.
     *
     * @param CsTokens $tokens
     * @param int      $closePtr
     */
    private function findMatchingOpener(array $tokens, int $closePtr): ?int
    {
        $depth = 1;

        for ($ptr = $closePtr - 1; $ptr >= 0; --$ptr) {
            if ($tokens[$ptr]['code'] === T_CLOSE_PARENTHESIS) {
                ++$depth;

                continue;
            }

            if ($tokens[$ptr]['code'] !== T_OPEN_PARENTHESIS) {
                continue;
            }

            --$depth;

            if ($depth === 0) {
                return $ptr;
            }
        }

        return null;
    }
}
