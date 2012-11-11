<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v308rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v307pl1');
	}

	function update_schema()
	{
		return array();
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_file_extension_group_names'))),
			array('custom', array(array(&$this, 'update_module_auth'))),
			array('custom', array(array(&$this, 'update_bots'))),
			array('custom', array(array(&$this, 'delete_orphan_shadow_topics'))),
			array('module.add', array(
				'post'	=> array(
					'base'		=> 'board',
					'class'		=> 'acp',
					'title'		=> 'ACP_POST_SETTINGS',
					'auth'		=> 'acl_a_board',
					'cat'		=> 'ACP_MESSAGES',
					'after'		=> array('message', 'ACP_MESSAGE_SETTINGS')
				),
			)),
			array('config.add', array('load_unreads_search', 1)),
			array('config.update_if_equals', array(600, 'queue_interval', 60)),
			array('config.update_if_equals', array(50, 'email_package_size', 20)),
		);
	}

	function update_file_extension_group_names()
	{
		// Update file extension group names to use language strings.
		$sql = 'SELECT lang_dir
			FROM ' . LANG_TABLE;
		$result = $this->db->sql_query($sql);

		$extension_groups_updated = array();
		while ($lang_dir = $this->db->sql_fetchfield('lang_dir'))
		{
			$lang_dir = basename($lang_dir);

			// The language strings we need are either in language/.../acp/attachments.php
			// in the update package if we're updating to 3.0.8-RC1 or later,
			// or they are in language/.../install.php when we're updating from 3.0.7-PL1 or earlier.
			// On an already updated board, they can also already be in language/.../acp/attachments.php
			// in the board root.
			$lang_files = array(
				"{$this->phpbb_root_path}install/update/new/language/$lang_dir/acp/attachments.$this->phpEx",
				"{$this->phpbb_root_path}language/$lang_dir/install.$this->phpEx",
				"{$this->phpbb_root_path}language/$lang_dir/acp/attachments.$this->phpEx",
			);

			foreach ($lang_files as $lang_file)
			{
				if (!file_exists($lang_file))
				{
					continue;
				}

				$lang = array();
				include($lang_file);

				foreach($lang as $lang_key => $lang_val)
				{
					if (isset($extension_groups_updated[$lang_key]) || strpos($lang_key, 'EXT_GROUP_') !== 0)
					{
						continue;
					}

					$sql_ary = array(
						'group_name'	=> substr($lang_key, 10), // Strip off 'EXT_GROUP_'
					);

					$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
						SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE group_name = '" . $this->db->sql_escape($lang_val) . "'";
					$this->sql_query($sql);

					$extension_groups_updated[$lang_key] = true;
				}
			}
		}
		$this->db->sql_freeresult($result);
	}

	function update_module_auth()
	{
		$sql = 'UPDATE ' . MODULES_TABLE . '
			SET module_auth = \'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_remote || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)\'
			WHERE module_class = \'ucp\'
				AND module_basename = \'profile\'
				AND module_mode = \'avatar\'';
		$this->sql_query($sql);
	}

	function update_bots()
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
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->phpEx);
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

	function delete_orphan_shadow_topics()
	{
		// Delete shadow topics pointing to not existing topics
		$batch_size = 500;

		// Set of affected forums we have to resync
		$sync_forum_ids = array();

		$sql_array = array(
			'SELECT'	=> 't1.topic_id, t1.forum_id',
			'FROM'		=> array(
				TOPICS_TABLE	=> 't1',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(TOPICS_TABLE	=> 't2'),
					'ON'	=> 't1.topic_moved_id = t2.topic_id',
				),
			),
			'WHERE'		=> 't1.topic_moved_id <> 0
						AND t2.topic_id IS NULL',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $batch_size);

		$topic_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_ids[] = (int) $row['topic_id'];

			$sync_forum_ids[(int) $row['forum_id']] = (int) $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($topic_ids))
		{
			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
			$this->db->sql_query($sql);

			// Sync the forums we have deleted shadow topics from.
			sync('forum', 'forum_id', $sync_forum_ids, true, true);

			return true;
		}
		else
		{
			return false;
		}
	}
}
