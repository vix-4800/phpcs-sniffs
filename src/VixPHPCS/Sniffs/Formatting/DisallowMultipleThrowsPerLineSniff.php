<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Formatting;

use Exception;
use JsonException;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Disallows multiple exception types in a single @throws annotation.
 *
 * Bad:
 *
 * @throws Exception
 * @throws JsonException
 * @throws JsonException
 *
 * Good:
 */
final class DisallowMultipleThrowsPerLineSniff implements Sniff
{
    /**
     * {@inheritDoc}
     *
     * @return array<int, mixed>
     */
    public function register(): array
    {
        return [T_DOC_COMMENT_TAG];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['content'] !== '@throws') {
            return;
        }

        $nextToken = $phpcsFile->findNext(
            [T_DOC_COMMENT_WHITESPACE],
            $stackPtr + 1,
            null,
            true,
        );

        if ($nextToken === false) {
            return;
        }

        if ($tokens[$nextToken]['code'] !== T_DOC_COMMENT_STRING) {
            return;
        }

        $exceptionTypes = $tokens[$nextToken]['content'];

        if (!str_contains($exceptionTypes, '|')) {
            return;
        }

        $types = array_map(trim(...), explode('|', $exceptionTypes));
        $types = array_filter($types, static fn($type): bool => $type !== '');

        if (count($types) <= 1) {
            return;
        }

        $error = 'Each @throws annotation must contain only one exception type. Found: %s. Use separate @throws for each exception.';
        $data = [implode(', ', $types)];
        $phpcsFile->addWarning($error, $stackPtr, 'MultipleExceptions', $data);
    }
}
