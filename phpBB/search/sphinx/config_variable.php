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
* \phpbb\search\sphinx\config_variable
* Represents a single variable inside the sphinx configuration
*/
class config_variable
{
	private $name;
	private $value;
	private $comment;

	/**
	* Constructs a new variable object
	*
	* @param	string	$name		Name of the variable
	* @param	string	$value		Value of the variable
	* @param	string	$comment	Optional comment after the variable in the
	*								config file
	*
	* @access	public
	*/
	function __construct($name, $value, $comment)
	{
		$this->name = $name;
		$this->value = $value;
		$this->comment = $comment;
	}

	/**
	* Getter for the variable's name
	*
	* @return	string	The variable object's name
	*
	* @access	public
	*/
	function get_name()
	{
		return $this->name;
	}

	/**
	* Allows changing the variable's value
	*
	* @param	string	$value	New value for this variable
	*
	* @access	public
	*/
	function set_value($value)
	{
		$this->value = $value;
	}

	/**
	* Turns this object into a string readable by sphinx
	*
	* @return	string	Config data in textual form
	*
	* @access	public
	*/
	function to_string()
	{
		return "\t" . $this->name . ' = ' . str_replace("\n", " \\\n", $this->value) . ' ' . $this->comment . "\n";
	}
}
