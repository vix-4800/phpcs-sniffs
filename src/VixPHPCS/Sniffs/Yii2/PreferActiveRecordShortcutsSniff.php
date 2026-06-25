<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Suggests using ActiveRecord shortcuts like findOne() and findAll() instead of find()->where()->one()/all().
 *
 * Yii2 provides convenient shortcut methods that are more concise and readable:
 * - Model::findOne($id) instead of Model::find()->where(['id' => $id])->one()
 * - Model::findAll($ids) instead of Model::find()->where(['id' => $ids])->all()
 */
final class PreferActiveRecordShortcutsSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * Processes this test when one of its tokens is encountered.
     *
     * @param File $phpcsFile
     * @param int  $stackPtr
     */
    public function process(File $phpcsFile, int $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        if ($token['content'] !== 'find') {
            return;
        }

        $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);

        if ($prevToken === false) {
            return;
        }

        $prevTokenCode = $tokens[$prevToken]['code'];

        if (!in_array($prevTokenCode, [T_DOUBLE_COLON, T_OBJECT_OPERATOR], strict: true)) {
            return;
        }

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($openParen === false || $tokens[$openParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$openParen]['parenthesis_closer'])) {
            return;
        }

        $closeParen = $tokens[$openParen]['parenthesis_closer'];

        $current = $closeParen + 1;
        $next = $phpcsFile->findNext(T_WHITESPACE, $current, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $current = $next + 1;
        $methodToken = $phpcsFile->findNext(T_WHITESPACE, $current, null, true);

        if (
            $methodToken === false
            || $tokens[$methodToken]['code'] !== T_STRING
            || $tokens[$methodToken]['content'] !== 'where'
        ) {
            return;
        }

        $whereOpenParen = $phpcsFile->findNext(T_WHITESPACE, $methodToken + 1, null, true);

        if ($whereOpenParen === false || $tokens[$whereOpenParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$whereOpenParen]['parenthesis_closer'])) {
            return;
        }

        $whereCloseParen = $tokens[$whereOpenParen]['parenthesis_closer'];

        $current = $whereCloseParen + 1;
        $next = $phpcsFile->findNext(T_WHITESPACE, $current, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $current = $next + 1;
        $methodToken = $phpcsFile->findNext(T_WHITESPACE, $current, null, true);

        if ($methodToken === false || $tokens[$methodToken]['code'] !== T_STRING) {
            return;
        }

        $methodName = $tokens[$methodToken]['content'];

        if (!in_array($methodName, ['one', 'all'], strict: true)) {
            return;
        }

        $methodOpenParen = $phpcsFile->findNext(T_WHITESPACE, $methodToken + 1, null, true);

        if ($methodOpenParen === false || $tokens[$methodOpenParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $this->reportViolation($phpcsFile, $stackPtr, $methodName);
    }

    /**
     * Report a violation.
     *
     * @param File   $phpcsFile
     * @param int    $stackPtr
     * @param string $endMethod
     */
    private function reportViolation(File $phpcsFile, int $stackPtr, string $endMethod): void
    {
        $shortcut = $endMethod === 'one' ? 'findOne()' : 'findAll()';
        $message = sprintf(
            'Use %s shortcut method instead of find()->where()->%s() for better readability',
            $shortcut,
            $endMethod,
        );

        $phpcsFile->addWarning($message, $stackPtr, 'UseShortcut');
    }
}
