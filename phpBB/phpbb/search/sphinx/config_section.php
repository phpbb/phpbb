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
* \phpbb\search\sphinx\config_section
* Represents a single section inside the sphinx configuration
*/
class config_section
{
	private $name;
	private $comment;
	private $end_comment;
	private $variables = array();

	/**
	* Construct a new section
	*
	* @param	string	$name		Name of the section
	* @param	string	$comment	Comment that should be appended after the name in the
	*								textual format.
	*
	* @access	public
	*/
	function __construct($name, $comment)
	{
		$this->name = $name;
		$this->comment = $comment;
		$this->end_comment = '';
	}

	/**
	* Add a variable object to the list of variables in this section
	*
	* @param	\phpbb\search\sphinx\config_variable	$variable	The variable object
	*
	* @access	public
	*/
	function add_variable($variable)
	{
		$this->variables[] = $variable;
	}

	/**
	* Adds a comment after the closing bracket in the textual representation
	*
	* @param	string	$end_comment
	*
	* @access	public
	*/
	function set_end_comment($end_comment)
	{
		$this->end_comment = $end_comment;
	}

	/**
	* Getter for the name of this section
	*
	* @return	string	Section's name
	*
	* @access	public
	*/
	function get_name()
	{
		return $this->name;
	}

	/**
	* Get a variable object by its name
	*
	* @param	string 								$name	The name of the variable that shall be returned
	* @return	\phpbb\search\sphinx\config_section			The first variable object from this section with the
	*														given name or null if none was found
	*
	* @access	public
	*/
	function get_variable_by_name($name)
	{
		for ($i = 0, $size = sizeof($this->variables); $i < $size; $i++)
		{
			// Make sure this is a variable object and not a comment
			if (($this->variables[$i] instanceof \phpbb\search\sphinx\config_variable) && $this->variables[$i]->get_name() == $name)
			{
				return $this->variables[$i];
			}
		}
	}

	/**
	* Deletes all variables with the given name
	*
	* @param	string	$name	The name of the variable objects that are supposed to be removed
	*
	* @access	public
	*/
	function delete_variables_by_name($name)
	{
		for ($i = 0, $size = sizeof($this->variables); $i < $size; $i++)
		{
			// Make sure this is a variable object and not a comment
			if (($this->variables[$i] instanceof \phpbb\search\sphinx\config_variable) && $this->variables[$i]->get_name() == $name)
			{
				array_splice($this->variables, $i, 1);
				$i--;
			}
		}
	}

	/**
	* Create a new variable object and append it to the variable list of this section
	*
	* @param	string								$name	The name for the new variable
	* @param	string								$value	The value for the new variable
	* @return	\phpbb\search\sphinx\config_variable			Variable object that was created
	*
	* @access	public
	*/
	function create_variable($name, $value)
	{
		$this->variables[] = new \phpbb\search\sphinx\config_variable($name, $value, '');
		return $this->variables[sizeof($this->variables) - 1];
	}

	/**
	* Turns this object into a string which can be written to a config file
	*
	* @return	string	Config data in textual form, parsable for sphinx
	*
	* @access	public
	*/
	function to_string()
	{
		$content = $this->name . ' ' . $this->comment . "\n{\n";

		// Make sure we don't get too many newlines after the opening bracket
		while (trim($this->variables[0]->to_string()) == '')
		{
			array_shift($this->variables);
		}

		foreach ($this->variables as $variable)
		{
			$content .= $variable->to_string();
		}
		$content .= '}' . $this->end_comment . "\n";

		return $content;
	}
}
