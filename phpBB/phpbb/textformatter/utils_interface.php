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

namespace phpbb\textformatter;

/**
* Used to manipulate a parsed text
*/
interface utils_interface
{
	/**
	* Replace BBCodes and other formatting elements with whitespace
	*
	* NOTE: preserves smilies as text
	*
	* @param  string $text Parsed text
	* @return string       Plain text
	*/
	public function clean_formatting($text);

	/**
	* Create a quote block for given text
	*
	* Possible attributes:
	*   - author:  author's name (usually a username)
	*   - post_id: post_id of the post being quoted
	*   - user_id: user_id of the user being quoted
	*   - time:    timestamp of the original message
	*
	* @param  string $text       Quote's text
	* @param  array  $attributes Quote's attributes
	* @return string             Quote block to be used in a new post/text
	*/
	public function generate_quote($text, array $attributes = array());

	/**
	* Get a list of quote authors, limited to the outermost quotes
	*
	* @param  string   $text Parsed text
	* @return string[]       List of authors
	*/
	public function get_outermost_quote_authors($text);

	/**
	* Remove given BBCode and its content, at given nesting depth
	*
	* @param  string  $text        Parsed text
	* @param  string  $bbcode_name BBCode's name
	* @param  integer $depth       Minimum nesting depth (number of parents of the same name)
	* @return string               Parsed text
	*/
	public function remove_bbcode($text, $bbcode_name, $depth = 0);

	/**
	 * Return a parsed text to its original form
	 *
	 * @param  string $text Parsed text
	 * @return string       Original plain text
	 */
	public function unparse($text);

	/**
	 * Return whether or not a parsed text represent an empty text.
	 *
	 * @param  string $text Parsed text
	 * @return bool         Tue if the original text is empty
	 */
	public function is_empty($text);
}
