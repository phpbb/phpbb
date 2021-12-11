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

namespace phpbb\search\backend\sphinx;

/**
* \phpbb\search\backend\sphinx\config_section
* Represents a single section inside the sphinx configuration
*/
class config_section extends config_item
{
	/** @var string Section comment */
	private $comment;

	/** @var string Section end comment */
	private $end_comment;

	/** @var array Section variables array */
	private $variables = [];

	/**
	* Construct a new section
	*
	* @param	string	$name		Name of the section
	* @param	string	$comment	Comment that should be appended after the name in the
	*								textual format.
	*/
	public function __construct(string $name, string $comment)
	{
		$this->name = $name;
		$this->comment = $comment;
		$this->end_comment = '';
	}

	/**
	* Adds a comment after the closing bracket in the textual representation
	*
	* @param	string	$end_comment
	*/
	public function set_end_comment(string $end_comment): void
	{
		$this->end_comment = $end_comment;
	}

	/**
	* Get a variable object by its name
	*
	* @param	string 					$name	The name of the variable that shall be returned
	*
	* @return	config_variable|null	The first variable object from this section with the
	*										given name or null if none was found
	*/
	public function get_variable_by_name(string $name): ?config_variable
	{
		for ($i = 0, $size = count($this->variables); $i < $size; $i++)
		{
			// Make sure this is a variable object and not a comment
			if ($this->variables[$i]->get_name() == $name)
			{
				return $this->variables[$i];
			}
		}

		return null;
	}

	/**
	* Deletes all variables with the given name
	*
	* @param	string	$name	The name of the variable objects that are supposed to be removed
	*/
	public function delete_variables_by_name(string $name)
	{
		for ($i = 0, $size = count($this->variables); $i < $size; $i++)
		{
			// Make sure this is a variable object and not a comment
			if ($this->variables[$i]->get_name() == $name)
			{
				array_splice($this->variables, $i, 1);
				$i--;
			}
		}
	}

	/**
	* Create a new variable object and append it to the variables list of this section
	*
	* @param	string				$name	The name for the new variable
	* @param	string				$value	The value for the new variable
	*
	* @return	config_variable		Variable object that was created
	*/
	public function create_variable(string $name, string $value): config_variable
	{
		$this->variables[] = new config_variable($name, $value);
		return $this->variables[count($this->variables) - 1];
	}

	/**
	* Turns this object into a string which can be written to a config file
	*
	* @return	string	Config data in textual form, parsable for sphinx
	*/
	public function to_string(): string
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
