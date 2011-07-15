<?php
/**
*
* @package db
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* Abstract base class for database migrations
*
* Each migration consists of a set of schema and data changes to be implemented
* in a subclass. This class provides various utility methods to simplify editing
* a phpBB.
*
* @package db
*/
class phpbb_db_migration
{
	var $db;
	var $db_tools;

	/**
	* Migration constructor
	*
	* @param dbal			$db			Connected database abstraction instance
	* @param phpbb_db_tools	$db_tools	Instance of db schema manipulation tools
	*/
	function phpbb_db_migration(&$db, &$db_tools)
	{
		$this->db = &$db;
		$this->db_tools = &$db_tools;
	}

	/**
	* Defines other migrationsto be applied first (abstract method)
	*
	* @return array An array of migration class names
	*/
	function depends_on()
	{
		return array();
	}

	/**
	* Updates the database schema
	*
	* @return null
	*/
	function update_schema()
	{
	}

	/**
	* Updates data
	*
	* @return null
	*/
	function update_data()
	{
	}

	/**
	* Adds a column to a database table
	*/
	function db_column_add($table_name, $column_name, $column_data)
	{
		$this->db_tools->sql_column_add($table_name, $column_name, $column_data);
	}
}
