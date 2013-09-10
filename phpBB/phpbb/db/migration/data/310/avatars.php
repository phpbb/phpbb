<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace phpbb\db\migration\data0;

class avatars extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['allow_avatar_gravatar']);
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
			array('custom', array(array($this, 'update_module_auth'))),
		);
	}

	public function update_module_auth()
	{
		$sql = 'UPDATE ' . $this->table_prefix . "modules
			SET module_auth = 'cfg_allow_avatar'
			WHERE module_class = 'ucp'
				AND module_basename = 'ucp_profile'
				AND module_mode = 'avatar'";
		$this->db->sql_query($sql);
	}
}
