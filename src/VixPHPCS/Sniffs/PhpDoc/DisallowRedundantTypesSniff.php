<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowRedundantTypesSniff implements Sniff
{
    use PhpDocTypeHelperTrait;

    private const array TAGS_WITH_TYPES = [
        '@param' => true,
        '@property' => true,
        '@property-read' => true,
        '@property-write' => true,
        '@return' => true,
        '@var' => true,
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

        if (!isset(self::TAGS_WITH_TYPES[$tagName])) {
            return;
        }

        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null) {
            return;
        }

        $types = $this->splitTopLevelUnionTypes($typeString);

        if (count($types) < 2) {
            return;
        }

        $this->reportDuplicateTypes($phpcsFile, $stackPtr, $tagName, $types);
        $this->reportRedundantNarrowerTypes($phpcsFile, $stackPtr, $typeString, $types);
    }

    /**
     * @param File         $phpcsFile
     * @param int          $stackPtr
     * @param string       $tagName
     * @param list<string> $types
     */
    private function reportDuplicateTypes(File $phpcsFile, int $stackPtr, string $tagName, array $types): void
    {
        $seenTypes = [];

        foreach ($types as $type) {
            $normalizedType = $this->normalizeTypeName($type);

            if (!isset($seenTypes[$normalizedType])) {
                $seenTypes[$normalizedType] = true;

                continue;
            }

            $phpcsFile->addWarning(
                'Duplicate PHPDoc union type "%s" in %s.',
                $stackPtr,
                'DuplicateUnionType',
                [$type, $tagName],
            );
        }
    }

    /**
     * @param File         $phpcsFile
     * @param int          $stackPtr
     * @param string       $typeString
     * @param list<string> $types
     */
    private function reportRedundantNarrowerTypes(File $phpcsFile, int $stackPtr, string $typeString, array $types): void
    {
        $normalizedTypes = array_fill_keys(
            array_map($this->normalizeTypeName(...), $types),
            value: true,
        );

        if (!$this->containsRedundantNarrowerType($normalizedTypes)) {
            return;
        }

        $phpcsFile->addWarning(
            'PHPDoc union type "%s" contains redundant narrower types.',
            $stackPtr,
            'RedundantNarrowerType',
            [$typeString],
        );
    }

    /**
     * @param array<string, true> $types
     */
    private function containsRedundantNarrowerType(array $types): bool
    {
        if (isset($types['mixed']) && count($types) > 1) {
            return true;
        }

        if (isset($types['bool']) && (isset($types['true']) || isset($types['false']))) {
            return true;
        }

        if (isset($types['object']) && $this->containsClassLikeType($types)) {
            return true;
        }

        if (isset($types['iterable']) && (isset($types['array']) || isset($types['traversable']))) {
            return true;
        }

        return isset($types['throwable']) && (isset($types['exception']) || isset($types['error']));
    }

    /**
     * @param array<string, true> $types
     */
    private function containsClassLikeType(array $types): bool
    {
        foreach (array_keys($types) as $type) {
            if ($type === 'object') {
                continue;
            }

            if (!$this->isNativeTypeName($type)) {
                return true;
            }
        }

        return false;
    }
}
