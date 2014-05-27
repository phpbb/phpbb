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
* An object representing the sphinx configuration
* Can read it from file and write it back out after modification
*/
class config
{
	private $sections = array();

	/**
	* Constructor which optionally loads data from a variable
	*
	* @param	string	$config_data	Variable containing the sphinx configuration data
	*
	* @access	public
	*/
	function __construct($config_data)
	{
		if ($config_data != '')
		{
			$this->read($config_data);
		}
	}

	/**
	* Get a section object by its name
	*
	* @param	string 								$name	The name of the section that shall be returned
	* @return	\phpbb\search\sphinx\config_section			The section object or null if none was found
	*
	* @access	public
	*/
	function get_section_by_name($name)
	{
		for ($i = 0, $size = sizeof($this->sections); $i < $size; $i++)
		{
			// Make sure this is really a section object and not a comment
			if (($this->sections[$i] instanceof \phpbb\search\sphinx\config_section) && $this->sections[$i]->get_name() == $name)
			{
				return $this->sections[$i];
			}
		}
	}

	/**
	* Appends a new empty section to the end of the config
	*
	* @param	string								$name	The name for the new section
	* @return	\phpbb\search\sphinx\config_section			The newly created section object
	*
	* @access	public
	*/
	function add_section($name)
	{
		$this->sections[] = new \phpbb\search\sphinx\config_section($name, '');
		return $this->sections[sizeof($this->sections) - 1];
	}

	/**
	* Reads the config file data
	*
	* @param	string	$config_data	The config file data
	*
	* @access	private
	*/
	function read($config_data)
	{
		$this->sections = array();

		$section = null;
		$found_opening_bracket = false;
		$in_value = false;

		foreach ($config_data as $i => $line)
		{
			// If the value of a variable continues to the next line because the line
			// break was escaped then we don't trim leading space but treat it as a part of the value
			if ($in_value)
			{
				$line = rtrim($line);
			}
			else
			{
				$line = trim($line);
			}

			// If we're not inside a section look for one
			if (!$section)
			{
				// Add empty lines and comments as comment objects to the section list
				// that way they're not deleted when reassembling the file from the sections
				if (!$line || $line[0] == '#')
				{
					$this->sections[] = new \phpbb\search\sphinx\config_comment($config_file[$i]);
					continue;
				}
				else
				{
					// Otherwise we scan the line reading the section name until we find
					// an opening curly bracket or a comment
					$section_name = '';
					$section_name_comment = '';
					$found_opening_bracket = false;
					for ($j = 0, $length = strlen($line); $j < $length; $j++)
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

					// And then we create the new section object
					$section_name = trim($section_name);
					$section = new \phpbb\search\sphinx\config_section($section_name, $section_name_comment);
				}
			}
			else
			{
				// If we're looking for variables inside a section
				$skip_first = false;

				// If we're not in a value continuing over the line feed
				if (!$in_value)
				{
					// Then add empty lines and comments as comment objects to the variable list
					// of this section so they're not deleted on reassembly
					if (!$line || $line[0] == '#')
					{
						$section->add_variable(new \phpbb\search\sphinx\config_comment($config_file[$i]));
						continue;
					}

					// As long as we haven't yet actually found an opening bracket for this section
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
							$section->add_variable(new \phpbb\search\sphinx\config_comment($config_file[$i]));
							continue;
						}
					}
				}

				// If we did not find a comment in this line or still add to the previous
				// line's value ...
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

					/* ... then we should prase this line char by char:
					 - first there's the variable name
					 - then an equal sign
					 - the variable value
					 - possibly a backslash before the linefeed in this case we need to continue
					   parsing the value in the next line
					 - a # indicating that the rest of the line is a comment
					 - a closing curly bracket indicating the end of this section*/
					for ($j = 0, $length = strlen($line); $j < $length; $j++)
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
							if ($line[$j] == '\\' && $j == $length - 1)
							{
								$value .= "\n";
								$in_value = true;
								// Go to the next line and keep processing the value in there
								continue 2;
							}
							$value .= $line[$j];
						}
					}

					// If a name and an equal sign were found then we have append a
					// new variable object to the section
					if ($name && $found_assignment)
					{
						$section->add_variable(new \phpbb\search\sphinx\config_variable(trim($name), trim($value), ($end_section) ? '' : $comment));
						continue;
					}

					/* If we found a closing curly bracket this section has been completed
					and we can append it to the section list and continue with looking for
					the next section */
					if ($end_section)
					{
						$section->set_end_comment($comment);
						$this->sections[] = $section;
						$section = null;
						continue;
					}
				}

				// If we did not find anything meaningful up to here, then just treat it
				// as a comment
				$comment = ($skip_first) ? "\t" . substr(ltrim($config_file[$i]), 1) : $config_file[$i];
				$section->add_variable(new \phpbb\search\sphinx\config_comment($comment));
			}
		}

	}

	/**
	* Returns the config data
	*
	* @return	string	$data	The config data that is generated
	*
	* @access	public
	*/
	function get_data()
	{
		$data = "";
		foreach ($this->sections as $section)
		{
			$data .= $section->to_string();
		}

		return $data;
	}
}
