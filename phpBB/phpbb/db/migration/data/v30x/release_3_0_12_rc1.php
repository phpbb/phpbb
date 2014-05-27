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
			array('custom', array(array(&$this, 'update_bots'))),
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

	public function update_bots()
	{
		// Update bots
		if (!function_exists('user_delete'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$bots_updates = array(
			// Bot Deletions
			'NG-Search [Bot]'		=> false,
			'Nutch/CVS [Bot]'		=> false,
			'OmniExplorer [Bot]'	=> false,
			'Seekport [Bot]'		=> false,
			'Synoo [Bot]'			=> false,
			'WiseNut [Bot]'			=> false,

			// Bot Updates
			// Bot name to bot user agent map
			'Baidu [Spider]'	=> 'Baiduspider',
			'Exabot [Bot]'		=> 'Exabot',
			'Voyager [Bot]'		=> 'voyager/',
			'W3C [Validator]'	=> 'W3C_Validator',
		);

		foreach ($bots_updates as $bot_name => $bot_agent)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE user_type = ' . USER_IGNORE . "
					AND username_clean = '" . $this->db->sql_escape(utf8_clean_string($bot_name)) . "'";
			$result = $this->db->sql_query($sql);
			$bot_user_id = (int) $this->db->sql_fetchfield('user_id');
			$this->db->sql_freeresult($result);

			if ($bot_user_id)
			{
				if ($bot_agent === false)
				{
					$sql = 'DELETE FROM ' . BOTS_TABLE . "
						WHERE user_id = $bot_user_id";
					$this->sql_query($sql);

					user_delete('retain', $bot_user_id);
				}
				else
				{
					$sql = 'UPDATE ' . BOTS_TABLE . "
						SET bot_agent = '" .  $this->db->sql_escape($bot_agent) . "'
						WHERE user_id = $bot_user_id";
					$this->sql_query($sql);
				}
			}
		}
	}
}
