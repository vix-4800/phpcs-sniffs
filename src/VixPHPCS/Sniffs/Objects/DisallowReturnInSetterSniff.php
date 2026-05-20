<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowReturnInSetterSniff implements Sniff
{
    /**
     * @var list<int|string>
     */
    private const array CLASS_LIKE_TOKENS = [
        T_ANON_CLASS,
        T_CLASS,
        T_ENUM,
        T_INTERFACE,
        T_TRAIT,
    ];

    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_RETURN];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $methodPtr = $this->findDirectSetterMethodOwner($tokens[$stackPtr]['conditions']);

        if ($methodPtr === null) {
            return;
        }

        $methodName = $phpcsFile->getDeclarationName($methodPtr);

        if (!$this->isSetterMethodName($methodName)) {
            return;
        }

        $phpcsFile->addError(
            'Return statements are not allowed inside setter methods',
            $stackPtr,
            'ReturnFound',
        );
    }

    private function isSetterMethodName(?string $methodName): bool
    {
        if ($methodName === null) {
            return false;
        }

        $normalizedMethodName = mb_strtolower($methodName);

        return str_starts_with($normalizedMethodName, 'set') && $normalizedMethodName !== 'set';
    }

    /**
     * @param array<int, int|string> $conditions
     */
    private function findDirectSetterMethodOwner(array $conditions): ?int
    {
        $functionPtr = null;

        foreach (array_reverse($conditions, true) as $conditionPtr => $conditionCode) {
            if ($conditionCode === T_CLOSURE) {
                return null;
            }

            if ($conditionCode === T_FUNCTION) {
                if ($functionPtr !== null) {
                    return null;
                }

                $functionPtr = $conditionPtr;

                continue;
            }

            if ($functionPtr === null) {
                continue;
            }

            if (in_array($conditionCode, self::CLASS_LIKE_TOKENS, true)) {
                return $functionPtr;
            }

            return null;
        }

        return null;
    }
}
