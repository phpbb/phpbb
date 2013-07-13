<?php
/**
 *
 * @package migration
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
 *
 */

class phpbb_db_migration_data_310_api extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'api_tokens');
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'api_tokens'	=> array(
					'COLUMNS'		=> array(
						'key_id'			=> array('UINT', 0, 'auto_increment'),
						'user_id'			=> array('UINT', 0),
						'name'				=> array('VCHAR', ''),
						'token'				=> array('VCHAR', ''),
						'sign_token'		=> array('VCHAR', ''),
					),
					'PRIMARY_KEY'			=> 'key_id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'api_tokens',
			),
		);
	}
}
