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

namespace phpbb\template\twig;

/**
* This class holds all DEFINE variables from the current page load
*/
class definition
{
	/** @var array **/
	protected $definitions = array();

	/**
	* Get a DEFINE'd variable
	*
	* @param string $name
	* @param array $arguments
	*
	* @return mixed Null if not found
	*/
	public function __call($name, $arguments)
	{
		return (isset($this->definitions[$name])) ? $this->definitions[$name] : null;
	}

	/**
	* DEFINE a variable
	*
	* @param string $name
	* @param mixed $value
	* @return \phpbb\template\twig\definition
	*/
	public function set($name, $value)
	{
		$this->definitions[$name] = $value;

		return $this;
	}

	/**
	* Append to a variable
	*
	* @param string $name
	* @param string $value
	* @return \phpbb\template\twig\definition
	*/
	public function append($name, $value)
	{
		if (!isset($this->definitions[$name]))
		{
			$this->definitions[$name] = '';
		}

		$this->definitions[$name] .= $value;

		return $this;
	}
}
