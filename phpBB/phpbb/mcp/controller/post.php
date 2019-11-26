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

namespace phpbb\mcp\controller;

use phpbb\exception\http_exception;

class post
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$language		Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\pagination					$pagination		Pagination object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main()
	{
		$this->language->add_lang('posting');

		$action = $this->request->variable('action', '');
		$post_id	= $this->request->variable('p', 0);

		// Get post data
		$post_info = phpbb_get_post_data([$post_id], false, true);

		if (empty($post_info))
		{
			$return = '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->helper->route('mcp_index') . '">&laquo; ', '</a>');

			return trigger_error($this->language->lang('POST_NOT_EXIST') . $return, E_USER_WARNING);
		}

		$post_info = $post_info[$post_id];

		$forum_id	= (int) $post_info['forum_id'];
		$topic_id	= (int) $post_info['topic_id'];
		$post_id	= (int) $post_info['post_id'];

		$route = 'mcp_view_post';
		$params = ['f' => $forum_id, 't' => $topic_id, 'p' => $post_id];
		$return = '<br><br>' . $this->language->lang('RETURN_PAGE', '<a href="' . $this->helper->route($route, $params) . '">&laquo; ', '</a>');

		$limit = (int) $this->config['posts_per_page'];
		$start = $this->request->variable('start', 0);

		$form_key = 'mcp_post_details';
		add_form_key($form_key);

		switch ($action)
		{
			case 'whois':
				if ($this->auth->acl_get('m_info', $post_info['forum_id']))
				{
					$ip = $this->request->variable('ip', '');
					if (!function_exists('user_ipwhois'))
					{
						include($this->root_path . 'includes/functions_user.' . $this->php_ext);
					}

					$this->template->assign_vars([
						'WHOIS'			=> user_ipwhois($ip),
						'RETURN_POST'	=> $this->language->lang('RETURN_POST', '<a href="' . $this->helper->route($route, $params) . '">', '</a>'),
						'L_RETURN_POST'	=> $this->language->lang('RETURN_POST', '', ''),
						'U_RETURN_POST'	=> $this->helper->route($route, $params),
					]);
				}

				// We're done with the whois page so return
				return $this->helper->render('mcp_whois.html', $this->language->lang('WHOIS'));
			break;

			case 'chgposter':
			case 'chgposter_ip':
				if ($action === 'chgposter')
				{
					$username = $this->request->variable('username', '', true);
					$sql_where = "username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";
				}
				else
				{
					$new_user_id = $this->request->variable('u', 0);
					$sql_where = 'user_id = ' . $new_user_id;
				}

				$sql = 'SELECT *
					FROM ' . $this->tables['users'] . '
					WHERE ' . $sql_where;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					return trigger_error($this->language->lang('NO_USER') . $return, E_USER_WARNING);
				}

				if ($this->auth->acl_get('m_chgposter', $post_info['forum_id']))
				{
					if (!check_form_key($form_key))
					{
						return trigger_error($this->language->lang('FORM_INVALID') . $return, E_USER_WARNING);
					}

					$this->change_poster($post_info, $row);
				}
			break;

			default:
				/**
				 * This event allows you to handle custom post moderation options
				 *
				 * @event core.mcp_post_additional_options
				 * @var string	action		Post moderation action name
				 * @var array	post_info	Information on the affected post
				 * @since 3.1.5-RC1
				 */
				$vars = ['action', 'post_info'];
				extract($this->dispatcher->trigger_event('core.mcp_post_additional_options', compact($vars)));
			break;
		}

		// Set some vars
		$users_ary = $usernames_ary = [];
		$attachments = $extensions = [];

		$post_id = (int) $post_info['post_id'];

		// Get topic tracking info
		if ($this->config['load_db_lastread'])
		{
			$tmp_topic_data = [$post_info['topic_id'] => $post_info];
			$topic_tracking_info = get_topic_tracking($post_info['forum_id'], $post_info['topic_id'], $tmp_topic_data, [$post_info['forum_id'] => $post_info['forum_mark_time']]);
			unset($tmp_topic_data);
		}
		else
		{
			$topic_tracking_info = get_complete_topic_tracking($post_info['forum_id'], $post_info['topic_id']);
		}

		$post_unread = (isset($topic_tracking_info[$post_info['topic_id']]) && $post_info['post_time'] > $topic_tracking_info[$post_info['topic_id']]) ? true : false;

		// Process message, leave it uncensored
		$parse_flags = ($post_info['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$message = generate_text_for_display($post_info['post_text'], $post_info['bbcode_uid'], $post_info['bbcode_bitfield'], $parse_flags, false);

		if ($post_info['post_attachment'] && $this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $post_info['forum_id']))
		{
			$sql = 'SELECT *
				FROM ' . $this->tables['attachments'] . '
				WHERE post_msg_id = ' . (int) $post_id . '
					AND in_message = 0
				ORDER BY filetime DESC, post_msg_id ASC';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$attachments[] = $row;
			}
			$this->db->sql_freeresult($result);

			if (!empty($attachments))
			{
				$this->language->add_lang('viewtopic');
				$update_count = [];
				parse_attachments($post_info['forum_id'], $message, $attachments, $update_count);
			}

			// Display not already displayed Attachments for this post, we already parsed them. ;)
			if (!empty($attachments))
			{
				$this->template->assign_var('S_HAS_ATTACHMENTS', true);

				foreach ($attachments as $attachment)
				{
					$this->template->assign_block_vars('attachment', ['DISPLAY_ATTACHMENT' => $attachment]);
				}
			}
		}

		// Deleting information
		if ($post_info['post_visibility'] == ITEM_DELETED && $post_info['post_delete_user'])
		{
			// User having deleted the post also being the post author?
			if (!$post_info['post_delete_user'] || $post_info['post_delete_user'] == $post_info['poster_id'])
			{
				$display_username = get_username_string('full', $post_info['poster_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']);
			}
			else
			{
				$sql = 'SELECT user_id, username, user_colour
					FROM ' . $this->tables['users'] . '
					WHERE user_id = ' . (int) $post_info['post_delete_user'];
				$result = $this->db->sql_query($sql);
				$user_delete_row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$display_username = get_username_string('full', $post_info['post_delete_user'], $user_delete_row['username'], $user_delete_row['user_colour']);
			}

			$this->language->add_lang('viewtopic');
			$l_deleted_by = $this->language->lang('DELETED_INFORMATION', $display_username, $this->user->format_date($post_info['post_delete_time'], false, true));
		}
		else
		{
			$l_deleted_by = '';
		}

		// parse signature
		$parse_flags = ($post_info['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$post_info['user_sig'] = generate_text_for_display($post_info['user_sig'], $post_info['user_sig_bbcode_uid'], $post_info['user_sig_bbcode_bitfield'], $parse_flags, true);

		$mcp_post_template_data = [
			'U_MCP_ACTION'			=> $this->helper->route($route, ['quickmod' => true]),
			'U_POST_ACTION'			=> $this->helper->route($route, $params),
			'U_APPROVE_ACTION'		=> $this->helper->route('mcp_unapproved_posts', $params),

			'S_CAN_VIEWIP'			=> (bool) $this->auth->acl_get('m_info', $post_info['forum_id']),
			'S_CAN_CHGPOSTER'		=> (bool) $this->auth->acl_get('m_chgposter', $post_info['forum_id']),
			'S_CAN_LOCK_POST'		=> (bool) $this->auth->acl_get('m_lock', $post_info['forum_id']),
			'S_CAN_DELETE_POST'		=> (bool) $this->auth->acl_get('m_delete', $post_info['forum_id']),

			'S_POST_REPORTED'		=> (bool) $post_info['post_reported'],
			'S_POST_UNAPPROVED'		=> (bool) ($post_info['post_visibility'] == ITEM_UNAPPROVED || $post_info['post_visibility'] == ITEM_REAPPROVE),
			'S_POST_DELETED'		=> (bool) $post_info['post_visibility'] == ITEM_DELETED,
			'S_POST_LOCKED'			=> (bool) $post_info['post_edit_locked'],
			'S_USER_NOTES'			=> true,
			'S_CLEAR_ALLOWED'		=> (bool) $this->auth->acl_get('a_clearlogs'),

			'DELETED_MESSAGE'		=> $l_deleted_by,
			'DELETE_REASON'			=> $post_info['post_delete_reason'],

			'U_EDIT'				=> $this->auth->acl_get('m_edit', $post_info['forum_id']) ? append_sid("{$this->root_path}posting.$this->php_ext", array_merge($params, ['mode' => 'edit'])) : '',
			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=mcp_chgposter&amp;field=username&amp;select_single=true'),
			'U_MCP_APPROVE'			=> $this->helper->route('mcp_approve_details', $params),
			'U_MCP_REPORT'			=> $this->helper->route('mcp_report_details', $params),
			'U_MCP_USER_NOTES'		=> $this->helper->route('mcp_notes_user', array_merge($params, ['u' => $post_info['user_id']])),
			'U_MCP_WARN_USER'		=> $this->auth->acl_get('m_warn') ? $this->helper->route('mcp_warn_user', array_merge($params, ['u' => $post_info['user_id']])) : '',
			'U_VIEW_POST'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", array_merge($params, ['#' => 'p' . $post_id])),
			'U_VIEW_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", $params),

			'MINI_POST_IMG'			=> $post_unread ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),

			'RETURN_TOPIC'			=> $this->language->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", "f={$post_info['forum_id']}&amp;p=$post_id") . "#p$post_id\">", '</a>'),
			'RETURN_FORUM'			=> $this->language->lang('RETURN_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", "f={$post_info['forum_id']}&amp;start={$start}") . '">', '</a>'),
			'REPORTED_IMG'			=> $this->user->img('icon_topic_reported', $this->language->lang('POST_REPORTED')),
			'UNAPPROVED_IMG'		=> $this->user->img('icon_topic_unapproved', $this->language->lang('POST_UNAPPROVED')),
			'DELETED_IMG'			=> $this->user->img('icon_topic_deleted', $this->language->lang('POST_DELETED')),
			'EDIT_IMG'				=> $this->user->img('icon_post_edit', $this->language->lang('EDIT_POST')),
			'SEARCH_IMG'			=> $this->user->img('icon_user_search', $this->language->lang('SEARCH')),

			'POST_AUTHOR_FULL'		=> get_username_string('full', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
			'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
			'POST_AUTHOR'			=> get_username_string('username', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
			'U_POST_AUTHOR'			=> get_username_string('profile', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),

			'POST_PREVIEW'			=> $message,
			'POST_SUBJECT'			=> $post_info['post_subject'],
			'POST_DATE'				=> $this->user->format_date($post_info['post_time']),
			'POST_IP'				=> $post_info['poster_ip'],
			'POST_IPADDR'			=> ($this->auth->acl_get('m_info', $post_info['forum_id']) && $this->request->variable('lookup', '')) ? @gethostbyaddr($post_info['poster_ip']) : '',
			'POST_ID'				=> $post_info['post_id'],
			'SIGNATURE'				=> $post_info['user_sig'],

			'U_LOOKUP_IP'			=> $this->auth->acl_get('m_info', $post_info['forum_id']) ? $this->helper->route($route, array_merge($params, ['lookup' => $post_info['poster_ip'], '#' => 'ip'])) : '',
			'U_WHOIS'				=> $this->auth->acl_get('m_info', $post_info['forum_id']) ? $this->helper->route($route, array_merge($params, ['action' => 'whois', 'ip' => $post_info['poster_ip']])) : '',
		];

		$s_additional_opts = false;

		/**
		 * Event to add/modify MCP post template data
		 *
		 * @event core.mcp_post_template_data
		 * @var array	post_info					Array with the post information
		 * @var array	mcp_post_template_data		Array with the MCP post template data
		 * @var array	attachments					Array with the post attachments, if any
		 * @var bool	s_additional_opts			Must be set to true in extension if additional options are presented in MCP post panel
		 * @since 3.1.5-RC1
		 */
		$vars = [
			'post_info',
			'mcp_post_template_data',
			'attachments',
			's_additional_opts',
		];
		extract($this->dispatcher->trigger_event('core.mcp_post_template_data', compact($vars)));

		$this->template->assign_vars($mcp_post_template_data);
		$this->template->assign_var('S_MCP_POST_ADDITIONAL_OPTS', $s_additional_opts);

		unset($mcp_post_template_data);

		// Get User Notes
		$log_data = [];
		$log_count = false;
		view_log('user', $log_data, $log_count, $limit, 0, 0, 0, $post_info['user_id']);

		if (!empty($log_data))
		{
			$this->template->assign_var('S_USER_NOTES', true);

			foreach ($log_data as $row)
			{
				$this->template->assign_block_vars('usernotes', [
					'ACTION'		=> $row['action'],
					'ID'			=> $row['id'],
					'REPORT_BY'		=> $row['username_full'],
					'REPORT_AT'		=> $this->user->format_date($row['time']),
				]);
			}
		}

		// Get Reports
		if ($this->auth->acl_get('m_report', (int) $post_info['forum_id']))
		{
			$sql = 'SELECT r.*, re.*, u.user_id, u.username
				FROM ' . $this->tables['reports'] . ' r, 
					' . $this->tables['users'] . ' u, 
					' . $this->tables['reports_reasons'] . ' re
				WHERE r.reason_id = re.reason_id
					AND u.user_id = r.user_id
					AND r.post_id = ' . (int) $post_id . '
				ORDER BY r.report_time DESC';
			$result = $this->db->sql_query($sql);

			if ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_var('S_SHOW_REPORTS', true);

				do
				{
					// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
					if (isset($this->language->get_lang_array()['report_reasons']['TITLE'][strtoupper($row['reason_title'])]) && isset($this->language->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])]))
					{
						$row['reason_description'] = $this->language->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($row['reason_title'])];
						$row['reason_title'] = $this->language->get_lang_array()['report_reasons']['TITLE'][strtoupper($row['reason_title'])];
					}

					$this->template->assign_block_vars('reports', [
						'REPORT_ID'		=> $row['report_id'],
						'REASON_TITLE'	=> $row['reason_title'],
						'REASON_DESC'	=> $row['reason_description'],

						'REPORT_TIME'	=> $this->user->format_date($row['report_time']),
						'REPORT_TEXT'	=> bbcode_nl2br(trim($row['report_text'])),

						'REPORTER'		=> get_username_string('username', $row['user_id'], $row['username']),
						'U_REPORTER'	=> get_username_string('profile', $row['user_id'], $row['username']),

						'USER_NOTIFY'	=> (bool) $row['user_notify'],
					]);
				}
				while ($row = $this->db->sql_fetchrow($result));
			}
			$this->db->sql_freeresult($result);
		}

		// Get IP
		if ($this->auth->acl_get('m_info', $post_info['forum_id']))
		{

			$rdns_ip_num = $this->request->variable('rdns', '');
			$start_users = $this->request->variable('start_users', 0);

			if ($rdns_ip_num !== 'all')
			{
				$this->template->assign_var('U_LOOKUP_ALL', $this->helper->route($route, array_merge($params, ['rdns' => 'all'])));
			}

			$num_users = false;

			if ($start_users)
			{
				$num_users = $this->get_num_posters_for_ip($post_info['poster_ip']);
				$start_users = $this->pagination->validate_start($start_users, $limit, $num_users);
			}

			// Get other users who've posted under this IP
			$page_users = 0;

			$sql = 'SELECT poster_id, COUNT(poster_id) as postings
				FROM ' . $this->tables['posts'] . "
				WHERE poster_ip = '" . $this->db->sql_escape($post_info['poster_ip']) . "'
					AND poster_id <> " . (int) $post_info['poster_id'] . "
				GROUP BY poster_id
				ORDER BY postings DESC, poster_id ASC";
			$result = $this->db->sql_query_limit($sql, $limit, $start_users);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$page_users++;
				$users_ary[(int) $row['poster_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			if ($page_users == $limit || $start_users)
			{
				if ($num_users === false)
				{
					$num_users = $this->get_num_posters_for_ip($post_info['poster_ip']);
				}

				$this->pagination->generate_template_pagination([
					'routes' => [$route],
					'params' => $params,
				], 'pagination', 'start_users', $num_users, $limit, $start_users);
			}

			if (!empty($users_ary))
			{
				// Get the usernames
				$sql = 'SELECT user_id, username
					FROM ' . $this->tables['users'] . '
					WHERE ' . $this->db->sql_in_set('user_id', array_keys($users_ary));
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$users_ary[(int) $row['user_id']]['username'] = $row['username'];
					$usernames_ary[utf8_clean_string($row['username'])] = $users_ary[(int) $row['user_id']];
				}
				$this->db->sql_freeresult($result);

				foreach ($users_ary as $user_id => $user_row)
				{
					$this->template->assign_block_vars('userrow', [
						'USERNAME'		=> get_username_string('username', $user_id, $user_row['username']),
						'NUM_POSTS'		=> $user_row['postings'],
						'L_POST_S'		=> $user_row['postings'] == 1 ? $this->language->lang('POST') : $this->language->lang('POSTS'),

						'U_PROFILE'		=> get_username_string('profile', $user_id, $user_row['username']),
						'U_SEARCHPOSTS' => append_sid("{$this->root_path}search.$this->php_ext", 'author_id=' . $user_id . '&amp;sr=topics'),
					]);
				}
			}

			// Get other IP's this user has posted under

			// A compound index on poster_id, poster_ip (posts table) would help speed up this query a lot,
			// but the extra size is only valuable if there are persons having more than a thousands posts.
			// This is better left to the really really big forums.
			$start_ips = $this->request->variable('start_ips', 0);

			$num_ips = false;
			if ($start_ips)
			{
				$num_ips = $this->get_num_ips_for_poster($post_info['poster_id']);
				$start_ips = $this->pagination->validate_start($start_ips, $limit, $num_ips);
			}

			$page_ips = 0;

			$sql = 'SELECT poster_ip, COUNT(poster_ip) AS postings
				FROM ' . $this->tables['posts'] . '
				WHERE poster_id = ' . (int) $post_info['poster_id'] . "
				GROUP BY poster_ip
				ORDER BY postings DESC, poster_ip ASC";
			$result = $this->db->sql_query_limit($sql, $limit, $start_ips);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$page_ips++;
				$hostname = (($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') && $row['poster_ip']) ? @gethostbyaddr($row['poster_ip']) : '';

				$this->template->assign_block_vars('iprow', [
					'IP'			=> $row['poster_ip'],
					'HOSTNAME'		=> $hostname,
					'NUM_POSTS'		=> $row['postings'],
					'L_POST_S'		=> $row['postings'] == 1 ? $this->language->lang('POST') : $this->language->lang('POSTS'),

					'U_LOOKUP_IP'	=> ($rdns_ip_num == $row['poster_ip'] || $rdns_ip_num == 'all') ? '' : $this->helper->route($route, array_merge($params, ['rdns' => $row['poster_ip'], '#' => 'ip'])),
					'U_WHOIS'		=> $this->helper->route($route, array_merge($params, ['action' => 'whois', 'ip' => $row['poster_ip']])),
				]);
			}
			$this->db->sql_freeresult($result);

			if ($page_ips == $limit || $start_ips)
			{
				if ($num_ips === false)
				{
					$num_ips = $this->get_num_ips_for_poster($post_info['poster_id']);
				}

				$this->pagination->generate_template_pagination([
					'routes' => [$route],
					'params' => $params,
				], 'pagination_ips', 'start_ips', $num_ips, $limit, $start_ips);
			}

			$user_select = '';

			if (!empty($usernames_ary))
			{
				ksort($usernames_ary);

				foreach ($usernames_ary as $row)
				{
					$user_select .= '<option value="' . $row['poster_id'] . '">' . $row['username'] . "</option>\n";
				}
			}

			$this->template->assign_var('S_USER_SELECT', $user_select);
		}

		return $this->helper->render('mcp_post.html', $this->language->lang('MCP_MAIN_POST_DETAILS'));
	}

	/**
	 * Get the number of posters for a given ip.
	 *
	 * @param string	$poster_ip		The poster's ip
	 * @return int						Number of posters
	 */
	public function get_num_posters_for_ip($poster_ip)
	{
		$sql = 'SELECT COUNT(DISTINCT poster_id) as num_users
			FROM ' . $this->tables['posts'] . "
			WHERE poster_ip = '" . $this->db->sql_escape($poster_ip) . "'";
		$result = $this->db->sql_query($sql);
		$num_users = (int) $this->db->sql_fetchfield('num_users');
		$this->db->sql_freeresult($result);

		return $num_users;
	}

	/**
	 * Get the number of ips for a given poster.
	 *
	 * @param int		$poster_id		The poster's user identifier
	 * @return int						Number of IPs for given poster
	 */
	public function get_num_ips_for_poster($poster_id)
	{
		$sql = 'SELECT COUNT(DISTINCT poster_ip) as num_ips
			FROM ' . $this->tables['posts'] . '
			WHERE poster_id = ' . (int) $poster_id;
		$result = $this->db->sql_query($sql);
		$num_ips = (int) $this->db->sql_fetchfield('num_ips');
		$this->db->sql_freeresult($result);

		return $num_ips;
	}

	/**
	 * Change a post author.
	 *
	 * @param array		$post_info		The post information
	 * @param array		$user_data		The user data
	 * @return void
	 */
	protected function change_poster(array &$post_info, array $user_data)
	{
		if (empty($user_data) || $user_data['user_id'] == $post_info['user_id'])
		{
			return;
		}

		$post_id = $post_info['post_id'];

		$sql = 'UPDATE ' . $this->tables['posts'] . '
			SET poster_id = ' . (int) $user_data['user_id'] . '
			WHERE post_id = ' . (int) $post_id;
		$this->db->sql_query($sql);

		// Resync topic/forum if needed
		if ($post_info['topic_last_post_id'] == $post_id || $post_info['forum_last_post_id'] == $post_id || $post_info['topic_first_post_id'] == $post_id)
		{
			sync('topic', 'topic_id', $post_info['topic_id'], false, false);
			sync('forum', 'forum_id', $post_info['forum_id'], false, false);
		}

		// Adjust post counts... only if the post is approved (else, it was not added the users post count anyway)
		if ($post_info['post_postcount'] && $post_info['post_visibility'] == ITEM_APPROVED)
		{
			$sql = 'UPDATE ' . $this->tables['users'] . '
				SET user_posts = user_posts - 1
				WHERE user_id = ' . (int) $post_info['user_id'] .'
				AND user_posts > 0';
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->tables['users'] . '
				SET user_posts = user_posts + 1
				WHERE user_id = ' . (int) $user_data['user_id'];
			$this->db->sql_query($sql);
		}

		// Add posted to information for this topic for the new user
		markread('post', $post_info['forum_id'], $post_info['topic_id'], time(), $user_data['user_id']);

		// Remove the dotted topic option if the old user has no more posts within this topic
		if ($this->config['load_db_track'] && $post_info['user_id'] != ANONYMOUS)
		{
			$sql = 'SELECT topic_id
				FROM ' . $this->tables['posts'] . '
				WHERE topic_id = ' . (int) $post_info['topic_id'] . '
					AND poster_id = ' . (int) $post_info['user_id'];
			$result = $this->db->sql_query_limit($sql, 1);
			$topic_id = (int) $this->db->sql_fetchfield('topic_id');
			$this->db->sql_freeresult($result);

			if ($topic_id === 0)
			{
				$sql = 'DELETE FROM ' . $this->tables['topics_posted'] . '
					WHERE user_id = ' . (int) $post_info['user_id'] . '
						AND topic_id = ' . (int) $post_info['topic_id'];
				$this->db->sql_query($sql);
			}
		}

		// change the poster_id within the attachments table, else the data becomes out of sync and errors displayed because of wrong ownership
		if ($post_info['post_attachment'])
		{
			$sql = 'UPDATE ' . $this->tables['attachments'] . '
				SET poster_id = ' . (int) $user_data['user_id'] . '
				WHERE poster_id = ' . (int) $post_info['user_id'] . '
					AND post_msg_id = ' . (int) $post_info['post_id'] . '
					AND topic_id = ' . (int) $post_info['topic_id'];
			$this->db->sql_query($sql);
		}

		// refresh search cache of this post
		$search_type = $this->config['search_type'];

		if (class_exists($search_type))
		{
			// We do some additional checks in the module to ensure it can actually be utilised
			$error = false;

			/** @var \phpbb\search\fulltext_mysql $search		@todo Search interface?? */
			$search = new $search_type($error, $this->root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user, $this->dispatcher);

			if (!$error && method_exists($search, 'destroy_cache'))
			{
				$search->destroy_cache([], [$post_info['user_id'], $user_data['user_id']]);
			}
		}

		$from_username = $post_info['username'];
		$to_username = $user_data['username'];

		/**
		 * This event allows you to perform additional tasks after changing a post's poster
		 *
		 * @event core.mcp_change_poster_after
		 * @var array	userdata	Information on a post's new poster
		 * @var array	post_info	Information on the affected post
		 * @since 3.1.6-RC1
		 * @changed 3.1.7-RC1		Change location to prevent post_info from being set to the new post information
		 */
		$vars = ['userdata', 'post_info'];
		extract($this->dispatcher->trigger_event('core.mcp_change_poster_after', compact($vars)));

		// Renew post info
		$post_info = phpbb_get_post_data([$post_id], false, true);

		if (empty($post_info))
		{
			throw new http_exception(404, 'POST_NOT_EXIST');
		}

		$post_info = $post_info[$post_id];

		// Now add log entry
		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MCP_CHANGE_POSTER', false, [
			'forum_id'	=> (int) $post_info['forum_id'],
			'topic_id'	=> (int) $post_info['topic_id'],
			'post_id'	=> (int) $post_info['post_id'],
			$post_info['topic_title'],
			$from_username,
			$to_username,
		]);
	}
}
