<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Shared helpers for method-chain related sniffs.
 */
trait MethodChainHelperTrait
{
    /**
     * Tokens that break a method chain when they appear at the top level between two operators.
     *
     * @var array<int|string, bool>
     */
    private const ADDITIONAL_BREAK_TOKENS = [
        T_SEMICOLON => true,
        T_COMMA => true,
        T_DOUBLE_ARROW => true,
        T_COLON => true,
        T_INLINE_THEN => true,
        T_INLINE_ELSE => true,
        T_CLOSE_CURLY_BRACKET => true,
        T_OPEN_CURLY_BRACKET => true,
        T_OPEN_TAG => true,
        T_CLOSE_TAG => true,
    ];

    /**
     * Checks if the operator is placed on its own line (i.e. starts a new line in the chain).
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function isMultiLineOperator(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $prevPtr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $stackPtr - 1, null, true);

        if ($prevPtr === false) {
            return false;
        }

        return $tokens[$prevPtr]['line'] !== $tokens[$stackPtr]['line'];
    }

    /**
     * Finds the previous multi-line operator in the same chain, if any.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function findPreviousMultiLineOperator(File $phpcsFile, int $stackPtr): ?int
    {
        $currentNestLevel = $this->getParenthesisNestLevel($phpcsFile, $stackPtr);
        $searchPtr = $stackPtr - 1;

        while (($prev = $phpcsFile->findPrevious([T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], $searchPtr, null, false)) !== false) {
            if (!$this->isMultiLineOperator($phpcsFile, $prev)) {
                $searchPtr = $prev - 1;

                continue;
            }

            $prevNestLevel = $this->getParenthesisNestLevel($phpcsFile, $prev);

            if ($prevNestLevel !== $currentNestLevel) {
                $searchPtr = $prev - 1;

                continue;
            }

            if ($this->hasChainBreakBetween($phpcsFile, $prev, $stackPtr)) {
                $searchPtr = $prev - 1;

                continue;
            }

            return $prev;
        }

        return null;
    }

    /**
     * Determines whether the tokens between two operators contain a chain-breaking token at the top level.
     *
     * @param File $phpcsFile
     * @param int  $startPtr
     * @param int  $endPtr
     */
    private function hasChainBreakBetween(File $phpcsFile, int $startPtr, int $endPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        $parenDepth = 0;
        $bracketDepth = 0;
        $braceDepth = 0;

        for ($ptr = $startPtr + 1; $ptr < $endPtr; ++$ptr) {
            $code = $tokens[$ptr]['code'];

            if ($code === T_OPEN_PARENTHESIS) {
                ++$parenDepth;

                continue;
            }

            if ($code === T_CLOSE_PARENTHESIS && $parenDepth > 0) {
                --$parenDepth;

                continue;
            }

            if (in_array($code, [T_OPEN_SQUARE_BRACKET, T_OPEN_SHORT_ARRAY], true)) {
                ++$bracketDepth;

                continue;
            }

            if (in_array($code, [T_CLOSE_SQUARE_BRACKET, T_CLOSE_SHORT_ARRAY], true) && $bracketDepth > 0) {
                --$bracketDepth;

                continue;
            }

            if ($code === T_OPEN_CURLY_BRACKET) {
                ++$braceDepth;

                continue;
            }

            if ($code === T_CLOSE_CURLY_BRACKET && $braceDepth > 0) {
                --$braceDepth;

                continue;
            }

            if ($parenDepth > 0) {
                continue;
            }

            if ($bracketDepth > 0) {
                continue;
            }

            if ($braceDepth > 0) {
                continue;
            }

            if ($this->isChainBreakToken($code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the given token code should break a method chain.
     *
     * @param int|string $code
     */
    private function isChainBreakToken(int|string $code): bool
    {
        return isset(self::ADDITIONAL_BREAK_TOKENS[$code])
            || isset(Tokens::ASSIGNMENT_TOKENS[$code])
            || isset(Tokens::COMPARISON_TOKENS[$code])
            || isset(Tokens::OPERATORS[$code])
            || isset(Tokens::BOOLEAN_OPERATORS[$code]);
    }

    /**
     * Calculates the parenthesis nesting level for a given token position.
     * This helps distinguish between chains at different nesting depths.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function getParenthesisNestLevel(File $phpcsFile, int $stackPtr): int
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['nested_parenthesis'])) {
            return 0;
        }

        return count($tokens[$stackPtr]['nested_parenthesis']);
    }
}
