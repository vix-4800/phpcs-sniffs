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
        '@param-out' => true,
        '@phpstan-param' => true,
        '@phpstan-param-out' => true,
        '@phpstan-var' => true,
        '@property' => true,
        '@property-read' => true,
        '@property-write' => true,
        '@psalm-param' => true,
        '@psalm-param-out' => true,
        '@psalm-var' => true,
        '@var' => true,
    ];

    private const array TYPE_CONTAINER_TAGS = [
        '@extends' => true,
        '@implements' => true,
        '@phpstan-extends' => true,
        '@phpstan-implements' => true,
        '@phpstan-return' => true,
        '@phpstan-use' => true,
        '@psalm-extends' => true,
        '@psalm-implements' => true,
        '@psalm-return' => true,
        '@psalm-use' => true,
        '@return' => true,
        '@use' => true,
    ];

    private const array INVALID_VALUE_TYPES = [
        'never' => true,
        'void' => true,
    ];

    private const array INVALID_THROWS_TYPES = [
        'array' => true,
        'bool' => true,
        'callable' => true,
        'class-string' => true,
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

        if (isset(self::VALUE_TAGS[$tagName]) || isset(self::TYPE_CONTAINER_TAGS[$tagName])) {
            $this->processTypeTag($phpcsFile, $stackPtr, $tagName);

            return;
        }

        if (in_array($tagName, ['@phpstan-throws', '@psalm-throws', '@throws'], strict: true)) {
            $this->processThrowsTag($phpcsFile, $stackPtr);

            return;
        }

        if (in_array($tagName, ['@mixin', '@phpstan-mixin', '@psalm-mixin'], strict: true)) {
            $this->processMixinTag($phpcsFile, $stackPtr);
        }
    }

    private function processTypeTag(File $phpcsFile, int $stackPtr, string $tagName): void
    {
        if (isset(self::VALUE_TAGS[$tagName])) {
            $this->processValueTag($phpcsFile, $stackPtr, $tagName);

            return;
        }

        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null) {
            return;
        }

        $this->reportInvalidNestedTypes($phpcsFile, $stackPtr, $typeString);
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

        $this->reportInvalidNestedTypes($phpcsFile, $stackPtr, $typeString);

        foreach ($this->findDuplicateArrayShapeKeys($typeString) as $key) {
            $phpcsFile->addError(
                'Duplicate array shape key "%s".',
                $stackPtr,
                'DuplicateArrayShapeKey',
                [$key],
            );
        }
    }

    private function reportInvalidNestedTypes(File $phpcsFile, int $stackPtr, string $typeString): void
    {
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
    }

    private function processThrowsTag(File $phpcsFile, int $stackPtr): void
    {
        $typeString = $this->getTagTypeExpression($phpcsFile, $stackPtr);

        if ($typeString === null) {
            return;
        }

        foreach ($this->splitTopLevelUnionTypes($typeString) as $type) {
            if (!isset(self::INVALID_THROWS_TYPES[$this->getBaseTypeName($type)])) {
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
