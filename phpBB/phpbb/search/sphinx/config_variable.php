<?php
/**
*
* @package search
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\search\sphinx;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

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
