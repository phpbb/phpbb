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

namespace phpbb\Sniffs\CodeLayout;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks that union type declarations follows the coding guidelines.
 */
class UnionTypesCheckSniff implements Sniff
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		return [
			T_TYPE_UNION,
			T_NULLABLE,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		// Check if nullable type shortcut syntax ('?typehint') is used
		if ($tokens[$stackPtr]['type'] === 'T_NULLABLE')
		{
			$type = $tokens[$stackPtr + 1]['content'];
			$error = 'Nullable shortcut syntax must not be used. Use union type instead: %2$s|null; found %1$s%2$s';
			$data  = [trim($tokens[$stackPtr]['content']), $type];
			$phpcsFile->addError($error, $stackPtr, 'ShortNullableSyntax', $data);
		}

		// Get the entry before the 1st '|' and all entries after it untill the end of union type declaration
        if ($tokens[$stackPtr]['type'] === 'T_TYPE_UNION' && $tokens[$stackPtr - 2]['type'] !== 'T_TYPE_UNION')
		{
			// Get all the types within the union type declaration
			$types_array = [];
			for ($i = $stackPtr - 2; $i++;)
			{
				if (in_array($tokens[$i]['type'], ['T_TYPE_UNION', 'T_STRING', 'T_NULL']))
				{
					if ($tokens[$i]['type'] != 'T_TYPE_UNION')
					{
						$types_array[] = $tokens[$i]['content'];
					}
				}
				else
				{
					break;
				}
			}

			$types_array_sorted = $types_array_null_less = $types_array;

			// Check 'null' to be the last element
			$null_position = array_search('null', $types_array);
			if ($null_position !== false && $null_position != array_key_last($types_array))
			{
				$error = 'The "null" type hint must be the last of the union type elements; found %s';
				$data  = [implode('|', $types_array)];
				$phpcsFile->addError($error, $stackPtr, 'NullAlwaysLast', $data);
			}

			// Check types excepting 'null' to follow alphabetical order
			if ($null_position !== false)
			{
				array_splice($types_array_null_less, $null_position, 1);
			}
			sort($types_array_sorted);
			if (!empty(array_diff_assoc($types_array_null_less, $types_array_sorted)))
			{
				$error = 'Union type elements must be sorted alphabetically excepting the "null" type hint must be the last if any; found %s';
				$data  = [implode('|', $types_array)];
				$phpcsFile->addError($error, $stackPtr, 'AlphabeticalSort', $data);
			}
        }
	}
}
