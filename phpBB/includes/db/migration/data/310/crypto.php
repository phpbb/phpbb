<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_crypto extends phpbb_db_migration
{
	public function effectively_installed()
	{
		$ret = false;
		$this->db->sql_return_on_error(true);
		// Set user_password to 64 character long string
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_password = '" . md5('foobar') . md5('foobar') . "'
			WHERE user_id = '" . ANONYMOUS . "'";
		$this->db->sql_query($sql);
		$this->db->sql_return_on_error(false);

		if ($this->db->sql_affectedrows())
		{
			$ret = true;
		}

		// Reset user password
		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_password = ''
			WHERE user_id = '" . ANONYMOUS . "'";
		$this->db->sql_query($sql);

		 return $ret;
	}

	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_password'			=> array('VCHAR:255', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_password'			=> array('VCHAR:40', ''),
				),
			),
		);
	}
}
