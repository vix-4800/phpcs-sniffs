<?php

declare(strict_types=1);

namespace VixPHPCS\Sniffs\Yii2;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Detects patterns where current user is queried from database and suggests using Yii::$app->user->identity.
 *
 * Using Yii::$app->user->identity is more efficient than querying the database.
 */
final class PreferIdentityOverFindOneSniff implements Sniff
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

        $next = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

        if ($next === false || $tokens[$next]['code'] !== T_DOUBLE_COLON) {
            return;
        }

        $methodToken = $phpcsFile->findNext(T_WHITESPACE, $next + 1, null, true);

        if ($methodToken === false || $tokens[$methodToken]['code'] !== T_STRING) {
            return;
        }

        $methodName = $tokens[$methodToken]['content'];

        if ($methodName === 'findOne') {
            $this->processFindOne($phpcsFile, $stackPtr, $methodToken);
        }

        if ($methodName === 'find') {
            $this->processFindWhere($phpcsFile, $stackPtr, $methodToken);
        }
    }

    /**
     * Process Model::findOne(...) pattern.
     *
     * @param File $phpcsFile
     * @param int  $classPtr
     * @param int  $methodPtr
     */
    private function processFindOne(File $phpcsFile, int $classPtr, int $methodPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $methodPtr + 1, null, true);

        if ($openParen === false || $tokens[$openParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$openParen]['parenthesis_closer'])) {
            return;
        }

        $closeParen = $tokens[$openParen]['parenthesis_closer'];

        if ($this->containsYiiUserId($phpcsFile, $openParen + 1, $closeParen)) {
            $className = $tokens[$classPtr]['content'];
            $phpcsFile->addWarning(
                sprintf(
                    'Use Yii::$app->user->identity instead of %s::findOne(Yii::$app->user->id)',
                    $className
                ),
                $classPtr,
                'PreferIdentityOverFindOne'
            );
        }
    }

    /**
     * Process Model::find()->where()->one() pattern.
     *
     * @param File $phpcsFile
     * @param int  $classPtr
     * @param int  $methodPtr
     */
    private function processFindWhere(File $phpcsFile, int $classPtr, int $methodPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $current = $methodPtr;

        $openParen = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($openParen === false || $tokens[$openParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$openParen]['parenthesis_closer'])) {
            return;
        }

        $current = $tokens[$openParen]['parenthesis_closer'];

        $arrow = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

        if ($arrow === false || $tokens[$arrow]['code'] !== T_OBJECT_OPERATOR) {
            return;
        }

        $whereToken = $phpcsFile->findNext(T_WHITESPACE, $arrow + 1, null, true);

        if ($whereToken === false || $tokens[$whereToken]['code'] !== T_STRING || $tokens[$whereToken]['content'] !== 'where') {
            return;
        }

        $whereOpenParen = $phpcsFile->findNext(T_WHITESPACE, $whereToken + 1, null, true);

        if ($whereOpenParen === false || $tokens[$whereOpenParen]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        if (!isset($tokens[$whereOpenParen]['parenthesis_closer'])) {
            return;
        }

        $whereCloseParen = $tokens[$whereOpenParen]['parenthesis_closer'];

        if ($this->containsIdYiiUserId($phpcsFile, $whereOpenParen + 1, $whereCloseParen)) {
            $current = $whereCloseParen;
            $hasOne = false;

            while (true) {
                $nextArrow = $phpcsFile->findNext(T_WHITESPACE, $current + 1, null, true);

                if ($nextArrow === false || $tokens[$nextArrow]['code'] !== T_OBJECT_OPERATOR) {
                    break;
                }

                $nextMethod = $phpcsFile->findNext(T_WHITESPACE, $nextArrow + 1, null, true);

                if ($nextMethod === false || $tokens[$nextMethod]['code'] !== T_STRING) {
                    break;
                }

                if ($tokens[$nextMethod]['content'] === 'one') {
                    $hasOne = true;

                    break;
                }

                $nextOpenParen = $phpcsFile->findNext(T_WHITESPACE, $nextMethod + 1, null, true);

                if ($nextOpenParen === false || $tokens[$nextOpenParen]['code'] !== T_OPEN_PARENTHESIS) {
                    break;
                }

                if (!isset($tokens[$nextOpenParen]['parenthesis_closer'])) {
                    break;
                }

                $current = $tokens[$nextOpenParen]['parenthesis_closer'];
            }

            if ($hasOne) {
                $className = $tokens[$classPtr]['content'];
                $phpcsFile->addWarning(
                    sprintf(
                        'Use Yii::$app->user->identity instead of %s::find()->where([\'id\' => Yii::$app->user->id])->one()',
                        $className
                    ),
                    $classPtr,
                    'PreferIdentityOverFindWhere'
                );
            }
        }
    }

    /**
     * Check if content contains patterns for getting current user ID.
     *
     * @param File $phpcsFile
     * @param int  $start
     * @param int  $end
     */
    private function containsYiiUserId(File $phpcsFile, int $start, int $end): bool
    {
        $tokens = $phpcsFile->getTokens();
        $content = '';

        for ($i = $start; $i < $end; ++$i) {
            if ($tokens[$i]['code'] !== T_WHITESPACE) {
                $content .= $tokens[$i]['content'];
            }
        }

        return str_contains($content, 'Yii::$app->user->id')
            || str_contains($content, 'Yii::$app->user->identity->id')
            || str_contains($content, 'Yii::$app->user->getId()')
            || str_contains($content, 'Yii::$app->user->identity->getId()');
    }

    /**
     * Check if content contains ['id' => Yii::$app->user->id] pattern.
     *
     * @param File $phpcsFile
     * @param int  $start
     * @param int  $end
     */
    private function containsIdYiiUserId(File $phpcsFile, int $start, int $end): bool
    {
        $tokens = $phpcsFile->getTokens();

        for ($i = $start; $i < $end; ++$i) {
            if (in_array($tokens[$i]['code'], [T_CONSTANT_ENCAPSED_STRING, T_DOUBLE_QUOTED_STRING], true)) {
                $value = mb_trim($tokens[$i]['content'], '"\'');

                if ($value === 'id') {
                    $arrow = $phpcsFile->findNext(T_WHITESPACE, $i + 1, $end, true);

                    if ($arrow !== false && $tokens[$arrow]['code'] === T_DOUBLE_ARROW) {
                        if ($this->containsYiiUserId($phpcsFile, $arrow + 1, $end)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
