<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_remove_msnm extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'users', 'user_msnm');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_msnm',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_msnm'		=> array('VCHAR_UNI', ''),
				),
			),
		);
	}
}
