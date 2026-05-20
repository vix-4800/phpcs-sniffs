<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Operators;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests `(bool)` casts instead of double negation for boolean coercion.
 */
final class PreferBooleanCastOverDoubleNegationSniff implements Sniff
{
    /**
     * Returns an array of tokens this sniff wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_BOOLEAN_NOT];
    }

    /**
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $previousToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($previousToken !== false && $tokens[$previousToken]['code'] === T_BOOLEAN_NOT) {
            return;
        }

        $secondNot = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($secondNot === false || $tokens[$secondNot]['code'] !== T_BOOLEAN_NOT) {
            return;
        }

        $afterSecondNot = $phpcsFile->findNext(T_WHITESPACE, $secondNot + 1, null, true);

        if ($afterSecondNot !== false && $tokens[$afterSecondNot]['code'] === T_BOOLEAN_NOT) {
            return;
        }

        $warning = 'Use (bool) cast instead of double negation for boolean coercion';

        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }
}
