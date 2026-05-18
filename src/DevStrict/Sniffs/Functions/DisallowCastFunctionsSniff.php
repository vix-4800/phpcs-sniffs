<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use ValueError;

/**
 * Disallows usage of strval(), intval(), floatval(), boolval() functions in favor of type casts.
 *
 * Type casts are shorter, more consistent with strict typing style, and clearer in intent.
 *
 * Bad:
 * strval($var)
 * intval($var)
 * floatval($var)
 * boolval($var)
 *
 * Good:
 * (string) $var
 * (int) $var
 * (float) $var
 * (bool) $var
 */
final class DisallowCastFunctionsSniff implements Sniff
{
    /**
     * Map of disallowed functions to their cast equivalents.
     *
     * @var array<string, string>
     */
    private const array DISALLOWED_FUNCTIONS = [
        'strval' => '(string)',
        'intval' => '(int)',
        'floatval' => '(float)',
        'boolval' => '(bool)',
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
     *
     * @throws ValueError
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $functionName = mb_strtolower((string) $token['content']);

        if (!isset(self::DISALLOWED_FUNCTIONS[$functionName])) {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken !== false) {
            $prevTokenCode = $tokens[$prevToken]['code'];

            if (in_array($prevTokenCode, [T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                return;
            }

            if ($prevTokenCode === T_FUNCTION) {
                return;
            }
        }

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $castEquivalent = self::DISALLOWED_FUNCTIONS[$functionName];
        $warning = sprintf(
            'Use of %s() is discouraged; use type cast %s instead for consistency and brevity',
            $functionName,
            $castEquivalent,
        );

        $phpcsFile->addWarning($warning, $stackPtr, 'Found');
    }
}
