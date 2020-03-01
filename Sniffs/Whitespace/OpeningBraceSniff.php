<?php

namespace Maagiline\Sniffs\Whitespace;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Checks that the opening brace is not followed by a blank line. Checks only function, closure,
 * class, interface and trait opening braces. Does not check control structure opening braces,
 * as these are covered by Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff.
 *
 * Based on Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff
 */
class OpeningBraceSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
        'JS',
    ];

    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return int[]
     */
    public function register(): array
    {
        return [
            T_FUNCTION,
            T_CLOSURE,
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $tokens = $phpcsFile->getTokens();

        $scopeOpener = $tokens[$stackPtr]['scope_opener'];

        for ($firstContent = ($scopeOpener + 1); $firstContent < $phpcsFile->numTokens; $firstContent++) {
            $code = $tokens[$firstContent]['code'];

            if ($code === T_WHITESPACE
                || ($code === T_INLINE_HTML
                    && trim($tokens[$firstContent]['content']) === '')
            ) {
                continue;
            }

            // Skip all empty tokens on the same line as the opener.
            if ($tokens[$firstContent]['line'] === $tokens[$scopeOpener]['line']
                && (isset(Tokens::$emptyTokens[$code]) === true
                    || $code === T_CLOSE_TAG)
            ) {
                continue;
            }

            break;
        }

        if ($tokens[$firstContent]['line'] >= ($tokens[$scopeOpener]['line'] + 2)
        ) {
            $gap = ($tokens[$firstContent]['line'] - $tokens[$scopeOpener]['line'] - 1);
            $phpcsFile->recordMetric($stackPtr, 'Blank lines after opening brace', $gap);

            $error = 'Blank line found after opening brace';
            $fix   = $phpcsFile->addFixableError($error, $scopeOpener, 'SpacingAfterOpen');

            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $i = ($scopeOpener + 1);
                while ($tokens[$i]['line'] !== $tokens[$firstContent]['line']) {
                    // Start removing content from the line after the opener.
                    if ($tokens[$i]['line'] !== $tokens[$scopeOpener]['line']) {
                        $phpcsFile->fixer->replaceToken($i, '');
                    }

                    $i++;
                }

                $phpcsFile->fixer->endChangeset();
            }
        } else {
            $phpcsFile->recordMetric($stackPtr, 'Blank lines after opening brace', 0);
        }
    }
}
