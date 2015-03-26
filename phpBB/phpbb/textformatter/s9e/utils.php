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
	* Replace BBCodes and other formatting elements with whitespace
	*
	* NOTE: preserves smilies as text
	*
	* @param  string $xml Parsed text
	* @return string      Plain text
	*/
	public function clean_formatting($xml)
	{
		// Insert a space before <s> and <e> then remove formatting
		$xml = preg_replace('#<[es]>#', ' $0', $xml);

		return \s9e\TextFormatter\Utils::removeFormatting($xml);
	}

	/**
	* Remove given BBCode and its content, at given nesting depth
	*
	* @param  string  $xml         Parsed text
	* @param  string  $bbcode_name BBCode's name
	* @param  integer $depth       Minimum nesting depth (number of parents of the same name)
	* @return string               Parsed text
	*/
	public function remove_bbcode($xml, $bbcode_name, $depth = 0)
	{
		return \s9e\TextFormatter\Utils::removeTag($xml, strtoupper($bbcode_name), $depth);
	}

	/**
	* Return a parsed text to its original form
	*
	* @param  string $xml Parsed text
	* @return string      Original plain text
	*/
	public function unparse($xml)
	{
		return \s9e\TextFormatter\Unparser::unparse($xml);
	}
}
