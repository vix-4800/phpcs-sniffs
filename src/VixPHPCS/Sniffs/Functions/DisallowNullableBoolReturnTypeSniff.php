<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

final class DisallowNullableBoolReturnTypeSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_FUNCTION, T_DOC_COMMENT_TAG];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr]['code'] === T_FUNCTION) {
            $this->processFunction($phpcsFile, $stackPtr);

            return;
        }

        $this->processReturnTag($phpcsFile, $stackPtr);
    }

    private function processFunction(File $phpcsFile, int $stackPtr): void
    {
        if ($phpcsFile->getDeclarationName($stackPtr) === null) {
            return;
        }

        $returnType = $this->getNativeReturnType($phpcsFile, $stackPtr);

        if ($returnType === null || !$this->containsNullableBool($returnType['type'])) {
            return;
        }

        $phpcsFile->addError(
            'Do not use nullable "bool" as a return type; return true/false instead of null.',
            $returnType['pointer'],
            'NullableBoolNativeReturnType',
        );
    }

    private function processReturnTag(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        if (mb_strtolower((string) $tokens[$stackPtr]['content']) !== '@return') {
            return;
        }

        if (!isset($tokens[$stackPtr]['comment_closer'])) {
            return;
        }

        $functionPtr = $this->findFunctionOwner($phpcsFile, $tokens[$stackPtr]['comment_closer']);

        if ($functionPtr === null || $phpcsFile->getDeclarationName($functionPtr) === null) {
            return;
        }

        $typeString = $this->getDocCommentReturnType($phpcsFile, $stackPtr);

        if ($typeString === null || !$this->containsNullableBool($typeString)) {
            return;
        }

        $phpcsFile->addError(
            'Do not use nullable "bool" in @return; return true/false instead of null.',
            $stackPtr,
            'NullableBoolDocblockReturnType',
        );
    }

    /**
     * @return array{type: string, pointer: int}|null
     */
    private function getNativeReturnType(File $phpcsFile, int $functionPtr): ?array
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$functionPtr]['parenthesis_closer'])) {
            return null;
        }

        $afterParametersPtr = $phpcsFile->findNext(
            Tokens::EMPTY_TOKENS,
            $tokens[$functionPtr]['parenthesis_closer'] + 1,
            null,
            true,
        );

        if ($afterParametersPtr === false || $tokens[$afterParametersPtr]['code'] !== T_COLON) {
            return null;
        }

        $returnTypeEndPtr = $phpcsFile->findNext([T_OPEN_CURLY_BRACKET, T_SEMICOLON], $afterParametersPtr + 1);

        if ($returnTypeEndPtr === false) {
            return null;
        }

        $returnTypeStartPtr = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $afterParametersPtr + 1, $returnTypeEndPtr, true);

        if ($returnTypeStartPtr === false) {
            return null;
        }

        $typeString = '';

        for ($pointer = $returnTypeStartPtr; $pointer < $returnTypeEndPtr; ++$pointer) {
            $typeString .= (string) $tokens[$pointer]['content'];
        }

        return [
            'type' => $typeString,
            'pointer' => $returnTypeStartPtr,
        ];
    }

    private function getDocCommentReturnType(File $phpcsFile, int $stackPtr): ?string
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

        return $this->extractLeadingTypeExpression($content);
    }

    private function findFunctionOwner(File $phpcsFile, int $commentCloser): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $tokenCount = count($tokens);
        $current = $commentCloser + 1;

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

            if ($tokens[$current]['code'] === T_FUNCTION) {
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

    private function containsNullableBool(string $typeString): bool
    {
        $normalizedType = mb_strtolower(trim($typeString));

        if ($normalizedType === '') {
            return false;
        }

        if ($normalizedType === '?bool') {
            return true;
        }

        $types = array_map(
            static fn (string $type): string => mb_strtolower(trim($type)),
            $this->splitTopLevelUnionTypes($normalizedType),
        );

        return in_array('bool', $types, true) && in_array('null', $types, true);
    }

    private function extractLeadingTypeExpression(string $content): ?string
    {
        $trimmedContent = trim($content);

        if ($trimmedContent === '') {
            return null;
        }

        $type = '';
        $length = strlen($trimmedContent);

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

        $trimmedType = trim($type);

        return $trimmedType !== '' ? $trimmedType : null;
    }

    private function findNextNonWhitespaceCharacter(string $content, int $start): ?string
    {
        $length = strlen($content);

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
        return in_array($character, ['|', '&', '<', '>', '(', ')', '{', '}', '[', ']', ',', ':', '?'], true);
    }

    /**
     * @return list<string>
     */
    private function splitTopLevelUnionTypes(string $typeString): array
    {
        $types = [];
        $buffer = '';
        $angleDepth = 0;
        $braceDepth = 0;
        $bracketDepth = 0;
        $parenthesisDepth = 0;

        $length = strlen($typeString);

        for ($index = 0; $index < $length; ++$index) {
            $character = $typeString[$index];

            switch ($character) {
                case '<':
                    ++$angleDepth;
                    break;
                case '>':
                    $angleDepth = max(0, $angleDepth - 1);
                    break;
                case '{':
                    ++$braceDepth;
                    break;
                case '}':
                    $braceDepth = max(0, $braceDepth - 1);
                    break;
                case '[':
                    ++$bracketDepth;
                    break;
                case ']':
                    $bracketDepth = max(0, $bracketDepth - 1);
                    break;
                case '(':
                    ++$parenthesisDepth;
                    break;
                case ')':
                    $parenthesisDepth = max(0, $parenthesisDepth - 1);
                    break;
            }

            if (
                $character === '|'
                && $angleDepth === 0
                && $braceDepth === 0
                && $bracketDepth === 0
                && $parenthesisDepth === 0
            ) {
                $trimmedBuffer = trim($buffer);

                if ($trimmedBuffer !== '') {
                    $types[] = $trimmedBuffer;
                }

                $buffer = '';

                continue;
            }

            $buffer .= $character;
        }

        $trimmedBuffer = trim($buffer);

        if ($trimmedBuffer !== '') {
            $types[] = $trimmedBuffer;
        }

        return $types;
    }
}
