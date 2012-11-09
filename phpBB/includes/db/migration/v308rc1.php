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
		// Update file extension group names to use language strings.
		$sql = 'SELECT lang_dir
			FROM ' . LANG_TABLE;
		$result = $db->sql_query($sql);

		$extension_groups_updated = array();
		while ($lang_dir = $db->sql_fetchfield('lang_dir'))
		{
			$lang_dir = basename($lang_dir);

			// The language strings we need are either in language/.../acp/attachments.php
			// in the update package if we're updating to 3.0.8-RC1 or later,
			// or they are in language/.../install.php when we're updating from 3.0.7-PL1 or earlier.
			// On an already updated board, they can also already be in language/.../acp/attachments.php
			// in the board root.
			$lang_files = array(
				"{$phpbb_root_path}install/update/new/language/$lang_dir/acp/attachments.$phpEx",
				"{$phpbb_root_path}language/$lang_dir/install.$phpEx",
				"{$phpbb_root_path}language/$lang_dir/acp/attachments.$phpEx",
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
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE group_name = '" . $db->sql_escape($lang_val) . "'";
					_sql($sql, $errored, $error_ary);

					$extension_groups_updated[$lang_key] = true;
				}
			}
		}
		$db->sql_freeresult($result);

		// Install modules
		$modules_to_install = array(
			'post'					=> array(
				'base'		=> 'board',
				'class'		=> 'acp',
				'title'		=> 'ACP_POST_SETTINGS',
				'auth'		=> 'acl_a_board',
				'cat'		=> 'ACP_MESSAGES',
				'after'		=> array('message', 'ACP_MESSAGE_SETTINGS')
			),
		);

		_add_modules($modules_to_install);

		// update
		$sql = 'UPDATE ' . MODULES_TABLE . '
			SET module_auth = \'cfg_allow_avatar && (cfg_allow_avatar_local || cfg_allow_avatar_remote || cfg_allow_avatar_upload || cfg_allow_avatar_remote_upload)\'
			WHERE module_class = \'ucp\'
				AND module_basename = \'profile\'
				AND module_mode = \'avatar\'';
		_sql($sql, $errored, $error_ary);

		// add Bing Bot
		$bot_name = 'Bing [Bot]';
		$bot_name_clean = utf8_clean_string($bot_name);

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape($bot_name_clean) . "'";
		$result = $db->sql_query($sql);
		$bing_already_added = (bool) $db->sql_fetchfield('user_id');
		$db->sql_freeresult($result);

		if (!$bing_already_added)
		{
			$bot_agent = 'bingbot/';
			$bot_ip = '';
			$sql = 'SELECT group_id, group_colour
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = 'BOTS'";
			$result = $db->sql_query($sql);
			$group_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$group_row)
			{
				// default fallback, should never get here
				$group_row['group_id'] = 6;
				$group_row['group_colour'] = '9E8DA7';
			}

			if (!function_exists('user_add'))
			{
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}

			$user_row = array(
				'user_type'				=> USER_IGNORE,
				'group_id'				=> $group_row['group_id'],
				'username'				=> $bot_name,
				'user_regdate'			=> time(),
				'user_password'			=> '',
				'user_colour'			=> $group_row['group_colour'],
				'user_email'			=> '',
				'user_lang'				=> $config['default_lang'],
				'user_style'			=> $config['default_style'],
				'user_timezone'			=> 0,
				'user_dateformat'		=> $config['default_dateformat'],
				'user_allow_massemail'	=> 0,
			);

			$user_id = user_add($user_row);

			$sql = 'INSERT INTO ' . BOTS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'bot_active'	=> 1,
				'bot_name'		=> (string) $bot_name,
				'user_id'		=> (int) $user_id,
				'bot_agent'		=> (string) $bot_agent,
				'bot_ip'		=> (string) $bot_ip,
			));

			_sql($sql, $errored, $error_ary);
		}
		// end Bing Bot addition

		// Delete shadow topics pointing to not existing topics
		$batch_size = 500;

		// Set of affected forums we have to resync
		$sync_forum_ids = array();

		do
		{
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
			$sql = $db->sql_build_query('SELECT', $sql_array);
			$result = $db->sql_query_limit($sql, $batch_size);

			$topic_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_ids[] = (int) $row['topic_id'];

				$sync_forum_ids[(int) $row['forum_id']] = (int) $row['forum_id'];
			}
			$db->sql_freeresult($result);

			if (!empty($topic_ids))
			{
				$sql = 'DELETE FROM ' . TOPICS_TABLE . '
					WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
				$db->sql_query($sql);
			}
		}
		while (sizeof($topic_ids) == $batch_size);

		// Sync the forums we have deleted shadow topics from.
		sync('forum', 'forum_id', $sync_forum_ids, true, true);

		// Unread posts search load switch
		set_config('load_unreads_search', '1');

		// Reduce queue interval to 60 seconds, email package size to 20
		if ($config['queue_interval'] == 600)
		{
			set_config('queue_interval', '60');
		}

		if ($config['email_package_size'] == 50)
		{
			set_config('email_package_size', '20');
		}
	}
}
