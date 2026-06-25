<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowVoidMixedWithOtherTypesSniff implements Sniff
{
    use PhpDocTypeHelperTrait;

    private const array TAGS_WITH_TYPES = [
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

        if ($tagName === '@return') {
            $this->reportReturnVoidMixedWithOtherTypes($phpcsFile, $stackPtr, $typeString);
        }

        $this->reportCallableReturnVoidMixedWithOtherTypes($phpcsFile, $stackPtr, $typeString);
    }

    private function reportReturnVoidMixedWithOtherTypes(File $phpcsFile, int $stackPtr, string $typeString): void
    {
        if (!$this->containsVoidMixedWithOtherTypes($typeString)) {
            return;
        }

        $phpcsFile->addError(
            '"void" cannot be combined with other return types in @return tag.',
            $stackPtr,
            'VoidMixedWithOtherTypes',
        );
    }

    private function reportCallableReturnVoidMixedWithOtherTypes(File $phpcsFile, int $stackPtr, string $typeString): void
    {
        foreach ($this->extractCallableReturnTypes($typeString) as $returnType) {
            if (!$this->containsVoidMixedWithOtherTypes($returnType)) {
                continue;
            }

            $phpcsFile->addError(
                '"void" cannot be combined with other return types in callable PHPDoc.',
                $stackPtr,
                'CallableVoidMixedWithOtherTypes',
            );
        }
    }

    private function containsVoidMixedWithOtherTypes(string $typeString): bool
    {
        $types = array_map($this->normalizeTypeName(...), $this->splitTopLevelUnionTypes($typeString));

        return in_array('void', $types, strict: true) && count($types) > 1;
    }

    /**
     * @param string $typeString
     *
     * @return list<string>
     */
    private function extractCallableReturnTypes(string $typeString): array
    {
        $returnTypes = [];
        $length = mb_strlen($typeString);

        for ($index = 0; $index < $length; ++$index) {
            if ($typeString[$index] !== '(') {
                continue;
            }

            if (!$this->isCallableOpeningParenthesis($typeString, $index)) {
                continue;
            }

            $closeIndex = $this->findMatchingDelimiter($typeString, $index, '(', ')');

            if ($closeIndex === null) {
                continue;
            }

            $colonIndex = $this->findCallableReturnColon($typeString, $closeIndex + 1);

            if ($colonIndex === null) {
                $index = $closeIndex;

                continue;
            }

            $returnType = $this->extractCallableReturnType($typeString, $colonIndex + 1);

            if ($returnType !== null) {
                $returnTypes[] = $returnType;
                array_push($returnTypes, ...$this->extractCallableReturnTypes($returnType));
            }

            $index = $closeIndex;
        }

        return $returnTypes;
    }

    private function findCallableReturnColon(string $typeString, int $start): ?int
    {
        $length = mb_strlen($typeString);

        for ($index = $start; $index < $length; ++$index) {
            if (ctype_space($typeString[$index])) {
                continue;
            }

            return $typeString[$index] === ':' ? $index : null;
        }

        return null;
    }

    private function extractCallableReturnType(string $typeString, int $start): ?string
    {
        $buffer = '';
        $angleDepth = 0;
        $braceDepth = 0;
        $bracketDepth = 0;
        $parenthesisDepth = 0;
        $length = mb_strlen($typeString);

        for ($index = $start; $index < $length; ++$index) {
            $character = $typeString[$index];

            if ($character === '<') {
                ++$angleDepth;
            } elseif ($character === '>') {
                if ($angleDepth === 0) {
                    break;
                }

                --$angleDepth;
            } elseif ($character === '{') {
                ++$braceDepth;
            } elseif ($character === '}') {
                if ($braceDepth === 0) {
                    break;
                }

                --$braceDepth;
            } elseif ($character === '[') {
                ++$bracketDepth;
            } elseif ($character === ']') {
                if ($bracketDepth === 0) {
                    break;
                }

                --$bracketDepth;
            } elseif ($character === '(') {
                ++$parenthesisDepth;
            } elseif ($character === ')') {
                if ($parenthesisDepth === 0) {
                    break;
                }

                --$parenthesisDepth;
            }

            if (
                $character === ','
                && $angleDepth === 0
                && $braceDepth === 0
                && $bracketDepth === 0
                && $parenthesisDepth === 0
            ) {
                break;
            }

            $buffer .= $character;
        }

        $returnType = mb_trim($buffer);

        return $returnType !== '' ? $returnType : null;
    }
}
