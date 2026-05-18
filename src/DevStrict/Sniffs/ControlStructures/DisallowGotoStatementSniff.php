<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows the use of goto statements.
 *
 * The goto statement is considered an anti-pattern in modern PHP as it makes code
 * harder to read, understand, and maintain. Use proper control structures instead.
 *
 * Bad:
 * goto label;
 * label:
 * // code
 *
 * Good:
 * Use if/else, loops, or early returns instead
 */
final class DisallowGotoStatementSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_GOTO];
    }

    /**
     * Processes this test when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $error = 'The goto statement is an anti-pattern in modern PHP and should be avoided.
            Use proper control structures (if/else, loops, early returns) instead';
        $phpcsFile->addError($error, $stackPtr, 'Found');
    }
}
