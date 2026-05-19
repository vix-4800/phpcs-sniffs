<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects duplicate explicit array keys in array declarations.
 */
final class DuplicateArrayKeySniff implements Sniff
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
        $seenKeys = [];
        $elementStart = $start;
        $depth = 0;

        for ($i = $start; $i < $end; ++$i) {
            $code = $tokens[$i]['code'];

            if (in_array($code, self::OPEN_NESTING_TOKENS, true)) {
                ++$depth;

                continue;
            }

            if (in_array($code, self::CLOSE_NESTING_TOKENS, true)) {
                if ($depth > 0) {
                    --$depth;
                }

                continue;
            }

            if ($depth !== 0) {
                continue;
            }

            if ($code === T_COMMA) {
                $elementStart = $i + 1;

                continue;
            }

            if ($code !== T_DOUBLE_ARROW) {
                continue;
            }

            $keyData = $this->getKeyData($phpcsFile, $elementStart, $i - 1);

            if ($keyData === null) {
                continue;
            }

            if (isset($seenKeys[$keyData['normalized']])) {
                $firstOccurrence = $seenKeys[$keyData['normalized']];
                $phpcsFile->addError(
                    sprintf(
                        'Duplicate array key %s detected; the previous entry is on line %d',
                        var_export($keyData['effective'], true),
                        $firstOccurrence['line'],
                    ),
                    $keyData['pointer'],
                    'DuplicateKey',
                );

                continue;
            }

            $seenKeys[$keyData['normalized']] = [
                'line' => $tokens[$keyData['pointer']]['line'],
            ];
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

            if ($closer === null) {
                return null;
            }

            return [$stackPtr + 1, $closer];
        }

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParen === false || $tokens[$openParen]['code'] !== T_OPEN_PARENTHESIS) {
            return null;
        }

        $closer = $tokens[$openParen]['parenthesis_closer'] ?? null;

        if ($closer === null) {
            return null;
        }

        return [$openParen + 1, $closer];
    }

    /**
     * @param File $phpcsFile
     * @param int  $start
     * @param int  $end
     *
     * @return array{normalized: string, effective: int|string, pointer: int}|null
     */
    private function getKeyData(File $phpcsFile, int $start, int $end): ?array
    {
        $tokens = $phpcsFile->getTokens();
        $sign = '';
        $keyTokens = [];
        $pointer = null;

        for ($i = $start; $i <= $end; ++$i) {
            if (!isset($tokens[$i])) {
                continue;
            }

            if (in_array($tokens[$i]['code'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG, T_DOC_COMMENT_WHITESPACE,], true)) {
                continue;
            }

            $pointer ??= $i;
            $keyTokens[] = $tokens[$i];
        }

        if ($keyTokens === [] || $pointer === null) {
            return null;
        }

        if (count($keyTokens) === 2 && in_array($keyTokens[0]['code'], [T_PLUS, T_MINUS], true) && in_array($keyTokens[1]['code'], [T_LNUMBER, T_DNUMBER], true)) {
            $sign = $keyTokens[0]['content'];
            $keyTokens = [$keyTokens[1]];
            $pointer = $pointer;
        }

        if (count($keyTokens) !== 1) {
            return null;
        }

        $token = $keyTokens[0];
        $content = $sign . $token['content'];

        return match ($token['code']) {
            T_LNUMBER => $this->createIntegerKeyData($content, $pointer),
            T_DNUMBER => $this->createFloatKeyData($content, $pointer),
            T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING => $this->createStringKeyData((string) $token['content'], $pointer),
            T_TRUE => $this->createEffectiveKeyData(1, $pointer),
            T_FALSE => $this->createEffectiveKeyData(0, $pointer),
            T_NULL => $this->createEffectiveKeyData('', $pointer),
            default => null,
        };
    }

    /**
     * @param int $pointer
     *
     * @return array{normalized: string, effective: int|string, pointer: int}
     */
    private function createIntegerKeyData(string $content, int $pointer): array
    {
        $normalizedContent = str_replace('_', '', $content);

        return $this->createEffectiveKeyData((int) $normalizedContent, $pointer);
    }

    /**
     * @param int $pointer
     *
     * @return array{normalized: string, effective: int|string, pointer: int}
     */
    private function createFloatKeyData(string $content, int $pointer): array
    {
        $normalizedContent = str_replace('_', '', $content);

        return $this->createEffectiveKeyData((int) ((float) $normalizedContent), $pointer);
    }

    /**
     * @param int $pointer
     *
     * @return array{normalized: string, effective: int|string, pointer: int}
     */
    private function createStringKeyData(string $content, int $pointer): array
    {
        $value = $this->stripStringQuotes($content);

        if (preg_match('/^-?(0|[1-9][0-9]*)$/', $value) === 1) {
            return $this->createEffectiveKeyData((int) $value, $pointer);
        }

        return $this->createEffectiveKeyData($value, $pointer);
    }

    /**
     * @param int|string $effectiveKey
     * @param int        $pointer
     *
     * @return array{normalized: string, effective: int|string, pointer: int}
     */
    private function createEffectiveKeyData(int|string $effectiveKey, int $pointer): array
    {
        $normalized = is_int($effectiveKey)
            ? 'i:' . $effectiveKey
            : 's:' . $effectiveKey;

        return [
            'normalized' => $normalized,
            'effective' => $effectiveKey,
            'pointer' => $pointer,
        ];
    }

    private function stripStringQuotes(string $content): string
    {
        $string = $content;

        if (($string[0] ?? '') === 'b' || ($string[0] ?? '') === 'B') {
            $string = mb_substr($string, 1);
        }

        $quote = $string[0] ?? '';
        $value = mb_substr($string, 1, -1);

        if ($quote === '\'') {
            return str_replace(
                ['\\\\', '\\\''],
                ['\\', '\''],
                $value,
            );
        }

        if ($quote === '"') {
            return stripcslashes($value);
        }

        return $value;
    }
}
