<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class StaticInFinalClassSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        if ($phpcsFile->getDeclarationName($stackPtr) === null) {
            return;
        }

        $classPtr = $this->findEnclosingClassPtr($phpcsFile, $stackPtr);

        if ($classPtr === null || !$this->isFinalClass($phpcsFile, $classPtr)) {
            return;
        }

        $staticReturnTypePtr = $this->findStaticReturnTypePtr($phpcsFile, $stackPtr);

        if ($staticReturnTypePtr === null) {
            return;
        }

        $phpcsFile->addWarning(
            'Use "self" instead of "static" as the return type inside final classes.',
            $staticReturnTypePtr,
            'StaticReturnType',
        );
    }

    private function findEnclosingClassPtr(File $phpcsFile, int $stackPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $conditions = $tokens[$stackPtr]['conditions'];

        foreach (array_reverse($conditions, true) as $conditionPtr => $conditionCode) {
            if ($conditionCode === T_CLASS) {
                return (int) $conditionPtr;
            }
        }

        return null;
    }

    private function isFinalClass(File $phpcsFile, int $classPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $currentPtr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $classPtr - 1, null, true);

        while ($currentPtr !== false) {
            if ($tokens[$currentPtr]['code'] === T_FINAL) {
                return true;
            }

            if ($tokens[$currentPtr]['code'] !== T_READONLY) {
                return false;
            }

            $currentPtr = $phpcsFile->findPrevious(Tokens::EMPTY_TOKENS, $currentPtr - 1, null, true);
        }

        return false;
    }

    private function findStaticReturnTypePtr(File $phpcsFile, int $functionPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$functionPtr]['parenthesis_closer'])) {
            return null;
        }

        $afterParametersPtr = $phpcsFile->findNext(
            Tokens::EMPTY_TOKENS,
            $tokens[$functionPtr]['parenthesis_closer'] + 1,
            null,
            true,
        );

        if ($afterParametersPtr === false || $tokens[$afterParametersPtr]['code'] !== T_COLON) {
            return null;
        }

        $returnTypeEndPtr = $phpcsFile->findNext([T_OPEN_CURLY_BRACKET, T_SEMICOLON], $afterParametersPtr + 1);

        if ($returnTypeEndPtr === false) {
            return null;
        }

        $staticReturnTypePtr = $phpcsFile->findNext(T_STATIC, $afterParametersPtr + 1, $returnTypeEndPtr);

        if ($staticReturnTypePtr === false) {
            return null;
        }

        return $staticReturnTypePtr;
    }
}
