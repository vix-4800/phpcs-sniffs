<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class DeprecatedTagSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_TAG];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (mb_strtolower((string) $tokens[$stackPtr]['content']) !== '@deprecated') {
            return;
        }

        if (!isset($tokens[$stackPtr]['comment_closer'])) {
            return;
        }

        $commentCloser = $tokens[$stackPtr]['comment_closer'];

        if ($this->findScopeOwner($phpcsFile, $commentCloser) === null) {
            return;
        }

        $phpcsFile->addWarning(
            'Use the #[\Deprecated] attribute instead of the @deprecated docblock tag.',
            $stackPtr,
            'UseAttribute',
        );
    }

    private function findScopeOwner(File $phpcsFile, int $commentCloser): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $tokenCount = count($tokens);
        $current = $commentCloser + 1;

        // #[\Deprecated] supports functions/methods, class constants, and enum cases,
        // but not class/interface/trait/enum declarations themselves.
        $scopeOwnerTokens = [
            T_FUNCTION => T_FUNCTION,
            T_CONST => T_CONST,
            T_ENUM_CASE => T_ENUM_CASE,
        ];

        $allowedTokens = [
            T_ABSTRACT => true,
            T_FINAL => true,
            T_READONLY => true,
            T_STATIC => true,
            T_PUBLIC => true,
            T_PROTECTED => true,
            T_PRIVATE => true,
        ];

        while ($current < $tokenCount) {
            $current = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $current, null, true);

            if ($current === false) {
                return null;
            }

            if ($tokens[$current]['code'] === T_ATTRIBUTE && isset($tokens[$current]['attribute_closer'])) {
                $current = $tokens[$current]['attribute_closer'] + 1;

                continue;
            }

            if (isset($scopeOwnerTokens[$tokens[$current]['code']])) {
                return $current;
            }

            if (isset($allowedTokens[$tokens[$current]['code']])) {
                ++$current;

                continue;
            }

            return null;
        }

        return null;
    }
}
