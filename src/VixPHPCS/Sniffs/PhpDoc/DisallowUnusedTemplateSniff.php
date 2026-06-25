<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class DisallowUnusedTemplateSniff implements Sniff
{
    private const array TEMPLATE_TAGS = [
        '@template',
        '@template-covariant',
        '@template-contravariant',
        '@phpstan-template',
        '@phpstan-template-covariant',
        '@phpstan-template-contravariant',
        '@psalm-template',
        '@psalm-template-covariant',
        '@psalm-template-contravariant',
    ];

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

        if (!in_array(mb_strtolower((string) $tokens[$stackPtr]['content']), self::TEMPLATE_TAGS, strict: true)) {
            return;
        }

        $templateName = $this->findTemplateName($phpcsFile, $stackPtr);

        if ($templateName === null) {
            return;
        }

        if ($this->isTemplateUsed($phpcsFile, $stackPtr, $templateName)) {
            return;
        }

        $phpcsFile->addWarning(
            'Template "%s" is declared but never used.',
            $stackPtr,
            'UnusedTemplate',
            [$templateName],
        );
    }

    private function findTemplateName(File $phpcsFile, int $stackPtr): ?string
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['comment_closer'])) {
            return null;
        }

        $commentCloser = $tokens[$stackPtr]['comment_closer'];
        $nextToken = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, $stackPtr + 1, $commentCloser, true);

        if ($nextToken === false || $tokens[$nextToken]['code'] !== T_DOC_COMMENT_STRING) {
            return null;
        }

        $matches = [];

        if (preg_match('/^([A-Za-z_]\w*)\b/', (string) $tokens[$nextToken]['content'], $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function isTemplateUsed(File $phpcsFile, int $stackPtr, string $templateName): bool
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$stackPtr]['comment_opener'], $tokens[$stackPtr]['comment_closer'])) {
            return false;
        }

        $commentOpener = $tokens[$stackPtr]['comment_opener'];
        $commentCloser = $tokens[$stackPtr]['comment_closer'];

        if ($this->containsTemplateUsage($phpcsFile, $commentOpener, $commentCloser, $templateName, $stackPtr)) {
            return true;
        }

        $scopeOwner = $this->findScopeOwner($phpcsFile, $commentCloser);

        if (
            $scopeOwner === null
            || !isset($tokens[$scopeOwner]['scope_opener'], $tokens[$scopeOwner]['scope_closer'])
        ) {
            return false;
        }

        return $this->containsTemplateUsage(
            $phpcsFile,
            $tokens[$scopeOwner]['scope_opener'] + 1,
            $tokens[$scopeOwner]['scope_closer'],
            $templateName,
            declarationTag: null,
        );
    }

    private function findScopeOwner(File $phpcsFile, int $commentCloser): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $tokenCount = count($tokens);
        $current = $commentCloser + 1;
        $scopeOwnerTokens = Tokens::OO_SCOPE_TOKENS + [T_FUNCTION => T_FUNCTION];
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

    private function containsTemplateUsage(
        File $phpcsFile,
        int $start,
        int $end,
        string $templateName,
        ?int $declarationTag,
    ): bool {
        $tokens = $phpcsFile->getTokens();
        $declarationLine = $declarationTag === null ? null : $tokens[$declarationTag]['line'];
        $pattern = '/(?<![A-Za-z0-9_\\\])' . preg_quote($templateName, '/') . '(?![A-Za-z0-9_])/';

        for ($i = $start; $i <= $end; ++$i) {
            if ($tokens[$i]['code'] !== T_DOC_COMMENT_STRING) {
                continue;
            }

            if ($declarationLine !== null && $tokens[$i]['line'] === $declarationLine) {
                continue;
            }

            if (preg_match($pattern, (string) $tokens[$i]['content']) === 1) {
                return true;
            }
        }

        return false;
    }
}
