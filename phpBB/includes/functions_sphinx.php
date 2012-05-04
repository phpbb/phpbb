<?php
/** 
*
* @package search
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* sphinx_config
* An object representing the sphinx configuration
* Can read it from file and write it back out after modification
* @package search
*/
class sphinx_config
{
	var $loaded = false;
	var $sections = array();

	/**
	* Constructor which optionally loads data from a file
	*
	* @param	string	$filename	The path to a file containing the sphinx configuration
	*/
	function sphinx_config($filename = false)
	{
		if ($filename !== false && file_exists($filename))
		{
			$this->read($filename);
		}
	}

	/**
	* Get a section object by its name
	*
	* @param	string 					$name	The name of the section that shall be returned
	* @return	sphinx_config_section			The section object or null if none was found
	*/
	function &get_section_by_name($name)
	{
		for ($i = 0, $n = sizeof($this->sections); $i < $n; $i++)
		{
			// make sure this is really a section object and not a comment
			if (is_a($this->sections[$i], 'sphinx_config_section') && $this->sections[$i]->get_name() == $name)
			{
				return $this->sections[$i];
			}
		}
		$null = null;
		return $null;
	}

	/**
	* Appends a new empty section to the end of the config
	*
	* @param	string					$name	The name for the new section
	* @return	sphinx_config_section			The newly created section object
	*/
	function &add_section($name)
	{
		$this->sections[] = new sphinx_config_section($name, '');
		return $this->sections[sizeof($this->sections) - 1];
	}

	/**
	* Parses the config file at the given path, which is stored in $this->loaded for later use
	*
	* @param	string	$filename	The path to the config file
	*/
	function read($filename)
	{
		// split the file into lines, we'll process it line by line
		$config_file = file($filename);

		$this->sections = array();

		$section = null;
		$found_opening_bracket = false;
		$in_value = false;

		foreach ($config_file as $i => $line)
		{
			// if the value of a variable continues to the next line because the line break was escaped
			// then we don't trim leading space but treat it as a part of the value
			if ($in_value)
			{
				$line = rtrim($line);
			}
			else
			{
				$line = trim($line);
			}

			// if we're not inside a section look for one
			if (!$section)
			{
				// add empty lines and comments as comment objects to the section list
				// that way they're not deleted when reassembling the file from the sections
				if (!$line || $line[0] == '#')
				{
					$this->sections[] = new sphinx_config_comment($config_file[$i]);
					continue;
				}
				else
				{
					// otherwise we scan the line reading the section name until we find
					// an opening curly bracket or a comment
					$section_name = '';
					$section_name_comment = '';
					$found_opening_bracket = false;
					for ($j = 0, $n = strlen($line); $j < $n; $j++)
					{
						if ($line[$j] == '#')
						{
							$section_name_comment = substr($line, $j);
							break;
						}

						if ($found_opening_bracket)
						{
							continue;
						}

						if ($line[$j] == '{')
						{
							$found_opening_bracket = true;
							continue;
						}

						$section_name .= $line[$j];
					}

					// and then we create the new section object
					$section_name = trim($section_name);
					$section = new sphinx_config_section($section_name, $section_name_comment);
				}
			}
			else // if we're looking for variables inside a section
			{
				$skip_first = false;

				// if we're not in a value continuing over the line feed
				if (!$in_value)
				{
					// then add empty lines and comments as comment objects to the variable list
					// of this section so they're not deleted on reassembly
					if (!$line || $line[0] == '#')
					{
						$section->add_variable(new sphinx_config_comment($config_file[$i]));
						continue;
					}
	
					// as long as we haven't yet actually found an opening bracket for this section
					// we treat everything as comments so it's not deleted either
					if (!$found_opening_bracket)
					{
						if ($line[0] == '{')
						{
							$skip_first = true;
							$line = substr($line, 1);
							$found_opening_bracket = true;
						}
						else
						{
							$section->add_variable(new sphinx_config_comment($config_file[$i]));
							continue;
						}
					}
				}

				// if we did not find a comment in this line or still add to the previous line's value ...
				if ($line || $in_value)
				{
					if (!$in_value)
					{
						$name = '';
						$value = '';
						$comment = '';
						$found_assignment = false;
					}
					$in_value = false;
					$end_section = false;

					// ... then we should prase this line char by char:
					// - first there's the variable name
					// - then an equal sign
					// - the variable value
					// - possibly a backslash before the linefeed in this case we need to continue
					//   parsing the value in the next line
					// - a # indicating that the rest of the line is a comment
					// - a closing curly bracket indicating the end of this section
					for ($j = 0, $n = strlen($line); $j < $n; $j++)
					{
						if ($line[$j] == '#')
						{
							$comment = substr($line, $j);
							break;
						}
						else if ($line[$j] == '}')
						{
							$comment = substr($line, $j + 1);
							$end_section = true;
							break;
						}
						else if (!$found_assignment)
						{
							if ($line[$j] == '=')
							{
								$found_assignment = true;
							}
							else
							{
								$name .= $line[$j];
							}
						}
						else
						{
							if ($line[$j] == '\\' && $j == $n - 1)
							{
								$value .= "\n";
								$in_value = true;
								continue 2; // go to the next line and keep processing the value in there
							}
							$value .= $line[$j];
						}
					}

					// if a name and an equal sign were found then we have append a new variable object to the section
					if ($name && $found_assignment)
					{
						$section->add_variable(new sphinx_config_variable(trim($name), trim($value), ($end_section) ? '' : $comment));
						continue;
					}

					// if we found a closing curly bracket this section has been completed and we can append it to the section list
					// and continue with looking for the next section
					if ($end_section)
					{
						$section->set_end_comment($comment);
						$this->sections[] = $section;
						$section = null;
						continue;
					}
				}

				// if we did not find anything meaningful up to here, then just treat it as a comment
				$comment = ($skip_first) ? "\t" . substr(ltrim($config_file[$i]), 1) : $config_file[$i];
				$section->add_variable(new sphinx_config_comment($comment));
			}
		}

		// keep the filename for later use
		$this->loaded = $filename;
	}

