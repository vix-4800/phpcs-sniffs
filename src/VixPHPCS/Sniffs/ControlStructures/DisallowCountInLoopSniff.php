<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ValueError;

/**
 * Disallows usage of count() function in loop conditions.
 *
 * This sniff detects count() function calls within for loop conditions,
 * which causes the count to be recalculated on every iteration.
 * It suggests storing the count in a variable before the loop or using foreach.
 */
final class DisallowCountInLoopSniff implements Sniff
{
    /**
     * Expected number of semicolons in a for loop (init; condition; increment).
     */
    private const int EXPECTED_SEMICOLON_COUNT = 2;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FOR];
    }

    /**
     * Processes this test when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @throws ValueError
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['parenthesis_opener'], $tokens[$stackPtr]['parenthesis_closer'])) {
            return;
        }

        $openParenthesis = $tokens[$stackPtr]['parenthesis_opener'];
        $closeParenthesis = $tokens[$stackPtr]['parenthesis_closer'];

        $semicolons = [];

        for ($i = $openParenthesis + 1; $i < $closeParenthesis; ++$i) {
            if ($tokens[$i]['code'] !== T_SEMICOLON) {
                continue;
            }

            $semicolons[] = $i;
        }

        if (count($semicolons) !== self::EXPECTED_SEMICOLON_COUNT) {
            return;
        }

        $conditionStart = $semicolons[0] + 1;
        $conditionEnd = $semicolons[1] - 1;

        for ($i = $conditionStart; $i <= $conditionEnd; ++$i) {
            if ($tokens[$i]['code'] !== T_STRING) {
                continue;
            }

            if (mb_strtolower((string) $tokens[$i]['content']) !== 'count') {
                continue;
            }

            $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, (int) $i - 1, null, true);

            if ($prevToken !== false) {
                $prevTokenCode = $tokens[$prevToken]['code'];

                if (in_array($prevTokenCode, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                    continue;
                }

                if ($prevTokenCode === T_FUNCTION) {
                    continue;
                }
            }

            $nextToken = $phpcsFile->findNext(T_WHITESPACE, $i + 1, null, true);

            if ($nextToken === false) {
                continue;
            }

            if ($tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
                continue;
            }

            $error = 'Using count() in loop condition causes performance issues as
                it is called on every iteration. Store count in a variable before the loop
                or use foreach instead';
            $phpcsFile->addWarning($error, $i, 'Found');
        }
    }
}
