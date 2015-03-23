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

namespace phpbb\textformatter\s9e;

/**
* Text manipulation utilities
*/
class utils implements \phpbb\textformatter\utils_interface
{
	/**
	* {@inheritdoc}
	*/
	public function clean_formatting($text)
	{
		// Insert a space before <s> and <e> then remove formatting
		$text = preg_replace('#<[es]>#', ' $0', $text);

		return \s9e\TextFormatter\Utils::removeFormatting($text);
	}

	/**
	* {@inheritdoc}
	*/
	public function remove_bbcode($text, $bbcode_name, $depth = 0)
	{
		return \s9e\TextFormatter\Utils::removeTag($text, strtoupper($bbcode_name), $depth);
	}

	/**
	* {@inheritdoc}
	*/
	public function remove_formatting($text)
	{
		return \s9e\TextFormatter\Utils::removeFormatting($text);
	}

	/**
	* {@inheritdoc}
	*/
	public function unparse($text)
	{
		return \s9e\TextFormatter\Unparser::unparse($text);
	}
}
