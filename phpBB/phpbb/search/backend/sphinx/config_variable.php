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
* \phpbb\search\backend\sphinx\config_variable
* Represents a single variable inside the sphinx configuration
*/
class config_variable extends config_item
{
	private $value;
	private $comment;

	/**
	* Constructs a new variable object
	*
	* @param	string	$name		Name of the variable
	* @param	string	$value		Value of the variable
	* @param	string	$comment	Optional comment after the variable in the
	*								config file
	*/
	public function __construct(string $name, string $value, string $comment = '')
	{
		$this->name = $name;
		$this->value = $value;
		$this->comment = $comment;
	}

	/**
	* Allows changing the variable's value
	*
	* @param	string	$value	New value for this variable
	*/
	public function set_value(string $value): void
	{
		$this->value = $value;
	}

	/**
	* {@inheritDoc}
	*/
	public function to_string(): string
	{
		return "\t" . $this->name . ' = ' . str_replace("\n", " \\\n", $this->value) . ($this->comment ? ' ' . $this->comment  : '') . "\n";
	}
}
