<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v3011rc2 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v3011rc1');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'profile_fields' => array(
					'field_show_novalue' => array('BOOL', 0),
				),
			),
		);
	}

	function update_data()
	{
	}
}
