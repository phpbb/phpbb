<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class phpbb_dbal_migration_dummy extends phpbb_db_migration
{
	function depends_on()
	{
		return array('installed_migration');
	}

	function update_schema()
	{
		$this->db_column_add('phpbb_config', 'extra_column', array('UINT', 0));
	}

	function update_data()
	{
		$this->db->sql_query('UPDATE phpbb_config SET extra_column = 1');
	}
}
