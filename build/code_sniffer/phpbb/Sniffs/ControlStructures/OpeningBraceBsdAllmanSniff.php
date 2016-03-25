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

/**
 * Checks that the opening brace of a control structures is on the line after.
 * From Generic_Sniffs_Functions_OpeningFunctionBraceBsdAllmanSniff
 */
class phpbb_Sniffs_ControlStructures_OpeningBraceBsdAllmanSniff implements PHP_CodeSniffer_Sniff
{
	/**
	 * Registers the tokens that this sniff wants to listen for.
	 */
	public function register()
	{
		return array(
			T_IF,
			T_ELSE,
			T_FOREACH,
			T_WHILE,
			T_DO,
			T_FOR,
			T_SWITCH,
		);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		if (isset($tokens[$stackPtr]['scope_opener']) === false)
		{
			return;
		}

		/*
		 * ...
		 * }
		 * else if ()
		 * {
		 * ...
		 */
		if ($tokens[$stackPtr]['code'] === T_ELSE && $tokens[$stackPtr + 2]['code'] === T_IF)
		{
			return;
		}

		$openingBrace = $tokens[$stackPtr]['scope_opener'];

		/*
		 * ...
		 * do
		 * {
		 *     <code>
		 * } while();
		 * ...
		 * }
		 * else
		 * {
		 * ...
		 */
		if ($tokens[$stackPtr]['code'] === T_DO ||$tokens[$stackPtr]['code'] === T_ELSE)
		{
			$cs_line = $tokens[$stackPtr]['line'];
		}
		else
		{
			// The end of the function occurs at the end of the argument list. Its
			// like this because some people like to break long function declarations
			// over multiple lines.
			$cs_line = $tokens[$tokens[$stackPtr]['parenthesis_closer']]['line'];
		}

		$braceLine    = $tokens[$openingBrace]['line'];

		$lineDifference = ($braceLine - $cs_line);

		if ($lineDifference === 0)
		{
			$error = 'Opening brace should be on a new line';
			$phpcsFile->addError($error, $openingBrace, 'BraceOnSameLine');
			return;
		}

		if ($lineDifference > 1)
		{
			$error = 'Opening brace should be on the line after the declaration; found %s blank line(s)';
			$data  = array(($lineDifference - 1));
			$phpcsFile->addError($error, $openingBrace, 'BraceSpacing', $data);
			return;
		}

		// We need to actually find the first piece of content on this line,
		// as if this is a method with tokens before it (public, static etc)
		// or an if with an else before it, then we need to start the scope
		// checking from there, rather than the current token.
		$lineStart = $stackPtr;
		while (($lineStart = $phpcsFile->findPrevious(array(T_WHITESPACE), ($lineStart - 1), null, false)) !== false)
		{
			if (strpos($tokens[$lineStart]['content'], $phpcsFile->eolChar) !== false)
			{
				break;
			}
		}

		// We found a new line, now go forward and find the first non-whitespace
		// token.
		$lineStart = $phpcsFile->findNext(array(T_WHITESPACE), $lineStart, null, true);

		// The opening brace is on the correct line, now it needs to be
		// checked to be correctly indented.
		$startColumn = $tokens[$lineStart]['column'];
		$braceIndent = $tokens[$openingBrace]['column'];

		if ($braceIndent !== $startColumn)
		{
			$error = 'Opening brace indented incorrectly; expected %s spaces, found %s';
			$data  = array(
				($startColumn - 1),
				($braceIndent - 1),
			);
			$phpcsFile->addError($error, $openingBrace, 'BraceIndent', $data);
		}
	}
}
