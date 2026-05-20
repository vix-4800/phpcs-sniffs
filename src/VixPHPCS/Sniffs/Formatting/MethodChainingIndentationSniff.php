<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Ensures chained method calls are indented consistently when split across multiple lines.
 */
final class MethodChainingIndentationSniff implements Sniff
{
    use MethodChainHelperTrait;

    private const int INDENT_SIZE = 4;

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        if (!$this->isMultiLineOperator($phpcsFile, $stackPtr)) {
            return;
        }

        $previousChainOperator = $this->findPreviousMultiLineOperator($phpcsFile, $stackPtr);

        if ($previousChainOperator !== null) {
            $this->assertIndentMatchesPreviousChain($phpcsFile, $stackPtr, $previousChainOperator);

            return;
        }

        $this->assertIndentRelativeToAnchor($phpcsFile, $stackPtr);
    }

    /**
     * Ensures the current operator lines up with the previous chain line.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     * @param int  $previousPtr
     */
    private function assertIndentMatchesPreviousChain(File $phpcsFile, int $stackPtr, int $previousPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $currentIndent = $tokens[$stackPtr]['column'] - 1;
        $expectedIndent = $tokens[$previousPtr]['column'] - 1;

        if ($currentIndent === $expectedIndent) {
            return;
        }

        $message = sprintf(
            'Chained call indentation must match the previous line (expected %d spaces, found %d)',
            $expectedIndent,
            $currentIndent,
        );

        $phpcsFile->addError($message, $stackPtr, 'MisalignedChainIndent');
    }

    /**
     * Ensures the first multi-line chained call is indented relative to the anchor line.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function assertIndentRelativeToAnchor(File $phpcsFile, int $stackPtr): void
    {
        $anchorInfo = $this->findAnchorLineInfo($phpcsFile, $stackPtr);

        if ($anchorInfo === null) {
            return;
        }

        $tokens = $phpcsFile->getTokens();
        $currentIndent = $tokens[$stackPtr]['column'] - 1;
        $expectedIndent = $anchorInfo['indent'] + self::INDENT_SIZE;

        if ($currentIndent === $expectedIndent) {
            return;
        }

        $message = sprintf(
            'First chained call must be indented by %d spaces relative to the anchor line (expected %d spaces, found %d)',
            self::INDENT_SIZE,
            $expectedIndent,
            $currentIndent,
        );

        $phpcsFile->addError($message, $stackPtr, 'InvalidFirstIndent');
    }

    /**
     * Locates the indentation info for the line that anchors the method chain.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return array{indent:int}|null
     */
    private function findAnchorLineInfo(File $phpcsFile, int $stackPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $currentLine = $tokens[$stackPtr]['line'];
        $ptr = $stackPtr - 1;

        while ($ptr >= 0) {
            $line = $tokens[$ptr]['line'];

            if ($line === $currentLine) {
                --$ptr;

                continue;
            }

            $lineStart = $this->findLineStartPtr($tokens, $ptr);
            $firstCodePtr = $this->findFirstCodeOnLine($tokens, $lineStart, $ptr);

            if ($firstCodePtr === null) {
                $ptr = $lineStart - 1;

                continue;
            }

            $code = $tokens[$firstCodePtr]['code'];

            if (isset(Tokens::COMMENT_TOKENS[$code])) {
                $ptr = $lineStart - 1;

                continue;
            }

            if (in_array($code, [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                $ptr = $lineStart - 1;

                continue;
            }

            return ['indent' => $tokens[$firstCodePtr]['column'] - 1];
        }

        return null;
    }

    /**
     * Finds the pointer representing the first token on the given line.
     *
     * @param CsTokens $tokens
     * @param int      $ptr
     */
    private function findLineStartPtr(array $tokens, int $ptr): int
    {
        $line = $tokens[$ptr]['line'];
        $lineStart = $ptr;

        while ($lineStart > 0 && $tokens[$lineStart - 1]['line'] === $line) {
            --$lineStart;
        }

        return $lineStart;
    }

    /**
     * Finds the first non-whitespace token on a line between the provided bounds.
     *
     * @param CsTokens $tokens
     * @param int      $startPtr
     * @param int      $endPtr
     */
    private function findFirstCodeOnLine(array $tokens, int $startPtr, int $endPtr): ?int
    {
        for ($ptr = $startPtr; $ptr <= $endPtr; ++$ptr) {
            if ($tokens[$ptr]['code'] === T_WHITESPACE) {
                continue;
            }

            return $ptr;
        }

        return null;
    }
}
