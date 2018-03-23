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

namespace phpbb\db\migration\data\v310;

class bot_update extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\rc6');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_bing_bot'))),
			array('custom', array(array(&$this, 'update_bots'))),
		);
	}

	public function update_bing_bot()
	{
		$bot_name = 'Bing [Bot]';
		$bot_name_clean = utf8_clean_string($bot_name);

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $this->db->sql_escape($bot_name_clean) . "'";
		$result = $this->db->sql_query($sql);
		$bing_already_added = (bool) $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);

		if (!$bing_already_added)
		{
			$bot_agent = 'bingbot/';
			$bot_ip = '';
			$sql = 'SELECT group_id, group_colour
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = 'BOTS'";
			$result = $this->db->sql_query($sql);
			$group_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$group_row)
			{
				// default fallback, should never get here
				$group_row['group_id'] = 6;
				$group_row['group_colour'] = '9E8DA7';
			}

			if (!function_exists('user_add'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}

			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_row['group_id'],
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> $group_row['group_colour'],
				'user_email'			=> '',
				'user_lang'				=> $this->config['default_lang'],
				'user_style'			=> $this->config['default_style'],
				'user_timezone'			=> 0,
				'user_dateformat'		=> $this->config['default_dateformat'],
				'user_allow_massemail'	=> 0,
			);

			$user_id = user_add($user_row);

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', array(
					'bot_active'	=> 1,
					'bot_name'		=> (string) $bot_name,
					'user_id'		=> (int) $user_id,
					'bot_agent'		=> (string) $bot_agent,
					'bot_ip'		=> (string) $bot_ip,
				));

			$this->sql_query($sql);
		}
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
