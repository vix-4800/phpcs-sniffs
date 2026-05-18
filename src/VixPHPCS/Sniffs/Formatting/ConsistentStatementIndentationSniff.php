<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures all statements inside the same scope have consistent indentation.
 *
 * This sniff checks that consecutive statements at the same nesting level
 * have the same indentation.
 */
final class ConsistentStatementIndentationSniff implements Sniff
{
    /**
     * The number of spaces for one indentation level.
     */
    public int $indent = 4;

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        // Only register for statement-starting tokens
        return [
            T_ECHO,
            T_PRINT,
            T_RETURN,
            T_IF,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_SWITCH,
            T_TRY,
            T_THROW,
            T_VARIABLE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        // Only process if this token is the first non-whitespace on its line
        $firstOnLine = $phpcsFile->findFirstOnLine(T_WHITESPACE, $stackPtr, true);

        if ($firstOnLine !== $stackPtr) {
            return;
        }

        if ($this->isInsideMultiLineExpression($phpcsFile, $stackPtr)) {
            return;
        }

        // Get the nesting level and conditions of this token
        $currentLevel = $token['level'] ?? 0;
        $currentConditions = $token['conditions'] ?? [];
        $currentIndent = $token['column'] - 1;

        // Find the previous statement at the same level with same conditions
        $prevStatement = $this->findPreviousStatementAtSameLevel($phpcsFile, $stackPtr, $currentLevel, $currentConditions);

        if ($prevStatement === null) {
            return;
        }

        $prevIndent = $tokens[$prevStatement]['column'] - 1;

        // If indentation differs and current has more spaces, report warning
        if ($currentIndent === $prevIndent || $currentIndent <= $prevIndent) {
            return;
        }

        $error = sprintf(
            'Statement indentation is inconsistent with previous statement at same level; found %d spaces but previous statement has %d',
            $currentIndent,
            $prevIndent,
        );

        $fix = $phpcsFile->addFixableWarning($error, $stackPtr, 'InconsistentIndentation');

        if ($fix !== true) {
            return;
        }

        $this->fixIndentation($phpcsFile, $stackPtr, (int) $prevIndent);
    }

    /**
     * Find the previous statement at the same nesting level with same conditions.
     *
     * @param File            $phpcsFile
     * @param int             $stackPtr
     * @param int             $level
     * @param array<int, int> $conditions
     */
    private function findPreviousStatementAtSameLevel(
        File $phpcsFile,
        int $stackPtr,
        int $level,
        array $conditions,
    ): ?int {
        $tokens = $phpcsFile->getTokens();
        $currentLine = $tokens[$stackPtr]['line'];

        $statementTokens = [
            T_ECHO,
            T_PRINT,
            T_RETURN,
            T_IF,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_SWITCH,
            T_TRY,
            T_THROW,
            T_VARIABLE,
            T_STRING,
        ];

        // Search backwards for the previous statement
        for ($i = $stackPtr - 1; $i >= 0; --$i) {
            $token = $tokens[$i];

            // Skip if on the same line
            if ($token['line'] === $currentLine) {
                continue;
            }

            // Check if this is a statement token
            if (!in_array($token['code'], $statementTokens, true)) {
                continue;
            }

            // Check if it's the first token on its line
            $firstOnLine = $phpcsFile->findFirstOnLine(T_WHITESPACE, $i, true);

            if ($firstOnLine !== $i) {
                continue;
            }

            if ($this->isInsideMultiLineExpression($phpcsFile, $i)) {
                continue;
            }

            // Check level and conditions
            $tokenLevel = $token['level'] ?? 0;
            $tokenConditions = $token['conditions'] ?? [];

            if ($tokenLevel === $level && $tokenConditions === $conditions) {
                return $i;
            }

            // If we hit a lower level, stop searching
            if ($tokenLevel < $level) {
                return null;
            }
        }

        return null;
    }

    /**
     * Check if a token is inside a multi-line expression (arrays, function calls, etc.).
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function isInsideMultiLineExpression(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['nested_parenthesis'])) {
            foreach ($tokens[$stackPtr]['nested_parenthesis'] as $opener => $closer) {
                if ($tokens[$opener]['line'] !== $tokens[$stackPtr]['line']) {
                    return true;
                }
            }
        }

        $arrayOpener = $phpcsFile->findPrevious(T_OPEN_SHORT_ARRAY, $stackPtr - 1);

        if ($arrayOpener !== false) {
            $arrayCloser = $tokens[$arrayOpener]['bracket_closer'] ?? null;

            if ($arrayCloser !== null && $arrayCloser > $stackPtr && $tokens[$arrayOpener]['line'] !== $tokens[$stackPtr]['line']) {
                return true;
            }
        }

        $arrayOpener = $phpcsFile->findPrevious(T_ARRAY, $stackPtr - 1);

        if ($arrayOpener !== false && isset($tokens[$arrayOpener]['parenthesis_closer'])) {
            $arrayCloser = $tokens[$arrayOpener]['parenthesis_closer'];

            if ($arrayCloser > $stackPtr && $tokens[$arrayOpener]['line'] !== $tokens[$stackPtr]['line']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fix the indentation.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     * @param int  $expectedIndent
     */
    private function fixIndentation(File $phpcsFile, int $stackPtr, int $expectedIndent): void
    {
        $tokens = $phpcsFile->getTokens();
        $actualIndent = $tokens[$stackPtr]['column'] - 1;

        if ($actualIndent === 0) {
            $phpcsFile->fixer->addContentBefore($stackPtr, str_repeat(' ', $expectedIndent));
        } else {
            // Find the whitespace token before
            $whitespace = $stackPtr - 1;

            if ($tokens[$whitespace]['code'] === T_WHITESPACE) {
                // Need to preserve newline and replace spaces
                $content = $tokens[$whitespace]['content'];
                $newContent = preg_replace('/\n[ ]*$/', "\n" . str_repeat(' ', $expectedIndent), (string) $content);

                if (is_string($newContent)) {
                    $phpcsFile->fixer->replaceToken($whitespace, $newContent);
                }
            }
        }
    }
}
