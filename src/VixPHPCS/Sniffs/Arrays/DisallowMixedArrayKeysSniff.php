<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows mixing keyed and unkeyed elements inside the same array literal.
 */
final class DisallowMixedArrayKeysSniff implements Sniff
{
    /**
     * Warning shown when an array mixes keyed and unkeyed elements.
     */
    private const string WARNING = 'Array literals must not mix keyed and unkeyed elements.';

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_OPEN_SHORT_ARRAY, T_ARRAY];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $bounds = $this->getArrayBounds($phpcsFile, $stackPtr);

        if ($bounds === null) {
            return;
        }

        [$contentStart, $contentEnd] = $bounds;

        if ($contentStart > $contentEnd) {
            return;
        }

        [$hasKeyedElement, $hasUnkeyedElement] = $this->detectElementTypes($phpcsFile, $contentStart, $contentEnd);

        if (!$hasKeyedElement || !$hasUnkeyedElement) {
            return;
        }

        $phpcsFile->addWarning(self::WARNING, $stackPtr, 'Found');
    }

    /**
     * @return array{int, int}|null
     */
    private function getArrayBounds(File $phpcsFile, int $stackPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        if ($token['code'] === T_OPEN_SHORT_ARRAY) {
            if (!isset($token['bracket_closer'])) {
                return null;
            }

            return [$stackPtr + 1, $token['bracket_closer'] - 1];
        }

        $openParenthesis = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
            return null;
        }

        if (!isset($tokens[$openParenthesis]['parenthesis_closer'])) {
            return null;
        }

        return [$openParenthesis + 1, $tokens[$openParenthesis]['parenthesis_closer'] - 1];
    }

    /**
     * @return array{bool, bool}
     */
    private function detectElementTypes(File $phpcsFile, int $contentStart, int $contentEnd): array
    {
        $tokens = $phpcsFile->getTokens();
        $hasKeyedElement = false;
        $hasUnkeyedElement = false;
        $elementHasContent = false;
        $elementIsKeyed = false;
        $elementStartsWithSpread = false;
        $arrowFunctionsToSkip = 0;

        for ($i = $contentStart; $i <= $contentEnd; ++$i) {
            $code = $tokens[$i]['code'];

            if ($code === T_COMMA) {
                [$hasKeyedElement, $hasUnkeyedElement] = $this->markElementType(
                    $hasKeyedElement,
                    $hasUnkeyedElement,
                    $elementHasContent,
                    $elementIsKeyed,
                    $elementStartsWithSpread,
                );

                if ($hasKeyedElement && $hasUnkeyedElement) {
                    return [$hasKeyedElement, $hasUnkeyedElement];
                }

                $elementHasContent = false;
                $elementIsKeyed = false;
                $elementStartsWithSpread = false;
                $arrowFunctionsToSkip = 0;

                continue;
            }

            $nestedCloser = $this->findNestedCloser($phpcsFile, $i);

            if ($nestedCloser !== null) {
                $elementHasContent = true;
                $i = $nestedCloser;

                continue;
            }

            if ($this->isIgnorableToken($code)) {
                continue;
            }

            if (!$elementHasContent && $code === T_ELLIPSIS) {
                $elementStartsWithSpread = true;
            }

            $elementHasContent = true;

            if ($code === T_FN) {
                ++$arrowFunctionsToSkip;

                continue;
            }

            if ($code !== T_DOUBLE_ARROW) {
                continue;
            }

            if ($arrowFunctionsToSkip > 0) {
                --$arrowFunctionsToSkip;

                continue;
            }

            $elementIsKeyed = true;
        }

        return $this->markElementType(
            $hasKeyedElement,
            $hasUnkeyedElement,
            $elementHasContent,
            $elementIsKeyed,
            $elementStartsWithSpread,
        );
    }

    /**
     * @return array{bool, bool}
     */
    private function markElementType(
        bool $hasKeyedElement,
        bool $hasUnkeyedElement,
        bool $elementHasContent,
        bool $elementIsKeyed,
        bool $elementStartsWithSpread,
    ): array {
        if (!$elementHasContent) {
            return [$hasKeyedElement, $hasUnkeyedElement];
        }

        if ($elementStartsWithSpread && !$elementIsKeyed) {
            return [$hasKeyedElement, $hasUnkeyedElement];
        }

        if ($elementIsKeyed) {
            return [true, $hasUnkeyedElement];
        }

        return [$hasKeyedElement, true];
    }

    private function findNestedCloser(File $phpcsFile, int $stackPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        if ($token['code'] === T_ARRAY) {
            $openParenthesis = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

            if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
                return null;
            }

            if (!isset($tokens[$openParenthesis]['parenthesis_closer'])) {
                return null;
            }

            return $tokens[$openParenthesis]['parenthesis_closer'];
        }

        if (isset($token['parenthesis_closer'])) {
            return $token['parenthesis_closer'];
        }

        if (isset($token['bracket_closer'])) {
            return $token['bracket_closer'];
        }

        if (isset($token['scope_closer'])) {
            return $token['scope_closer'];
        }

        return null;
    }

    private function isIgnorableToken(int|string $code): bool
    {
        return in_array(
            $code,
            [
                T_WHITESPACE,
                T_COMMENT,
                T_DOC_COMMENT_OPEN_TAG,
                T_DOC_COMMENT_WHITESPACE,
                T_DOC_COMMENT_STAR,
                T_DOC_COMMENT_STRING,
                T_DOC_COMMENT_TAG,
                T_DOC_COMMENT_CLOSE_TAG,
            ],
            true,
        );
    }
}
