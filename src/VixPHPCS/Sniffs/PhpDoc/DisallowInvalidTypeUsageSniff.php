<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowInvalidTypeUsageSniff implements Sniff
{
    use PhpDocTypeHelperTrait;

    private const array VALUE_TAGS = [
        '@param' => true,
        '@property' => true,
        '@property-read' => true,
        '@property-write' => true,
        '@var' => true,
    ];

    private const array INVALID_VALUE_TYPES = [
        'never' => true,
        'void' => true,
    ];

    private const array INVALID_THROWS_TYPES = [
        'array' => true,
        'bool' => true,
        'callable' => true,
        'false' => true,
        'float' => true,
        'int' => true,
        'iterable' => true,
        'mixed' => true,
        'never' => true,
        'null' => true,
        'object' => true,
        'resource' => true,
        'scalar' => true,
        'stdclass' => true,
        'string' => true,
        'true' => true,
        'void' => true,
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

        if (isset(self::VALUE_TAGS[$tagName])) {
            $this->processValueTag($phpcsFile, $stackPtr, $tagName);

            return;
        }

        if ($tagName === '@throws') {
            $this->processThrowsTag($phpcsFile, $stackPtr);

            return;
        }

        if ($tagName === '@mixin') {
            $this->processMixinTag($phpcsFile, $stackPtr);
        }
    }

    private function processValueTag(File $phpcsFile, int $stackPtr, string $tagName): void
    {
        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null) {
            return;
        }

        foreach ($this->splitTopLevelUnionTypes($typeString) as $unionType) {
            foreach ($this->splitTopLevelIntersectionTypes($unionType) as $type) {
                $normalizedType = $this->normalizeTypeName($type);

                if (!isset(self::INVALID_VALUE_TYPES[$normalizedType])) {
                    continue;
                }

                $phpcsFile->addError(
                    'Do not use "%s" in %s value types.',
                    $stackPtr,
                    'InvalidValueType',
                    [$normalizedType, $tagName],
                );
            }

            $this->reportImpossibleIntersection($phpcsFile, $stackPtr, $tagName, $unionType);
        }

        foreach ($this->collectNestedValueTypes($typeString) as $nestedType) {
            foreach ($this->splitTopLevelUnionTypes($nestedType) as $unionType) {
                foreach ($this->splitTopLevelIntersectionTypes($unionType) as $type) {
                    $normalizedType = $this->normalizeTypeName($type);

                    if (!isset(self::INVALID_VALUE_TYPES[$normalizedType])) {
                        continue;
                    }

                    $phpcsFile->addError(
                        'Do not use "%s" in nested PHPDoc value types.',
                        $stackPtr,
                        'InvalidNestedValueType',
                        [$normalizedType],
                    );
                }
            }
        }

        foreach ($this->findDuplicateArrayShapeKeys($typeString) as $key) {
            $phpcsFile->addError(
                'Duplicate array shape key "%s".',
                $stackPtr,
                'DuplicateArrayShapeKey',
                [$key],
            );
        }
    }

    private function processThrowsTag(File $phpcsFile, int $stackPtr): void
    {
        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null) {
            return;
        }

        foreach ($this->splitTopLevelUnionTypes($typeString) as $type) {
            if (!isset(self::INVALID_THROWS_TYPES[$this->normalizeTypeName($type)])) {
                continue;
            }

            $phpcsFile->addError(
                '@throws must reference throwable class types.',
                $stackPtr,
                'InvalidThrowsType',
            );
        }
    }

    private function processMixinTag(File $phpcsFile, int $stackPtr): void
    {
        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null || !$this->isNativeTypeName($typeString)) {
            return;
        }

        $phpcsFile->addError(
            '@mixin must reference class-like types.',
            $stackPtr,
            'InvalidMixinType',
        );
    }

    private function reportImpossibleIntersection(File $phpcsFile, int $stackPtr, string $tagName, string $typeString): void
    {
        $types = $this->splitTopLevelIntersectionTypes($typeString);

        if (count($types) < 2) {
            return;
        }

        $normalizedTypes = array_map($this->normalizeTypeName(...), $types);

        if (!$this->containsImpossibleIntersection($normalizedTypes)) {
            return;
        }

        $phpcsFile->addError(
            'Impossible intersection type "%s" in %s.',
            $stackPtr,
            'ImpossibleIntersection',
            [implode('&', $types), $tagName],
        );
    }

    /**
     * @param list<string> $types
     */
    private function containsImpossibleIntersection(array $types): bool
    {
        $exclusiveTypes = [
            'array' => true,
            'bool' => true,
            'callable' => true,
            'false' => true,
            'float' => true,
            'int' => true,
            'never' => true,
            'null' => true,
            'string' => true,
            'true' => true,
            'void' => true,
        ];

        return count(array_intersect_key(array_flip($types), $exclusiveTypes)) > 1;
    }
}
