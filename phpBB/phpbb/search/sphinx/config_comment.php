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

namespace phpbb\search\sphinx;

/**
* \phpbb\search\sphinx\config_comment
* Represents a comment inside the sphinx configuration
*/
class config_comment
{
	private $exact_string;

	/**
	* Create a new comment
	*
	* @param	string	$exact_string	The content of the comment including newlines, leading whitespace, etc.
	*
	* @access	public
	*/
	function __construct($exact_string)
	{
		$this->exact_string = $exact_string;
	}

	/**
	* Simply returns the comment as it was created
	*
	* @return	string	The exact string that was specified in the constructor
	*
	* @access	public
	*/
	function to_string()
	{
		return $this->exact_string;
	}
}
