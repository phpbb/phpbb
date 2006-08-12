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
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;

		$action = request_var('action', '');
		$mark	= (isset($_REQUEST['mark'])) ? request_var('mark', array(0)) : array();

		if (sizeof($mark))
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
						FROM ' . USERS_TABLE . '
						WHERE ' . $db->sql_in_set('user_id', $mark);
					$result = $db->sql_query($sql);
				
					$user_affected = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$user_affected[] = $row['username'];
					}
					$db->sql_freeresult($result);

					if ($action == 'activate')
					{
						include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);

						foreach ($mark as $user_id)
						{
							user_active_flip($user_id, USER_INACTIVE);
						}

						set_config('num_users', $config['num_users'] + sizeof($mark), true);

						// Update latest username
						update_last_username();
					}
					else if ($action == 'delete')
					{
						if (!$auth->acl_get('a_userdel'))
						{
							trigger_error($user->lang['NO_ADMIN']);
						}

						$sql = 'DELETE FROM ' . USER_GROUP_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $mark);
						$db->sql_query($sql);
						$sql = 'DELETE FROM ' . USERS_TABLE . ' WHERE ' . $db->sql_in_set('user_id', $mark);
						$db->sql_query($sql);
	
						add_log('admin', 'LOG_INDEX_' . strtoupper($action), implode(', ', $user_affected));
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
						FROM ' . USERS_TABLE . ' 
						WHERE ' . $db->sql_in_set('user_id', $mark);
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
								'USERNAME'		=> html_entity_decode($row['username']),
								'SITENAME'		=> $config['sitename'],
								'REGISTER_DATE'	=> $user->format_date($row['user_regdate']), 
							
								'U_ACTIVATE'	=> "$board_url&mode=activate&u=" . $row['user_id'] . '&k=' . $row['user_actkey'])
							);

							$messenger->send($row['user_notify_type']);

							$usernames[] = $row['username'];
						}
						while ($row = $db->sql_fetchrow($result));

						$messenger->save_queue();

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
				if (!$auth->acl_get('a_board'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				set_config('record_online_users', 1, true);
				set_config('record_online_date', time(), true);
				add_log('admin', 'LOG_RESET_ONLINE');
			break;

			case 'stats':
				if (!$auth->acl_get('a_board'))
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

				set_config('num_files', (int) $db->sql_fetchfield('stat'), true);
				$db->sql_freeresult($result);

				$sql = 'SELECT SUM(filesize) as stat
					FROM ' . ATTACHMENTS_TABLE;
				$result = $db->sql_query($sql);

				set_config('upload_dir_size', (int) $db->sql_fetchfield('stat'), true);
				$db->sql_freeresult($result);

				add_log('admin', 'LOG_RESYNC_STATS');
			break;

			case 'user':
				if (!$auth->acl_get('a_board'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
					FROM ' . POSTS_TABLE . '
					WHERE post_postcount = 1
					GROUP BY poster_id';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_posts = {$row['num_posts']} WHERE user_id = {$row['poster_id']}");
				}
				$db->sql_freeresult($result);

				add_log('admin', 'LOG_RESYNC_POSTCOUNTS');

			break;
	
			case 'date':
				if (!$auth->acl_get('a_board'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				set_config('board_startdate', time() - 1);
				add_log('admin', 'LOG_RESET_DATE');
			break;
		
			case 'db_track':
				$db->sql_query(((SQL_LAYER != 'sqlite') ? 'TRUNCATE TABLE ' : 'DELETE FROM ') . TOPICS_POSTED_TABLE);

				// This can get really nasty... therefore we only do the last six months
				$get_from_time = time() - (6 * 4 * 7 * 24 * 60 * 60);

				// Select forum ids, do not include categories
				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type <> ' . FORUM_CAT;
				$result = $db->sql_query($sql);
		
				$forum_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$forum_ids[] = $row['forum_id'];
				}
				$db->sql_freeresult($result);

				// Any global announcements? ;)
				$forum_ids[] = 0;

				// Now go through the forums and get us some topics...
				foreach ($forum_ids as $forum_id)
				{
					$sql = 'SELECT p.poster_id, p.topic_id
						FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t
						WHERE t.forum_id = ' . $forum_id . '
							AND t.topic_moved_id = 0
							AND t.topic_last_post_time > ' . $get_from_time . '
							AND t.topic_id = p.topic_id
							AND p.poster_id <> ' . ANONYMOUS . '
						GROUP BY p.poster_id, p.topic_id';
					$result = $db->sql_query($sql);

					$posted = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$posted[$row['poster_id']][] = $row['topic_id'];
					}
					$db->sql_freeresult($result);

					$sql_ary = array();
					foreach ($posted as $user_id => $topic_row)
					{
						foreach ($topic_row as $topic_id)
						{
							$sql_ary[] = array(
								'user_id'		=> $user_id,
								'topic_id'		=> $topic_id,
								'topic_posted'	=> 1,
							);
						}
					}
					unset($posted);

					if (sizeof($sql_ary))
					{
						switch (SQL_LAYER)
						{
							case 'mysql':
							case 'mysql4':
							case 'mysqli':
								$db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . $db->sql_build_array('MULTI_INSERT', $sql_ary));
							break;

							default:
								foreach ($sql_ary as $ary)
								{
									$db->sql_query('INSERT INTO ' . TOPICS_POSTED_TABLE . ' ' . $db->sql_build_array('INSERT', $ary));
								}
							break;
						}
					}
				}
	
				add_log('admin', 'LOG_RESYNC_POST_MARKING');
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
			while (($file = readdir($avatar_dir)) !== false)
			{
				if ($file{0} != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
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
		$s_action_options = build_select(array('online' => 'RESET_ONLINE', 'date' => 'RESET_DATE', 'stats' => 'RESYNC_STATS', 'user' => 'RESYNC_POSTCOUNTS', 'db_track' => 'RESYNC_POST_MARKING'));

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
			'DATABASE_INFO'		=> $db->sql_server_info(),

			'U_ACTION'			=> append_sid("{$phpbb_admin_path}index.$phpEx"),
			'U_ADMIN_LOG'		=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=logs&amp;mode=admin'),

			'S_ACTION_OPTIONS'	=> ($auth->acl_get('a_board')) ? $s_action_options : '',
			)
		);

		$log_data = array();
		$log_count = 0;

		if ($auth->acl_get('a_viewlogs'))
		{
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
		}

		if ($auth->acl_get('a_user'))
		{
			$sql = 'SELECT user_id, username, user_regdate, user_lastvisit
				FROM ' . USERS_TABLE . ' 
				WHERE user_type = ' . USER_INACTIVE . ' 
				ORDER BY user_regdate ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('inactive', array(
					'DATE'			=> $user->format_date($row['user_regdate']),
					'LAST_VISIT'	=> (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),
					'USER_ID'		=> $row['user_id'],
					'USERNAME'		=> $row['username'],
					'U_USER_ADMIN'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;mode=overview&amp;u={$row['user_id']}"))
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

		// Display debug_extra notice
		if (defined('DEBUG_EXTRA'))
		{
			$template->assign_var('S_DEBUG_EXTRA', true);
		}

		// Warn if install is still present
		if (file_exists($phpbb_root_path . 'install'))
		{
			$template->assign_var('S_REMOVE_INSTALL', true);
		}

		$this->tpl_name = 'acp_main';
		$this->page_title = 'ACP_MAIN';
	}
}

?>