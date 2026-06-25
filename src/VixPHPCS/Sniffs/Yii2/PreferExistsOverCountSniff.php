<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests using exists() instead of count() > 0 or ->one() for ActiveQuery existence checks.
 *
 * The exists() method is more efficient than count() when you only need to check
 * if any records exist, as it stops after finding the first match instead of counting all records.
 * Similarly, using ->one() in conditional expressions loads the entire record when you only
 * need to know if it exists.
 *
 * Examples:
 * - ->count() > 0 should be ->exists()
 * - ->count() >= 1 should be ->exists()
 * - ->count() !== 0 should be ->exists()
 * - ->count() == 0 should be !->exists()
 * - ->count() < 1 should be !->exists()
 * - if (Model::find()->where(...)->one()) should be if (Model::find()->where(...)->exists())
 */
final class PreferExistsOverCountSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_STRING];
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
        $token = $tokens[$stackPtr];

        if ($token['content'] === 'count') {
            $this->processCount($phpcsFile, $stackPtr);
        } elseif ($token['content'] === 'one') {
            $this->processOne($phpcsFile, $stackPtr);
        }
    }

    /**
     * Process count() method calls.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function processCount(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken === false || $tokens[$prevToken]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$nextToken]['parenthesis_closer'])) {
            return;
        }

        $closeParen = $tokens[$nextToken]['parenthesis_closer'];
        $hasArguments = $this->hasArgumentsBetween($phpcsFile, $nextToken, $closeParen);

        if ($hasArguments) {
            return;
        }

        $comparisonStart = $phpcsFile->findNext(T_WHITESPACE, $closeParen + 1, null, true);

        if ($comparisonStart === false) {
            return;
        }

        $comparison = $this->getComparison($phpcsFile, $comparisonStart);

        if ($comparison === null) {
            return;
        }

        $this->reportViolation($phpcsFile, $stackPtr, $comparison);
    }

    /**
     * Process one() method calls in conditional contexts.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function processOne(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken === false || $tokens[$prevToken]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$nextToken]['parenthesis_closer'])) {
            return;
        }

        $closeParen = $tokens[$nextToken]['parenthesis_closer'];

        $hasArguments = $this->hasArgumentsBetween($phpcsFile, $nextToken, $closeParen);

        if ($hasArguments) {
            return;
        }

        if (!$this->isInConditionalContext($phpcsFile, $stackPtr)) {
            return;
        }

        $phpcsFile->addWarning(
            'Use ->exists() instead of ->one() when checking for record existence',
            $stackPtr,
            'PreferExistsOverOne',
        );
    }

    /**
     * Check if the method call is used in a conditional context (if, while, etc.).
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function isInConditionalContext(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        for ($i = $stackPtr; $i >= 0; --$i) {
            $token = $tokens[$i];

            if (in_array($token['code'], [T_IF, T_ELSEIF, T_WHILE, T_DO], strict: true)) {
                $openParen = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $i, $stackPtr);

                if ($openParen !== false && isset($tokens[$openParen]['parenthesis_closer'])) {
                    $closeParen = $tokens[$openParen]['parenthesis_closer'];

                    if ($stackPtr > $openParen && $stackPtr < $closeParen) {
                        return true;
                    }
                }
            }

            if (in_array($token['code'], [T_BOOLEAN_AND, T_BOOLEAN_OR, T_LOGICAL_AND, T_LOGICAL_OR], strict: true)) {
                return true;
            }

            if (in_array($token['code'], [T_SEMICOLON, T_OPEN_CURLY_BRACKET, T_CLOSE_CURLY_BRACKET], strict: true)) {
                break;
            }
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken !== false) {
            if ($tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS && isset($tokens[$nextToken]['parenthesis_closer'])) {
                $nextToken = $phpcsFile->findNext(T_WHITESPACE, $tokens[$nextToken]['parenthesis_closer'] + 1, null, true);
            }

            if ($nextToken !== false && $tokens[$nextToken]['code'] === T_INLINE_THEN) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if there are any arguments between parentheses.
     *
     * @param File $phpcsFile
     * @param int  $openParen
     * @param int  $closeParen
     */
    private function hasArgumentsBetween(File $phpcsFile, int $openParen, int $closeParen): bool
    {
        $tokens = $phpcsFile->getTokens();

        for ($i = $openParen + 1; $i < $closeParen; ++$i) {
            if (in_array($tokens[$i]['code'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], strict: true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the comparison type if it's a count existence check.
     *
     * @param File $phpcsFile
     * @param int  $startPtr
     *
     * @return array{operator: string, value: string, shouldNegate: bool}|null
     */
    private function getComparison(File $phpcsFile, int $startPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$startPtr];

        $operatorMap = [
            T_GREATER_THAN => '>',
            T_IS_GREATER_OR_EQUAL => '>=',
            T_IS_NOT_EQUAL => '!=',
            T_IS_NOT_IDENTICAL => '!==',
            T_IS_EQUAL => '==',
            T_IS_IDENTICAL => '===',
            T_LESS_THAN => '<',
            T_IS_SMALLER_OR_EQUAL => '<=',
        ];

        if (!array_key_exists($token['code'], $operatorMap)) {
            return null;
        }

        /** @var string $operator */
        $operator = $operatorMap[$token['code']];

        $valuePtr = $phpcsFile->findNext(T_WHITESPACE, $startPtr + 1, null, true);

        if ($valuePtr === false) {
            return null;
        }

        $valueToken = $tokens[$valuePtr];

        if ($valueToken['code'] !== T_LNUMBER) {
            return null;
        }

        $value = $valueToken['content'];

        $shouldNegate = false;

        if (
            ($operator === '>' && $value === '0')
            || ($operator === '>=' && $value === '1')
            || ($operator === '!=' && $value === '0')
            || ($operator === '!==' && $value === '0')
        ) {
            $shouldNegate = false;
        } elseif (
            ($operator === '==' && $value === '0')
            || ($operator === '===' && $value === '0')
            || ($operator === '<' && $value === '1')
            || ($operator === '<=' && $value === '0')
        ) {
            $shouldNegate = true;
        } else {
            return null;
        }

        return [
            'operator' => $operator,
            'value' => $value,
            'shouldNegate' => $shouldNegate,
        ];
    }

    /**
     * Report a violation.
     *
     * @param File                                                       $phpcsFile
     * @param int                                                        $stackPtr
     * @param array{operator: string, value: string, shouldNegate: bool} $comparison
     */
    private function reportViolation(File $phpcsFile, int $stackPtr, array $comparison): void
    {
        $suggestion = $comparison['shouldNegate'] ? '!...->exists()' : '...->exists()';
        $pattern = sprintf('count() %s %s', $comparison['operator'], $comparison['value']);

        $message = sprintf(
            'Use %s instead of %s for better performance when checking record existence',
            $suggestion,
            $pattern,
        );

        $phpcsFile->addWarning($message, $stackPtr, 'Found');
    }
}
