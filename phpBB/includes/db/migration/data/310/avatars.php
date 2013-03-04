<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_310_avatars extends phpbb_db_migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_auth
				FROM ' . MODULES_TABLE . "
				WHERE module_class = 'ucp'
					AND module_basename = 'ucp_profile'
					AND module_mode = 'avatar'";
		$result = $this->db->sql_query($sql);
		$module_auth = $this->db->sql_fetchfield('module_auth');
		$this->db->sql_freeresult($result);
		return ($module_auth == 'cfg_allow_avatar');
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
					'user_avatar_type'		=> array('VCHAR:255', ''),
				),
				$this->table_prefix . 'groups'			=> array(
					'group_avatar_type'		=> array('VCHAR:255', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'change_columns'	=> array(
				$this->table_prefix . 'users'			=> array(
					'user_avatar_type'		=> array('TINT:2', ''),
				),
				$this->table_prefix . 'groups'			=> array(
					'group_avatar_type'		=> array('TINT:2', ''),
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('allow_avatar_gravatar', 0)),
		);
	}
}
