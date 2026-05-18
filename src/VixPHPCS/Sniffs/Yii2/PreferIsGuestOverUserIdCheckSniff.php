<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class PreferIsGuestOverUserIdCheckSniff implements Sniff
{
    public function register(): array
    {
        return [
            T_EMPTY,
            T_IS_IDENTICAL,
            T_IS_NOT_IDENTICAL,
            T_IS_EQUAL,
            T_IS_NOT_EQUAL,
        ];
    }

    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $code = $tokens[$stackPtr]['code'];

        if ($code === T_EMPTY) {
            $this->processEmpty($phpcsFile, $stackPtr);

            return;
        }

        $this->processNullComparison($phpcsFile, $stackPtr);
    }

    private function processEmpty(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $open = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($open === false || $tokens[$open]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$open]['parenthesis_closer'])) {
            return;
        }

        $close = $tokens[$open]['parenthesis_closer'];

        if (!$this->isYiiUserIdInside($phpcsFile, $open + 1, $close)) {
            return;
        }

        $prev = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        $neg = $prev !== false && $tokens[$prev]['code'] === T_BOOLEAN_NOT;

        $message = $neg
            ? 'Use !Yii::$app->user->isGuest instead of !empty(Yii::$app->user->id)'
            : 'Use Yii::$app->user->isGuest instead of empty(Yii::$app->user->id)';

        $phpcsFile->addWarning($message, $stackPtr, 'PreferIsGuestOverEmpty');
    }

    private function isYiiUserIdInside(File $phpcsFile, int $start, int $end): bool
    {
        $tokens = $phpcsFile->getTokens();
        $content = '';

        for ($i = $start; $i < $end; ++$i) {
            if ($tokens[$i]['code'] !== T_WHITESPACE) {
                $content .= $tokens[$i]['content'];
            }
        }

        return $content === 'Yii::$app->user->id';
    }

    private function processNullComparison(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $after = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($after === false || $tokens[$after]['code'] !== T_NULL) {
            return;
        }

        $before = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($before === false) {
            return;
        }

        if (!$this->isYiiUserIdChain($phpcsFile, $before)) {
            return;
        }

        $neg = in_array($tokens[$stackPtr]['code'], [T_IS_NOT_IDENTICAL, T_IS_NOT_EQUAL], true);
        $op = $tokens[$stackPtr]['content'];

        $message = $neg
            ? sprintf('Use !Yii::$app->user->isGuest instead of Yii::$app->user->id %s null', $op)
            : sprintf('Use Yii::$app->user->isGuest instead of Yii::$app->user->id %s null', $op);

        $phpcsFile->addWarning($message, $stackPtr, 'PreferIsGuestOverNullCheck');
    }

    private function isYiiUserIdChain(File $phpcsFile, int $ptr): bool
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$ptr]['code'] !== T_STRING || $tokens[$ptr]['content'] !== 'id') {
            return false;
        }

        $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);

        if ($ptr === false || $tokens[$ptr]['code'] !== T_OBJECT_OPERATOR) {
            return false;
        }

        $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);

        if ($ptr === false || $tokens[$ptr]['code'] !== T_STRING || $tokens[$ptr]['content'] !== 'user') {
            return false;
        }

        $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);

        if ($ptr === false || $tokens[$ptr]['code'] !== T_OBJECT_OPERATOR) {
            return false;
        }

        $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);

        if ($ptr === false || $tokens[$ptr]['code'] !== T_VARIABLE || $tokens[$ptr]['content'] !== '$app') {
            return false;
        }

        $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);

        if ($ptr === false || $tokens[$ptr]['code'] !== T_DOUBLE_COLON) {
            return false;
        }

        $ptr = $phpcsFile->findPrevious(T_WHITESPACE, $ptr - 1, null, true);

        return $ptr !== false
            && $tokens[$ptr]['code'] === T_STRING
            && $tokens[$ptr]['content'] === 'Yii';
    }
}
