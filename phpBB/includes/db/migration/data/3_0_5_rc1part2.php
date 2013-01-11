<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_5_rc1part2 extends phpbb_db_migration
{
	static public function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_5_rc1');
	}

	public function update_schema()
	{
		return array(
			'drop_keys'			=> array(
				$this->table_prefix . 'acl_options'		=> array('auth_option'),
			),
			'add_unique_index'	=> array(
				$this->table_prefix . 'acl_options'		=> array(
					'auth_option'		=> array('auth_option'),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.5-rc1')),
		);
	}
}
