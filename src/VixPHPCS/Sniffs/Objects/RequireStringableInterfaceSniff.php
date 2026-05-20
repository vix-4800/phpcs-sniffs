<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Objects;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class RequireStringableInterfaceSniff implements Sniff
{
    /**
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_CLASS, T_ANON_CLASS];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $toStringPtr = $this->findToStringMethod($phpcsFile, $stackPtr);

        if ($toStringPtr === null || $this->implementsStringable($phpcsFile, $stackPtr)) {
            return;
        }

        $phpcsFile->addWarning(
            'Classes declaring __toString() must implement Stringable',
            $toStringPtr,
            'MissingStringable',
        );
    }

    private function findToStringMethod(File $phpcsFile, int $classPtr): ?int
    {
        $tokens = $phpcsFile->getTokens();
        $scopeOpener = $tokens[$classPtr]['scope_opener'] ?? null;
        $scopeCloser = $tokens[$classPtr]['scope_closer'] ?? null;

        if (!is_int($scopeOpener) || !is_int($scopeCloser)) {
            return null;
        }

        for ($ptr = $scopeOpener + 1; $ptr < $scopeCloser; ++$ptr) {
            if ($tokens[$ptr]['code'] !== T_FUNCTION) {
                continue;
            }

            if ($this->findDirectClassOwner($tokens[$ptr]['conditions'] ?? []) !== $classPtr) {
                continue;
            }

            $functionName = $phpcsFile->getDeclarationName($ptr);

            if ($functionName !== null && mb_strtolower($functionName) === '__tostring') {
                return $ptr;
            }
        }

        return null;
    }

    private function implementsStringable(File $phpcsFile, int $classPtr): bool
    {
        $implementedInterfaceNames = $phpcsFile->findImplementedInterfaceNames($classPtr);

        if (!is_array($implementedInterfaceNames)) {
            return false;
        }

        foreach ($implementedInterfaceNames as $interfaceName) {
            if (ltrim(mb_strtolower($interfaceName), '\\') === 'stringable') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, int|string> $conditions
     */
    private function findDirectClassOwner(array $conditions): ?int
    {
        foreach (array_reverse($conditions, true) as $conditionPtr => $conditionCode) {
            if ($conditionCode === T_CLOSURE) {
                return null;
            }

            if (in_array($conditionCode, [T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
                return $conditionPtr;
            }
        }

        return null;
    }
}
