<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects multiple OR/AND comparisons of the same variable and suggests using in_array() or !in_array().
 *
 * This sniff detects patterns like:
 * $var === Value1 || $var === Value2 || $var === Value3
 * $var !== Value1 && $var !== Value2 && $var !== Value3
 *
 * And suggests using:
 * in_array($var, [Value1, Value2, Value3], true)
 * !in_array($var, [Value1, Value2, Value3], true)
 */
final class UseInArraySniff implements Sniff
{
    /**
     * Minimum number of comparisons to trigger the sniff.
     */
    public int $minComparisons = 3;

    /**
     * Configurable threshold that overrides minComparisons when set.
     */
    public ?int $threshold = null;

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_IS_IDENTICAL, T_IS_NOT_IDENTICAL];
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

        $leftVar = $this->getComparisonVariable($phpcsFile, $stackPtr, true);

        if ($leftVar === null) {
            return;
        }

        $comparisonType = $tokens[$stackPtr]['code'];
        $isIdentical = ($comparisonType === T_IS_IDENTICAL);

        $comparisons = [$stackPtr];
        $expectedLogicalOperator = $isIdentical ? T_BOOLEAN_OR : T_BOOLEAN_AND;

        $statementEnd = $this->findStatementEnd($phpcsFile, $stackPtr);

        $currentPtr = $stackPtr;

        while (true) {
            $logicalOperator = $this->findNextLogicalOperator($phpcsFile, $currentPtr, $statementEnd);

            if ($logicalOperator === false || $tokens[$logicalOperator]['code'] !== $expectedLogicalOperator) {
                break;
            }

            $nextComparison = $this->findNextComparison($phpcsFile, $logicalOperator, $comparisonType, $statementEnd);

            if ($nextComparison === false) {
                break;
            }

            $nextVar = $this->getComparisonVariable($phpcsFile, $nextComparison, true);

            if ($nextVar === null || $nextVar !== $leftVar) {
                break;
            }

            $comparisons[] = $nextComparison;
            $currentPtr = $nextComparison;
        }

        $threshold = $this->getThreshold();

        if (count($comparisons) < $threshold) {
            return;
        }

        $operator = $isIdentical ? 'OR (||)' : 'AND (&&)';
        $function = $isIdentical ? 'in_array()' : '!in_array()';

        $warning = sprintf(
            'Multiple %s comparisons detected (%d comparisons). Consider using %s instead',
            $operator,
            count($comparisons),
            $function,
        );

        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }

    /**
     * Gets the variable name being compared.
     *
     * @param File $phpcsFile
     * @param int  $comparisonPtr
     * @param bool $left          True to get left side, false to get right side
     *
     * @return string|null The variable name or null if not found
     */
    private function getComparisonVariable(File $phpcsFile, int $comparisonPtr, bool $left): ?string
    {
        $tokens = $phpcsFile->getTokens();

        if ($left) {
            $varPtr = $phpcsFile->findPrevious(T_WHITESPACE, $comparisonPtr - 1, null, true);
        } else {
            $varPtr = $phpcsFile->findNext(T_WHITESPACE, $comparisonPtr + 1, null, true);
        }

        if ($varPtr === false || !isset($tokens[$varPtr])) {
            return null;
        }

        $varTokens = [];
        $ptr = $varPtr;

        if ($left) {
            while ($ptr !== false && isset($tokens[$ptr])) {
                $code = $tokens[$ptr]['code'];

                if (!in_array($code, [T_VARIABLE, T_STRING, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                    break;
                }

                $varTokens[] = $tokens[$ptr]['content'];
                $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);
            }

            $varTokens = array_reverse($varTokens);
        } else {
            while ($ptr !== false && isset($tokens[$ptr])) {
                $code = $tokens[$ptr]['code'];

                if (!in_array($code, [T_VARIABLE, T_STRING, T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                    break;
                }

                $varTokens[] = $tokens[$ptr]['content'];
                $ptr = $phpcsFile->findNext(T_WHITESPACE, $ptr + 1, null, true);
            }
        }

        if ($varTokens === []) {
            return null;
        }

        return implode('', $varTokens);
    }

    /**
     * Finds the end of the current statement.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return int Position of the statement end
     */
    private function findStatementEnd(File $phpcsFile, int $stackPtr): int
    {
        $tokens = $phpcsFile->getTokens();

        $endTokens = [T_SEMICOLON, T_CLOSE_PARENTHESIS, T_OPEN_CURLY_BRACKET, T_COLON];
        $end = $phpcsFile->findNext($endTokens, $stackPtr + 1);

        if ($end === false) {
            return count($tokens);
        }

        return $end;
    }

    /**
     * Finds the next logical operator (|| or &&) after the current position.
     *
     * @param File     $phpcsFile
     * @param int      $stackPtr
     * @param int|null $endPtr    Maximum position to search until
     *
     * @return false|int Position of the logical operator or false if not found
     */
    private function findNextLogicalOperator(File $phpcsFile, int $stackPtr, ?int $endPtr = null): false|int
    {
        return $phpcsFile->findNext(
            [T_BOOLEAN_OR, T_BOOLEAN_AND],
            $stackPtr + 1,
            $endPtr,
            false,
        );
    }

    /**
     * Finds the next comparison of the specified type after the current position.
     *
     * @param File       $phpcsFile
     * @param int        $stackPtr
     * @param int|string $comparisonType The comparison type to look for (T_IS_IDENTICAL or T_IS_NOT_IDENTICAL)
     * @param int|null   $endPtr         Maximum position to search until
     *
     * @return false|int Position of the comparison or false if not found
     */
    private function findNextComparison(
        File $phpcsFile,
        int $stackPtr,
        int|string $comparisonType,
        ?int $endPtr = null,
    ): false|int {
        return $phpcsFile->findNext(
            $comparisonType,
            $stackPtr + 1,
            $endPtr,
            false,
        );
    }

    /**
     * Returns the configured comparison threshold.
     */
    private function getThreshold(): int
    {
        if ($this->threshold !== null) {
            return $this->threshold;
        }

        return $this->minComparisons;
    }
}
