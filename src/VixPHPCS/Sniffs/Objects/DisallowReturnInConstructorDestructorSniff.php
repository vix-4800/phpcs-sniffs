<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowReturnInConstructorDestructorSniff implements Sniff
{
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
        $functionPtr = $this->findDirectFunctionOwner($tokens[$stackPtr]['conditions']);

        if ($functionPtr === null) {
            return;
        }

        $functionName = $phpcsFile->getDeclarationName($functionPtr);

        if ($functionName === null || !in_array(mb_strtolower($functionName), ['__construct', '__destruct'], strict: true)) {
            return;
        }

        $phpcsFile->addError(
            'Return statements are not allowed inside constructors or destructors',
            $stackPtr,
            'ReturnFound',
        );
    }

    /**
     * @param array<int, int|string> $conditions
     */
    private function findDirectFunctionOwner(array $conditions): ?int
    {
        foreach (array_reverse($conditions, preserve_keys: true) as $conditionPtr => $conditionCode) {
            if ($conditionCode === T_CLOSURE) {
                return null;
            }

            if ($conditionCode === T_FUNCTION) {
                return $conditionPtr;
            }
        }

        return null;
    }
}
