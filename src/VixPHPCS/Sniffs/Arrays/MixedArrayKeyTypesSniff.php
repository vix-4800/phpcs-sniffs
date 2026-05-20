<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class MixedArrayKeyTypesSniff implements Sniff
{
    /**
     * @var list<int|string>
     */
    private const array NESTING_OPEN_TOKENS = [
        T_OPEN_PARENTHESIS,
        T_OPEN_SHORT_ARRAY,
        T_OPEN_SQUARE_BRACKET,
        T_OPEN_CURLY_BRACKET,
    ];

    /**
     * @var list<int|string>
     */
    private const array NESTING_CLOSE_TOKENS = [
        T_CLOSE_PARENTHESIS,
        T_CLOSE_SHORT_ARRAY,
        T_CLOSE_SQUARE_BRACKET,
        T_CLOSE_CURLY_BRACKET,
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [
            T_OPEN_SHORT_ARRAY,
            T_ARRAY,
        ];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $bounds = $this->getArrayBounds($phpcsFile, $stackPtr);

        if ($bounds === null) {
            return;
        }

        [$startPtr, $endPtr] = $bounds;
        $tokens = $phpcsFile->getTokens();
        $depth = 0;
        $hasIntegerKeys = false;
        $hasStringKeys = false;
        $itemStart = $startPtr;

        for ($i = $startPtr; $i < $endPtr; ++$i) {
            $tokenCode = $tokens[$i]['code'];

            if (in_array($tokenCode, self::NESTING_OPEN_TOKENS, true)) {
                ++$depth;

                continue;
            }

            if (in_array($tokenCode, self::NESTING_CLOSE_TOKENS, true) && $depth > 0) {
                --$depth;

                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if ($tokenCode !== T_COMMA) {
                continue;
            }

            $this->collectItemKeyType($phpcsFile, $itemStart, $i - 1, $hasIntegerKeys, $hasStringKeys);

            if ($hasIntegerKeys && $hasStringKeys) {
                $phpcsFile->addWarning(
                    'Do not mix integer and string keys in the same array literal.',
                    $stackPtr,
                    'MixedKeyTypes',
                );

                return;
            }

            $itemStart = $i + 1;
        }

        $this->collectItemKeyType($phpcsFile, $itemStart, $endPtr - 1, $hasIntegerKeys, $hasStringKeys);

        if (!$hasIntegerKeys || !$hasStringKeys) {
            return;
        }

        $phpcsFile->addWarning(
            'Do not mix integer and string keys in the same array literal.',
            $stackPtr,
            'MixedKeyTypes',
        );
    }

    /**
     * @return array{int, int}|null
     */
    private function getArrayBounds(File $phpcsFile, int $stackPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $tokenCode = $tokens[$stackPtr]['code'];

        if ($tokenCode === T_OPEN_SHORT_ARRAY) {
            $closer = $tokens[$stackPtr]['bracket_closer'] ?? null;

            return $closer === null ? null : [$stackPtr + 1, $closer];
        }

        $openParenthesis = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
            return null;
        }

        $closer = $tokens[$openParenthesis]['parenthesis_closer'] ?? null;

        return $closer === null ? null : [$openParenthesis + 1, $closer];
    }

    private function collectItemKeyType(
        File $phpcsFile,
        int $startPtr,
        int $endPtr,
        bool &$hasIntegerKeys,
        bool &$hasStringKeys,
    ): void {
        if ($startPtr > $endPtr) {
            return;
        }

        $keyType = $this->resolveItemKeyType($phpcsFile, $startPtr, $endPtr);

        if ($keyType === 'integer') {
            $hasIntegerKeys = true;

            return;
        }

        if ($keyType === 'string') {
            $hasStringKeys = true;
        }
    }

    private function resolveItemKeyType(File $phpcsFile, int $startPtr, int $endPtr): ?string
    {
        $firstToken = $phpcsFile->findNext(T_WHITESPACE, $startPtr, $endPtr + 1, true);

        if ($firstToken === false) {
            return null;
        }

        $tokens = $phpcsFile->getTokens();

        if ($tokens[$firstToken]['code'] === T_ELLIPSIS) {
            return null;
        }

        if ($this->startsWithArrowFunction($phpcsFile, $firstToken, $endPtr)) {
            return 'integer';
        }

        $keyArrow = $this->findArrayKeyArrow($phpcsFile, $startPtr, $endPtr);

        if ($keyArrow === null) {
            return 'integer';
        }

        return $this->resolveExplicitKeyType($phpcsFile, $startPtr, $keyArrow - 1);
    }

    private function startsWithArrowFunction(File $phpcsFile, int $startPtr, int $endPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $firstCode = $tokens[$startPtr]['code'];

        if ($firstCode === T_FN) {
            return true;
        }

        if ($firstCode !== T_STATIC) {
            return false;
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $startPtr + 1, $endPtr + 1, true);

        return $nextToken !== false && $tokens[$nextToken]['code'] === T_FN;
    }

    private function findArrayKeyArrow(File $phpcsFile, int $startPtr, int $endPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $depth = 0;

        for ($i = $startPtr; $i <= $endPtr; ++$i) {
            $tokenCode = $tokens[$i]['code'];

            if (in_array($tokenCode, self::NESTING_OPEN_TOKENS, true)) {
                ++$depth;

                continue;
            }

            if (in_array($tokenCode, self::NESTING_CLOSE_TOKENS, true) && $depth > 0) {
                --$depth;

                continue;
            }

            if ($depth === 0 && $tokenCode === T_DOUBLE_ARROW) {
                return $i;
            }
        }

        return null;
    }

    private function resolveExplicitKeyType(File $phpcsFile, int $startPtr, int $endPtr): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $firstToken = $phpcsFile->findNext(T_WHITESPACE, $startPtr, $endPtr + 1, true);

        if ($firstToken === false) {
            return null;
        }

        $tokenCode = $tokens[$firstToken]['code'];

        if ($tokenCode === T_LNUMBER) {
            return 'integer';
        }

        if (in_array($tokenCode, [T_MINUS, T_PLUS], true)) {
            $numberToken = $phpcsFile->findNext(T_WHITESPACE, $firstToken + 1, $endPtr + 1, true);

            if ($numberToken !== false && $tokens[$numberToken]['code'] === T_LNUMBER) {
                return 'integer';
            }
        }

        if (!in_array($tokenCode, [T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING], true)) {
            return null;
        }

        $literal = $this->normalizeStringLiteral((string) $tokens[$firstToken]['content']);

        if ($literal === null) {
            return 'string';
        }

        return preg_match('/^-?(0|[1-9]\d*)$/', $literal) === 1 ? 'integer' : 'string';
    }

    private function normalizeStringLiteral(string $literal): ?string
    {
        $value = ltrim($literal, 'bB');
        $quote = $value[0] ?? null;

        if ($quote === null || !in_array($quote, ['\'', '"'], true)) {
            return null;
        }

        $lastChar = substr($value, -1);

        if ($lastChar !== $quote) {
            return null;
        }

        $unquoted = substr($value, 1, -1);

        if (str_contains($unquoted, '\\') || ($quote === '"' && str_contains($unquoted, '$'))) {
            return null;
        }

        return $unquoted;
    }
}
