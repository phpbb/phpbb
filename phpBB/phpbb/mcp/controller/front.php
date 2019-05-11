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

class front
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

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
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function view($id)
	{
		/** @todo */
		global $module;

		// Latest 5 unapproved
		if ($module->loaded('queue'))
		{
			$forum_id = $this->request->variable('f', 0);
			$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_approve')));
			$forum_names = [];
			$post_list = [];

			$this->template->assign_var('S_SHOW_UNAPPROVED', !empty($forum_list));

			if (!empty($forum_list))
			{
				$sql_ary = [
					'SELECT' => 'COUNT(post_id) AS total',
					'FROM' => [$this->tables['posts'] => 'p'],
					'WHERE' => $this->db->sql_in_set('p.forum_id', $forum_list) . '
						AND ' . $this->db->sql_in_set('p.post_visibility', [ITEM_UNAPPROVED, ITEM_REAPPROVE]),
				];

				/**
				 * Allow altering the query to get the number of unapproved posts
				 *
				 * @event core.mcp_front_queue_unapproved_total_before
				 * @var	array	sql_ary			Query array to get the total number of unapproved posts
				 * @var	array	forum_list		List of forums to look for unapproved posts
				 * @since 3.1.5-RC1
				 */
				$vars = ['sql_ary', 'forum_list'];
				extract($this->dispatcher->trigger_event('core.mcp_front_queue_unapproved_total_before', compact($vars)));

				$sql = $this->db->sql_build_query('SELECT', $sql_ary);
				$result = $this->db->sql_query($sql);
				$total = (int) $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);

				if ($total)
				{
					$sql = 'SELECT forum_id, forum_name
						FROM ' . $this->tables['forums'] . '
						WHERE ' . $this->db->sql_in_set('forum_id', $forum_list);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$forum_names[(int) $row['forum_id']] = $row['forum_name'];
					}
					$this->db->sql_freeresult($result);

					$sql = 'SELECT post_id
						FROM ' . $this->tables['posts'] . '
						WHERE ' . $this->db->sql_in_set('forum_id', $forum_list) . '
							AND ' . $this->db->sql_in_set('post_visibility', [ITEM_UNAPPROVED, ITEM_REAPPROVE]) . '
						ORDER BY post_time DESC, post_id DESC';
					$result = $this->db->sql_query_limit($sql, 5);

					while ($row = $this->db->sql_fetchrow($result))
					{
						$post_list[] = (int) $row['post_id'];
					}
					$this->db->sql_freeresult($result);

					if (empty($post_list))
					{
						$total = 0;
					}
				}

				/**
				 * Alter list of posts and total as required
				 *
				 * @event core.mcp_front_view_queue_postid_list_after
				 * @var	int		total						Number of unapproved posts
				 * @var	array	post_list					List of unapproved posts
				 * @var	array	forum_list					List of forums that contain the posts
				 * @var	array	forum_names					Associative array with forum_id as key and it's corresponding forum_name as value
				 * @since 3.1.0-RC3
				 */
				$vars = ['total', 'post_list', 'forum_list', 'forum_names'];
				extract($this->dispatcher->trigger_event('core.mcp_front_view_queue_postid_list_after', compact($vars)));

				if ($total)
				{
					$sql = 'SELECT p.post_id, p.post_subject, p.post_time, p.post_attachment, p.poster_id, p.post_username, u.username, u.username_clean, u.user_colour, t.topic_id, t.topic_title, t.topic_first_post_id, p.forum_id
						FROM ' . $this->tables['posts'] . ' p, 
							' . $this->tables['topics'] . ' t, 
							' . $this->tables['users'] . ' u
						WHERE ' . $this->db->sql_in_set('p.post_id', $post_list) . '
							AND t.topic_id = p.topic_id
							AND p.poster_id = u.user_id
						ORDER BY p.post_time DESC, p.post_id DESC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$this->template->assign_block_vars('unapproved', [
							'U_POST_DETAILS'	=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']),
							'U_MCP_FORUM'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=main&amp;mode=forum_view&amp;f=' . $row['forum_id']),
							'U_MCP_TOPIC'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=main&amp;mode=topic_view&amp;f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),
							'U_FORUM'			=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $row['forum_id']),
							'U_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),

							'AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour']),
							'AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour']),
							'AUTHOR_COLOUR'		=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour']),
							'U_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour']),

							'FORUM_NAME'		=> $forum_names[$row['forum_id']],
							'POST_ID'			=> $row['post_id'],
							'TOPIC_TITLE'		=> $row['topic_title'],
							'SUBJECT'			=> ($row['post_subject']) ? $row['post_subject'] : $this->lang->lang('NO_SUBJECT'),
							'POST_TIME'			=> $this->user->format_date($row['post_time']),
							'ATTACH_ICON_IMG'	=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',
						]);
					}
					$this->db->sql_freeresult($result);
				}

				$s_hidden_fields = build_hidden_fields([
					'redirect'	=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=main' . ($forum_id ? '&amp;f=' . $forum_id : '')),
				]);

				$this->template->assign_vars([
					'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
					'S_MCP_QUEUE_ACTION'	=> append_sid("{$this->root_path}mcp.$this->php_ext", "i=queue"),
					'L_UNAPPROVED_TOTAL'	=> $this->lang->lang('UNAPPROVED_POSTS_TOTAL', (int) $total),
					'S_HAS_UNAPPROVED_POSTS'=> $total !== 0,
				]);
			}
		}

		// Latest 5 reported
		if ($module->loaded('reports'))
		{
			$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_report')));

			$this->template->assign_var('S_SHOW_REPORTS', !empty($forum_list));

			if (!empty($forum_list))
			{
				$sql = 'SELECT COUNT(r.report_id) AS total
					FROM ' . $this->tables['reports'] . ' r, 
						' . $this->tables['posts'] . ' p
					WHERE r.post_id = p.post_id
						AND r.pm_id = 0
						AND r.report_closed = 0
						AND ' . $this->db->sql_in_set('p.forum_id', $forum_list);

				/**
				 * Alter sql query to count the number of reported posts
				 *
				 * @event core.mcp_front_reports_count_query_before
				 * @var	string	sql				The query string used to get the number of reports that exist
				 * @var	array	forum_list		List of forums that contain the posts
				 * @since 3.1.5-RC1
				 */
				$vars = ['sql', 'forum_list'];
				extract($this->dispatcher->trigger_event('core.mcp_front_reports_count_query_before', compact($vars)));

				$result = $this->db->sql_query($sql);
				$total = (int) $this->db->sql_fetchfield('total');
				$this->db->sql_freeresult($result);

				if ($total)
				{
					$sql_ary = [
						'SELECT'	=> 'r.report_time, p.post_id, p.post_subject, p.post_time, p.post_attachment, u.username, u.username_clean, u.user_colour, u.user_id, u2.username as author_name, u2.username_clean as author_name_clean, u2.user_colour as author_colour, u2.user_id as author_id, t.topic_id, t.topic_title, f.forum_id, f.forum_name',

						'FROM'		=> [
							$this->tables['reports']			=> 'r',
							$this->tables['reports_reasons']	=> 'rr',
							$this->tables['topics']				=> 't',
							$this->tables['users']				=> ['u', 'u2'],
							$this->tables['posts']				=> 'p',
						],

						'LEFT_JOIN'	=> [
							[
								'FROM'	=> [$this->tables['forums'] => 'f'],
								'ON'	=> 'f.forum_id = p.forum_id',
							],
						],

						'WHERE'		=> 'r.post_id = p.post_id
							AND r.pm_id = 0
							AND r.report_closed = 0
							AND r.reason_id = rr.reason_id
							AND p.topic_id = t.topic_id
							AND r.user_id = u.user_id
							AND p.poster_id = u2.user_id
							AND ' . $this->db->sql_in_set('p.forum_id', $forum_list),

						'ORDER_BY'	=> 'p.post_time DESC, p.post_id DESC',
					];

					/**
					 * Alter sql query to get latest reported posts
					 *
					 * @event core.mcp_front_reports_listing_query_before
					 * @var	array	sql_ary			Associative array with the query to be executed
					 * @var	array	forum_list		List of forums that contain the posts
					 * @since 3.1.0-RC3
					 */
					$vars = ['sql_ary', 'forum_list'];
					extract($this->dispatcher->trigger_event('core.mcp_front_reports_listing_query_before', compact($vars)));

					$sql = $this->db->sql_build_query('SELECT', $sql_ary);
					$result = $this->db->sql_query_limit($sql, 5);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$this->template->assign_block_vars('report', [
							'U_POST_DETAILS'	=> append_sid("{$this->root_path}mcp.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id'] . "&amp;i=reports&amp;mode=report_details"),
							'U_MCP_FORUM'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'f=' . $row['forum_id'] . "&amp;i=$id&amp;mode=forum_view"),
							'U_MCP_TOPIC'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . "&amp;i=$id&amp;mode=topic_view"),
							'U_FORUM'			=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $row['forum_id']),
							'U_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id']),

							'REPORTER_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
							'REPORTER'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
							'REPORTER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
							'U_REPORTER'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),

							'AUTHOR_FULL'		=> get_username_string('full', $row['author_id'], $row['author_name'], $row['author_colour']),
							'AUTHOR'			=> get_username_string('username', $row['author_id'], $row['author_name'], $row['author_colour']),
							'AUTHOR_COLOUR'		=> get_username_string('colour', $row['author_id'], $row['author_name'], $row['author_colour']),
							'U_AUTHOR'			=> get_username_string('profile', $row['author_id'], $row['author_name'], $row['author_colour']),

							'FORUM_NAME'		=> $row['forum_name'],
							'TOPIC_TITLE'		=> $row['topic_title'],
							'SUBJECT'			=> ($row['post_subject']) ? $row['post_subject'] : $this->lang->lang('NO_SUBJECT'),
							'REPORT_TIME'		=> $this->user->format_date($row['report_time']),
							'POST_TIME'			=> $this->user->format_date($row['post_time']),
							'ATTACH_ICON_IMG'	=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',
						]);
					}
					$this->db->sql_freeresult($result);
				}

				$this->template->assign_vars([
					'L_REPORTS_TOTAL'	=> $this->lang->lang('REPORTS_TOTAL', (int) $total),
					'S_HAS_REPORTS'		=> $total !== 0,
				]);
			}
		}

		// Latest 5 reported PMs
		if ($module->loaded('pm_reports') && $this->auth->acl_get('m_pm_report'))
		{
			$this->template->assign_var('S_SHOW_PM_REPORTS', true);
			$this->lang->add_lang(['ucp']);

			$sql = 'SELECT COUNT(r.report_id) AS total
				FROM ' . $this->tables['reports'] . ' r, 
					' . $this->tables['privmsgs'] . ' p
				WHERE r.post_id = 0
					AND r.pm_id = p.msg_id
					AND r.report_closed = 0';
			$result = $this->db->sql_query($sql);
			$total = (int) $this->db->sql_fetchfield('total');
			$this->db->sql_freeresult($result);

			if ($total)
			{
				include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);

				$pm_by_id = $pm_list = [];

				$sql_ary = [
					'SELECT'	=> 'r.report_id, r.report_time, p.msg_id, p.message_subject, p.message_time, p.to_address, p.bcc_address, p.message_attachment, u.username, u.username_clean, u.user_colour, u.user_id, u2.username as author_name, u2.username_clean as author_name_clean, u2.user_colour as author_colour, u2.user_id as author_id',

					'FROM'		=> [
						$this->tables['reports']			=> 'r',
						$this->tables['reports_reasons']	=> 'rr',
						$this->tables['users']				=> ['u', 'u2'],
						$this->tables['privmsgs']			=> 'p',
					],

					'WHERE'		=> 'r.pm_id = p.msg_id
						AND r.post_id = 0
						AND r.report_closed = 0
						AND r.reason_id = rr.reason_id
						AND r.user_id = u.user_id
						AND p.author_id = u2.user_id',

					'ORDER_BY'	=> 'p.message_time DESC',
				];
				$sql = $this->db->sql_build_query('SELECT', $sql_ary);
				$result = $this->db->sql_query_limit($sql, 5);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$pm_by_id[(int) $row['msg_id']] = $row;
					$pm_list[] = (int) $row['msg_id'];
				}
				$this->db->sql_freeresult($result);

				$address_list = get_recipient_strings($pm_by_id);

				foreach ($pm_list as $message_id)
				{
					$row = $pm_by_id[$message_id];

					$this->template->assign_block_vars('pm_report', [
						'U_PM_DETAILS'	=> append_sid("{$this->root_path}mcp.$this->php_ext", 'r=' . $row['report_id'] . "&amp;i=pm_reports&amp;mode=pm_report_details"),

						'REPORTER_FULL'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'REPORTER'			=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
						'REPORTER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
						'U_REPORTER'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),

						'PM_AUTHOR_FULL'	=> get_username_string('full', $row['author_id'], $row['author_name'], $row['author_colour']),
						'PM_AUTHOR'			=> get_username_string('username', $row['author_id'], $row['author_name'], $row['author_colour']),
						'PM_AUTHOR_COLOUR'	=> get_username_string('colour', $row['author_id'], $row['author_name'], $row['author_colour']),
						'U_PM_AUTHOR'		=> get_username_string('profile', $row['author_id'], $row['author_name'], $row['author_colour']),

						'PM_SUBJECT'		=> $row['message_subject'],
						'REPORT_TIME'		=> $this->user->format_date($row['report_time']),
						'PM_TIME'			=> $this->user->format_date($row['message_time']),
						'RECIPIENTS'		=> implode(', ', $address_list[$row['msg_id']]),
						'ATTACH_ICON_IMG'	=> ($this->auth->acl_get('u_download') && $row['message_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',
					]);
				}
			}

			$this->template->assign_vars([
				'L_PM_REPORTS_TOTAL'	=> $this->lang->lang('PM_REPORTS_TOTAL', (int) $total),
				'S_HAS_PM_REPORTS'		=> $total !== 0,
			]);
		}

		// Latest 5 logs
		if ($module->loaded('logs'))
		{
			$forum_list = array_values(array_intersect(get_forum_list('f_read'), get_forum_list('m_')));

			if (!empty($forum_list))
			{
				$log_count = false;
				$log = [];
				view_log('mod', $log, $log_count, 5, 0, $forum_list);

				foreach ($log as $row)
				{
					$this->template->assign_block_vars('log', [
						'USERNAME'		=> $row['username_full'],
						'IP'			=> $row['ip'],
						'TIME'			=> $this->user->format_date($row['time']),
						'ACTION'		=> $row['action'],
						'U_VIEW_TOPIC'	=> !empty($row['viewtopic']) ? $row['viewtopic'] : '',
						'U_VIEWLOGS'	=> !empty($row['viewlogs']) ? $row['viewlogs'] : '',
					]);
				}
			}

			$this->template->assign_vars([
				'S_SHOW_LOGS'	=> !empty($forum_list),
				'S_HAS_LOGS'	=> !empty($log),
			]);
		}

		$this->template->assign_var('S_MCP_ACTION', append_sid("{$this->root_path}mcp.$this->php_ext"));
		make_jumpbox(append_sid("{$this->root_path}mcp.$this->php_ext", 'i=main&amp;mode=forum_view'), 0, false, 'm_', true);
	}
}
