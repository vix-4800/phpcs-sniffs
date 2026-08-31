<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use VixPHPCS\Tests\Sniffs\Arrays\RequireAscendingIntegerArrayKeysSniffTest;

/**
 * Requires explicit integer array keys to be in ascending order.
 *
 * @see RequireAscendingIntegerArrayKeysSniffTest
 */
final class RequireAscendingIntegerArrayKeysSniff implements Sniff
{
    /**
     * @var list<int|string>
     */
    private const array OPEN_NESTING_TOKENS = [
        T_OPEN_PARENTHESIS,
        T_OPEN_SHORT_ARRAY,
        T_OPEN_SQUARE_BRACKET,
        T_OPEN_CURLY_BRACKET,
        T_CURLY_OPEN,
        T_DOLLAR_OPEN_CURLY_BRACES,
    ];

    /**
     * @var list<int|string>
     */
    private const array CLOSE_NESTING_TOKENS = [
        T_CLOSE_PARENTHESIS,
        T_CLOSE_SHORT_ARRAY,
        T_CLOSE_SQUARE_BRACKET,
        T_CLOSE_CURLY_BRACKET,
    ];

    /**
     * @var list<int|string>
     */
    private const array IGNORED_KEY_TOKENS = [
        T_WHITESPACE,
        T_COMMENT,
        T_DOC_COMMENT_OPEN_TAG,
        T_DOC_COMMENT_CLOSE_TAG,
        T_DOC_COMMENT_STRING,
        T_DOC_COMMENT_TAG,
        T_DOC_COMMENT_WHITESPACE,
    ];

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

        [$start, $end] = $bounds;
        $tokens = $phpcsFile->getTokens();
        $previousKey = null;
        $elementStart = $start;
        $depth = 0;

        for ($pointer = $start; $pointer < $end; ++$pointer) {
            $code = $tokens[$pointer]['code'];

            if (in_array($code, self::OPEN_NESTING_TOKENS, strict: true)) {
                ++$depth;

                continue;
            }

            if (in_array($code, self::CLOSE_NESTING_TOKENS, strict: true)) {
                if ($depth > 0) {
                    --$depth;
                }

                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if ($code === T_COMMA) {
                $elementStart = $pointer + 1;

                continue;
            }

            if ($code !== T_DOUBLE_ARROW) {
                continue;
            }

            $keyData = $this->getIntegerKeyData($phpcsFile, $elementStart, $pointer - 1);

            if ($keyData === null) {
                continue;
            }

            if ($previousKey !== null && $keyData['value'] <= $previousKey['value']) {
                $phpcsFile->addWarning(
                    'Integer array key %d must be greater than the preceding integer key %d.',
                    $keyData['pointer'],
                    'NonAscendingIntegerKey',
                    [$keyData['value'], $previousKey['value']],
                );
            }

            $previousKey = $keyData;
        }
    }

    /**
     * @param File $phpcsFile
     * @param int  $stackPtr
     *
     * @return array{0: int, 1: int}|null
     */
    private function getArrayBounds(File $phpcsFile, int $stackPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_OPEN_SHORT_ARRAY) {
            $closer = $tokens[$stackPtr]['bracket_closer'] ?? null;

            return $closer === null ? null : [$stackPtr + 1, $closer];
        }

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParen === false || $tokens[$openParen]['code'] !== T_OPEN_PARENTHESIS) {
            return null;
        }

        $closer = $tokens[$openParen]['parenthesis_closer'] ?? null;

        return $closer === null ? null : [$openParen + 1, $closer];
    }

    /**
     * @param File $phpcsFile
     * @param int  $start
     * @param int  $end
     *
     * @return array{value: int, pointer: int}|null
     */
    private function getIntegerKeyData(File $phpcsFile, int $start, int $end): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $keyTokens = [];
        $keyPointers = [];

        for ($pointer = $start; $pointer <= $end; ++$pointer) {
            if (!isset($tokens[$pointer]) || in_array($tokens[$pointer]['code'], self::IGNORED_KEY_TOKENS, strict: true)) {
                continue;
            }

            $keyTokens[] = $tokens[$pointer];
            $keyPointers[] = $pointer;
        }

        if (count($keyTokens) === 1 && $keyTokens[0]['code'] === T_LNUMBER) {
            return $this->createIntegerKeyData($keyTokens[0]['content'], $keyPointers[0]);
        }

        if (
            count($keyTokens) === 2
            && in_array($keyTokens[0]['code'], [T_PLUS, T_MINUS], strict: true)
            && $keyTokens[1]['code'] === T_LNUMBER
        ) {
            return $this->createIntegerKeyData(
                $keyTokens[0]['content'] . $keyTokens[1]['content'],
                $keyPointers[0],
            );
        }

        return null;
    }

    /**
     * @return array{value: int, pointer: int}
     */
    private function createIntegerKeyData(string $content, int $pointer): array
    {
        return [
            'value' => (int) str_replace('_', '', $content),
            'pointer' => $pointer,
        ];
    }
}
