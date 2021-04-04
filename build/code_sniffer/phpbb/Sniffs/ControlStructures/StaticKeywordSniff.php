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
 * Checks that the visibility qualifiers are placed after the static keyword
 * according to the coding guidelines
 */
class phpbb_Sniffs_ControlStructures_StaticKeywordSniff implements Sniff
{
	/**
	 * Registers the tokens that this sniff wants to listen for.
	 */
	public function register()
	{
		return [
			T_STATIC,
		];
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		$disallowed_after_tokens = [
			T_PUBLIC,
			T_PROTECTED,
			T_PRIVATE,
		];

		if (in_array($tokens[$stackPtr + 2]['code'], $disallowed_after_tokens))
		{
			$error = 'Access specifier (e.g. public) should not follow static scope attribute. Encountered "' . $tokens[$stackPtr + 2]['content'] . '" after static';
			$phpcsFile->addError($error, $stackPtr, 'InvalidStaticFunctionDeclaration');
		}
	}
}
