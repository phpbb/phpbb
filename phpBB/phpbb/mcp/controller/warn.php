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

use phpbb\exception\back_exception;
use phpbb\exception\form_invalid_exception;

class warn
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
	protected $lang;

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
	 * @param \phpbb\language\language			$lang			Language object
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
		\phpbb\language\language $lang,
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
		$this->lang			= $lang;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	/**
	 * Display a summary of user warnings.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function warn_overview()
	{
		// Obtain a list of the 5 naughtiest users....
		// These are the 5 users with the highest warning count
		$highest = [];
		$count = 0;

		view_warned_users($highest, $count, 5);

		foreach ($highest as $row)
		{
			$this->template->assign_block_vars('highest', [
				'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

				'WARNING_TIME'	=> $this->user->format_date($row['user_last_warning']),
				'WARNINGS'		=> $row['user_warnings'],

				'U_NOTES'		=> $this->helper->route('mcp_notes_user', ['u' => (int) $row['user_id']]),
			]);
		}

		// And now the 5 most recent users to get in trouble
		$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_warnings, w.warning_time
			FROM ' . $this->tables['users'] . ' u,
				' . $this->tables['warnings'] . ' w
			WHERE u.user_id = w.user_id
			ORDER BY w.warning_time DESC';
		$result = $this->db->sql_query_limit($sql, 5);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('latest', [
				'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

				'WARNING_TIME'	=> $this->user->format_date($row['warning_time']),
				'WARNINGS'		=> $row['user_warnings'],

				'U_NOTES'		=> $this->helper->route('mcp_notes_user', ['u' => (int) $row['user_id']]),
			]);
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('mcp_warn_front.html', $this->lang->lang('MCP_WARN'));
	}

	/**
	 * Lists all users with warnings.
	 *
	 * @param int		$page		The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function warn_list($page = 1)
	{
		$this->lang->add_lang('memberlist');

		// Pagination variables
		$limit = (int) $this->config['topics_per_page'];
		$start = ($page - 1) * $limit;

		// Sorting variables
		$st = $this->request->variable('st', 0);
		$sk = $this->request->variable('sk', 'b');
		$sd = $this->request->variable('sd', 'd');

		$limit_days		= [0 => $this->lang->lang('ALL_ENTRIES'), 1 => $this->lang->lang('1_DAY'), 7 => $this->lang->lang('7_DAYS'), 14 => $this->lang->lang('2_WEEKS'), 30 => $this->lang->lang('1_MONTH'), 90 => $this->lang->lang('3_MONTHS'), 180 => $this->lang->lang('6_MONTHS'), 365 => $this->lang->lang('1_YEAR')];
		$sort_by_text	= ['a' => $this->lang->lang('SORT_USERNAME'), 'b' => $this->lang->lang('SORT_DATE'), 'c' => $this->lang->lang('SORT_WARNINGS')];
		$sort_by_sql	= ['a' => 'username_clean', 'b' => 'user_last_warning', 'c' => 'user_warnings'];

		$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
		gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

		// Define where and sort sql for use in displaying logs
		$sql_where = $st ? (time() - ($st * 86400)) : 0;
		$sql_sort = $sort_by_sql[$sk] . ' ' . ($sd === 'd' ? 'DESC' : 'ASC');

		$users = [];
		$user_count = 0;

		view_warned_users($users, $user_count, $limit, $start, $sql_where, $sql_sort);

		foreach ($users as $row)
		{
			$this->template->assign_block_vars('user', [
				'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),

				'WARNING_TIME'	=> $this->user->format_date($row['user_last_warning']),
				'WARNINGS'		=> $row['user_warnings'],

				'U_NOTES'		=> $this->helper->route('mcp_notes_user', ['u' => (int) $row['user_id']]),
			]);
		}

		parse_str(html_entity_decode($u_sort_param), $sort_param);

		$this->pagination->generate_template_pagination([
			'routes' => ['mcp_warn_list', 'mcp_warn_list_pagination'],
			'params' => $sort_param,
		], 'pagination', 'page', $user_count, $limit, $start);

		$this->template->assign_vars([
			'TOTAL_USERS'			=> $this->lang->lang('LIST_USERS', (int) $user_count),

			'S_CLEAR_ALLOWED'		=> (bool) $this->auth->acl_get('a_clearlogs'),
			'S_SELECT_SORT_DIR'		=> $s_sort_dir,
			'S_SELECT_SORT_KEY'		=> $s_sort_key,
			'S_SELECT_SORT_DAYS'	=> $s_limit_days,

			'U_POST_ACTION'			=> $this->helper->route('mcp_warn_list'),
		]);

		return $this->helper->render('mcp_warn_list.html', $this->lang->lang('MCP_WARN_LIST'));
	}

	/**
	 * Handles warning the user.
	 *
	 * @param int		$u			The user identifier
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function warn_user($u = 0)
	{
		$user_id = $u ? $u : $this->request->variable('user_id', 0);

		if ($user_id || $this->request->is_set_post('submituser'))
		{
			$username	= $this->request->variable('username', '', true);

			$sql_where = $user_id ? 'user_id = ' . (int) $user_id : "username_clean = '" . $this->db->sql_escape(utf8_clean_string($username)) . "'";

			$sql = 'SELECT *
				FROM ' . $this->tables['users'] . '
				WHERE ' . $sql_where;
			$result = $this->db->sql_query($sql);
			$user_row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($user_row === false)
			{
				throw new back_exception(404, 'NO_USER', 'mcp_warn_user');
			}

			// Prevent someone from warning themselves
			if ($user_row['user_id'] == $this->user->data['user_id'])
			{
				throw new back_exception(404, 'CANNOT_WARN_SELF', 'mcp_warn_user');
			}

			$user_id = $user_row['user_id'];

			$action = $this->request->variable('action', ['' => '']);
			$action = is_array($action) ? key($action) : $this->request->variable('action', '');

			$notify = $this->request->is_set('notify_user');
			$warning = $this->request->variable('warning', '', true);

			// Check if can send a notification
			if ($this->config['allow_privmsg'])
			{
				$auth2 = new \phpbb\auth\auth();
				$auth2->acl($user_row);

				$s_can_notify = (bool) $auth2->acl_get('u_readpm');

				unset($auth2);
			}
			else
			{
				$s_can_notify = false;
			}

			// Prevent against clever people
			if ($notify && !$s_can_notify)
			{
				$notify = false;
			}

			$form_key = 'mcp_warn';
			add_form_key($form_key);

			if ($warning && $action === 'add_warning')
			{
				if (!check_form_key($form_key))
				{
					throw new form_invalid_exception(['mcp_warn_user', 'u' => $user_id]);
				}

				$s_mcp_warn_user = true;
				$message = $this->lang->lang('USER_WARNING_ADDED');

				/**
				 * Event for before warning a user from MCP.
				 *
				 * If setting the $s_mcp_warn_user to false, please update the $message aswell.
				 *
				 * @event core.mcp_warn_user_before
				 * @var array	user_row		The entire user row
				 * @var string	warning			The warning message
				 * @var string	message			Message display to the moderator
				 * @var bool	notify			If true, we notify the user for the warning
				 * @var bool	s_mcp_warn_user If true, we add the warning else we omit it
				 * @since 3.1.0-b4
				 */
				$vars = [
					'user_row',
					'warning',
					'message',
					'notify',
					's_mcp_warn_user',
				];
				extract($this->dispatcher->trigger_event('core.mcp_warn_user_before', compact($vars)));

				if ($s_mcp_warn_user)
				{
					$this->add_warning($user_row, $warning, $notify);

					/**
					 * Event for after warning a user from MCP.
					 *
					 * @event core.mcp_warn_user_after
					 * @var array	user_row	The entire user row
					 * @var string	warning		The warning message
					 * @var bool	notify		If true, the user was notified for the warning
					 * @var string	message		Message displayed to the moderator
					 * @since 3.1.0-b4
					 */
					$vars = [
						'user_row',
						'warning',
						'notify',
						'message',
					];
					extract($this->dispatcher->trigger_event('core.mcp_warn_user_after', compact($vars)));

					$u_notes = $this->helper->route('mcp_notes_user', ['u' => $user_id]);
					$return = $this->lang->lang('RETURN_PAGE', '<a href="' . $u_notes . '">', '</a>');

					$this->helper->assign_meta_refresh_var(2, $u_notes);

					return $this->helper->render($message . '<br /><br />' . $return);
				}

				// The warning was omitted by: @event core.mcp_warn_user_before
				throw new back_exception(503, $message, ['mcp_warn_user', 'u' => $user_id]);
			}

			// Generate the appropriate user information for the user we are looking at
			if (!function_exists('phpbb_get_user_rank'))
			{
				include($this->root_path . 'includes/functions_display.' . $this->php_ext);
			}

			$rank	= phpbb_get_user_rank($user_row, $user_row['user_posts']);
			$avatar	= phpbb_get_user_avatar($user_row);

			// OK, they didn't submit a warning so lets build the page for them to do so
			$this->template->assign_vars([
				'RANK_TITLE'		=> $rank['title'],
				'JOINED'			=> $this->user->format_date($user_row['user_regdate']),
				'POSTS'				=> (int) $user_row['user_posts'],
				'WARNINGS'			=> (int) $user_row['user_warnings'],

				'USERNAME_FULL'		=> get_username_string('full', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
				'USERNAME_COLOUR'	=> get_username_string('colour', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
				'USERNAME'			=> get_username_string('username', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),
				'U_PROFILE'			=> get_username_string('profile', $user_row['user_id'], $user_row['username'], $user_row['user_colour']),

				'AVATAR_IMG'		=> $avatar,
				'RANK_IMG'			=> $rank['img'],

				'S_CAN_NOTIFY'		=> $s_can_notify,

				'U_POST_ACTION'		=> $this->helper->route('mcp_warn_user', ['u' => $user_id]),
			]);
		}
		else
		{
			// Select a user
			$this->template->assign_vars([
				'S_FIND_USERNAME'	=> true,
				'U_FIND_USERNAME'	=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=mcp&amp;field=username&amp;select_single=true'),
				'U_POST_ACTION'		=> $this->helper->route('mcp_warn_user'),
			]);
		}

		return $this->helper->render('mcp_warn_user.html', $this->lang->lang('MCP_WARN_USER'));
	}

	/**
	 * Handles warning the user when the warning is for a specific post.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function warn_post()
	{
		$post_id = $this->request->variable('p', 0);

		$action = $this->request->variable('action', ['' => '']);
		$action = is_array($action) ? key($action) : $this->request->variable('action', '');

		$notify = $this->request->is_set('notify_user');
		$warning = $this->request->variable('warning', '', true);

		$sql = 'SELECT u.*, p.*
			FROM ' . $this->tables['posts'] . ' p, 
				' . $this->tables['users'] . ' u
			WHERE p.post_id = ' . (int) $post_id . '
				AND u.user_id = p.poster_id';
		$result = $this->db->sql_query($sql);
		$user_row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($user_row === false)
		{
			throw new back_exception(404, 'NO_POST', 'mcp_warn_overview');
		}

		// There is no point issuing a warning to ignored users (ie anonymous and bots)
		if ($user_row['user_type'] == USER_IGNORE)
		{
			throw new back_exception(400, 'CANNOT_WARN_ANONYMOUS', 'mcp_warn_overview');
		}

		// Prevent someone from warning themselves
		if ($user_row['user_id'] == $this->user->data['user_id'])
		{
			throw new back_exception(400, 'CANNOT_WARN_SELF', 'mcp_warn_overview');
		}

		// Check if there is already a warning for this post to prevent multiple
		// warnings for the same offence
		$sql = 'SELECT post_id
			FROM ' . $this->tables['warnings'] . '
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row !== false)
		{
			throw new back_exception(400, 'ALREADY_WARNED', 'mcp_warn_overview');
		}

		$forum_id = (int) $user_row['forum_id'];
		$topic_id = (int) $user_row['topic_id'];
		$post_id = (int) $user_row['post_id'];
		$user_id = (int) $user_row['user_id'];

		// Check if can send a notification
		if ($this->config['allow_privmsg'])
		{
			$auth2 = new \phpbb\auth\auth();
			$auth2->acl($user_row);

			$s_can_notify = (bool) $auth2->acl_get('u_readpm');

			unset($auth2);
		}
		else
		{
			$s_can_notify = false;
		}

		// Prevent against clever people
		if ($notify && !$s_can_notify)
		{
			$notify = false;
		}

		$form_key = 'mcp_warn';
		add_form_key($form_key);

		if ($warning && $action === 'add_warning')
		{
			if (!check_form_key($form_key))
			{
				throw new form_invalid_exception(['mcp_warn_post', 'f' => $forum_id, 't' => $topic_id, 'p' => $post_id]);
			}

			$s_mcp_warn_post = true;
			$message = $this->lang->lang('USER_WARNING_ADDED');

			/**
			 * Event for before warning a user for a post.
			 *
			 * If setting the $s_mcp_warn_post to false, please update the $message aswell.
			 *
			 * @event core.mcp_warn_post_before
			 * @var array	user_row		The entire user row
			 * @var string	warning			The warning message
			 * @var string	message			Message displayed to the moderator
			 * @var bool	notify			If true, we notify the user for the warning
			 * @var int		post_id			The post id for which the warning is added
			 * @var bool	s_mcp_warn_post If true, we add the warning else we omit it
			 * @since 3.1.0-b4
			 */
			$vars = [
				'user_row',
				'warning',
				'message',
				'notify',
				'post_id',
				's_mcp_warn_post',
			];
			extract($this->dispatcher->trigger_event('core.mcp_warn_post_before', compact($vars)));

			if ($s_mcp_warn_post)
			{
				$this->add_warning($user_row, $warning, $notify, $post_id);

				/**
				 * Event for after warning a user for a post.
				 *
				 * @event core.mcp_warn_post_after
				 * @var array	user_row	The entire user row
				 * @var string	warning		The warning message
				 * @var bool	notify		If true, the user was notified for the warning
				 * @var int		post_id		The post id for which the warning is added
				 * @var string	message		Message displayed to the moderator
				 * @since 3.1.0-b4
				 */
				$vars = [
					'user_row',
					'warning',
					'notify',
					'post_id',
					'message',
				];
				extract($this->dispatcher->trigger_event('core.mcp_warn_post_after', compact($vars)));

				$u_notes = $this->helper->route('mcp_notes_user', ['u' => $user_id, 'f' => $forum_id, 't' => $topic_id, 'p' => $post_id]);
				$return = $this->lang->lang('RETURN_PAGE', '<a href="' . $u_notes . '">', '</a>');

				$this->helper->assign_meta_refresh_var(2, $u_notes);

				return $this->helper->render($message . '<br /><br />' . $return);
			}

			// The warning was omitted by: @event core.mcp_warn_user_before
			throw new back_exception(503, $message, ['mcp_warn_post', 'f' => $forum_id, 't' => $topic_id, 'p' => $post_id]);
		}

		// OK, they didn't submit a warning so lets build the page for them to do so

		// We want to make the message available here as a reminder
		// Parse the message and subject
		$parse_flags = OPTION_FLAG_SMILIES | ($user_row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0);
		$message = generate_text_for_display($user_row['post_text'], $user_row['bbcode_uid'], $user_row['bbcode_bitfield'], $parse_flags, true);

		// Generate the appropriate user information for the user we are looking at
		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}

		$user_rank_data = phpbb_get_user_rank($user_row, $user_row['user_posts']);
		$avatar_img = phpbb_get_user_avatar($user_row);

		$this->template->assign_vars([
			'POST'				=> $message,
			'USERNAME'			=> $user_row['username'],
			'USER_COLOR'		=> !empty($user_row['user_colour']) ? $user_row['user_colour'] : '',
			'RANK_TITLE'		=> $user_rank_data['title'],
			'JOINED'			=> $this->user->format_date($user_row['user_regdate']),
			'POSTS'				=> $user_row['user_posts'] ? $user_row['user_posts'] : 0,
			'WARNINGS'			=> $user_row['user_warnings'] ? $user_row['user_warnings'] : 0,

			'AVATAR_IMG'		=> $avatar_img,
			'RANK_IMG'			=> $user_rank_data['img'],

			'L_WARNING_POST_DEFAULT'	=> $this->lang->lang('WARNING_POST_DEFAULT', generate_board_url() . "/viewtopic.$this->php_ext?f=$forum_id&amp;p=$post_id#p$post_id"),

			'S_CAN_NOTIFY'		=> $s_can_notify,

			'U_POST_ACTION'		=> $this->helper->route('mcp_warn_post', ['f' => $forum_id, 't' => $topic_id, 'p' => $post_id]),
		]);

		return $this->helper->render('mcp_warn_post.html', $this->lang->lang('MCP_WARN_POST'));
	}

	/**
	 * Insert the warning into the database.
	 *
	 * @param array		$user_row		The user data row
	 * @param string	$warning		The warning message
	 * @param bool		$send_pm		Whether or not to send a private message
	 * @param int		$post_id		The post identifier
	 * @return void
	 */
	protected function add_warning(array $user_row, $warning, $send_pm = true, $post_id = 0)
	{
		if ($send_pm)
		{
			include_once($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
			include_once($this->root_path . 'includes/message_parser.' . $this->php_ext);

			// Attempt to translate warning to language of user being warned if user's language differs from issuer's language
			if ($user_row['user_lang'] != $this->lang->get_used_language())
			{
				$lang = [];

				$user_row['user_lang'] = (file_exists($this->root_path . 'language/' . basename($user_row['user_lang']) . "/mcp." . $this->php_ext)) ? $user_row['user_lang'] : $this->config['default_lang'];
				include($this->root_path . 'language/' . basename($user_row['user_lang']) . "/mcp." . $this->php_ext);

				$warn_pm_subject = $lang['WARNING_PM_SUBJECT'];
				$warn_pm_body = sprintf($lang['WARNING_PM_BODY'], $warning);

				unset($lang);
			}
			else
			{
				$warn_pm_subject = $this->lang->lang('WARNING_PM_SUBJECT');
				$warn_pm_body = $this->lang->lang('WARNING_PM_BODY', $warning);
			}

			$message_parser = new \parse_message();

			$message_parser->message = $warn_pm_body;
			$message_parser->parse(true, true, true, false, false, true, true);

			$pm_data = [
				'from_user_id'			=> $this->user->data['user_id'],
				'from_user_ip'			=> $this->user->ip,
				'from_username'			=> $this->user->data['username'],
				'enable_sig'			=> false,
				'enable_bbcode'			=> true,
				'enable_smilies'		=> true,
				'enable_urls'			=> false,
				'icon_id'				=> 0,
				'bbcode_bitfield'		=> $message_parser->bbcode_bitfield,
				'bbcode_uid'			=> $message_parser->bbcode_uid,
				'message'				=> $message_parser->message,
				'address_list'			=> ['u' => [$user_row['user_id'] => 'to']],
			];

			submit_pm('post', $warn_pm_subject, $pm_data, false);
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_WARNING', false, [$user_row['username']]);
		$log_id = $this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_WARNING_BODY', false, [
			'reportee_id' => (int) $user_row['user_id'],
			$warning,
		]);

		$sql_ary = [
			'user_id'		=> (int) $user_row['user_id'],
			'post_id'		=> (int) $post_id,
			'log_id'		=> (int) $log_id,
			'warning_time'	=> (int) time(),
		];

		$sql = 'INSERT INTO ' . $this->tables['warnings'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . $this->tables['users'] . '
			SET user_warnings = user_warnings + 1,
				user_last_warning = ' . (int) time() . '
			WHERE user_id = ' . (int) $user_row['user_id'];
		$this->db->sql_query($sql);

		// We add this to the mod log too for moderators to see that a specific user got warned.
		$sql = 'SELECT forum_id, topic_id
			FROM ' . $this->tables['posts'] . '
			WHERE post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_WARNING', false, [
			'forum_id'	=> (int) $row['forum_id'],
			'topic_id'	=> (int) $row['topic_id'],
			'post_id'	=> (int) $post_id,
			$user_row['username'],
		]);
	}
}
