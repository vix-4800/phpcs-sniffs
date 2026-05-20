<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Enforces the use of && and || instead of and/or.
 */
final class DisallowLogicalOperatorsSniff implements Sniff
{
    /**
     * Returns an array of tokens this sniff wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_LOGICAL_AND, T_LOGICAL_OR];
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
        $tokenCode = $tokens[$stackPtr]['code'];

        $preferredOperator = match ($tokenCode) {
            T_LOGICAL_AND => '&&',
            T_LOGICAL_OR => '||',
            default => null,
        };

        if ($preferredOperator === null) {
            return;
        }

        $warning = sprintf(
            'Use %s instead of %s',
            $preferredOperator,
            $tokens[$stackPtr]['content'],
        );

        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }
}
