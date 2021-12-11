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
* An object representing the sphinx configuration
* Can read it from file and write it back out after modification
*/
class config
{
	/** @var array Sections array */
	private $sections = [];

	/**
	* Get a section object by its name
	*
	* @param	string 					$name	The name of the section that shall be returned
	* @return	config_section|null		The section object or null if none was found
	*/
	public function get_section_by_name(string $name): ?config_section
	{
		for ($i = 0, $size = count($this->sections); $i < $size; $i++)
		{
			// Make sure this is really a section object and not a comment
			if (($this->sections[$i] instanceof config_section) && $this->sections[$i]->get_name() == $name)
			{
				return $this->sections[$i];
			}
		}

		return null;
	}

	/**
	* Appends a new empty section to the end of the config
	*
	* @param	string					$name	The name for the new section
	* @return	config_section			The newly created section object
	*/
	public function add_section(string $name): config_section
	{
		$this->sections[] = new config_section($name, '');
		return $this->sections[count($this->sections) - 1];
	}

	/**
	* Returns the config data
	*
	* @return	string	$data	The config data that is generated
	*/
	public function get_data(): string
	{
		$data = "";
		foreach ($this->sections as $section)
		{
			$data .= $section->to_string();
		}

		return $data;
	}
}
