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

abstract class parser
{
	/**
	* Parse given text
	*
	* @param  string $text
	* @return string
	*/
	abstract public function parse($text);

	/**
	* Disable a specific BBCode
	*
	* @param  string $name BBCode name
	* @return null
	*/
	abstract public function disable_bbcode($name);

	/**
	* Disable BBCodes in general
	*/
	abstract public function disable_bbcodes();

	/**
	* Disable the censor
	*/
	abstract public function disable_censor();

	/**
	* Disable magic URLs
	*/
	abstract public function disable_magic_url();

	/**
	* Disable smilies
	*/
	abstract public function disable_smilies();

	/**
	* Enable a specific BBCode
	*
	* @param  string $name BBCode name
	* @return null
	*/
	abstract public function enable_bbcode($name);

	/**
	* Enable BBCodes in general
	*/
	abstract public function enable_bbcodes();

	/**
	* Enable the censor
	*/
	abstract public function enable_censor();

	/**
	* Enable magic URLs
	*/
	abstract public function enable_magic_url();

	/**
	* Enable smilies
	*/
	abstract public function enable_smilies();

	/**
	* Get the list of errors that were generated during last parsing
	*
	* @return array
	*/
	abstract public function get_errors();

	/**
	* Set a variable to be used by the parser
	*
	*  - max_font_size
	*  - max_img_height
	*  - max_img_width
	*  - max_smilies
	*  - max_urls
	*
	* @param  string $name
	* @param  mixed  $value
	* @return null
	*/
	abstract public function set_var($name, $value);

	/**
	* Set multiple variables to be used by the parser
	*
	* @param  array Associative array of [name => value]
	* @return null
	*/
	public function set_vars(array $vars)
	{
		foreach ($vars as $name => $value)
		{
			$this->set_var($name, $value);
		}
	}
}
