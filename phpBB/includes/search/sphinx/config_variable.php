<?php
/**
*
* @package search
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* phpbb_search_sphinx_config_variable
* Represents a single variable inside the sphinx configuration
*/
class phpbb_search_sphinx_config_variable
{
	var $name;
	var $value;
	var $comment;

	/**
	* Constructs a new variable object
	*
	* @param	string	$name		Name of the variable
	* @param	string	$value		Value of the variable
	* @param	string	$comment	Optional comment after the variable in the
	*								config file
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
	*/
	function get_name()
	{
		return $this->name;
	}

	/**
	* Allows changing the variable's value
	*
	* @param	string	$value	New value for this variable
	*/
	function set_value($value)
	{
		$this->value = $value;
	}

	/**
	* Turns this object into a string readable by sphinx
	*
	* @return	string	Config data in textual form
	*/
	function to_string()
	{
		return "\t" . $this->name . ' = ' . str_replace("\n", "\\\n", $this->value) . ' ' . $this->comment . "\n";
	}
}
