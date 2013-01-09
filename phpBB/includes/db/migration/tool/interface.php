<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

/**
* Migration tool interface
*
* @package db
*/
interface phpbb_db_migration_tool_interface
{
	/**
	* Retrieve a short name used for commands in migrations.
	*
	* @return string short name
	*/
	public function get_name();
}
