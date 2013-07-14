<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_30x_3_0_11_rc2 extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return version_compare($this->config['version'], '3.0.11-rc2', '>=');
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_11_rc1');
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'profile_fields' => array(
					'field_show_novalue' => array('BOOL', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'profile_fields' => array(
					'field_show_novalue',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.update', array('version', '3.0.11-rc2')),
		);
	}
}
