<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Disallows array keys that are not declared as integer or string literals.
 */
final class DisallowNonIntStringArrayKeySniff implements Sniff
{
    /**
     * @var list<int|string>
     */
    private const array OPEN_TOKENS = [
        T_OPEN_PARENTHESIS,
        T_OPEN_SHORT_ARRAY,
        T_OPEN_SQUARE_BRACKET,
    ];

    /**
     * @var list<int|string>
     */
    private const array CLOSE_TOKENS = [
        T_CLOSE_PARENTHESIS,
        T_CLOSE_SHORT_ARRAY,
        T_CLOSE_SQUARE_BRACKET,
    ];

    /**
     * {@inheritDoc}
     *
     * @return array<int, int>
     */
    public function register(): array
    {
        return [T_DOUBLE_ARROW];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $keyStartPtr = $this->findArrayKeyStart($phpcsFile, $stackPtr);

        if ($keyStartPtr === null) {
            return;
        }

        $keyEndPtr = $this->findPreviousNonEmpty($phpcsFile, $stackPtr - 1);

        if ($keyEndPtr === null || $keyEndPtr < $keyStartPtr) {
            return;
        }

        if ($this->isAllowedKeyExpression($phpcsFile, $keyStartPtr, $keyEndPtr)) {
            return;
        }

        $phpcsFile->addError(
            'Array keys must be int or string literals',
            $keyStartPtr,
            'InvalidKeyType',
        );
    }

    /**
     * Finds the first significant token of the current array key.
     *
     * @param File $phpcsFile
     * @param int  $doubleArrowPtr
     */
    private function findArrayKeyStart(File $phpcsFile, int $doubleArrowPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $depth = 0;

        for ($ptr = $doubleArrowPtr - 1; $ptr >= 0; --$ptr) {
            $code = $tokens[$ptr]['code'];

            if (in_array($code, self::CLOSE_TOKENS, strict: true)) {
                ++$depth;

                continue;
            }

            if (in_array($code, self::OPEN_TOKENS, strict: true)) {
                if ($depth === 0) {
                    if ($code === T_OPEN_SHORT_ARRAY || $this->isLongArrayOpener($phpcsFile, $ptr)) {
                        return $this->findNextNonEmpty($phpcsFile, $ptr + 1, $doubleArrowPtr);
                    }

                    return null;
                }

                --$depth;

                continue;
            }

            if ($depth === 0 && $code === T_COMMA) {
                return $this->findNextNonEmpty($phpcsFile, $ptr + 1, $doubleArrowPtr);
            }
        }

        return null;
    }

    /**
     * Checks whether an expression is an allowed array key.
     *
     * @param File $phpcsFile
     * @param int  $startPtr
     * @param int  $endPtr
     */
    private function isAllowedKeyExpression(File $phpcsFile, int $startPtr, int $endPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        while (
            $tokens[$startPtr]['code'] === T_OPEN_PARENTHESIS
            && $tokens[$endPtr]['code'] === T_CLOSE_PARENTHESIS
            && ($tokens[$startPtr]['parenthesis_closer'] ?? null) === $endPtr
        ) {
            $nextStartPtr = $this->findNextNonEmpty($phpcsFile, $startPtr + 1, $endPtr);
            $nextEndPtr = $this->findPreviousNonEmpty($phpcsFile, $endPtr - 1, $startPtr);

            if ($nextStartPtr === null || $nextEndPtr === null) {
                return false;
            }

            $startPtr = $nextStartPtr;
            $endPtr = $nextEndPtr;
        }

        $significantTokens = [];

        for ($ptr = $startPtr; $ptr <= $endPtr; ++$ptr) {
            if (in_array($tokens[$ptr]['code'], Tokens::EMPTY_TOKENS, strict: true)) {
                continue;
            }

            $significantTokens[] = $tokens[$ptr]['code'];
        }

        if ($significantTokens === []) {
            return false;
        }

        if (count($significantTokens) === 1) {
            return in_array(
                $significantTokens[0],
                [T_LNUMBER, T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING],
                strict: true,
            );
        }

        return count($significantTokens) === 2
            && in_array($significantTokens[0], [T_MINUS, T_PLUS], strict: true)
            && $significantTokens[1] === T_LNUMBER;
    }

    /**
     * Checks whether an opening parenthesis belongs to array().
     *
     * @param File $phpcsFile
     * @param int  $openPtr
     */
    private function isLongArrayOpener(File $phpcsFile, int $openPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $prevPtr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $openPtr - 1, null, true);

        return $prevPtr !== false && $tokens[$prevPtr]['code'] === T_ARRAY;
    }

    /**
     * Finds the next significant token.
     *
     * @param File $phpcsFile
     * @param int  $startPtr
     * @param ?int $endPtr
     */
    private function findNextNonEmpty(File $phpcsFile, int $startPtr, ?int $endPtr = null): ?int
    {
        $ptr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $startPtr, $endPtr, true);

        return $ptr === false ? null : $ptr;
    }

    /**
     * Finds the previous significant token.
     *
     * @param File $phpcsFile
     * @param int  $startPtr
     * @param ?int $endPtr
     */
    private function findPreviousNonEmpty(File $phpcsFile, int $startPtr, ?int $endPtr = null): ?int
    {
        $ptr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $startPtr, $endPtr, true);

        return $ptr === false ? null : $ptr;
    }
}
