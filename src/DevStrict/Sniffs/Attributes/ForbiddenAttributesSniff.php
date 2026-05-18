<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Attributes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class ForbiddenAttributesSniff implements Sniff
{
    /**
     * List of forbidden attributes.
     *
     * @var list<string>
     */
    public array $forbiddenAttributes = [];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_ATTRIBUTE];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        // T_ATTRIBUTE is the `#[` token.
        // We need to find the attribute name which follows.
        $attributeNameStart = $phpcsFile->findNext([T_WHITESPACE, T_COMMENT], $stackPtr + 1, null, true);

        if ($attributeNameStart === false) {
            return;
        }

        // The attribute name can be a simple string, or a namespaced name.
        // It ends when we hit something that is not part of the name (like `(`, `]`, whitespace, etc.)
        $nameEnd = $phpcsFile->findNext(
            [T_STRING, T_NS_SEPARATOR, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED],
            $attributeNameStart,
            null,
            true,
        );

        if ($nameEnd === false) {
            return;
        }

        $name = $phpcsFile->getTokensAsString($attributeNameStart, $nameEnd - $attributeNameStart);

        // Check if the attribute is in the forbidden list.
        // We check both the exact match and the name without the leading backslash.
        foreach ($this->forbiddenAttributes as $forbidden) {
            if ($name === $forbidden || mb_ltrim($name, '\\') === $forbidden) {
                $phpcsFile->addWarning(
                    'Usage of attribute "%s" is forbidden.',
                    $attributeNameStart,
                    'Found',
                    [$name],
                );
            }
        }
    }
}
