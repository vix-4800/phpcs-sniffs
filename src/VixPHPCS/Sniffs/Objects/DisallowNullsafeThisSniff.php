<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Disallows nullsafe operator usage on $this.
 */
final class DisallowNullsafeThisSniff implements Sniff
{
    /**
     * {@inheritDoc}
     *
     * @return array<int, int>
     */
    public function register(): array
    {
        return [T_NULLSAFE_OBJECT_OPERATOR];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $prevPtr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, true);

        if ($prevPtr === false || $tokens[$prevPtr]['code'] !== T_VARIABLE || $tokens[$prevPtr]['content'] !== '$this') {
            return;
        }

        $phpcsFile->addWarning(
            'Nullsafe operator is redundant on $this because $this cannot be null',
            $stackPtr,
            'NullsafeThis',
        );
    }
}
