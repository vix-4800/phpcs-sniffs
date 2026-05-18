<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows usage of `throw` expressions inside ternary and null coalescing operators.
 */
final class DisallowThrowInTernarySniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     */
    public function register(): array
    {
        return [T_THROW];
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

        $operatorToken = $phpcsFile->findPrevious(
            [T_INLINE_THEN, T_INLINE_ELSE, T_COALESCE, T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET],
            $stackPtr - 1,
            null,
            false,
        );

        if ($operatorToken === false) {
            return;
        }

        $tokenCode = $tokens[$operatorToken]['code'];

        if (in_array($tokenCode, [T_INLINE_THEN, T_INLINE_ELSE, T_COALESCE], true)) {
            $error = 'Throwing exceptions inside ternary or null coalescing operators is not allowed.
                Use if-else statement or extract to a separate expression for better readability.';

            $phpcsFile->addError($error, $stackPtr, 'Found');
        }
    }
}