	/**
	* Writes the config data into a file
	*
	* @param	string	$filename	The optional filename into which the config data shall be written.
	*								If it's not specified it will be written into the file that the config
	*								was originally read from.
	*/
	function write($filename = false)
	{
		if ($filename === false && $this->loaded)
		{
			$filename = $this->loaded;
		}

		$data = "";
		foreach ($this->sections as $section)
		{
			$data .= $section->to_string();
		}

		$fp = fopen($filename, 'wb');
		fwrite($fp, $data);
		fclose($fp);
	}
}

/**
* sphinx_config_section
* Represents a single section inside the sphinx configuration
*/
class sphinx_config_section
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
	function sphinx_config_section($name, $comment)
	{
		$this->name = $name;
		$this->comment = $comment;
		$this->end_comment = '';
	}

	/**
	* Add a variable object to the list of variables in this section
	*
	* @param	sphinx_config_variable	$variable	The variable object
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
	* @param	string 					$name	The name of the variable that shall be returned
	* @return	sphinx_config_section			The first variable object from this section with the
	*											given name or null if none was found
	*/
	function &get_variable_by_name($name)
	{
		for ($i = 0, $n = sizeof($this->variables); $i < $n; $i++)
		{
			// make sure this is a variable object and not a comment
			if (is_a($this->variables[$i], 'sphinx_config_variable') && $this->variables[$i]->get_name() == $name)
			{
				return $this->variables[$i];
			}
		}
		$null = null;
		return $null;
	}

	/**
	* Deletes all variables with the given name
	*
	* @param	string	$name	The name of the variable objects that are supposed to be removed
	*/
	function delete_variables_by_name($name)
	{
		for ($i = 0; $i < sizeof($this->variables); $i++)
		{
			// make sure this is a variable object and not a comment
			if (is_a($this->variables[$i], 'sphinx_config_variable') && $this->variables[$i]->get_name() == $name)
			{
				array_splice($this->variables, $i, 1);
				$i--;
			}
		}
	}

	/**
	* Create a new variable object and append it to the variable list of this section
	*
	* @param	string					$name	The name for the new variable
	* @param	string					$value	The value for the new variable
	* @return	sphinx_config_variable			Variable object that was created
	*/
	function &create_variable($name, $value)
	{
		$this->variables[] = new sphinx_config_variable($name, $value, '');
		return $this->variables[sizeof($this->variables) - 1];
	}

	/**
	* Turns this object into a string which can be written to a config file
	*
	* @return	string	Config data in textual form, parsable for sphinx
	*/
	function to_string()
	{
		$content = $this->name . " " . $this->comment . "\n{\n";

		// make sure we don't get too many newlines after the opening bracket
		while (trim($this->variables[0]->to_string()) == "")
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

/**
* sphinx_config_variable
* Represents a single variable inside the sphinx configuration
*/
class sphinx_config_variable
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
	function sphinx_config_variable($name, $value, $comment)
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


/**
* sphinx_config_comment
* Represents a comment inside the sphinx configuration
*/
class sphinx_config_comment
{
	var $exact_string;

	/**
	* Create a new comment
	*
	* @param	string	$exact_string	The content of the comment including newlines, leading whitespace, etc.
	*/
	function sphinx_config_comment($exact_string)
	{
		$this->exact_string = $exact_string;
	}

	/**
	* Simply returns the comment as it was created
	*
	* @return	string	The exact string that was specified in the constructor
	*/
	function to_string()
	{
		return $this->exact_string;
	}
}

?>