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
* phpbb_search_sphinx_config_section
* Represents a single section inside the sphinx configuration
*/
class phpbb_search_sphinx_config_section
{
	var $name;
	var $comment;
	var $end_comment;
	var $variables = array();

	/**
	* Construct a new section
	*
	* @param	string	$name		Name of the section
	* @param	string	$comment	Comment that should be appended after the name in the
	*								textual format.
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
	* @param	phpbb_search_sphinx_config_variable	$variable	The variable object
	*/
	function add_variable($variable)
	{
		$this->variables[] = $variable;
	}

	/**
	* Adds a comment after the closing bracket in the textual representation
	*/
	function set_end_comment($end_comment)
	{
		$this->end_comment = $end_comment;
	}

	/**
	* Getter for the name of this section
	*
	* @return	string	Section's name
	*/
	function get_name()
	{
		return $this->name;
	}

	/**
	* Get a variable object by its name
	*
	* @param	string 								$name	The name of the variable that shall be returned
	* @return	phpbb_search_sphinx_config_section			The first variable object from this section with the
	*														given name or null if none was found
	*/
	function get_variable_by_name($name)
	{
		for ($i = 0, $size = sizeof($this->variables); $i < $size; $i++)
		{
			// Make sure this is a variable object and not a comment
			if (($this->variables[$i] instanceof phpbb_search_sphinx_config_variable) && $this->variables[$i]->get_name() == $name)
			{
				return $this->variables[$i];
			}
		}
	}

	/**
	* Deletes all variables with the given name
	*
	* @param	string	$name	The name of the variable objects that are supposed to be removed
	*/
	function delete_variables_by_name($name)
	{
		for ($i = 0, $size = sizeof($this->variables); $i < $size; $i++)
		{
			// Make sure this is a variable object and not a comment
			if (($this->variables[$i] instanceof phpbb_search_sphinx_config_variable) && $this->variables[$i]->get_name() == $name)
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
	* @return	phpbb_search_sphinx_config_variable			Variable object that was created
	*/
	function create_variable($name, $value)
	{
		$this->variables[] = new phpbb_search_sphinx_config_variable($name, $value, '');
		return $this->variables[sizeof($this->variables) - 1];
	}

	/**
	* Turns this object into a string which can be written to a config file
	*
	* @return	string	Config data in textual form, parsable for sphinx
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
