<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_3_0_4 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_3_0_4_rc1');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'rename_log_delete_topic'))),
		);
	}

	function rename_log_delete_topic()
	{
		if ($db->sql_layer == 'oracle')
		{
			// log_operation is CLOB - but we can change this later
			$sql = 'UPDATE ' . $this->table_prefix . "log
				SET log_operation = 'LOG_DELETE_TOPIC'
				WHERE log_operation LIKE 'LOG_TOPIC_DELETED'";
			$this->sql_query($sql);
		}
		else
		{
			$sql = 'UPDATE ' . $this->table_prefix . "log
				SET log_operation = 'LOG_DELETE_TOPIC'
				WHERE log_operation = 'LOG_TOPIC_DELETED'";
			$this->sql_query($sql);
		}
	}
}
