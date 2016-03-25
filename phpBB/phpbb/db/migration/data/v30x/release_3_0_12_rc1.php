<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\db\migration\data\v30x;

/** @todo DROP LOGIN_ATTEMPT_TABLE.attempt_id in 3.0.12-RC1 **/

class release_3_0_12_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.12-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_11');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_module_auth'))),
			array('custom', array(array(&$this, 'disable_bots_from_receiving_pms'))),

			array('config.update', array('version', '3.0.12-RC1')),
		);
	}

	public function disable_bots_from_receiving_pms()
	{
		// Disable receiving pms for bots
		$sql = 'SELECT user_id
			FROM ' . BOTS_TABLE;
		$result = $this->db->sql_query($sql);

		$bot_user_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$bot_user_ids[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($bot_user_ids))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_allow_pm = 0
				WHERE ' . $this->db->sql_in_set('user_id', $bot_user_ids);
			$this->sql_query($sql);
		}
	}

	public function update_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . '
			SET module_auth = \'acl_u_sig\'
			WHERE module_class = \'ucp\'
				AND module_basename = \'profile\'
				AND module_mode = \'signature\'';
		$this->sql_query($sql);
	}
}
