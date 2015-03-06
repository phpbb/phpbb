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

		return \s9e\TextFormatter\Unparser::removeFormatting($text);
	}

	/**
	* {@inheritdoc}
	*/
	public function remove_bbcode($text, $bbcode_name, $depth = 0)
	{
		$dom = new \DOMDocument;
		$dom->loadXML($text);

		$xpath = new \DOMXPath($dom);
		$nodes = $xpath->query(str_repeat('//' . strtoupper($bbcode_name), 1 + $depth));

		foreach ($nodes as $node)
		{
			$node->parentNode->removeChild($node);
		}

		return $dom->saveXML($dom->documentElement);
	}

	/**
	* {@inheritdoc}
	*/
	public function remove_formatting($text)
	{
		return \s9e\TextFormatter\Unparser::removeFormatting($text);
	}

	/**
	* {@inheritdoc}
	*/
	public function unparse($text)
	{
		return \s9e\TextFormatter\Unparser::unparse($text);
	}
}
