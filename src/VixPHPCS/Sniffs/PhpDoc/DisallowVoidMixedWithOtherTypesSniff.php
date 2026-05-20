<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowVoidMixedWithOtherTypesSniff implements Sniff
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

        if (mb_strtolower((string) $tokens[$stackPtr]['content']) !== '@return') {
            return;
        }

        if (!isset($tokens[$stackPtr]['comment_closer'])) {
            return;
        }

        $commentCloser = $tokens[$stackPtr]['comment_closer'];
        $nextToken = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, $stackPtr + 1, $commentCloser, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_DOC_COMMENT_STRING) {
            return;
        }

        $typeString = preg_split('/\s+/', (string) $tokens[$nextToken]['content'], 2)[0] ?? '';
        $types = array_map(trim(...), explode('|', $typeString));

        if (!in_array('void', $types, true)) {
            return;
        }

        if (count($types) === 1) {
            return;
        }

        $phpcsFile->addError(
            '"void" cannot be combined with other return types in @return tag.',
            $stackPtr,
            'VoidMixedWithOtherTypes',
        );
    }
}
