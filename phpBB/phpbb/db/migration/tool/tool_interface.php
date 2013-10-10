<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\tool;

/**
* Migration tool interface
*
* @package db
*/
interface tool_interface
{
	/**
	* Retrieve a short name used for commands in migrations.
	*
	* @return string short name
	*/
	public function get_name();

	/**
	* Reverse an original install action
	*
	* First argument is the original call to the class (e.g. add, remove)
	* After the first argument, send the original arguments to the function in the original call
	*
	* @return null
	*/
	public function reverse();
}
