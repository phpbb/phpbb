<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package acp
*/
class acp_main
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;
		global $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		$action = request_var('action', '');
		$mark	= (isset($_REQUEST['mark'])) ? implode(', ', request_var('mark', array(0))) : '';

		if ($mark)
		{
			switch ($action)
			{
				case 'activate':
				case 'delete':

					if (!$auth->acl_get('a_user'))
					{
						trigger_error($user->lang['NO_ADMIN']);
					}

					$sql = 'SELECT username 
						FROM ' . USERS_TABLE . "
						WHERE user_id IN ($mark)";
					$result = $db->sql_query($sql);
				
					$user_affected = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$user_affected[] = $row['username'];
					}
					$db->sql_freeresult($result);

					if ($action == 'activate')
					{
						include($phpbb_root_path . 'includes/functions_user.php');
						$mark_ary = explode(', ', $mark);

						foreach ($mark_ary as $user_id)
						{
							user_active_flip($user_id, USER_INACTIVE);
						}
					}
					else if ($action == 'delete')
					{
						$sql = 'DELETE FROM ' . USER_GROUP_TABLE . " WHERE user_id IN ($mark)";
						$db->sql_query($sql);
						$sql = 'DELETE FROM ' . USERS_TABLE . " WHERE user_id IN ($mark)";
						$db->sql_query($sql);
	
						add_log('admin', 'LOG_INDEX_' . strtoupper($action), implode(', ', $user_affected));
					}

					if ($action != 'delete')
					{
						set_config('num_users', $config['num_users'] + $db->sql_affectedrows(), true);
					}

				break;

				case 'remind':
					if (!$auth->acl_get('a_user'))
					{
						trigger_error($user->lang['NO_ADMIN']);
					}

					if (empty($config['email_enable']))
					{
						trigger_error($user->lang['EMAIL_DISABLED']);
					}

					$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type, user_regdate, user_actkey 
						FROM ' . USERS_TABLE . " 
						WHERE user_id IN ($mark)";
					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						// Send the messages
						include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

						$messenger = new messenger();

						$board_url = generate_board_url() . "/ucp.$phpEx?mode=activate";
						$sig = str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']);

						$usernames = array();
						do
						{
							$messenger->template('user_remind_inactive', $row['user_lang']);

							$messenger->replyto($config['board_email']);
							$messenger->to($row['user_email'], $row['username']);
							$messenger->im($row['user_jabber'], $row['username']);

							$messenger->assign_vars(array(
								'EMAIL_SIG'		=> $sig,
								'USERNAME'		=> $row['username'],
								'SITENAME'		=> $config['sitename'],
								'REGISTER_DATE'	=> $user->format_date($row['user_regdate']), 
							
								'U_ACTIVATE'	=> "$board_url&mode=activate&u=" . $row['user_id'] . '&k=' . $row['user_actkey'])
							);

							$messenger->send($row['user_notify_type']);

							$usernames[] = $row['username'];
						}
						while ($row = $db->sql_fetchrow($result));

						$messenger->save_queue();

						unset($email_list);

						add_log('admin', 'LOG_INDEX_REMIND', implode(', ', $usernames));
						unset($usernames);
					}
					$db->sql_freeresult($result);
		
				break;
			}
		}

		switch ($action)
		{
			case 'online':
				if (!$auth->acl_get('a_defaults'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				set_config('record_online_users', 1, true);
				set_config('record_online_date', time(), true);
				add_log('admin', 'LOG_RESET_ONLINE');
			break;

			case 'stats':
				if (!$auth->acl_get('a_defaults'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				$sql = 'SELECT COUNT(post_id) AS stat 
					FROM ' . POSTS_TABLE . '
					WHERE post_approved = 1';
				$result = $db->sql_query($sql);

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				set_config('num_posts', (int) $row['stat'], true);

				$sql = 'SELECT COUNT(topic_id) AS stat
					FROM ' . TOPICS_TABLE . '
					WHERE topic_approved = 1';
				$result = $db->sql_query($sql);

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				set_config('num_topics', (int) $row['stat'], true);

				$sql = 'SELECT COUNT(user_id) AS stat
					FROM ' . USERS_TABLE . '
					WHERE user_type IN (' . USER_NORMAL . ',' . USER_FOUNDER . ')';
				$result = $db->sql_query($sql);

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				set_config('num_users', (int) $row['stat'], true);

				$sql = 'SELECT COUNT(attach_id) as stat
					FROM ' . ATTACHMENTS_TABLE;
				$result = $db->sql_query($sql);

				set_config('num_files', (int) $db->sql_fetchfield('stat', 0, $result), true);
				$db->sql_freeresult($result);

				$sql = 'SELECT SUM(filesize) as stat
					FROM ' . ATTACHMENTS_TABLE;
				$result = $db->sql_query($sql);

				set_config('upload_dir_size', (int) $db->sql_fetchfield('stat', 0, $result), true);
				$db->sql_freeresult($result);

				add_log('admin', 'LOG_RESYNC_STATS');
			break;

			case 'user':
				if (!$auth->acl_get('a_defaults'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				$post_count_ary = $auth->acl_getf('f_postcount');
				$forum_read_ary = $auth->acl_getf('f_read');
				
				$forum_ary = array();
				foreach ($post_count_ary as $forum_id => $allowed)
				{
					if ($allowed['f_postcount'] && $forum_read_ary[$forum_id]['f_read'])
					{
						$forum_ary[] = $forum_id;
					}
				}

				if (!sizeof($forum_ary))
				{
					$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_posts = 0');
				}
				else
				{
					$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
						FROM ' . POSTS_TABLE . '
						WHERE poster_id <> ' . ANONYMOUS . '
							AND forum_id IN (' . implode(', ', $forum_ary) . ')
						GROUP BY poster_id';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_posts = {$row['num_posts']} WHERE user_id = {$row['poster_id']}");
					}
					$db->sql_freeresult($result);
				}

				add_log('admin', 'LOG_RESYNC_POSTCOUNTS');
			break;
	
			case 'date':
				if (!$auth->acl_get('a_defaults'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				set_config('board_startdate', time() - 1);
				add_log('admin', 'LOG_RESET_DATE');
			break;
		}

		// Get forum statistics
		$total_posts = $config['num_posts'];
		$total_topics = $config['num_topics'];
		$total_users = $config['num_users'];
		$total_files = $config['num_files'];

		$start_date = $user->format_date($config['board_startdate']);

		$boarddays = (time() - $config['board_startdate']) / 86400;

		$posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
		$topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
		$users_per_day = sprintf('%.2f', $total_users / $boarddays);
		$files_per_day = sprintf('%.2f', $total_files / $boarddays);

		$upload_dir_size = ($config['upload_dir_size'] >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($config['upload_dir_size'] / 1048576)) : (($config['upload_dir_size'] >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($config['upload_dir_size'] / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $config['upload_dir_size']));

		$avatar_dir_size = 0;

		if ($avatar_dir = @opendir($phpbb_root_path . $config['avatar_path']))
		{
			while ($file = readdir($avatar_dir))
			{
				if ($file{0} != '.')
				{
					$avatar_dir_size += filesize($phpbb_root_path . $config['avatar_path'] . '/' . $file);
				}
			}
			@closedir($avatar_dir);

			// This bit of code translates the avatar directory size into human readable format
			// Borrowed the code from the PHP.net annoted manual, origanally written by:
			// Jesse (jesse@jess.on.ca)
			$avatar_dir_size = ($avatar_dir_size >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($avatar_dir_size / 1048576)) : (($avatar_dir_size >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($avatar_dir_size / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $avatar_dir_size));
		}
		else
		{
			// Couldn't open Avatar dir.
			$avatar_dir_size = $user->lang['NOT_AVAILABLE'];
		}

		if ($posts_per_day > $total_posts)
		{
			$posts_per_day = $total_posts;
		}

		if ($topics_per_day > $total_topics)
		{
			$topics_per_day = $total_topics;
		}

		if ($users_per_day > $total_users)
		{
			$users_per_day = $total_users;
		}

		if ($files_per_day > $total_files)
		{
			$files_per_day = $total_files;
		}

		$dbsize = get_database_size();
		$s_action_options = build_select(array('online' => 'RESET_ONLINE', 'date' => 'RESET_DATE', 'stats' => 'RESYNC_STATS', 'user' => 'RESYNC_POSTCOUNTS'));

		$template->assign_vars(array(
			'TOTAL_POSTS'		=> $total_posts,
			'POSTS_PER_DAY'		=> $posts_per_day,
			'TOTAL_TOPICS'		=> $total_topics,
			'TOPICS_PER_DAY'	=> $topics_per_day,
			'TOTAL_USERS'		=> $total_users,
			'USERS_PER_DAY'		=> $users_per_day,
			'TOTAL_FILES'		=> $total_files,
			'FILES_PER_DAY'		=> $files_per_day,
			'START_DATE'		=> $start_date,
			'AVATAR_DIR_SIZE'	=> $avatar_dir_size,
			'DBSIZE'			=> $dbsize,
			'UPLOAD_DIR_SIZE'	=> $upload_dir_size,
			'GZIP_COMPRESSION'	=> ($config['gzip_compress']) ? $user->lang['ON'] : $user->lang['OFF'],

			'U_ACTION'			=> "{$phpbb_admin_path}index.$phpEx$SID",

			'S_ACTION_OPTIONS'	=> $s_action_options,
			)
		);

		view_log('admin', $log_data, $log_count, 5);

		foreach ($log_data as $row)
		{
			$template->assign_block_vars('log', array(
				'USERNAME'	=> $row['username'],
				'IP'		=> $row['ip'],
				'DATE'		=> $user->format_date($row['time']),
				'ACTION'	=> $row['action'])
			);
		}
		
		if ($auth->acl_get('a_user'))
		{
			$sql = 'SELECT user_id, username, user_regdate
				FROM ' . USERS_TABLE . ' 
				WHERE user_type = ' . USER_INACTIVE . ' 
				ORDER BY user_regdate ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('inactive', array(
					'DATE'			=> $user->format_date($row['user_regdate']),
					'USER_ID'		=> $row['user_id'],
					'USERNAME'		=> $row['username'],
					'U_USER_ADMIN'	=> "{$phpbb_admin_path}admin_users.$phpEx$SID&amp;u={$row['user_id']}")
				);
			}

			$option_ary = array('activate' => 'ACTIVATE', 'delete' => 'DELETE');
			if ($config['email_enable'])
			{
				$option_ary += array('remind' => 'REMIND');
			}

			$template->assign_vars(array(
				'S_INACTIVE_USERS'		=> true,
				'S_INACTIVE_OPTIONS'	=> build_select($option_ary))
			);
		}
		
		$this->tpl_name = 'acp_main';
		$this->page_title = 'ACP_MAIN';
	}
}

/**
* @package module_install
*/
class acp_main_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_main',
			'title'		=> 'ACP_INDEX',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array('title' => 'ACP_INDEX', 'auth' => ''),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>