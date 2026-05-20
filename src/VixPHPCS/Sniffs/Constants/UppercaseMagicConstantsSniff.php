<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Constants;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Enforces uppercase spelling for PHP native magic constants.
 */
final class UppercaseMagicConstantsSniff implements Sniff
{
    /**
     * Canonical uppercase names for supported magic-constant token types.
     *
     * @var array<string, string>
     */
    private const array MAGIC_CONSTANTS = [
        'T_CLASS_C' => '__CLASS__',
        'T_DIR' => '__DIR__',
        'T_FILE' => '__FILE__',
        'T_FUNC_C' => '__FUNCTION__',
        'T_LINE' => '__LINE__',
        'T_METHOD_C' => '__METHOD__',
        'T_NS_C' => '__NAMESPACE__',
        'T_PROPERTY_C' => '__PROPERTY__',
        'T_TRAIT_C' => '__TRAIT__',
    ];

    /**
     * Returns an array of tokens this sniff wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        $tokens = [];

        foreach (array_keys(self::MAGIC_CONSTANTS) as $tokenType) {
            if (defined($tokenType)) {
                $tokens[] = constant($tokenType);
            }
        }

        return $tokens;
    }

    /**
     * Processes this sniff when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $tokenType = $tokens[$stackPtr]['type'];
        $expectedContent = self::MAGIC_CONSTANTS[$tokenType] ?? null;

        if ($expectedContent === null) {
            return;
        }

        $actualContent = (string) $tokens[$stackPtr]['content'];

        if ($actualContent === $expectedContent) {
            return;
        }

        $warning = sprintf(
            'PHP native magic constants must be written in uppercase; use %s instead of %s',
            $expectedContent,
            $actualContent,
        );

        $fix = $phpcsFile->addFixableWarning($warning, $stackPtr, 'NotUppercase');

        if ($fix !== true) {
            return;
        }

        $phpcsFile->fixer->replaceToken($stackPtr, $expectedContent);
    }
}
