<?php
/**
 * Based on SlevomatCodingStandard\Sniffs\Whitespaces\ObjectOperatorSpacingSniff
 */

namespace Maagiline\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ObjectOperatorSpacingSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_OBJECT_OPERATOR,
            T_DOUBLE_COLON,
            T_NULLSAFE_OBJECT_OPERATOR,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
            $before = 0;
        } else {
            if ($tokens[($stackPtr - 2)]['line'] !== $tokens[$stackPtr]['line']) {
                $before = 'newline';
            } else {
                $before = $tokens[($stackPtr - 1)]['length'];
            }
        }

        $phpcsFile->recordMetric($stackPtr, 'Spacing before object operator', $before);
        $this->checkSpacingBeforeOperator($phpcsFile, $stackPtr, $before);

        if (isset($tokens[($stackPtr + 1)]) === false
            || isset($tokens[($stackPtr + 2)]) === false
        ) {
            return;
        }

        if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
            $after = 0;
        } else {
            if ($tokens[($stackPtr + 2)]['line'] !== $tokens[$stackPtr]['line']) {
                $after = 'newline';
            } else {
                $after = $tokens[($stackPtr + 1)]['length'];
            }
        }

        $phpcsFile->recordMetric($stackPtr, 'Spacing after object operator', $after);
        $this->checkSpacingAfterOperator($phpcsFile, $stackPtr, $after);
    }


        /**
         * Check the spacing before the operator.
         *
         * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
         * @param int $stackPtr The position of the current token
         *                                               in the stack passed in $tokens.
         * @param mixed $before The number of spaces found before the
         *                                               operator or the string 'newline'.
         *
         * @return boolean true if there was no error, false otherwise.
         */
    protected function checkSpacingBeforeOperator(File $phpcsFile, $stackPtr, $before)
    {
        $tokens = $phpcsFile->getTokens();

        if ($before === 'newline') {
            // Check if there are non-whitespace characters before the `->` on the same line
            $prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
            if ($tokens[$prevContent]['line'] === $tokens[$stackPtr]['line']) {
                $error = 'Object operator should be the first non-whitespace character on the line';
                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'Before');
                if ($fix === true) {
                    $phpcsFile->fixer->beginChangeset();
                    for ($i = $prevContent; $tokens[$i]['line'] === $tokens[$stackPtr]['line']; $i++) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }
                    $phpcsFile->fixer->addContentBefore($stackPtr, "\n");
                    $phpcsFile->fixer->endChangeset();
                }
                return false;
            }
        }

        return true;
    }


    /**
     * Check the spacing after the operator.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token
     *                                               in the stack passed in $tokens.
     * @param mixed $after The number of spaces found after the
     *                                               operator or the string 'newline'.
     *
     * @return boolean true if there was no error, false otherwise.
     */
    protected function checkSpacingAfterOperator(File $phpcsFile, $stackPtr, $after)
    {
        $tokens = $phpcsFile->getTokens();

        // Check if the arrow operator is at the end of the line.
        $nextContent = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$nextContent]['line'] !== $tokens[$stackPtr]['line']) {
            $error = 'Object operator should not be at the end of the line';
            $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'AfterEndOfLine');
            if ($fix === true) {
                $phpcsFile->fixer->addContent($stackPtr, "\n");
                $phpcsFile->fixer->replaceToken($stackPtr + 1, '');
            }
            return false;
        }

        return true;
    }
}
