<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\PhpDoc;

use PHP_CodeSniffer\Files\File;

trait PhpDocTypeHelperTrait
{
    private function getTagName(File $phpcsFile, int $stackPtr): string
    {
        $tokens = $phpcsFile->getTokens();

        return mb_strtolower((string) $tokens[$stackPtr]['content']);
    }

    private function getTagContent(File $phpcsFile, int $stackPtr): ?string
    {
        $tokens = $phpcsFile->getTokens();
        $commentCloser = $tokens[$stackPtr]['comment_closer'] ?? null;

        if ($commentCloser === null) {
            return null;
        }

        $nextToken = $phpcsFile->findNext(T_DOC_COMMENT_WHITESPACE, $stackPtr + 1, $commentCloser, true);

        if ($nextToken === false) {
            return null;
        }

        $line = $tokens[$stackPtr]['line'];
        $content = '';

        for ($pointer = $nextToken; $pointer < $commentCloser; ++$pointer) {
            if ($tokens[$pointer]['line'] !== $line) {
                break;
            }

            if ($tokens[$pointer]['code'] === T_DOC_COMMENT_TAG) {
                break;
            }

            $content .= (string) $tokens[$pointer]['content'];
        }

        $trimmedContent = mb_trim($content);

        return $trimmedContent !== '' ? $trimmedContent : null;
    }

    private function getTagTypeExpression(File $phpcsFile, int $stackPtr): ?string
    {
        $content = $this->getTagContent($phpcsFile, $stackPtr);

        if ($content === null) {
            return null;
        }

        return $this->extractLeadingTypeExpression($content);
    }

    private function extractLeadingTypeExpression(string $content): ?string
    {
        $trimmedContent = mb_trim($content);

        if ($trimmedContent === '') {
            return null;
        }

        $type = '';
        $length = mb_strlen($trimmedContent);

        for ($index = 0; $index < $length; ++$index) {
            $character = $trimmedContent[$index];

            if (ctype_space($character)) {
                $previousCharacter = $this->findPreviousNonWhitespaceCharacter($trimmedContent, $index - 1);
                $nextCharacter = $this->findNextNonWhitespaceCharacter($trimmedContent, $index + 1);

                if (
                    $previousCharacter !== null
                    && $nextCharacter !== null
                    && ($this->isTypeSeparator($previousCharacter) || $this->isTypeSeparator($nextCharacter))
                ) {
                    $type .= $character;

                    continue;
                }

                break;
            }

            $type .= $character;
        }

        $trimmedType = mb_trim($type);

        return $trimmedType !== '' ? $trimmedType : null;
    }

    /**
     * @param string $typeString
     *
     * @return list<string>
     */
    private function splitTopLevelUnionTypes(string $typeString): array
    {
        return $this->splitTopLevelBy($typeString, '|');
    }

    /**
     * @param string $typeString
     *
     * @return list<string>
     */
    private function splitTopLevelIntersectionTypes(string $typeString): array
    {
        return $this->splitTopLevelBy($typeString, '&');
    }

    /**
     * @param string $typeString
     * @param string $separator
     *
     * @return list<string>
     */
    private function splitTopLevelBy(string $typeString, string $separator): array
    {
        $parts = [];
        $buffer = '';
        $angleDepth = 0;
        $braceDepth = 0;
        $bracketDepth = 0;
        $parenthesisDepth = 0;
        $length = mb_strlen($typeString);

        for ($index = 0; $index < $length; ++$index) {
            $character = $typeString[$index];

            if ($character === '<') {
                ++$angleDepth;
            } elseif ($character === '>') {
                $angleDepth = max(0, $angleDepth - 1);
            } elseif ($character === '{') {
                ++$braceDepth;
            } elseif ($character === '}') {
                $braceDepth = max(0, $braceDepth - 1);
            } elseif ($character === '[') {
                ++$bracketDepth;
            } elseif ($character === ']') {
                $bracketDepth = max(0, $bracketDepth - 1);
            } elseif ($character === '(') {
                ++$parenthesisDepth;
            } elseif ($character === ')') {
                $parenthesisDepth = max(0, $parenthesisDepth - 1);
            }

            if (
                $character === $separator
                && $angleDepth === 0
                && $braceDepth === 0
                && $bracketDepth === 0
                && $parenthesisDepth === 0
            ) {
                $trimmedBuffer = mb_trim($buffer);

                if ($trimmedBuffer !== '') {
                    $parts[] = $trimmedBuffer;
                }

                $buffer = '';

                continue;
            }

            $buffer .= $character;
        }

        $trimmedBuffer = mb_trim($buffer);

        if ($trimmedBuffer !== '') {
            $parts[] = $trimmedBuffer;
        }

        return $parts;
    }

