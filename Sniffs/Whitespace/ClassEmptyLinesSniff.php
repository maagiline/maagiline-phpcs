<?php

namespace Maagiline\Sniffs\Whitespace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Sourced from George Mponos <gmponos@gmail.com>.
 * Source repo: https://github.com/webthinkgr/codesniffer
 *
 * Copied here because original repo has aged dependencies.
 */
class ClassEmptyLinesSniff implements Sniff
{
    /**
     * The number of maximum allowed lines inside a class
     *
     * @var int
     */
    public $allowedLines = 1;

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_WHITESPACE];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (
            ($phpcsFile->hasCondition($stackPtr, T_CLASS) || $phpcsFile->hasCondition($stackPtr, T_CLOSURE))
            && $tokens[($stackPtr - 1)]['line'] < $tokens[$stackPtr]['line']
            && $tokens[($stackPtr - 2)]['line'] === $tokens[($stackPtr - 1)]['line']
        ) {
            // This is an empty line and the line before this one is not empty, so this could be
            // the start of a multiple empty line block.
            $next = $phpcsFile->findNext(T_WHITESPACE, $stackPtr, null, true);
            $lines = ($tokens[$next]['line'] - $tokens[$stackPtr]['line']);
            if ($lines <= $this->allowedLines) {
                return;
            }

            $fix = $phpcsFile->addFixableError(
                'Classes must not contain multiple empty lines in a row; found %s empty lines',
                $stackPtr,
                'EmptyLines',
                [$lines]
            );

            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $i = $stackPtr;
                while ($tokens[$i]['line'] !== $tokens[$next]['line']) {
                    $phpcsFile->fixer->replaceToken($i, '');
                    $i++;
                }

                $phpcsFile->fixer->addNewlineBefore($i);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
