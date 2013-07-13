<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_auth_provider_oauth extends phpbb_db_migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'auth_provider_oauth');
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'auth_provider_oauth'	=> array(
					'COLUMNS' => array(
						'user_id'			=> array('UINT', 0), // phpbb_users.user_id
						'oauth_provider'	=> array('VCHAR'), // Name of the OAuth provider
						'oauth_token'		=> array('TEXT_UNI'), // Serialized token
					),
					'PRIMARY_KEY' => array('user_id', 'oauth_provider'),
				),
			),

		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'auth_provider_oauth',
			),
		);
	}
}
