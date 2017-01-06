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
	* Format given string to be used as an attribute value
	*
	* Will return the string as-is if it can be used in a BBCode without quotes. Otherwise,
	* it will use either single- or double- quotes depending on whichever requires less escaping.
	* Quotes and backslashes are escaped with backslashes where necessary
	*
	* @param  string $str Original string
	* @return string      Same string if possible, escaped string within quotes otherwise
	*/
	protected function format_attribute_value($str)
	{
		if (!preg_match('/[ "\'\\\\\\]]/', $str))
		{
			// Return as-is if it contains none of: space, ' " \ or ]
			return $str;
		}
		$singleQuoted = "'" . addcslashes($str, "\\'") . "'";
		$doubleQuoted = '"' . addcslashes($str, '\\"') . '"';

		return (strlen($singleQuoted) < strlen($doubleQuoted)) ? $singleQuoted : $doubleQuoted;
	}

	/**
	* {@inheritdoc}
	*/
	public function generate_quote($text, array $attributes = array())
	{
		$text = trim($text);
		$quote = '[quote';
		if (isset($attributes['author']))
		{
			// Add the author as the BBCode's default attribute
			$quote .= '=' . $this->format_attribute_value($attributes['author']);
			unset($attributes['author']);
		}

		if (isset($attributes['user_id']) && $attributes['user_id'] == ANONYMOUS)
		{
			unset($attributes['user_id']);
		}

		ksort($attributes);
		foreach ($attributes as $name => $value)
		{
			$quote .= ' ' . $name . '=' . $this->format_attribute_value($value);
		}
		$quote .= ']';
		$newline = (strlen($quote . $text . '[/quote]') > 80 || strpos($text, "\n") !== false) ? "\n" : '';
		$quote .= $newline . $text . $newline . '[/quote]';

		return $quote;
	}

	/**
	* Get a list of quote authors, limited to the outermost quotes
	*
	* @param  string   $xml Parsed text
	* @return string[]      List of authors
	*/
	public function get_outermost_quote_authors($xml)
	{
		$authors = array();
		if (strpos($xml, '<QUOTE ') === false)
		{
			return $authors;
		}

		$dom = new \DOMDocument;
		$dom->loadXML($xml);
		$xpath = new \DOMXPath($dom);
		foreach ($xpath->query('//QUOTE[not(ancestor::QUOTE)]/@author') as $author)
		{
			$authors[] = $author->textContent;
		}

		return $authors;
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

	/**
	 * {@inheritdoc}
	 */
	public function is_empty($text)
	{
		if ($text === null || $text === '')
		{
			return true;
		}

		return trim($this->unparse($text)) === '';
	}
}
