<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowSuspiciousLiteralTypesSniff implements Sniff
{
    use PhpDocTypeHelperTrait;

    private const array VALUE_TAGS = [
        '@param' => true,
        '@param-out' => true,
        '@phpstan-param' => true,
        '@phpstan-param-out' => true,
        '@phpstan-return' => true,
        '@phpstan-var' => true,
        '@property' => true,
        '@property-read' => true,
        '@property-write' => true,
        '@psalm-param' => true,
        '@psalm-param-out' => true,
        '@psalm-return' => true,
        '@psalm-var' => true,
        '@return' => true,
        '@var' => true,
    ];

    private const array TYPE_CONTAINER_TAGS = [
        '@extends' => true,
        '@implements' => true,
        '@phpstan-extends' => true,
        '@phpstan-implements' => true,
        '@phpstan-use' => true,
        '@psalm-extends' => true,
        '@psalm-implements' => true,
        '@psalm-use' => true,
        '@use' => true,
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
        $tagName = $this->getTagName($phpcsFile, $stackPtr);

        if (isset(self::VALUE_TAGS[$tagName]) || isset(self::TYPE_CONTAINER_TAGS[$tagName])) {
            $this->processValueTag($phpcsFile, $stackPtr, $tagName);

            return;
        }

        if (preg_match('/^@(phpstan-|psalm-)?template/', $tagName) === 1) {
            $this->processTemplateTag($phpcsFile, $stackPtr);
        }
    }

    private function processValueTag(File $phpcsFile, int $stackPtr, string $tagName): void
    {
        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null) {
            return;
        }

        if ($this->isSingleValueType($typeString)) {
            $phpcsFile->addWarning(
                'Suspicious single-value type "%s" in %s.',
                $stackPtr,
                'SuspiciousSingleValueType',
                [$typeString, $tagName],
            );
        }

        foreach ($this->collectNestedValueTypes($typeString) as $nestedType) {
            if (!$this->isSingleValueType($nestedType)) {
                continue;
            }

            $phpcsFile->addWarning(
                'Suspicious single-value type "%s" in nested PHPDoc value types.',
                $stackPtr,
                'SuspiciousNestedSingleValueType',
                [$nestedType],
            );
        }
    }

    private function processTemplateTag(File $phpcsFile, int $stackPtr): void
    {
        $content = $this->getTagContent($phpcsFile, $stackPtr);

        if ($content === null) {
            return;
        }

        $matches = [];

        if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\b(?:\s+of\s+(.+?))?(?:\s|$)/i', $content, $matches) !== 1) {
            return;
        }

        $templateName = $matches[1];

        if ($this->isNativeTypeName($templateName)) {
            $phpcsFile->addWarning(
                'Template name "%s" conflicts with a native PHPDoc type.',
                $stackPtr,
                'TemplateNameConflictsWithNativeType',
                [$templateName],
            );
        }

        $templateBound = $matches[2] ?? null;

        if ($templateBound === null) {
            return;
        }

        $templateBound = mb_trim($templateBound);

        if (!in_array($this->normalizeTypeName($templateBound), ['never', 'null', 'void'], strict: true)) {
            return;
        }

        $phpcsFile->addWarning(
            'Suspicious template bound "%s" in @template.',
            $stackPtr,
            'SuspiciousTemplateBound',
            [$templateBound],
        );
    }
}
