<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v304 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v304rc1');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		if ($db->sql_layer == 'oracle')
		{
			// log_operation is CLOB - but we can change this later
			$sql = 'UPDATE ' . LOG_TABLE . "
				SET log_operation = 'LOG_DELETE_TOPIC'
				WHERE log_operation LIKE 'LOG_TOPIC_DELETED'";
			_sql($sql, $errored, $error_ary);
		}
		else
		{
			$sql = 'UPDATE ' . LOG_TABLE . "
				SET log_operation = 'LOG_DELETE_TOPIC'
				WHERE log_operation = 'LOG_TOPIC_DELETED'";
			_sql($sql, $errored, $error_ary);
		}
	}
}
