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
* Checks that each PHP source file contains a valid header as defined by the
* phpBB Coding Guidelines.
*
* @package code_sniffer
* @author Manuel Pichler <mapi@phpundercontrol.org>
*/
class phpbb_Sniffs_Commenting_FileCommentSniff implements Sniff
{
	/**
	* Returns an array of tokens this test wants to listen for.
	*
	* @return array
	*/
	public function register()
	{
		return array(T_OPEN_TAG);
	}

	/**
	* Processes this test, when one of its tokens is encountered.
	*
	* @param File $phpcsFile The file being scanned.
	* @param int				  $stackPtr  The position of the current token
	*										in the stack passed in $tokens.
	*
	* @return void
	*/
	public function process(File $phpcsFile, $stackPtr): void
	{
		// We are only interested in the first file comment.
		if ($stackPtr !== 0)
		{
			if ($phpcsFile->findPrevious(T_OPEN_TAG, $stackPtr - 1) !== false)
			{
				return;
			}
		}

		// Fetch next non whitespace token
		$tokens = $phpcsFile->getTokens();
		$start  = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);

		// Skip empty files
		if ($tokens[$start]['code'] === T_CLOSE_TAG)
		{
			return;
		}
		// Mark as error if this is not a doc comment
		else if ($start === false || $tokens[$start]['code'] !== T_DOC_COMMENT_OPEN_TAG)
		{
			$phpcsFile->addError('Missing required file doc comment.', $stackPtr, 'MissingComment');
			return;
		}

		// Find comment end token
		$end = $tokens[$start]['comment_closer'];

		// If there is no end, skip processing here
		if ($end === false)
		{
			return;
		}

		// check comment lines without the first(/**) an last(*/) line
		for ($token = $start + 1, $c = $end - 2; $token <= $c; ++$token)
		{
			// Check that each line starts with a '*'
			if ($tokens[$token]['column'] === 1 && (($tokens[$token]['content'] !== '*' && $tokens[$token]['content'] !== ' ') || ($tokens[$token]['content'] === ' ' && $tokens[$token + 1]['content'] !== '*')))
			{
				$message = 'The file doc comment should not be indented.';
				$phpcsFile->addWarning($message, $token, 'CommentIndented');
			}
		}

		// Check that the first and last line is empty
		// /**T_WHITESPACE
		// (T_WHITESPACE)*T_WHITESPACE
		// (T_WHITESPACE)* ...
		// (T_WHITESPACE)*T_WHITESPACE
		// T_WHITESPACE*/
		if (!(($tokens[$start + 2]['content'] !== '*' && $tokens[$start + 4]['content'] !== '*') || ($tokens[$start + 3]['content'] !== '*' && $tokens[$start + 6]['content'] !== '*')))
		{
			$message = 'The first file comment line should be empty.';
			$phpcsFile->addWarning($message, ($start + 1), 'CommentFirstNotEmpty');
		}

		if ($tokens[$end - 3]['content'] !== '*' && $tokens[$end - 6]['content'] !== '*')
		{
			$message = 'The last file comment line should be empty.';
			$phpcsFile->addWarning($message, $end - 1, 'CommentLastNotEmpty');
		}

		//$this->processPackage($phpcsFile, $start, $tags);
		//$this->processVersion($phpcsFile, $start, $tags);
		$this->processCopyright($phpcsFile, $start, $tokens[$start]['comment_tags']);
		$this->processLicense($phpcsFile, $start, $tokens[$start]['comment_tags']);
	}

	/**
	* Checks that the tags array contains a valid package tag
	*
	* @param File $phpcsFile The context source file instance.
	* @param integer The stack pointer for the first comment token.
	* @param array(string=>array) $tags The found file doc comment tags.
	*
	* @return void
	*/
	protected function processPackage(File $phpcsFile, $ptr, $tags): void
	{
		if (!isset($tags['package']))
		{
			$message = 'Missing require @package tag in file doc comment.';
			$phpcsFile->addError($message, $ptr, 'MissingTagPackage');
		}
		else if (preg_match('/^([\w]+)$/', $tags['package'][0]) === 0)
		{
			$message = 'Invalid content found for @package tag.';
			$phpcsFile->addWarning($message, $tags['package'][1], 'InvalidTagPackage');
		}
	}

	/**
	* Checks that the tags array contains a valid version tag
	*
	* @param File $phpcsFile The context source file instance.
	* @param integer The stack pointer for the first comment token.
	* @param array(string=>array) $tags The found file doc comment tags.
	*
	* @return void
	*/
	protected function processVersion(File $phpcsFile, $ptr, $tags): void
	{
		if (!isset($tags['version']))
		{
			$message = 'Missing require @version tag in file doc comment.';
			$phpcsFile->addError($message, $ptr, 'MissingTagVersion');
		}
		else if (preg_match('/^\$Id:[^\$]+\$$/', $tags['version'][0]) === 0)
		{
			$message = 'Invalid content found for @version tag, use "$Id: $".';
			$phpcsFile->addError($message, $tags['version'][1], 'InvalidTagVersion');
		}
	}

	/**
	* Checks that the tags array contains a valid copyright tag
	*
	* @param File $phpcsFile The context source file instance.
	* @param integer The stack pointer for the first comment token.
	* @param array(string=>array) $tags The found file doc comment tags.
	*
	* @return void
	*/
	protected function processCopyright(File $phpcsFile, $ptr, $tags): void
	{
		$copyright = '(c) phpBB Limited <https://www.phpbb.com>';
		$tokens = $phpcsFile->getTokens();

		foreach ($tags as $tag)
		{
			if ($tokens[$tag]['content'] === '@copyright')
			{
				if ($tokens[$tag + 2]['content'] !== $copyright)
				{
					$message = 'Invalid content found for the first @copyright tag, use "' . $copyright . '".';
					$phpcsFile->addError($message, $tags['copyright'][0][1], 'InvalidTagCopyright');
				}

				return;
			}
		}

		$message = 'Missing require @copyright tag in file doc comment.';
		$phpcsFile->addError($message, $ptr, 'MissingTagCopyright');
	}

	/**
	* Checks that the tags array contains a valid license tag
	*
	* @param File $phpcsFile The context source file instance.
	* @param integer The stack pointer for the first comment token.
	* @param array(string=>array) $tags The found file doc comment tags.
	*
	* @return void
	*/
	protected function processLicense(File $phpcsFile, $ptr, $tags): void
	{
		$license = 'GNU General Public License, version 2 (GPL-2.0)';
		$tokens = $phpcsFile->getTokens();

		$found = false;
		foreach ($tags as $tag)
		{
			if ($tokens[$tag]['content'] === '@license')
			{
				if ($found)
				{
					$message = 'It must be only one @license tag in file doc comment.';
					$phpcsFile->addError($message, $ptr, 'MultiTagVersion');
				}

				$found = true;

				if ($tokens[$tag + 2]['content'] !== $license)
				{
					$message = 'Invalid content found for @license tag, use "' . $license . '".';
					$phpcsFile->addError($message, $tags['license'][0][1], 'InvalidTagLicense');
				}
			}
		}

		if (!$found)
		{
			$message = 'Missing require @license tag in file doc comment.';
			$phpcsFile->addError($message, $ptr, 'MissingTagLicense');
		}
	}
}