    /**
     * @param string $typeString
     *
     * @return list<string>
     */
    private function collectNestedValueTypes(string $typeString): array
    {
        $types = [];
        $length = mb_strlen($typeString);

        for ($index = 0; $index < $length; ++$index) {
            $character = $typeString[$index];

            if ($character === '<') {
                $closeIndex = $this->findMatchingDelimiter($typeString, $index, '<', '>');

                if ($closeIndex === null) {
                    continue;
                }

                $innerTypes = mb_substr($typeString, $index + 1, $closeIndex - $index - 1);

                foreach ($this->splitTopLevelBy($innerTypes, ',') as $part) {
                    $types[] = $part;
                    array_push($types, ...$this->collectNestedValueTypes($part));
                }

                $index = $closeIndex;

                continue;
            }

            if ($character === '{') {
                $closeIndex = $this->findMatchingDelimiter($typeString, $index, '{', '}');

                if ($closeIndex === null) {
                    continue;
                }

                $shapeTypes = mb_substr($typeString, $index + 1, $closeIndex - $index - 1);

                foreach ($this->extractArrayShapeValueTypes($shapeTypes) as $part) {
                    $types[] = $part;
                    array_push($types, ...$this->collectNestedValueTypes($part));
                }

                $index = $closeIndex;

                continue;
            }

            if ($character === '(' && $this->isCallableOpeningParenthesis($typeString, $index)) {
                $closeIndex = $this->findMatchingDelimiter($typeString, $index, '(', ')');

                if ($closeIndex === null) {
                    continue;
                }

                $callableParameterTypes = mb_substr($typeString, $index + 1, $closeIndex - $index - 1);

                foreach ($this->splitTopLevelBy($callableParameterTypes, ',') as $part) {
                    $types[] = $part;
                    array_push($types, ...$this->collectNestedValueTypes($part));
                }

                $index = $closeIndex;
            }
        }

        return $types;
    }

    /**
     * @param string $typeString
     *
     * @return list<string>
     */
    private function findDuplicateArrayShapeKeys(string $typeString): array
    {
        $duplicates = [];
        $length = mb_strlen($typeString);

        for ($index = 0; $index < $length; ++$index) {
            if ($typeString[$index] !== '{') {
                continue;
            }

            $closeIndex = $this->findMatchingDelimiter($typeString, $index, '{', '}');

            if ($closeIndex === null) {
                continue;
            }

            $seenKeys = [];
            $shape = mb_substr($typeString, $index + 1, $closeIndex - $index - 1);

            foreach ($this->splitTopLevelBy($shape, ',') as $item) {
                $colonIndex = $this->findTopLevelCharacter($item, ':');

                if ($colonIndex === null) {
                    continue;
                }

                $key = mb_rtrim(mb_trim(mb_substr((string) $item, 0, $colonIndex)), '?');
                $key = mb_trim($key, '\'"');

                if ($key === '') {
                    continue;
                }

                if (isset($seenKeys[$key])) {
                    $duplicates[] = $key;

                    continue;
                }

                $seenKeys[$key] = true;
            }

            $index = $closeIndex;
        }

        return array_values(array_unique($duplicates));
    }

