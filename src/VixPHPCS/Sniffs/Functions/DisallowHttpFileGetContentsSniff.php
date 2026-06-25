<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows file_get_contents() for HTTP requests.
 */
final class DisallowHttpFileGetContentsSniff implements Sniff
{
    /**
     * @var list<int|string>
     */
    private const array IGNORED_PREVIOUS_TOKENS = [
        T_OBJECT_OPERATOR,
        T_DOUBLE_COLON,
        T_NULLSAFE_OBJECT_OPERATOR,
        T_FUNCTION,
    ];

    /**
     * @var list<int|string>
     */
    private const array NESTING_OPEN_TOKENS = [
        T_OPEN_PARENTHESIS,
        T_OPEN_SHORT_ARRAY,
        T_OPEN_SQUARE_BRACKET,
    ];

    /**
     * @var list<int|string>
     */
    private const array NESTING_CLOSE_TOKENS = [
        T_CLOSE_PARENTHESIS,
        T_CLOSE_SHORT_ARRAY,
        T_CLOSE_SQUARE_BRACKET,
    ];

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
        $functionName = mb_strtolower((string) $tokens[$stackPtr]['content']);

        if ($functionName !== 'file_get_contents') {
            return;
        }

        if (!$this->isFunctionCall($phpcsFile, $stackPtr)) {
            return;
        }

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParen === false || !isset($tokens[$openParen]['parenthesis_closer'])) {
            return;
        }

        if (!$this->firstArgumentContainsHttpUrl($phpcsFile, $openParen, $tokens[$openParen]['parenthesis_closer'])) {
            return;
        }

        $warning = 'Do not use file_get_contents() for HTTP requests; use an HTTP client instead';
        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }

    /**
     * Checks that token is a global function call, not a method call or declaration.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function isFunctionCall(File $phpcsFile, int $stackPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken !== false) {
            $prevTokenCode = $tokens[$prevToken]['code'];

            if (in_array($prevTokenCode, self::IGNORED_PREVIOUS_TOKENS, strict: true)) {
                return false;
            }
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        return $nextToken !== false && $tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS;
    }

    /**
     * Checks whether first function argument contains an HTTP URL literal.
     *
     * @param File $phpcsFile
     * @param int  $openParen
     * @param int  $closeParen
     */
    private function firstArgumentContainsHttpUrl(File $phpcsFile, int $openParen, int $closeParen): bool
    {
        $tokens = $phpcsFile->getTokens();
        $depth = 0;

        for ($i = $openParen + 1; $i < $closeParen; ++$i) {
            if (in_array($tokens[$i]['code'], self::NESTING_OPEN_TOKENS, strict: true)) {
                ++$depth;

                continue;
            }

            if (in_array($tokens[$i]['code'], self::NESTING_CLOSE_TOKENS, strict: true)) {
                --$depth;

                continue;
            }

            if ($depth === 0 && $tokens[$i]['code'] === T_COMMA) {
                return false;
            }

            if ($this->tokenContainsHttpUrl((string) $tokens[$i]['content'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks token content for an HTTP URL string literal.
     *
     * @param string $content
     */
    private function tokenContainsHttpUrl(string $content): bool
    {
        $rawValue = mb_strtolower($content);

        if (str_starts_with($rawValue, 'http://') || str_starts_with($rawValue, 'https://')) {
            return true;
        }

        $string = mb_ltrim($content, 'bB');
        $quote = $string[0] ?? '';

        if (!in_array($quote, ["'", '"'], strict: true)) {
            return false;
        }

        $value = mb_substr($string, 1, -1);
        $value = mb_strtolower($value);

        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }
}
