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
* Checks that each use statement is used.
*/
class phpbb_Sniffs_Namespaces_UnusedUseSniff implements Sniff
{
	const FIND = [
		T_NS_SEPARATOR,
		T_STRING,
		T_WHITESPACE,
	];

	/**
	* {@inheritdoc}
	*/
	public function register()
	{
		return [T_USE];
	}

	protected function check(File $phpcsFile, $found_name, $full_name, $short_name, $stack_pointer)
	{
		$found_name_normalized = ltrim($found_name, '\\');
		$full_name = ltrim($full_name, '\\');

		$is_global = ($full_name === $short_name);
		$unnecessarily_fully_qualified = ($is_global)
			? ($found_name_normalized !== $found_name && $found_name_normalized === $short_name)
			: ($found_name_normalized === $full_name);

		if ($unnecessarily_fully_qualified)
		{
			$error = 'Either use statement or full name must be used.';
			$phpcsFile->addError($error, $stack_pointer, 'FullName');
		}

		if ($found_name === $short_name)
		{
			return true;
		}

		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function process(File $phpcsFile, $stackPtr)
	{
		if ($this->should_ignore_use($phpcsFile, $stackPtr) === true)
		{
			return;
		}

		$tokens = $phpcsFile->getTokens();

		$class_name_start = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), ($stackPtr + 1));

		$class_name_end = $phpcsFile->findNext(self::FIND, ($stackPtr + 1), null, true);

		$aliasing_as_position = $phpcsFile->findNext(T_AS, $class_name_end, null, false, null, true);
		if ($aliasing_as_position !== false)
		{
			$alias_position = $phpcsFile->findNext(T_STRING, $aliasing_as_position, null, false, null, true);
			$name_short = $tokens[$alias_position]['content'];
			$name_full = $phpcsFile->getTokensAsString($class_name_start, ($class_name_end - $class_name_start - 1));
		}
		else
		{
			$name_full = $phpcsFile->getTokensAsString($class_name_start, ($class_name_end - $class_name_start));
			$name_short = $tokens[$class_name_end - 1]['content'];
		}

		if ($tokens[$class_name_start]['content'] === 'function'
			&& $tokens[$class_name_start + 1]['code'] === T_WHITESPACE)
		{
			$class_name_start += 2;
			$name_full = $phpcsFile->getTokensAsString(
				$class_name_start,
				($class_name_end - $class_name_start - (int) ($aliasing_as_position !== false))
			);
			$ok = $this->findFunctionUsage($phpcsFile, $stackPtr, $tokens, $name_full, $name_short);
		}
		else
		{
			$ok = $this->findClassUsage($phpcsFile, $stackPtr, $tokens, $name_full, $name_short);
		}

		if ($name_full[0] === '\\')
		{
			$phpcsFile->addError("There must not be a leading '\\' in use statements.", $stackPtr, 'Malformed');
		}