    /**
     * @param string $shape
     *
     * @return list<string>
     */
    private function extractArrayShapeValueTypes(string $shape): array
    {
        $types = [];

        foreach ($this->splitTopLevelBy($shape, ',') as $item) {
            $colonIndex = $this->findTopLevelCharacter($item, ':');

            if ($colonIndex === null) {
                $types[] = $item;

                continue;
            }

            $type = mb_trim(mb_substr((string) $item, $colonIndex + 1));

            if ($type !== '') {
                $types[] = $type;
            }
        }

        return $types;
    }

    private function findTopLevelCharacter(string $typeString, string $target): ?int
    {
        $angleDepth = 0;
        $braceDepth = 0;
        $bracketDepth = 0;
        $parenthesisDepth = 0;
        $length = mb_strlen($typeString);

        for ($index = 0; $index < $length; ++$index) {
            $character = $typeString[$index];

            if ($character === '<') {
                ++$angleDepth;
            } elseif ($character === '>') {
                $angleDepth = max(0, $angleDepth - 1);
            } elseif ($character === '{') {
                ++$braceDepth;
            } elseif ($character === '}') {
                $braceDepth = max(0, $braceDepth - 1);
            } elseif ($character === '[') {
                ++$bracketDepth;
            } elseif ($character === ']') {
                $bracketDepth = max(0, $bracketDepth - 1);
            } elseif ($character === '(') {
                ++$parenthesisDepth;
            } elseif ($character === ')') {
                $parenthesisDepth = max(0, $parenthesisDepth - 1);
            }

            if (
                $character === $target
                && $angleDepth === 0
                && $braceDepth === 0
                && $bracketDepth === 0
                && $parenthesisDepth === 0
            ) {
                return $index;
            }
        }

        return null;
    }

    private function normalizeTypeName(string $type): string
    {
        return mb_strtolower(mb_ltrim(mb_trim($type), '\\'));
    }

    private function isSingleValueType(string $type): bool
    {
        $normalizedType = $this->normalizeTypeName($type);

        if (in_array($normalizedType, ['null', 'false', 'true'], strict: true)) {
            return true;
        }

        if (preg_match('/^-?\d+(?:\.\d+)?$/', $normalizedType) === 1) {
            return true;
        }

        return preg_match('/^([\'"]).*\1$/', mb_trim($type)) === 1;
    }

    private function isNativeTypeName(string $type): bool
    {
        return in_array(
            $this->normalizeTypeName($type),
            [
                'array',
                'bool',
                'callable',
                'false',
                'float',
                'int',
                'iterable',
                'mixed',
                'never',
                'null',
                'object',
                'resource',
                'scalar',
                'self',
                'static',
                'string',
                'true',
                'void',
            ],
            strict: true,
        );
    }

    private function findMatchingDelimiter(string $typeString, int $openIndex, string $open, string $close): ?int
    {
        $depth = 0;
        $length = mb_strlen($typeString);

        for ($index = $openIndex; $index < $length; ++$index) {
            if ($typeString[$index] === $open) {
                ++$depth;
            } elseif ($typeString[$index] === $close) {
                --$depth;

                if ($depth === 0) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function isCallableOpeningParenthesis(string $typeString, int $openIndex): bool
    {
        $prefix = mb_rtrim(mb_substr($typeString, 0, $openIndex));

        return preg_match('/(^|[^A-Za-z0-9_\\\])callable$/i', $prefix) === 1;
    }

    private function findNextNonWhitespaceCharacter(string $content, int $start): ?string
    {
        $length = mb_strlen($content);

        for ($index = $start; $index < $length; ++$index) {
            if (!ctype_space($content[$index])) {
                return $content[$index];
            }
        }

        return null;
    }

    private function findPreviousNonWhitespaceCharacter(string $content, int $start): ?string
    {
        for ($index = $start; $index >= 0; --$index) {
            if (!ctype_space($content[$index])) {
                return $content[$index];
            }
        }

        return null;
    }

    private function isTypeSeparator(string $character): bool
    {
        return in_array($character, ['|', '&', '<', '>', '(', ')', '{', '}', '[', ']', ',', ':', '?'], strict: true);
    }
}
