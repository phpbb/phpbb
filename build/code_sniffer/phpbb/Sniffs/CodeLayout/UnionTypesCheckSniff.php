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
			T_FUNCTION,
			T_CLASS,
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function process(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();
		if ($tokens[$stackPtr]['type'] === 'T_FUNCTION')
		{
			$method_params = $phpcsFile->getMethodParameters($stackPtr);
			$method_params_array = array_column($method_params, 'type_hint', 'token');
			foreach ($method_params_array as $stack_pointer => $type_hint)
			{
				$this->check_union_type($phpcsFile, $stack_pointer, $type_hint);
			}

			$method_properties = $phpcsFile->getMethodProperties($stackPtr);
			$this->check_union_type($phpcsFile, $stackPtr, $method_properties['return_type']);
		}
		else if ($tokens[$stackPtr]['type'] === 'T_CLASS')
		{
			$class_token = $tokens[$stackPtr];
			$class_closer_pointer = $class_token['scope_closer'];
			$first_method_pointer = $phpcsFile->findNext(T_FUNCTION, $stackPtr);
			$class_members_declarations_end_pointer = $first_method_pointer ?: $class_closer_pointer;

			$stack_pointer = $stackPtr;
			while(($class_member_pointer = $phpcsFile->findNext(T_VARIABLE, $stack_pointer)) !== false && ($class_member_pointer < $class_members_declarations_end_pointer))
			{
				$properties = $phpcsFile->getMemberProperties($class_member_pointer);
				$this->check_union_type($phpcsFile, $class_member_pointer, $properties['type']);
				$stack_pointer = $class_member_pointer + 1;
			}
		}
	}

	public function check_union_type(File $phpcsFile, $stack_pointer, $type_hint)
	{
		if (empty($type_hint))
		{
			return;
		}

		if (!strpos($type_hint, '|') && $type_hint[0] == '?') // Check nullable shortcut syntax
		{
			$type = substr($type_hint, 1);
			$error = 'Nullable shortcut syntax must not be used. Use union type instead: %1$s|null; found %2$s';
			$data  = [$type, $type_hint];
			$phpcsFile->addError($error, $stack_pointer, 'ShortNullableSyntax', $data);					
		}
		else if ((count($types_array = explode('|', $type_hint))) > 1) // Check union type layout
		{
			$types_array_null_less = $types_array;

			// Check 'null' to be the last element
			$null_position = array_search('null', $types_array);
			if ($null_position !== false && $null_position != array_key_last($types_array))
			{
				$error = 'The "null" type hint must be the last of the union type elements; found %s';
				$data  = [implode('|', $types_array)];
				$phpcsFile->addError($error, $stack_pointer, 'NullAlwaysLast', $data);
			}

			// Check types excepting 'null' to follow alphabetical order
			if ($null_position !== false)
			{
				array_splice($types_array_null_less, $null_position, 1);
			}

			if (count($types_array_null_less) > 1)
			{
				$types_array_null_less_sorted = $types_array_null_less;
				sort($types_array_null_less_sorted);
				if (!empty(array_diff_assoc($types_array_null_less, $types_array_null_less_sorted)))
				{
					$error = 'Union type elements must be sorted alphabetically excepting the "null" type hint must be the last if any; found %s';
					$data  = [implode('|', $types_array)];
					$phpcsFile->addError($error, $stack_pointer, 'AlphabeticalSort', $data);
				}
			}
		}
	}
}