		if (!$ok)
		{
			$error = 'There must not be unused USE statements.';
			$phpcsFile->addError($error, $stackPtr, 'Unused');
		}
	}

	private function findClassUsage(File $phpcsFile, $stackPtr, $tokens, $class_name_full, $class_name_short)
	{
		$ok = false;

		// Checks in simple statements (new, instanceof and extends)
		foreach (array(T_INSTANCEOF, T_NEW, T_EXTENDS) as $keyword)
		{
			$old_simple_statement = $stackPtr;
			while (($simple_statement = $phpcsFile->findNext($keyword, ($old_simple_statement + 1))) !== false)
			{
				$old_simple_statement = $simple_statement;

				$simple_class_name_start = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), ($simple_statement + 1));

				if ($simple_class_name_start === false) {
					continue;
				}

				$simple_class_name_end = $phpcsFile->findNext(self::FIND, ($simple_statement + 1), null, true);

				$simple_class_name = trim($phpcsFile->getTokensAsString($simple_class_name_start, ($simple_class_name_end - $simple_class_name_start)));

				$ok = $this->check($phpcsFile, $simple_class_name, $class_name_full, $class_name_short, $simple_statement) || $ok;
			}
		}

		// Checks paamayim nekudotayim
		$old_paamayim_nekudotayim = $stackPtr;
		while (($paamayim_nekudotayim = $phpcsFile->findNext(T_PAAMAYIM_NEKUDOTAYIM, ($old_paamayim_nekudotayim + 1))) !== false)
		{
			$old_paamayim_nekudotayim = $paamayim_nekudotayim;

			$paamayim_nekudotayim_class_name_start = $phpcsFile->findPrevious(self::FIND, $paamayim_nekudotayim - 1, null, true);
			$paamayim_nekudotayim_class_name_end = $paamayim_nekudotayim - 1;

			$paamayim_nekudotayim_class_name = trim($phpcsFile->getTokensAsString($paamayim_nekudotayim_class_name_start + 1, ($paamayim_nekudotayim_class_name_end - $paamayim_nekudotayim_class_name_start)));

			$ok = $this->check($phpcsFile, $paamayim_nekudotayim_class_name, $class_name_full, $class_name_short, $paamayim_nekudotayim) || $ok;
		}

		// Checks in implements
		$old_implements = $stackPtr;
		while (($implements = $phpcsFile->findNext(T_IMPLEMENTS, ($old_implements + 1))) !== false)
		{
			$old_implements = $implements;

			$old_implemented_class = $implements;
			while (($implemented_class = $phpcsFile->findNext(T_STRING, ($old_implemented_class + 1), null, false, null, true)) !== false)
			{
				$old_implemented_class = $implemented_class;

				$implements_class_name_start = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), ($implemented_class - 1));
				$implements_class_name_end = $phpcsFile->findNext(self::FIND, ($implemented_class - 1), null, true);

				$implements_class_name = trim($phpcsFile->getTokensAsString($implements_class_name_start, ($implements_class_name_end - $implements_class_name_start)));

				$ok = $this->check($phpcsFile, $implements_class_name, $class_name_full, $class_name_short, $implements) || $ok;
			}
		}

		$old_docblock = $stackPtr;
		while (($docblock = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, ($old_docblock + 1))) !== false)
		{
			$old_docblock = $docblock;
			$ok = $this->checkDocblock($phpcsFile, $docblock, $tokens, $class_name_full, $class_name_short) || $ok;
		}

		// Checks in type hinting
		$old_function_declaration = $stackPtr;
		while (($function_declaration = $phpcsFile->findNext([T_FUNCTION, T_CLOSURE], ($old_function_declaration + 1))) !== false)
		{
			$old_function_declaration = $function_declaration;

			// Check type hint
			$params = $phpcsFile->getMethodParameters($function_declaration);
			foreach ($params as $param)
			{
				$ok = $this->check($phpcsFile, $param['type_hint'], $class_name_full, $class_name_short, $function_declaration) || $ok;
			}

			$method_properties = $phpcsFile->getMethodProperties($function_declaration);
			$ok = $this->check($phpcsFile, $method_properties['return_type'], $class_name_full, $class_name_short, $function_declaration) || $ok;
		}

		// Checks in catch blocks
		$old_catch = $stackPtr;
		while (($catch = $phpcsFile->findNext(T_CATCH, ($old_catch + 1))) !== false)
		{
			$old_catch = $catch;

			$caught_class_name_start = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), $catch + 1);
			$caught_class_name_end = $phpcsFile->findNext(self::FIND, $caught_class_name_start + 1, null, true);

			$caught_class_name = trim($phpcsFile->getTokensAsString($caught_class_name_start, ($caught_class_name_end - $caught_class_name_start)));

			$ok = $this->check($phpcsFile, $caught_class_name, $class_name_full, $class_name_short, $catch) || $ok;
		}

		$old_use = $stackPtr;
		while (($use = $phpcsFile->findNext(T_USE, ($old_use + 1))) !== false)
		{
			$old_use = $use;

			// Needs to be inside a class and must not be inside a function scope.
			if (!$phpcsFile->hasCondition($use, [T_CLASS, T_TRAIT]) || $phpcsFile->hasCondition($use, T_FUNCTION))
			{
				continue;
			}

			$next = $phpcsFile->findNext(T_WHITESPACE, ($use + 1), null, true, null, true);
			if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS)
			{
				continue;
			}

			$class_name_start = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), $use + 1, null, false, null, true);
			$class_name_end = $phpcsFile->findNext(self::FIND, $class_name_start + 1, null, true, null, true);
			$found_name = trim($phpcsFile->getTokensAsString($class_name_start, ($class_name_end - $class_name_start)));

			$ok = $this->check($phpcsFile, $found_name, $class_name_full, $class_name_short, $use) || $ok;
		}

		return $ok;
	}

	private function findFunctionUsage(File $phpcsFile, $stackPtr, $tokens, $name_full, $name_short)
	{
		$ok = false;
		$position = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr + 1);
		while ($position !== false)
		{
			$function_name_end = $position;
			$found_start = 1 + $phpcsFile->findPrevious(
				[T_NS_SEPARATOR, T_STRING, T_WHITESPACE],
				$function_name_end - 1,
				null,
				true
			);

			$position = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $position + 1);
			if ($found_start === null)
			{
				continue;
			}

			$function_name_start = $found_start;

			// Trim the output.
			while ($tokens[$function_name_start]['code'] === T_WHITESPACE && $function_name_start < $function_name_end)
			{
				++$function_name_start;
			}

			while ($tokens[$function_name_end]['code'] === T_WHITESPACE && $function_name_end > $function_name_start)
			{
				--$function_name_end;
			}

			$function_name_length = $function_name_end - $function_name_start;

			// Filter out control structures, built in type constructors, etc.
			if ($function_name_length <= 0)
			{
				continue;
			}

			// This doesn't seem to be valid PHP, where is the opening tag?
			if ($found_start === 0)
			{
				continue;
			}

			$previous_token = $found_start - 1;
			$filter = [
				T_FUNCTION, // Function declaration
				T_OBJECT_OPERATOR, // Method call
				T_DOUBLE_COLON, // Static method call
				T_NEW, // Constructors
			];

			// Filter out calls to methods and function declarations.
			if (in_array($tokens[$previous_token]['code'], $filter))
			{
				continue;
			}

			$function_name = $phpcsFile->getTokensAsString($function_name_start, $function_name_length);
			$ok = $this->check($phpcsFile, $function_name, $name_full, $name_short, $function_name_start) || $ok;
		}

		return $ok;
	}

	/**
	* Check if this use statement is part of the namespace block.
	*
	* @param File $phpcsFile The file being scanned.
	* @param int                  $stackPtr  The position of the current token in
	*                                        the stack passed in $tokens.
	*
	* @return bool
	*/
	private function should_ignore_use(File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		// Ignore USE keywords inside closures.
		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS)
		{
			return true;
		}

		// Ignore USE keywords for traits.
		if ($phpcsFile->hasCondition($stackPtr, array(T_CLASS, T_TRAIT)) === true)
		{
			return true;
		}

		return false;

	}

	/**
	 * @param File $phpcsFile
	 * @param int $field
	 * @param array $tokens
	 * @param string $class_name_full
	 * @param string $class_name_short
	 * @param bool $ok
	 *
	 * @return bool
	 */
	private function checkDocblock(File $phpcsFile, $comment_end, $tokens, $class_name_full, $class_name_short)
	{
		$ok = false;

		$comment_start = $tokens[$comment_end]['comment_opener'];
		foreach ($tokens[$comment_start]['comment_tags'] as $tag)
		{
			if (!in_array($tokens[$tag]['content'], array('@param', '@var', '@return', '@throws'), true))
			{
				continue;
			}

			$classes = $tokens[($tag + 2)]['content'];
			$space = strpos($classes, ' ');
			if ($space !== false)
			{
				$classes = substr($classes, 0, $space);
			}

			$tab = strpos($classes, "\t");
			if ($tab !== false)
			{
				$classes = substr($classes, 0, $tab);
			}

			$classes = explode('|', str_replace('[]', '', $classes));
			foreach ($classes as $class)
			{
				$ok = $this->check($phpcsFile, $class, $class_name_full, $class_name_short, $tag + 2) || $ok;
			}
		}

		return $ok;
	}
}
