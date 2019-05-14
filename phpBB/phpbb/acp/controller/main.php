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

namespace phpbb\acp\controller;

use phpbb\exception\runtime_exception;

class main
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\filesystem\filesystem */
	protected $filesystem;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\storage\storage */
	protected $storage_avatar;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\textformatter\cache_interface */
	protected $tf_cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\version_helper */
	protected $version_helper;

	/** @var bool Allow install directory */
	protected $allow_install_dir;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @todo */
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth						$auth				Auth object
	 * @param \phpbb\cache\driver\driver_interface	$cache				Cache object
	 * @param \phpbb\config\config					$config				Config object
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\event\dispatcher				$dispatcher			Event dispatcher object
	 * @param \phpbb\filesystem\filesystem			$filesystem			Filesystem object
	 * @param \phpbb\acp\helper\controller			$helper				ACP Controller helper
	 * @param \phpbb\language\language				$lang				Language object
	 * @param \phpbb\log\log						$log				Log object
	 * @param \phpbb\request\request				$request			Request object
	 * @param \phpbb\storage\storage				$storage_avatar		Avatar storage object
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\textformatter\cache_interface	$tf_cache			Textformatter cache object
	 * @param \phpbb\user							$user				User object
	 * @param \phpbb\version_helper					$version_helper		Version helper object
	 * @param bool									$allow_install_dir	Allow install directory
	 * @param string								$admin_path			phpBB admin path
	 * @param string								$root_path			phpBB root path
	 * @param string								$php_ext			php File extension
	 * @param array									$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\filesystem\filesystem $filesystem,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\storage\storage $storage_avatar,
		\phpbb\template\template $template,
		\phpbb\textformatter\cache_interface $tf_cache,
		\phpbb\user $user,
		\phpbb\version_helper $version_helper,
		$allow_install_dir,
		$admin_path,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->cache				= $cache;
		$this->config				= $config;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->filesystem			= $filesystem;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->request				= $request;
		$this->storage_avatar		= $storage_avatar;
		$this->template				= $template;
		$this->tf_cache				= $tf_cache;
		$this->user					= $user;
		$this->version_helper		= $version_helper;

		$this->allow_install_dir	= $allow_install_dir;
		$this->admin_path			= $admin_path;
		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	function main($id, $mode)
	{
		// Show restore permissions notice
		if ($this->user->data['user_perm_from'] && $this->auth->acl_get('a_switchperm'))
		{
			$sql = 'SELECT user_id, username, user_colour
				FROM ' . $this->tables['users'] . '
				WHERE user_id = ' . $this->user->data['user_perm_from'];
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$perm_from = get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']);

			$this->template->assign_vars([
				'S_RESTORE_PERMISSIONS'		=> true,
				'U_RESTORE_PERMISSIONS'		=> append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=restore_perm'),
				'PERM_FROM'					=> $perm_from,
				'L_PERMISSIONS_TRANSFERRED_EXPLAIN'	=> $this->lang->lang('PERMISSIONS_TRANSFERRED_EXPLAIN', $perm_from, append_sid("{$this->root_path}ucp.$this->php_ext", 'mode=restore_perm')),
			]);

			return $this->helper->render('acp_main.html', $this->lang->lang('ACP_MAIN'));
		}

		$action = $this->request->variable('action', '');

		if ($action)
		{
			if ($action === 'admlogout')
			{
				$this->user->unset_admin();
				redirect(append_sid("{$this->root_path}index.$this->php_ext"));
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
					confirm_box(false, $this->lang->lang($confirm_lang), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
					]));
				}
			}
			else
			{
				switch ($action)
				{

					case 'online':
						if (!$this->auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$this->config->set('record_online_users', 1, false);
						$this->config->set('record_online_date', time(), false);
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESET_ONLINE');

						if ($this->request->is_ajax())
						{
							trigger_error('RESET_ONLINE_SUCCESS');
						}
					break;

					case 'stats':
						if (!$this->auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql = 'SELECT COUNT(post_id) AS stat
							FROM ' . $this->tables['posts'] . '
							WHERE post_visibility = ' . ITEM_APPROVED;
						$result = $this->db->sql_query($sql);
						$this->config->set('num_posts', (int) $this->db->sql_fetchfield('stat'), false);
						$this->db->sql_freeresult($result);

						$sql = 'SELECT COUNT(topic_id) AS stat
							FROM ' . $this->tables['topics'] . '
							WHERE topic_visibility = ' . ITEM_APPROVED;
						$result = $this->db->sql_query($sql);
						$this->config->set('num_topics', (int) $this->db->sql_fetchfield('stat'), false);
						$this->db->sql_freeresult($result);

						$sql = 'SELECT COUNT(user_id) AS stat
							FROM ' . $this->tables['users'] . '
							WHERE user_type IN (' . USER_NORMAL . ',' . USER_FOUNDER . ')';
						$result = $this->db->sql_query($sql);
						$this->config->set('num_users', (int) $this->db->sql_fetchfield('stat'), false);
						$this->db->sql_freeresult($result);

						$sql = 'SELECT COUNT(attach_id) as stat
							FROM ' . $this->tables['attachments'] . '
							WHERE is_orphan = 0';
						$result = $this->db->sql_query($sql);
						$this->config->set('num_files', (int) $this->db->sql_fetchfield('stat'), false);
						$this->db->sql_freeresult($result);

						$sql = 'SELECT SUM(filesize) as stat
							FROM ' . $this->tables['attachments'] . '
							WHERE is_orphan = 0';
						$result = $this->db->sql_query($sql);
						$this->config->set('upload_dir_size', (float) $this->db->sql_fetchfield('stat'), false);
						$this->db->sql_freeresult($result);

						if (!function_exists('update_last_username'))
						{
							include($this->root_path . "includes/functions_user.$this->php_ext");
						}
						update_last_username();

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESYNC_STATS');

						if ($this->request->is_ajax())
						{
							trigger_error('RESYNC_STATS_SUCCESS');
						}
					break;

					case 'user':
						if (!$this->auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						// Resync post counts
						$start = $max_post_id = 0;

						// Find the maximum post ID, we can only stop the cycle when we've reached it
						$sql = 'SELECT MAX(forum_last_post_id) as max_post_id
							FROM ' . $this->tables['forums'];
						$result = $this->db->sql_query($sql);
						$max_post_id = (int) $this->db->sql_fetchfield('max_post_id');
						$this->db->sql_freeresult($result);

						// No maximum post id? :o
						if (!$max_post_id)
						{
							$sql = 'SELECT MAX(post_id) as max_post_id
								FROM ' . $this->tables['posts'];
							$result = $this->db->sql_query($sql);
							$max_post_id = (int) $this->db->sql_fetchfield('max_post_id');
							$this->db->sql_freeresult($result);
						}

						// Still no maximum post id? Then we are finished
						if (!$max_post_id)
						{
							$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESYNC_POSTCOUNTS');
							break;
						}

						$step = ($this->config['num_posts']) ? (max((int) ($this->config['num_posts'] / 5), 20000)) : 20000;
						$this->db->sql_query('UPDATE ' . $this->tables['users'] . ' SET user_posts = 0');

						while ($start < $max_post_id)
						{
							$sql = 'SELECT COUNT(post_id) AS num_posts, poster_id
								FROM ' . $this->tables['posts'] . '
								WHERE post_id BETWEEN ' . ($start + 1) . ' AND ' . ($start + $step) . '
									AND post_postcount = 1 AND post_visibility = ' . ITEM_APPROVED . '
								GROUP BY poster_id';
							$result = $this->db->sql_query($sql);

							if ($row = $this->db->sql_fetchrow($result))
							{
								do
								{
									$sql = 'UPDATE ' . $this->tables['users'] . " SET user_posts = user_posts + {$row['num_posts']} WHERE user_id = {$row['poster_id']}";
									$this->db->sql_query($sql);
								}
								while ($row = $this->db->sql_fetchrow($result));
							}
							$this->db->sql_freeresult($result);

							$start += $step;
						}

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESYNC_POSTCOUNTS');

						if ($this->request->is_ajax())
						{
							trigger_error('RESYNC_POSTCOUNTS_SUCCESS');
						}
					break;

					case 'date':
						if (!$this->auth->acl_get('a_board'))
						{
							send_status_line(403, 'Forbidden');
							trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$this->config->set('board_startdate', time() - 1);
						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESET_DATE');

						if ($this->request->is_ajax())
						{
							trigger_error('RESET_DATE_SUCCESS');
						}
					break;

					case 'db_track':
						switch ($this->db->get_sql_layer())
						{
							case 'sqlite3':
								$this->db->sql_query('DELETE FROM ' . $this->tables['topics_posted']);
							break;

							default:
								$this->db->sql_query('TRUNCATE TABLE ' . $this->tables['topics_posted']);
							break;
						}

						// This can get really nasty... therefore we only do the last six months
						$get_from_time = time() - (6 * 4 * 7 * 24 * 60 * 60);

						// Select forum ids, do not include categories
						$sql = 'SELECT forum_id
							FROM ' . $this->tables['forums'] . '
							WHERE forum_type <> ' . FORUM_CAT;
						$result = $this->db->sql_query($sql);

						$forum_ids = [];
						while ($row = $this->db->sql_fetchrow($result))
						{
							$forum_ids[] = $row['forum_id'];
						}
						$this->db->sql_freeresult($result);

						// Any global announcements? ;)
						$forum_ids[] = 0;

						// Now go through the forums and get us some topics...
						foreach ($forum_ids as $forum_id)
						{
							$posted = [];

							$sql = 'SELECT p.poster_id, p.topic_id
								FROM ' . $this->tables['posts'] . ' p, ' . $this->tables['topics'] . ' t
								WHERE t.forum_id = ' . (int) $forum_id . '
									AND t.topic_moved_id = 0
									AND t.topic_last_post_time > ' . (int) $get_from_time . '
									AND t.topic_id = p.topic_id
									AND p.poster_id <> ' . ANONYMOUS . '
								GROUP BY p.poster_id, p.topic_id';
							$result = $this->db->sql_query($sql);
							while ($row = $this->db->sql_fetchrow($result))
							{
								$posted[$row['poster_id']][] = $row['topic_id'];
							}
							$this->db->sql_freeresult($result);

							$sql_ary = [];
							foreach ($posted as $user_id => $topic_row)
							{
								foreach ($topic_row as $topic_id)
								{
									$sql_ary[] = [
										'user_id'		=> (int) $user_id,
										'topic_id'		=> (int) $topic_id,
										'topic_posted'	=> 1,
									];
								}
							}
							unset($posted);

							if (!empty($sql_ary))
							{
								$this->db->sql_multi_insert($this->tables['topics_posted'], $sql_ary);
							}
						}

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RESYNC_POST_MARKING');

						if ($this->request->is_ajax())
						{
							trigger_error('RESYNC_POST_MARKING_SUCCESS');
						}
					break;

					case 'purge_cache':
						$this->config->increment('assets_version', 1);
						$this->cache->purge();

						// Remove old renderers from the text_formatter service. Since this
						// operation is performed after the cache is purged, there is not "current"
						// renderer and in effect all renderers will be purged
						$this->tf_cache->tidy();

						// Clear permissions
						$this->auth->acl_clear_prefetch();
						phpbb_cache_moderators($this->db, $this->cache, $this->auth);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PURGE_CACHE');

						if ($this->request->is_ajax())
						{
							trigger_error('PURGE_CACHE_SUCCESS');
						}
					break;

					case 'purge_sessions':
						if ((int) $this->user->data['user_type'] !== USER_FOUNDER)
						{
							send_status_line(403, 'Forbidden');
							trigger_error($this->lang->lang('NO_AUTH_OPERATION') . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$tables = [$this->tables['confirm'], $this->tables['sessions']];

						foreach ($tables as $table)
						{
							switch ($this->db->get_sql_layer())
							{
								case 'sqlite3':
									$this->db->sql_query("DELETE FROM $table");
								break;

								default:
									$this->db->sql_query("TRUNCATE TABLE $table");
								break;
							}
						}

						// let's restore the admin session
						$reinsert_ary = [
							'session_id'			=> (string) $this->user->session_id,
							'session_page'			=> (string) substr($this->user->page['page'], 0, 199),
							'session_forum_id'		=> $this->user->page['forum'],
							'session_user_id'		=> (int) $this->user->data['user_id'],
							'session_start'			=> (int) $this->user->data['session_start'],
							'session_last_visit'	=> (int) $this->user->data['session_last_visit'],
							'session_time'			=> (int) $this->user->time_now,
							'session_browser'		=> (string) trim(substr($this->user->browser, 0, 149)),
							'session_forwarded_for'	=> (string) $this->user->forwarded_for,
							'session_ip'			=> (string) $this->user->ip,
							'session_autologin'		=> (int) $this->user->data['session_autologin'],
							'session_admin'			=> 1,
							'session_viewonline'	=> (int) $this->user->data['session_viewonline'],
						];

						$sql = 'INSERT INTO ' . $this->tables['sessions'] . ' ' . $this->db->sql_build_array('INSERT', $reinsert_ary);
						$this->db->sql_query($sql);

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PURGE_SESSIONS');

						if ($this->request->is_ajax())
						{
							trigger_error('PURGE_SESSIONS_SUCCESS');
						}
					break;
				}
			}
		}

		// Version check
		$this->lang->add_lang('install');

		if ($this->auth->acl_get('a_server') && version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			$this->template->assign_vars([
				'S_PHP_VERSION_OLD'	=> true,
				'L_PHP_VERSION_OLD'	=> $this->lang->lang('PHP_VERSION_OLD', PHP_VERSION, '5.4.0', '<a href="https://www.phpbb.com/support/docs/en/3.2/ug/quickstart/requirements">', '</a>'),
			]);
		}

		if ($this->auth->acl_get('a_board'))
		{
			try
			{
				$recheck = $this->request->variable('versioncheck_force', false);
				$updates_available = $this->version_helper->get_update_on_branch($recheck);
				$upgrades_available = $this->version_helper->get_suggested_updates();
				if (!empty($upgrades_available))
				{
					$upgrades_available = array_pop($upgrades_available);
				}

				$this->template->assign_vars([
					'S_VERSION_UP_TO_DATE'		=> empty($updates_available),
					'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
					'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $this->lang->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,
				]);
			}
			catch (runtime_exception $e)
			{
				$message = call_user_func_array([$this->lang, 'lang'], array_merge([$e->getMessage()], $e->get_parameters()));
				$this->template->assign_vars([
					'S_VERSIONCHECK_FAIL'		=> true,
					'VERSIONCHECK_FAIL_REASON'	=> ($e->getMessage() !== 'VERSIONCHECK_FAIL') ? $message : '',
				]);
			}
		}
		else
		{
			// We set this template var to true, to not display an outdated version notice.
			$this->template->assign_var('S_VERSION_UP_TO_DATE', true);
		}

		// Incomplete update?
		if (phpbb_version_compare($this->config['version'], PHPBB_VERSION, '<'))
		{
			$this->template->assign_var('S_UPDATE_INCOMPLETE', true);
		}

		/**
		 * Notice admin
		 *
		 * @event core.acp_main_notice
		 * @since 3.1.0-RC3
		 */
		$this->dispatcher->dispatch('core.acp_main_notice');

		// Get forum statistics
		$total_posts = $this->config['num_posts'];
		$total_topics = $this->config['num_topics'];
		$total_users = $this->config['num_users'];
		$total_files = $this->config['num_files'];

		$start_date = $this->user->format_date($this->config['board_startdate']);

		$board_days = (time() - $this->config['board_startdate']) / 86400;

		$posts_per_day = sprintf('%.2f', $total_posts / $board_days);
		$topics_per_day = sprintf('%.2f', $total_topics / $board_days);
		$users_per_day = sprintf('%.2f', $total_users / $board_days);
		$files_per_day = sprintf('%.2f', $total_files / $board_days);

		$upload_dir_size = get_formatted_filesize($this->config['upload_dir_size']);

		$avatar_dir_size = get_formatted_filesize($this->storage_avatar->get_size());

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

		if ($this->config['allow_attachments'] || $this->config['allow_pm_attach'])
		{
			$sql = 'SELECT COUNT(attach_id) AS total_orphan
				FROM ' . $this->tables['attachments'] . '
				WHERE is_orphan = 1
					AND filetime < ' . (time() - 3*60*60);
			$result = $this->db->sql_query($sql);
			$total_orphan = (int) $this->db->sql_fetchfield('total_orphan');
			$this->db->sql_freeresult($result);
		}
		else
		{
			$total_orphan = false;
		}

		$this->template->assign_vars([
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
			'DBSIZE'			=> get_database_size(),
			'UPLOAD_DIR_SIZE'	=> $upload_dir_size,
			'TOTAL_ORPHAN'		=> $total_orphan,
			'S_TOTAL_ORPHAN'	=> $total_orphan === false ? false : true,
			'GZIP_COMPRESSION'	=> ($this->config['gzip_compress'] && @extension_loaded('zlib')) ? $this->lang->lang('ON') : $this->lang->lang('OFF'),
			'DATABASE_INFO'		=> $this->db->sql_server_info(),
			'PHP_VERSION_INFO'	=> PHP_VERSION,
			'BOARD_VERSION'		=> $this->config['version'],

			'U_ACTION'			=> $this->u_action,
			'U_ADMIN_LOG'		=> append_sid("{$this->admin_path}index.$this->php_ext", 'i=logs&amp;mode=admin'),
			'U_INACTIVE_USERS'	=> append_sid("{$this->admin_path}index.$this->php_ext", 'i=inactive&amp;mode=list'),
			'U_VERSIONCHECK'	=> append_sid("{$this->admin_path}index.$this->php_ext", 'i=update&amp;mode=version_check'),
			'U_VERSIONCHECK_FORCE'	=> append_sid("{$this->admin_path}index.$this->php_ext", 'versioncheck_force=1'),
			'U_ATTACH_ORPHAN'	=> append_sid("{$this->admin_path}index.$this->php_ext", 'i=acp_attachments&mode=orphan'),

			'S_VERSIONCHECK'	=> $this->auth->acl_get('a_board'),
			'S_ACTION_OPTIONS'	=> $this->auth->acl_get('a_board'),
			'S_FOUNDER'			=> $this->user->data['user_type'] == USER_FOUNDER,
		]);

		$log_data = [];
		$log_count = false;

		if ($this->auth->acl_get('a_viewlogs'))
		{
			view_log('admin', $log_data, $log_count, 5);

			foreach ($log_data as $row)
			{
				$this->template->assign_block_vars('log', [
					'USERNAME'	=> $row['username_full'],
					'IP'		=> $row['ip'],
					'DATE'		=> $this->user->format_date($row['time']),
					'ACTION'	=> $row['action'],
				]);
			}
		}

		if ($this->auth->acl_get('a_user'))
		{
			$this->lang->add_lang('memberlist');

			$inactive = [];
			$inactive_count = 0;

			view_inactive_users($inactive, $inactive_count, 10);

			foreach ($inactive as $row)
			{
				$this->template->assign_block_vars('inactive', [
					'INACTIVE_DATE'	=> $this->user->format_date($row['user_inactive_time']),
					'REMINDED_DATE'	=> $this->user->format_date($row['user_reminded_time']),
					'JOINED'		=> $this->user->format_date($row['user_regdate']),
					'LAST_VISIT'	=> $row['user_lastvisit'] ? $this->user->format_date($row['user_lastvisit']) : ' - ',

					'REASON'		=> $row['inactive_reason'],
					'USER_ID'		=> $row['user_id'],
					'POSTS'			=> $row['user_posts'] ? $row['user_posts'] : 0,
					'REMINDED'		=> $row['user_reminded'],

					'REMINDED_EXPLAIN'	=> $this->lang->lang('USER_LAST_REMINDED', (int) $row['user_reminded'], $this->user->format_date($row['user_reminded_time'])),

					'USERNAME_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], false, append_sid("{$this->admin_path}index.$this->php_ext", 'i=users&amp;mode=overview')),
					'USERNAME'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
					'USER_COLOR'		=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),

					'U_USER_ADMIN'	=> append_sid("{$this->admin_path}index.$this->php_ext", "i=users&amp;mode=overview&amp;u={$row['user_id']}"),
					'U_SEARCH_USER'	=> $this->auth->acl_get('u_search') ? append_sid("{$this->root_path}search.$this->php_ext", "author_id={$row['user_id']}&amp;sr=posts") : '',
				]);
			}

			$option_ary = ['activate' => 'ACTIVATE', 'delete' => 'DELETE'];
			if ($this->config['email_enable'])
			{
				$option_ary += ['remind' => 'REMIND'];
			}

			$this->template->assign_vars([
				'S_INACTIVE_USERS'		=> true,
				'S_INACTIVE_OPTIONS'	=> build_select($option_ary),
			]);
		}

		// Warn if install is still present
		if (!$this->allow_install_dir && file_exists($this->root_path . 'install') && !is_file($this->root_path . 'install'))
		{
			$this->template->assign_var('S_REMOVE_INSTALL', true);
		}

		// Warn if no search index is created
		if ($this->config['num_posts'] && class_exists($this->config['search_type']))
		{
			$error = false;
			$search_type = $this->config['search_type'];

			/** @var \phpbb\search\fulltext_mysql $search		@todo Search interface?? */
			$search = new $search_type($error, $this->root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user, $this->dispatcher);

			if (!$search->index_created())
			{
				$this->template->assign_vars([
					'S_SEARCH_INDEX_MISSING'	=> true,
					'L_NO_SEARCH_INDEX'			=> $this->lang->lang('NO_SEARCH_INDEX', $search->get_name(), '<a href="' . append_sid("{$this->admin_path}index.$this->php_ext", 'i=acp_search&amp;mode=index') . '">', '</a>'),
				]);
			}
		}

		if (!defined('PHPBB_DISABLE_CONFIG_CHECK') && file_exists($this->root_path . 'config.' . $this->php_ext) && $this->filesystem->is_writable($this->root_path . 'config.' . $this->php_ext))
		{
			// World-Writable? (000x)
			$this->template->assign_var('S_WRITABLE_CONFIG', (bool) (@fileperms($this->root_path . 'config.' . $this->php_ext) & 0x0002));
		}

		if (extension_loaded('mbstring'))
		{
			$this->template->assign_vars([
				'S_MBSTRING_LOADED'						=> true,
				'S_MBSTRING_FUNC_OVERLOAD_FAIL'			=> (intval(@ini_get('mbstring.func_overload')) & (MB_OVERLOAD_MAIL | MB_OVERLOAD_STRING)),
				'S_MBSTRING_ENCODING_TRANSLATION_FAIL'	=> (@ini_get('mbstring.encoding_translation') != 0),
				'S_MBSTRING_HTTP_INPUT_FAIL'			=> !in_array(@ini_get('mbstring.http_input'), ['pass', '']),
				'S_MBSTRING_HTTP_OUTPUT_FAIL'			=> !in_array(@ini_get('mbstring.http_output'), ['pass', '']),
			]);
		}

		// Fill dbms version if not yet filled
		if (empty($this->config['dbms_version']))
		{
			$this->config->set('dbms_version', $this->db->sql_server_info(true));
		}

		return $this->helper->render('acp_main.html', $this->lang->lang('ACP_MAIN'));
	}
}
