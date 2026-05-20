<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Disallows using the same variable for foreach key and value.
 */
final class DisallowSameKeyAndValueInForeachSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_FOREACH];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['parenthesis_opener'], $tokens[$stackPtr]['parenthesis_closer'])) {
            return;
        }

        $openParenthesisPtr = $tokens[$stackPtr]['parenthesis_opener'];
        $closeParenthesisPtr = $tokens[$stackPtr]['parenthesis_closer'];
        $asPtr = $phpcsFile->findNext(T_AS, $openParenthesisPtr + 1, $closeParenthesisPtr);

        if ($asPtr === false) {
            return;
        }

        $doubleArrowPtr = $phpcsFile->findNext(T_DOUBLE_ARROW, $asPtr + 1, $closeParenthesisPtr);

        if ($doubleArrowPtr === false) {
            return;
        }

        $keyVariablePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $asPtr + 1, $doubleArrowPtr, true);

        if ($keyVariablePtr === false || $tokens[$keyVariablePtr]['code'] !== T_VARIABLE) {
            return;
        }

        $valueVariablePtr = $this->findSimpleValueVariablePtr($phpcsFile, $doubleArrowPtr, $closeParenthesisPtr);

        if ($valueVariablePtr === null) {
            return;
        }

        if ($tokens[$keyVariablePtr]['content'] !== $tokens[$valueVariablePtr]['content']) {
            return;
        }

        $phpcsFile->addWarning(
            'Foreach key and value variables must be different variables',
            $valueVariablePtr,
            'SameVariable',
        );
    }

    /**
     * Finds the foreach value variable when it is a simple variable or a reference to one.
     *
     * @param File $phpcsFile
     * @param int  $doubleArrowPtr
     * @param int  $closeParenthesisPtr
     */
    private function findSimpleValueVariablePtr(File $phpcsFile, int $doubleArrowPtr, int $closeParenthesisPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $valuePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $doubleArrowPtr + 1, $closeParenthesisPtr, true);

        if ($valuePtr === false) {
            return null;
        }

        while ($valuePtr < $closeParenthesisPtr && $tokens[$valuePtr]['content'] === '&') {
            $valuePtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $valuePtr + 1, $closeParenthesisPtr, true);

            if ($valuePtr === false) {
                return null;
            }
        }

        if ($tokens[$valuePtr]['code'] !== T_VARIABLE) {
            return null;
        }

        $nextContentPtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $valuePtr + 1, $closeParenthesisPtr, true);

        if ($nextContentPtr !== false) {
            return null;
        }

        return $valuePtr;
    }
}
