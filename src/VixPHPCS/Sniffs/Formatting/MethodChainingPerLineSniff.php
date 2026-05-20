<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Enforces that multi-line method chains keep one call per line and that inline calls aren't mixed in.
 */
final class MethodChainingPerLineSniff implements Sniff
{
    use MethodChainHelperTrait;

    /**
     * Keeps track of inline operators already reported.
     *
     * @var array<int, bool>
     */
    private array $reportedInlineOperators = [];

    /**
     * {@inheritDoc}
     */
    public function register(): array
    {
        return [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        if (!$this->isMultiLineOperator($phpcsFile, $stackPtr)) {
            return;
        }

        $this->ensureOnlyOneCallPerLine($phpcsFile, $stackPtr);
        $this->flagInlineCallsBefore($phpcsFile, $stackPtr);
    }

    /**
     * Ensures there is only one chained call per physical line once the chain spans multiple lines.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function ensureOnlyOneCallPerLine(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$stackPtr]['line'];

        for ($ptr = $stackPtr + 1, $count = count($tokens); $ptr < $count && $tokens[$ptr]['line'] === $line; ++$ptr) {
            if (!in_array($tokens[$ptr]['code'], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], true)) {
                continue;
            }

            if (!$this->isSameChainContext($tokens, $stackPtr, $ptr)) {
                continue;
            }

            if ($this->hasChainBreakBetween($phpcsFile, $stackPtr, $ptr)) {
                continue;
            }

            $phpcsFile->addError(
                'Only one chained method call is allowed per line when the chain spans multiple lines',
                $ptr,
                'MultipleCallsPerLine',
            );

            break;
        }
    }

    /**
     * Flags inline calls that appear before the first multi-line segment in the same chain.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    private function flagInlineCallsBefore(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $searchPtr = $stackPtr - 1;

        while (($prev = $phpcsFile->findPrevious([T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], $searchPtr, null, false)) !== false) {
            if ($this->hasChainBreakBetween($phpcsFile, $prev, $stackPtr)) {
                break;
            }

            if (!$this->isSameChainContext($tokens, $stackPtr, $prev)) {
                break;
            }

            if ($this->isMultiLineOperator($phpcsFile, $prev)) {
                break;
            }

            if ($this->isFirstOperatorInChain($phpcsFile, $prev)) {
                $searchPtr = $prev - 1;

                continue;
            }

            if (!isset($this->reportedInlineOperators[$prev])) {
                $this->reportedInlineOperators[$prev] = true;
                $phpcsFile->addError(
                    'Move this chained call to its own line to keep the multi-line chain consistent',
                    $prev,
                    'InlineBeforeMultiline',
                );
            }

            $searchPtr = $prev - 1;
        }
    }

    /**
     * Determines whether the provided operator is the first chained call in its context.
     *
     * @param File $phpcsFile
     * @param int  $operatorPtr
     */
    private function isFirstOperatorInChain(File $phpcsFile, int $operatorPtr): bool
    {
        $tokens = $phpcsFile->getTokens();
        $searchPtr = $operatorPtr - 1;

        while (($prev = $phpcsFile->findPrevious([T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR], $searchPtr, null, false)) !== false) {
            if ($this->hasChainBreakBetween($phpcsFile, $prev, $operatorPtr)) {
                return true;
            }

            return !$this->isSameChainContext($tokens, $operatorPtr, $prev);
        }

        return true;
    }

    /**
     * Checks whether two operators share the same chain context (scope and parenthesis nesting).
     *
     * @param CsTokens $tokens
     * @param int      $firstPtr
     * @param int      $secondPtr
     */
    private function isSameChainContext(array $tokens, int $firstPtr, int $secondPtr): bool
    {
        return $tokens[$firstPtr]['level'] === $tokens[$secondPtr]['level']
            && $this->buildContextKey($tokens[$firstPtr]) === $this->buildContextKey($tokens[$secondPtr]);
    }

    /**
     * Creates a comparable context key from nested parenthesis and conditions info.
     *
     * @param CsToken $token
     */
    private function buildContextKey(array $token): string
    {
        $conditions = $token['conditions'];
        $nested = $token['nested_parenthesis'] ?? [];

        $conditionPart = $conditions === [] ? '' : implode(',', array_keys($conditions));
        $nestedPart = $nested === [] ? '' : implode(',', array_keys($nested));

        return $conditionPart . '|' . $nestedPart;
    }
}
