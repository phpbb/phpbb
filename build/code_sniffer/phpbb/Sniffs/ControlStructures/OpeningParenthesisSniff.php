<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that there is exactly one space between the keyword and the opening
 * parenthesis of a control structures.
 */
class phpbb_Sniffs_ControlStructures_OpeningParenthesisSniff implements Sniff
{
	/**
	 * Registers the tokens that this sniff wants to listen for.
	 */
	public function register()
	{
		return array(
			T_IF,
			T_FOREACH,
			T_WHILE,
			T_FOR,
			T_SWITCH,
			T_ELSEIF,
			T_CATCH,
		);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		if ($tokens[$stackPtr + 1]['content'] === '(')
		{
			$error = 'There should be exactly one space between the keyword and opening parenthesis';
			$phpcsFile->addError($error, $stackPtr, 'NoSpaceBeforeOpeningParenthesis');
		}
		else if ($tokens[$stackPtr + 1]['content'] !== ' ')
		{
			$error = 'There should be exactly one space between the keyword and opening parenthesis';
			$phpcsFile->addError($error, $stackPtr, 'IncorrectSpaceBeforeOpeningParenthesis');
		}
	}
}
