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
	var $table_prefix;

	var $phpbb_root_path;
	var $php_ext;

	var $errors;

	/**
	* Migration constructor
	*
	* @param dbal			$db			Connected database abstraction instance
	* @param phpbb_db_tools	$db_tools	Instance of db schema manipulation tools
	* @param string			$table_prefix The prefix for all table names
	* @param string			$phpbb_root_path
	* @param string			$php_ext
	*/
	function phpbb_db_migration(&$db, &$db_tools, $table_prefix, $phpbb_root_path, $php_ext)
	{
		$this->db = &$db;
		$this->db_tools = &$db_tools;
		$this->table_prefix = $table_prefix;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

		$this->errors = array();
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
	* Updates the database schema by providing a set of change instructions
	*
	* @return array
	*/
	function update_schema()
	{
		return array();
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
	* Wrapper for running queries to generate user feedback on updates
	*/
	function sql_query($sql)
	{
		if (defined('DEBUG_EXTRA'))
		{
			echo "<br />\n{$sql}\n<br />";
		}

		$db->sql_return_on_error(true);

		if ($sql === 'begin')
		{
			$result = $db->sql_transaction('begin');
		}
		else if ($sql === 'commit')
		{
			$result = $db->sql_transaction('commit');
		}
		else
		{
			$result = $db->sql_query($sql);
			if ($db->sql_error_triggered)
			{
				$this->errors[] = array(
					'sql' => $db->sql_error_sql,
					'code' => $db->sql_error_returned,
				);
			}
		}

		$db->sql_return_on_error(false);

		return $result;
	}
}
