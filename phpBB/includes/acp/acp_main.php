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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_main
{
	var $u_action;
	private $php_ini;

	function main($id, $mode)
	{
		global $config, $db, $cache, $user, $auth, $template, $request, $phpbb_log;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $phpbb_container, $phpbb_dispatcher, $phpbb_filesystem;

		// Show restore permissions notice
		if ($user->data['user_perm_from'] && $auth->acl_get('a_switchperm'))
		{
			$this->tpl_name = 'acp_main';
			$this->page_title = 'ACP_MAIN';

			$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . $user->data['user_perm_from'];
			$result = $db->sql_query($sql);
			$user_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$perm_from = get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);

			$template->assign_vars(array(
				'S_RESTORE_PERMISSIONS'		=> true,
				'U_RESTORE_PERMISSIONS'		=> append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=restore_perm'),
				'PERM_FROM'					=> $perm_from,
				'L_PERMISSIONS_TRANSFERRED_EXPLAIN'	=> sprintf($user->lang['PERMISSIONS_TRANSFERRED_EXPLAIN'], $perm_from, append_sid("{$phpbb_root_path}ucp.$phpEx", 'mode=restore_perm')),
			));

			return;
		}

		$action = $request->variable('action', '');

		if ($action)
		{
			if ($action === 'admlogout')
			{
				$user->unset_admin();
				redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
			}

			if (!confirm_box(true))
			{
				switch ($action)
				{
					case 'online':
						$confirm = true;
						$confirm_lang = 'RESET_ONLINE_CONFIRM';
					break;
					case 'stats':
						$confirm = true;
						$confirm_lang = 'RESYNC_STATS_CONFIRM';
					break;
					case 'user':
						$confirm = true;
						$confirm_lang = 'RESYNC_POSTCOUNTS_CONFIRM';
					break;
					case 'date':
						$confirm = true;
						$confirm_lang = 'RESET_DATE_CONFIRM';
					break;
					case 'db_track':
						$confirm = true;
						$confirm_lang = 'RESYNC_POST_MARKING_CONFIRM';
					break;
					case 'purge_cache':
						$confirm = true;
						$confirm_lang = 'PURGE_CACHE_CONFIRM';
					break;
					case 'purge_sessions':
						$confirm = true;
						$confirm_lang = 'PURGE_SESSIONS_CONFIRM';
					break;

					default:
						$confirm = true;
						$confirm_lang = 'CONFIRM_OPERATION';
				}

				if ($confirm)
				{
					confirm_box(false, $user->lang[$confirm_lang], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
					)));
				}
			}
			else
			{
				switch ($action)
				{

					case 'online':
						if (!$auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$config->set('record_online_users', 1, false);
						$config->set('record_online_date', time(), false);
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RESET_ONLINE');

						if ($request->is_ajax())
						{
							trigger_error('RESET_ONLINE_SUCCESS');
						}
					break;

					case 'stats':
						if (!$auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT COUNT(post_id) AS stat
							FROM ' . POSTS_TABLE . '
							WHERE post_visibility = ' . ITEM_APPROVED;
						$result = $db->sql_query($sql);
						$config->set('num_posts', (int) $db->sql_fetchfield('stat'), false);
						$db->sql_freeresult($result);

						$sql = 'SELECT COUNT(topic_id) AS stat
							FROM ' . TOPICS_TABLE . '
							WHERE topic_visibility = ' . ITEM_APPROVED;
						$result = $db->sql_query($sql);
						$config->set('num_topics', (int) $db->sql_fetchfield('stat'), false);
						$db->sql_freeresult($result);

						$sql = 'SELECT COUNT(user_id) AS stat
							FROM ' . USERS_TABLE . '
							WHERE user_type IN (' . USER_NORMAL . ',' . USER_FOUNDER . ')';
						$result = $db->sql_query($sql);
						$config->set('num_users', (int) $db->sql_fetchfield('stat'), false);
						$db->sql_freeresult($result);

						$sql = 'SELECT COUNT(attach_id) as stat
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE is_orphan = 0';
						$result = $db->sql_query($sql);
						$config->set('num_files', (int) $db->sql_fetchfield('stat'), false);
						$db->sql_freeresult($result);

						$sql = 'SELECT SUM(filesize) as stat
							FROM ' . ATTACHMENTS_TABLE . '
							WHERE is_orphan = 0';
						$result = $db->sql_query($sql);
						$config->set('upload_dir_size', (float) $db->sql_fetchfield('stat'), false);
						$db->sql_freeresult($result);

						if (!function_exists('update_last_username'))
						{
							include($phpbb_root_path . "includes/functions_user.$phpEx");
						}
						update_last_username();

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RESYNC_STATS');

						if ($request->is_ajax())
						{
							trigger_error('RESYNC_STATS_SUCCESS');
						}
					break;

					case 'user':
						if (!$auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						// Resync post counts
						$start = $max_post_id = 0;

						// Find the maximum post ID, we can only stop the cycle when we've reached it
						$sql = 'SELECT MAX(forum_last_post_id) as max_post_id
							FROM ' . FORUMS_TABLE;
						$result = $db->sql_query($sql);
						$max_post_id = (int) $db->sql_fetchfield('max_post_id');
						$db->sql_freeresult($result);

						// No maximum post id? :o
						if (!$max_post_id)
						{
							$sql = 'SELECT MAX(post_id) as max_post_id
								FROM ' . POSTS_TABLE;
							$result = $db->sql_query($sql);
							$max_post_id = (int) $db->sql_fetchfield('max_post_id');
							$db->sql_freeresult($result);
						}

						// Still no maximum post id? Then we are finished
						if (!$max_post_id)
						{
							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RESYNC_POSTCOUNTS');
							break;
						}

						$step = ($config['num_posts']) ? (max((int) ($config['num_posts'] / 5), 20000)) : 20000;
						$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_posts = 0');

						while ($start < $max_post_id)
						{
							$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
								FROM ' . POSTS_TABLE . '
								WHERE post_id BETWEEN ' . ($start + 1) . ' AND ' . ($start + $step) . '
									AND post_postcount = 1 AND post_visibility = ' . ITEM_APPROVED . '
								GROUP BY poster_id';
							$result = $db->sql_query($sql);

							if ($row = $db->sql_fetchrow($result))
							{
								do
								{
									$sql = 'UPDATE ' . USERS_TABLE . " SET user_posts = user_posts + {$row['num_posts']} WHERE user_id = {$row['poster_id']}";
									$db->sql_query($sql);
								}
								while ($row = $db->sql_fetchrow($result));
							}
							$db->sql_freeresult($result);

							$start += $step;
						}

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RESYNC_POSTCOUNTS');

						if ($request->is_ajax())
						{
							trigger_error('RESYNC_POSTCOUNTS_SUCCESS');
						}
					break;

					case 'date':
						if (!$auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$config->set('board_startdate', time() - 1);
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RESET_DATE');

						if ($request->is_ajax())
						{
							trigger_error('RESET_DATE_SUCCESS');
						}
					break;

					case 'db_track':
						switch ($db->get_sql_layer())
						{
							case 'sqlite3':
								$db->sql_query('DELETE FROM ' . TOPICS_POSTED_TABLE);
							break;

							default:
								$db->sql_query('TRUNCATE TABLE ' . TOPICS_POSTED_TABLE);
							break;
						}

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
										'user_id'		=> (int) $user_id,
										'topic_id'		=> (int) $topic_id,
										'topic_posted'	=> 1,
									);
								}
							}
							unset($posted);

							if (count($sql_ary))
							{
								$db->sql_multi_insert(TOPICS_POSTED_TABLE, $sql_ary);
							}
						}

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RESYNC_POST_MARKING');

						if ($request->is_ajax())
						{
							trigger_error('RESYNC_POST_MARKING_SUCCESS');
						}
					break;

					case 'purge_cache':
						$config->increment('assets_version', 1);
						$cache->purge();

						// Remove old renderers from the text_formatter service. Since this
						// operation is performed after the cache is purged, there is not "current"
						// renderer and in effect all renderers will be purged
						$phpbb_container->get('text_formatter.cache')->tidy();

						// Clear permissions
						$auth->acl_clear_prefetch();
						phpbb_cache_moderators($db, $cache, $auth);

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_PURGE_CACHE');

						if ($request->is_ajax())
						{
							trigger_error('PURGE_CACHE_SUCCESS');
						}
					break;

					case 'purge_sessions':
						if ((int) $user->data['user_type'] !== USER_FOUNDER)
						{
							send_status_line(403, 'Forbidden');
							trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$tables = array(CONFIRM_TABLE, SESSIONS_TABLE);

						foreach ($tables as $table)
						{
							switch ($db->get_sql_layer())
							{
								case 'sqlite3':
									$db->sql_query("DELETE FROM $table");
								break;

								default:
									$db->sql_query("TRUNCATE TABLE $table");
								break;
							}
						}

						// let's restore the admin session
						$reinsert_ary = array(
								'session_id'			=> (string) $user->session_id,
								'session_page'			=> (string) substr($user->page['page'], 0, 199),
								'session_forum_id'		=> $user->page['forum'],
								'session_user_id'		=> (int) $user->data['user_id'],
								'session_start'			=> (int) $user->data['session_start'],
								'session_last_visit'	=> (int) $user->data['session_last_visit'],
								'session_time'			=> (int) $user->time_now,
								'session_browser'		=> (string) trim(substr($user->browser, 0, 149)),
								'session_forwarded_for'	=> (string) $user->forwarded_for,
								'session_ip'			=> (string) $user->ip,
								'session_autologin'		=> (int) $user->data['session_autologin'],
								'session_admin'			=> 1,
								'session_viewonline'	=> (int) $user->data['session_viewonline'],
						);

						$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $reinsert_ary);
						$db->sql_query($sql);

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_PURGE_SESSIONS');

						if ($request->is_ajax())
						{
							trigger_error('PURGE_SESSIONS_SUCCESS');
						}
					break;
				}
			}
		}

		// Version check
		$user->add_lang('install');

		if ($auth->acl_get('a_server') && version_compare(PHP_VERSION, '7.1.3', '<'))
		{
			$template->assign_vars(array(
				'S_PHP_VERSION_OLD'	=> true,
				'L_PHP_VERSION_OLD'	=> sprintf($user->lang['PHP_VERSION_OLD'], PHP_VERSION, '7.1.3', '<a href="https://www.phpbb.com/support/docs/en/3.3/ug/quickstart/requirements">', '</a>'),
			));
		}

		if ($auth->acl_get('a_board'))
		{
			$version_helper = $phpbb_container->get('version_helper');
			try
			{
				$recheck = $request->variable('versioncheck_force', false);
				$updates_available = $version_helper->get_update_on_branch($recheck);
				$upgrades_available = $version_helper->get_suggested_updates();
				if (!empty($upgrades_available))
				{
					$upgrades_available = array_pop($upgrades_available);
				}

				$template->assign_vars(array(
					'S_VERSION_UP_TO_DATE'		=> empty($updates_available),
					'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
					'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $user->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,
				));
			}
			catch (\RuntimeException $e)
			{
				$message = call_user_func_array(array($user, 'lang'), array_merge(array($e->getMessage()), $e->get_parameters()));
				$template->assign_vars(array(
					'S_VERSIONCHECK_FAIL'		=> true,
					'VERSIONCHECK_FAIL_REASON'	=> ($e->getMessage() !== 'VERSIONCHECK_FAIL') ? $message : '',
				));
			}
		}
		else
		{
			// We set this template var to true, to not display an outdated version notice.
			$template->assign_var('S_VERSION_UP_TO_DATE', true);
		}

		// Incomplete update?
		if (phpbb_version_compare($config['version'], PHPBB_VERSION, '<'))
		{
			$template->assign_var('S_UPDATE_INCOMPLETE', true);
		}

		/**
		* Notice admin
		*
		* @event core.acp_main_notice
		* @since 3.1.0-RC3
		*/
		$phpbb_dispatcher->dispatch('core.acp_main_notice');

		// Get forum statistics
		$total_posts = $config['num_posts'];
		$total_topics = $config['num_topics'];
		$total_users = $config['num_users'];
		$total_files = $config['num_files'];

		$start_date = $user->format_date($config['board_startdate']);

		$boarddays = (time() - (int) $config['board_startdate']) / 86400;

		$posts_per_day = sprintf('%.2f', $total_posts / $boarddays);
		$topics_per_day = sprintf('%.2f', $total_topics / $boarddays);
		$users_per_day = sprintf('%.2f', $total_users / $boarddays);
		$files_per_day = sprintf('%.2f', $total_files / $boarddays);

		$upload_dir_size = get_formatted_filesize($config['upload_dir_size']);

		$avatar_dir_size = 0;

		if ($avatar_dir = @opendir($phpbb_root_path . $config['avatar_path']))
		{
			while (($file = readdir($avatar_dir)) !== false)
			{
				if ($file[0] != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
				{
					$avatar_dir_size += filesize($phpbb_root_path . $config['avatar_path'] . '/' . $file);
				}
			}
			closedir($avatar_dir);

			$avatar_dir_size = get_formatted_filesize($avatar_dir_size);
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

		$sql = 'SELECT COUNT(attach_id) AS total_orphan
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE is_orphan = 1
				AND filetime < ' . (time() - 3*60*60);
		$result = $db->sql_query($sql);
		$total_orphan = (int) $db->sql_fetchfield('total_orphan');
		$db->sql_freeresult($result);

		$dbsize = get_database_size();

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
			'TOTAL_ORPHAN'		=> $total_orphan,
			'GZIP_COMPRESSION'	=> ($config['gzip_compress'] && @extension_loaded('zlib')) ? $user->lang['ON'] : $user->lang['OFF'],
			'DATABASE_INFO'		=> $db->sql_server_info(),
			'PHP_VERSION_INFO'	=> PHP_VERSION,
			'BOARD_VERSION'		=> $config['version'],

			'U_ACTION'			=> $this->u_action,
			'U_ADMIN_LOG'		=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=logs&amp;mode=admin'),
			'U_INACTIVE_USERS'	=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=inactive&amp;mode=list'),
			'U_VERSIONCHECK'	=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=update&amp;mode=version_check'),
			'U_VERSIONCHECK_FORCE'	=> append_sid("{$phpbb_admin_path}index.$phpEx", 'versioncheck_force=1'),
			'U_ATTACH_ORPHAN'	=> append_sid("{$phpbb_admin_path}index.$phpEx", 'i=acp_attachments&mode=orphan'),

			'S_VERSIONCHECK'	=> ($auth->acl_get('a_board')) ? true : false,
			'S_ACTION_OPTIONS'	=> ($auth->acl_get('a_board')) ? true : false,
			'S_FOUNDER'			=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,
			)
		);

		$log_data = array();
		$log_count = false;

		if ($auth->acl_get('a_viewlogs'))
		{
			view_log('admin', $log_data, $log_count, 5);

			foreach ($log_data as $row)
			{
				$template->assign_block_vars('log', array(
					'USERNAME'	=> $row['username_full'],
					'IP'		=> $row['ip'],
					'DATE'		=> $user->format_date($row['time']),
					'ACTION'	=> $row['action'])
				);
			}
		}

		if ($auth->acl_get('a_user'))
		{
			$user->add_lang('memberlist');

			$inactive = array();
			$inactive_count = 0;

			view_inactive_users($inactive, $inactive_count, 10);

			foreach ($inactive as $row)
			{
				$template->assign_block_vars('inactive', array(
					'INACTIVE_DATE'	=> $user->format_date($row['user_inactive_time']),
					'REMINDED_DATE'	=> $user->format_date($row['user_reminded_time']),
					'JOINED'		=> $user->format_date($row['user_regdate']),
					'LAST_VISIT'	=> (!$row['user_lastvisit']) ? ' - ' : $user->format_date($row['user_lastvisit']),

					'REASON'		=> $row['inactive_reason'],
					'USER_ID'		=> $row['user_id'],
					'POSTS'			=> ($row['user_posts']) ? $row['user_posts'] : 0,
					'REMINDED'		=> $row['user_reminded'],

					'REMINDED_EXPLAIN'	=> $user->lang('USER_LAST_REMINDED', (int) $row['user_reminded'], $user->format_date($row['user_reminded_time'])),

					'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview')),
					'USERNAME'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
					'USER_COLOR'		=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),

					'U_USER_ADMIN'	=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
					'U_SEARCH_USER'	=> ($auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id={$row['user_id']}&amp;sr=posts") : '',
				));
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

		// Warn if install is still present
		if (!defined('IN_INSTALL') && !$phpbb_container->getParameter('allow_install_dir') && file_exists($phpbb_root_path . 'install') && !is_file($phpbb_root_path . 'install'))
		{
			$template->assign_var('S_REMOVE_INSTALL', true);
		}

		// Warn if no search index is created
		if ($config['num_posts'] && class_exists($config['search_type']))
		{
			$error = false;
			$search_type = $config['search_type'];
			$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

			if (!$search->index_created())
			{
				$template->assign_vars(array(
					'S_SEARCH_INDEX_MISSING'	=> true,
					'L_NO_SEARCH_INDEX'			=> $user->lang('NO_SEARCH_INDEX', $search->get_name(), '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=acp_search&amp;mode=index') . '">', '</a>'),
				));
			}
		}

		if (!defined('PHPBB_DISABLE_CONFIG_CHECK'))
		{
			// World-Writable? (000x)
			$template->assign_var('S_WRITABLE_CONFIG', (bool) (@fileperms($phpbb_root_path . 'config.' . $phpEx) & 0x0002));
		}

		$this->php_ini			= $phpbb_container->get('php_ini');
		$func_overload			= $this->php_ini->getNumeric('mbstring.func_overload');
		$encoding_translation	= $this->php_ini->getString('mbstring.encoding_translation');
		$http_input				= $this->php_ini->getString('mbstring.http_input');
		$http_output			= $this->php_ini->getString('mbstring.http_output');
		$default_charset		= $this->php_ini->getString('default_charset');

		if (extension_loaded('mbstring'))
		{
			/**
			 * “mbstring.http_input” and “mbstring.http_output” are deprecated as of PHP 5.6.0
			 * @link https://www.php.net/manual/mbstring.configuration.php#ini.mbstring.http-input
			 */
			$template->assign_vars([
				'S_MBSTRING_LOADED'						=> true,
				'S_MBSTRING_FUNC_OVERLOAD_FAIL'			=> $func_overload && ($func_overload & (MB_OVERLOAD_MAIL | MB_OVERLOAD_STRING)),
				'S_MBSTRING_ENCODING_TRANSLATION_FAIL'	=> $encoding_translation && ($encoding_translation != 0),
				'S_MBSTRING_HTTP_INPUT_FAIL'			=> !empty($http_input),
				'S_MBSTRING_HTTP_OUTPUT_FAIL'			=> !empty($http_output),
				'S_DEFAULT_CHARSET_FAIL'				=> $default_charset !== null && strtolower($default_charset) !== 'utf-8',
			]);
		}

		// Fill dbms version if not yet filled
		if (empty($config['dbms_version']))
		{
			$config->set('dbms_version', $db->sql_server_info(true));
		}

		$this->tpl_name = 'acp_main';
		$this->page_title = 'ACP_MAIN';
	}
}
