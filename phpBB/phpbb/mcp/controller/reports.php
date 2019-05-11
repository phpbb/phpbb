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

class reports
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

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

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\event\dispatcher			$dispatcher				Event dispatcher
	 * @param \phpbb\language\language			$lang					Language object
	 * @param \phpbb\log\log					$log					Log object
	 * @param \phpbb\notification\manager		$notification_manager	Notification manager object
	 * @param \phpbb\pagination					$pagination				Pagination object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 * @param array								$tables					phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\notification\manager $notification_manager,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->notification_manager	= $notification_manager;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function main($id, $mode)
	{
		/** @todo */
		global $action;

		include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);

		$forum_id = $this->request->variable('f', 0);
		$start = $this->request->variable('start', 0);

		$this->page_title = 'MCP_REPORTS';

		switch ($action)
		{
			case 'close':
			case 'delete':
				include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

				$report_id_list = $this->request->variable('report_id_list', [0]);

				if (empty($report_id_list))
				{
					trigger_error('NO_REPORT_SELECTED');
				}

				close_report($report_id_list, $mode, $action);
			break;
		}

		switch ($mode)
		{
			case 'report_details':
				$this->lang->add_lang(['posting', 'viewforum', 'viewtopic']);

				$post_id = $this->request->variable('p', 0);

				// closed reports are accessed by report id
				$report_id = $this->request->variable('r', 0);

				$sql_ary = [
					'SELECT'	=> 'r.post_id, r.user_id, r.report_id, r.report_closed, report_time, r.report_text, 
									r.reported_post_text, r.reported_post_uid, r.reported_post_bitfield, r.reported_post_enable_magic_url, 
									r.reported_post_enable_smilies, r.reported_post_enable_bbcode, 
									rr.reason_title, rr.reason_description, 
									u.username, u.username_clean, u.user_colour',

					'FROM'		=> [
						$this->tables['reports']			=> 'r',
						$this->tables['reports_reasons']	=> 'rr',
						$this->tables['users']				=> 'u',
					],

					'WHERE'		=> ($report_id ? 'r.report_id = ' . (int) $report_id : 'r.post_id = ' . (int) $post_id) . '
						AND rr.reason_id = r.reason_id
						AND r.user_id = u.user_id
						AND r.pm_id = 0',

					'ORDER_BY'	=> 'report_closed ASC',
				];

				/**
				* Allow changing the query to obtain the user-submitted report.
				*
				* @event core.mcp_reports_report_details_query_before
				* @var	array	sql_ary			The array in the format of the query builder with the query
				* @var	int		forum_id		The forum_id, the number in the f GET parameter
				* @var	int		post_id			The post_id of the report being viewed (if 0, it is meaningless)
				* @var	int		report_id		The report_id of the report being viewed
				* @since 3.1.5-RC1
				*/
				$vars = [
					'sql_ary',
					'forum_id',
					'post_id',
					'report_id',
				];
				extract($this->dispatcher->trigger_event('core.mcp_reports_report_details_query_before', compact($vars)));

				$sql = $this->db->sql_build_query('SELECT', $sql_ary);
				$result = $this->db->sql_query_limit($sql, 1);
				$report = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				/**
				* Allow changing the data obtained from the user-submitted report.
				*
				* @event core.mcp_reports_report_details_query_after
				* @var	array	sql_ary		The array in the format of the query builder with the query that had been executed
				* @var	int		forum_id	The forum_id, the number in the f GET parameter
				* @var	int		post_id		The post_id of the report being viewed (if 0, it is meaningless)
				* @var	int		report_id	The report_id of the report being viewed
				* @var	array	report		The query's resulting row.
				* @since 3.1.5-RC1
				*/
				$vars = [
					'sql_ary',
					'forum_id',
					'post_id',
					'report_id',
					'report',
				];
				extract($this->dispatcher->trigger_event('core.mcp_reports_report_details_query_after', compact($vars)));

				if (!$report)
				{
					trigger_error('NO_REPORT');
				}

				$this->notification_manager->mark_notifications('report_post', $post_id, $this->user->data['user_id']);

				if (!$report_id && $report['report_closed'])
				{
					trigger_error('REPORT_CLOSED');
				}

				$post_id = $report['post_id'];
				$report_id = $report['report_id'];

				$parse_post_flags = $report['reported_post_enable_bbcode'] ? OPTION_FLAG_BBCODE : 0;
				$parse_post_flags += $report['reported_post_enable_smilies'] ? OPTION_FLAG_SMILIES : 0;
				$parse_post_flags += $report['reported_post_enable_magic_url'] ? OPTION_FLAG_LINKS : 0;

				$post_info = phpbb_get_post_data([$post_id], 'm_report', true);

				if (empty($post_info))
				{
					trigger_error('NO_REPORT_SELECTED');
				}

				$post_info = $post_info[$post_id];

				$reason = ['title' => $report['reason_title'], 'description' => $report['reason_description']];
				if (isset($this->lang->get_lang_array()['report_reasons']['TITLE'][strtoupper($reason['title'])]) && isset($this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])]))
				{
					$reason['description'] = $this->lang->get_lang_array()['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])];
					$reason['title'] = $this->lang->get_lang_array()['report_reasons']['TITLE'][strtoupper($reason['title'])];
				}

				if (topic_review($post_info['topic_id'], $post_info['forum_id'], 'topic_review', 0, false))
				{
					$this->template->assign_vars([
						'S_TOPIC_REVIEW'	=> true,
						'S_BBCODE_ALLOWED'	=> $post_info['enable_bbcode'],
						'TOPIC_TITLE'		=> $post_info['topic_title'],
						'REPORTED_POST_ID'	=> $post_id,
					]);
				}

				$attachments = [];
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
				$message = generate_text_for_display(
					$report['reported_post_text'],
					$report['reported_post_uid'],
					$report['reported_post_bitfield'],
					$parse_post_flags,
					false
				);

				$report['report_text'] = make_clickable(bbcode_nl2br($report['report_text']));

				if ($post_info['post_attachment'] && $this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $post_info['forum_id']))
				{
					$sql = 'SELECT *
						FROM ' . $this->tables['attachments'] . '
						WHERE post_msg_id = ' . $post_id . '
							AND in_message = 0
							AND filetime <= ' . (int) $report['report_time'] . '
						ORDER BY filetime DESC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$attachments[] = $row;
					}
					$this->db->sql_freeresult($result);

					if (!empty($attachments))
					{
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

				// parse signature
				$parse_flags = ($post_info['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
				$post_info['user_sig'] = generate_text_for_display($post_info['user_sig'], $post_info['user_sig_bbcode_uid'], $post_info['user_sig_bbcode_bitfield'], $parse_flags, true);

				$topic_id = (int) $post_info['topic_id'];

				// So it can be sent through the event below.
				$report_template = [
					'S_MCP_REPORT'			=> true,
					'S_CLOSE_ACTION'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'S_CAN_VIEWIP'			=> $this->auth->acl_get('m_info', $post_info['forum_id']),
					'S_POST_REPORTED'		=> $post_info['post_reported'],
					'S_POST_UNAPPROVED'		=> $post_info['post_visibility'] == ITEM_UNAPPROVED || $post_info['post_visibility'] == ITEM_REAPPROVE,
					'S_POST_LOCKED'			=> $post_info['post_edit_locked'],
					'S_REPORT_CLOSED'		=> $report['report_closed'],
					'S_USER_NOTES'			=> true,

					'U_EDIT'				=> $this->auth->acl_get('m_edit', $post_info['forum_id']) ? append_sid("{$this->root_path}posting.$this->php_ext", "mode=edit&amp;f={$post_info['forum_id']}&amp;p={$post_info['post_id']}") : '',
					'U_MCP_APPROVE'			=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORT'			=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $post_info['forum_id'] . '&amp;p=' . $post_id),
					'U_MCP_REPORTER_NOTES'	=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $report['user_id']),
					'U_MCP_USER_NOTES'		=> append_sid("{$this->root_path}mcp.$this->php_ext", 'i=notes&amp;mode=user_notes&amp;u=' . $post_info['user_id']),
					'U_MCP_WARN_REPORTER'	=> $this->auth->acl_get('m_warn') ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_user&amp;u=' . $report['user_id']) : '',
					'U_MCP_WARN_USER'		=> $this->auth->acl_get('m_warn') ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=warn&amp;mode=warn_user&amp;u=' . $post_info['user_id']) : '',
					'U_VIEW_FORUM'			=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $post_info['forum_id']),
					'U_VIEW_POST'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $post_info['forum_id'] . '&amp;p=' . $post_info['post_id'] . '#p' . $post_info['post_id']),
					'U_VIEW_TOPIC'			=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']),

					'EDIT_IMG'				=> $this->user->img('icon_post_edit', $this->lang->lang('EDIT_POST')),
					'MINI_POST_IMG'			=> $post_unread ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),
					'UNAPPROVED_IMG'		=> $this->user->img('icon_topic_unapproved', $this->lang->lang('POST_UNAPPROVED')),

					'RETURN_REPORTS'			=> $this->lang->lang('RETURN_REPORTS', '<a href="' . append_sid("{$this->root_path}mcp.$this->php_ext", 'i=reports' . ($post_info['post_reported'] ? '&amp;mode=reports' : '&amp;mode=reports_closed') . '&amp;start=' . $start . '&amp;f=' . $post_info['forum_id']) . '">', '</a>'),
					'REPORTED_IMG'				=> $this->user->img('icon_topic_reported', $this->lang->lang('POST_REPORTED')),
					'REPORT_DATE'				=> $this->user->format_date($report['report_time']),
					'REPORT_ID'					=> $report_id,
					'REPORT_REASON_TITLE'		=> $reason['title'],
					'REPORT_REASON_DESCRIPTION'	=> $reason['description'],
					'REPORT_TEXT'				=> $report['report_text'],

					'POST_AUTHOR_FULL'		=> get_username_string('full', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'POST_AUTHOR'			=> get_username_string('username', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),
					'U_POST_AUTHOR'			=> get_username_string('profile', $post_info['user_id'], $post_info['username'], $post_info['user_colour'], $post_info['post_username']),

					'REPORTER_FULL'				=> get_username_string('full', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_COLOUR'			=> get_username_string('colour', $report['user_id'], $report['username'], $report['user_colour']),
					'REPORTER_NAME'				=> get_username_string('username', $report['user_id'], $report['username'], $report['user_colour']),
					'U_VIEW_REPORTER_PROFILE'	=> get_username_string('profile', $report['user_id'], $report['username'], $report['user_colour']),

					'POST_PREVIEW'			=> $message,
					'POST_SUBJECT'			=> $post_info['post_subject'] ? $post_info['post_subject'] : $this->lang->lang('NO_SUBJECT'),
					'POST_DATE'				=> $this->user->format_date($post_info['post_time']),
					'POST_IP'				=> $post_info['poster_ip'],
					'POST_IPADDR'			=> ($this->auth->acl_get('m_info', $post_info['forum_id']) && $this->request->variable('lookup', '')) ? @gethostbyaddr($post_info['poster_ip']) : '',
					'POST_ID'				=> $post_info['post_id'],
					'SIGNATURE'				=> $post_info['user_sig'],

					'U_LOOKUP_IP'			=> $this->auth->acl_get('m_info', $post_info['forum_id']) ? $this->u_action . '&amp;r=' . $report_id . '&amp;p=' . $post_id . '&amp;f=' . $forum_id . '&amp;lookup=' . $post_info['poster_ip'] . '#ip' : '',
				];

				/**
				 * Event to add/modify MCP report details template data.
				 *
				 * @event core.mcp_report_template_data
				 * @var int		forum_id					The forum_id, the number in the f GET parameter
				 * @var int		topic_id					The topic_id of the report being viewed
				 * @var int		post_id						The post_id of the report being viewed (if 0, it is meaningless)
				 * @var int		report_id					The report_id of the report being viewed
				 * @var array	report						Array with the report data
				 * @var	array	report_template				Array with the report template data
				 * @var array	post_info					Array with the reported post data
				 * @since 3.2.5-RC1
				 */
				$vars = [
					'forum_id',
					'topic_id',
					'post_id',
					'report_id',
					'report',
					'report_template',
					'post_info',
				];
				extract($this->dispatcher->trigger_event('core.mcp_report_template_data', compact($vars)));

				$this->template->assign_vars($report_template);

				$this->tpl_name = 'mcp_post';
			break;

			case 'reports':
			case 'reports_closed':
				$topic_id = $this->request->variable('t', 0);

				$topic_info = [];
				$forum_info = [];
				$forum_list_reports = get_forum_list('m_report', false, true);
				$forum_list_read = array_flip(get_forum_list('f_read', true, true)); // Flipped so we can isset() the forum IDs

				// Remove forums we cannot read
				foreach ($forum_list_reports as $k => $forum_data)
				{
					if (!isset($forum_list_read[$forum_data['forum_id']]))
					{
						unset($forum_list_reports[$k]);
					}
				}
				unset($forum_list_read);

				if ($topic_id)
				{
					$topic_info = phpbb_get_topic_data([$topic_id]);

					if (empty($topic_info))
					{
						trigger_error('TOPIC_NOT_EXIST');
					}

					if ($forum_id != $topic_info[$topic_id]['forum_id'])
					{
						$topic_id = 0;
					}
					else
					{
						$topic_info = $topic_info[$topic_id];
						$forum_id = (int) $topic_info['forum_id'];
					}
				}

				$forum_list = [];

				if (!$forum_id)
				{
					foreach ($forum_list_reports as $row)
					{
						$forum_list[] = $row['forum_id'];
					}

					if (empty($forum_list))
					{
						trigger_error('NOT_MODERATOR');
					}

					$sql = 'SELECT SUM(forum_topics_approved) as sum_forum_topics
						FROM ' . $this->tables['forums'] . '
						WHERE ' . $this->db->sql_in_set('forum_id', $forum_list);
					$result = $this->db->sql_query($sql);
					$forum_info['forum_topics_approved'] = (int) $this->db->sql_fetchfield('sum_forum_topics');
					$this->db->sql_freeresult($result);
				}
				else
				{
					$forum_info = phpbb_get_forum_data([$forum_id], 'm_report');

					if (empty($forum_info))
					{
						trigger_error('NOT_MODERATOR');
					}

					$forum_list = [$forum_id];
				}

				$forum_list[] = 0;
				$forum_data = [];

				$forum_options = '<option value="0"' . ($forum_id == 0 ? ' selected="selected"' : '') . '>' . $this->lang->lang('ALL_FORUMS') . '</option>';
				foreach ($forum_list_reports as $row)
				{
					$forum_options .= '<option value="' . $row['forum_id'] . '"' . ($forum_id == $row['forum_id'] ? ' selected="selected"' : '') . '>' . str_repeat('&nbsp; &nbsp;', $row['padding']) . $row['forum_name'] . '</option>';
					$forum_data[$row['forum_id']] = $row;
				}
				unset($forum_list_reports);

				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = [];
				phpbb_mcp_sorting($mode, $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id, $topic_id);

				$limit_time_sql = $sort_days ? 'AND r.report_time >= ' . (time() - ($sort_days * 86400)) : '';

				if ($mode === 'reports')
				{
					$report_state = 'AND p.post_reported = 1 AND r.report_closed = 0';
				}
				else
				{
					$report_state = 'AND r.report_closed = 1';
				}

				$sql = 'SELECT r.report_id
					FROM ' . $this->tables['posts'] . ' p, 
						' . $this->tables['topics'] . ' t, 
						' . $this->tables['reports'] . ' r 
						' . ($sort_order_sql[0] === 'u' ? ', ' . $this->tables['users'] . ' u' : '') .
							($sort_order_sql[0] === 'r' ? ', ' . $this->tables['users'] . ' ru' : '') . '
					WHERE ' . $this->db->sql_in_set('p.forum_id', $forum_list) . "
						$report_state
						AND r.post_id = p.post_id
						" . ($sort_order_sql[0] === 'u' ? 'AND u.user_id = p.poster_id' : '') . '
						' . ($sort_order_sql[0] === 'r' ? 'AND ru.user_id = r.user_id' : '') . '
						' . ($topic_id ? 'AND p.topic_id = ' . (int) $topic_id : '') . "
						AND t.topic_id = p.topic_id
						AND r.pm_id = 0
						$limit_time_sql
					ORDER BY $sort_order_sql";

				/**
				* Alter sql query to get report id of all reports for requested forum and topic or just forum
				*
				* @event core.mcp_reports_get_reports_query_before
				* @var	string	sql						String with the query to be executed
				* @var	array	forum_list				List of forums that contain the posts
				* @var	int		topic_id				topic_id in the page request
				* @var	string	limit_time_sql			String with the SQL code to limit the time interval of the post (Note: May be empty string)
				* @var	string	sort_order_sql			String with the ORDER BY SQL code used in this query
				* @since 3.1.0-RC4
				*/
				$vars = [
					'sql',
					'forum_list',
					'topic_id',
					'limit_time_sql',
					'sort_order_sql',
				];
				extract($this->dispatcher->trigger_event('core.mcp_reports_get_reports_query_before', compact($vars)));

				$i = 0;
				$report_ids = [];

				$result = $this->db->sql_query_limit($sql, $this->config['topics_per_page'], $start);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$report_ids[] = (int) $row['report_id'];
					$row_num[(int) $row['report_id']] = $i++;
				}
				$this->db->sql_freeresult($result);

				if (!empty($report_ids))
				{
					$sql = 'SELECT t.forum_id, t.topic_id, t.topic_title, p.post_id, p.post_subject, p.post_username, p.poster_id, p.post_time, p.post_attachment, u.username, u.username_clean, u.user_colour, r.user_id as reporter_id, ru.username as reporter_name, ru.user_colour as reporter_colour, r.report_time, r.report_id
						FROM ' . $this->tables['reports'] . ' r, ' . $this->tables['posts'] . ' p, ' . $this->tables['topics'] . ' t, ' . $this->tables['users'] . ' u, ' . $this->tables['users'] . ' ru
						WHERE ' . $this->db->sql_in_set('r.report_id', $report_ids) . '
							AND t.topic_id = p.topic_id
							AND r.post_id = p.post_id
							AND u.user_id = p.poster_id
							AND ru.user_id = r.user_id
							AND r.pm_id = 0
						ORDER BY ' . $sort_order_sql;
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$this->template->assign_block_vars('postrow', [
							'U_VIEWFORUM'				=> append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . $row['forum_id']),
							'U_VIEWPOST'				=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;p=' . $row['post_id']) . '#p' . $row['post_id'],
							'U_VIEW_DETAILS'			=> append_sid("{$this->root_path}mcp.$this->php_ext", "i=reports&amp;start=$start&amp;mode=report_details&amp;f={$row['forum_id']}&amp;r={$row['report_id']}"),

							'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
							'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
							'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
							'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),

							'REPORTER_FULL'			=> get_username_string('full', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
							'REPORTER_COLOUR'		=> get_username_string('colour', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
							'REPORTER'				=> get_username_string('username', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),
							'U_REPORTER'			=> get_username_string('profile', $row['reporter_id'], $row['reporter_name'], $row['reporter_colour']),

							'FORUM_NAME'		=> $forum_data[$row['forum_id']]['forum_name'],
							'POST_ID'			=> $row['post_id'],
							'POST_SUBJECT'		=> $row['post_subject'] ? $row['post_subject'] : $this->lang->lang('NO_SUBJECT'),
							'POST_TIME'			=> $this->user->format_date($row['post_time']),
							'REPORT_ID'			=> $row['report_id'],
							'REPORT_TIME'		=> $this->user->format_date($row['report_time']),
							'TOPIC_TITLE'		=> $row['topic_title'],
							'ATTACH_ICON_IMG'	=> ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $row['forum_id']) && $row['post_attachment']) ? $this->user->img('icon_topic_attach', $this->lang->lang('TOTAL_ATTACHMENTS')) : '',
						]);
					}
					$this->db->sql_freeresult($result);
					unset($report_ids, $row);
				}

				$base_url = $this->u_action . "&amp;f=$forum_id&amp;t=$topic_id&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir";
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $this->config['topics_per_page'], $start);

				// Now display the page
				$this->template->assign_vars([
					'L_EXPLAIN'				=> ($mode === 'reports') ? $this->lang->lang('MCP_REPORTS_OPEN_EXPLAIN') : $this->lang->lang('MCP_REPORTS_CLOSED_EXPLAIN'),
					'L_TITLE'				=> ($mode === 'reports') ? $this->lang->lang('MCP_REPORTS_OPEN') : $this->lang->lang('MCP_REPORTS_CLOSED'),
					'L_ONLY_TOPIC'			=> $topic_id ? $this->lang->lang('ONLY_TOPIC', $topic_info['topic_title']) : '',

					'S_MCP_ACTION'			=> $this->u_action,
					'S_FORUM_OPTIONS'		=> $forum_options,
					'S_CLOSED'				=> $mode === 'reports_closed',

					'TOPIC_ID'				=> $topic_id,
					'TOTAL'					=> $total,
					'TOTAL_REPORTS'			=> $this->lang->lang('LIST_REPORTS', (int) $total),
				]);

				$this->tpl_name = 'mcp_reports';
			break;
		}
	}

	/**
	 * Handle closing (PM) reports.
	 *
	 * @param array		$report_id_list		The report identifiers
	 * @param string	$mode				The report mode
	 * @param string	$action				The report action
	 * @param bool		$pm					Whether or not it is a PM report
	 * @return void
	 */
	function close_report(array $report_id_list, $mode, $action, $pm = false)
	{
		$pm_where	= $pm ? ' AND r.post_id = 0 ' : ' AND r.pm_id = 0 ';
		$id_column	= $pm ? 'pm_id' : 'post_id';
		$module		= $pm ? 'pm_reports' : 'reports';
		$pm_prefix	= $pm ? 'PM_' : '';

		$post_id_list = [];

		$sql = "SELECT r.$id_column
			FROM " . $this->tables['reports'] . ' r
			WHERE ' . $this->db->sql_in_set('r.report_id', $report_id_list) . $pm_where;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_id_list[] = (int) $row[$id_column];
		}
		$this->db->sql_freeresult($result);
		$post_id_list = array_unique($post_id_list);

		if ($pm)
		{
			if (!$this->auth->acl_getf_global('m_report'))
			{
				send_status_line(403, 'Forbidden');
				trigger_error('NOT_AUTHORISED');
			}
		}
		else
		{
			if (!phpbb_check_ids($post_id_list, $this->tables['posts'], 'post_id', ['m_report']))
			{
				send_status_line(403, 'Forbidden');
				trigger_error('NOT_AUTHORISED');
			}
		}

		if ($action === 'delete' && strpos($this->user->data['session_page'], 'mode=report_details') !== false)
		{
			$redirect = $this->request->variable('redirect', build_url(['mode', 'r', 'quickmod']) . '&amp;mode=reports');
		}
		else if ($action === 'delete' && strpos($this->user->data['session_page'], 'mode=pm_report_details') !== false)
		{
			$redirect = $this->request->variable('redirect', build_url(['mode', 'r', 'quickmod']) . '&amp;mode=pm_reports');
		}
		else if ($action === 'close' && !$this->request->variable('r', 0))
		{
			$redirect = $this->request->variable('redirect', build_url(['mode', 'p', 'quickmod']) . '&amp;mode=' . $module);
		}
		else
		{
			$redirect = $this->request->variable('redirect', build_url(['quickmod']));
		}

		$success_msg = '';
		$forum_ids = [];
		$topic_ids = [];

		$s_hidden_fields = build_hidden_fields([
			'i'					=> $module,
			'mode'				=> $mode,
			'action'			=> $action,
			'redirect'			=> $redirect,
			'report_id_list'	=> $report_id_list,
		]);

		if (confirm_box(true))
		{
			$post_info = $pm ? phpbb_get_pm_data($post_id_list) : phpbb_get_post_data($post_id_list, 'm_report');

			$sql = "SELECT r.report_id, r.$id_column, r.report_closed, r.user_id, r.user_notify, u.username, u.username_clean, u.user_email, u.user_jabber, u.user_lang, u.user_notify_type
				FROM " . $this->tables['reports'] . ' r, 
					' . $this->tables['users'] . ' u
				WHERE ' . $this->db->sql_in_set('r.report_id', $report_id_list) . '
					' . ($action === 'close' ? 'AND r.report_closed = 0' : '') . '
					AND r.user_id = u.user_id' . $pm_where;
			$result = $this->db->sql_query($sql);

			$reports = $close_report_posts = $close_report_topics = $notify_reporters = $report_id_list = [];
			while ($report = $this->db->sql_fetchrow($result))
			{
				$reports[(int) $report['report_id']] = $report;
				$report_id_list[] = (int) $report['report_id'];

				if (!$report['report_closed'])
				{
					$close_report_posts[] = (int) $report[$id_column];

					if (!$pm)
					{
						$close_report_topics[] = (int) $post_info[(int) $report['post_id']]['topic_id'];
					}
				}

				if ($report['user_notify'] && !$report['report_closed'])
				{
					$notify_reporters[(int) $report['report_id']] = &$reports[$report['report_id']];
				}
			}
			$this->db->sql_freeresult($result);

			if (!empty($reports))
			{
				$close_report_posts = array_unique($close_report_posts);
				$close_report_topics = array_unique($close_report_topics);

				if (!$pm && !empty($close_report_posts))
				{
					// Get a list of topics that still contain reported posts
					$keep_report_topics = [];

					$sql = 'SELECT DISTINCT topic_id
						FROM ' . $this->tables['posts'] . '
						WHERE ' . $this->db->sql_in_set('topic_id', $close_report_topics) . '
							AND post_reported = 1
							AND ' . $this->db->sql_in_set('post_id', $close_report_posts, true);
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$keep_report_topics[] = (int) $row['topic_id'];
					}
					$this->db->sql_freeresult($result);

					$close_report_topics = array_diff($close_report_topics, $keep_report_topics);
					unset($keep_report_topics);
				}

				$this->db->sql_transaction('begin');

				if ($action === 'close')
				{
					$sql = 'UPDATE ' . $this->tables['reports'] . '
						SET report_closed = 1
						WHERE ' . $this->db->sql_in_set('report_id', $report_id_list);
				}
				else
				{
					$sql = 'DELETE FROM ' . $this->tables['reports'] . '
						WHERE ' . $this->db->sql_in_set('report_id', $report_id_list);
				}
				$this->db->sql_query($sql);

				if (!empty($close_report_posts))
				{
					if ($pm)
					{
						$sql = 'UPDATE ' . $this->tables['privmsgs'] . '
							SET message_reported = 0
							WHERE ' . $this->db->sql_in_set('msg_id', $close_report_posts);
						$this->db->sql_query($sql);

						if ($action === 'delete')
						{
							delete_pm(ANONYMOUS, $close_report_posts, PRIVMSGS_INBOX);
						}
					}
					else
					{
						$sql = 'UPDATE ' . $this->tables['posts'] . '
							SET post_reported = 0
							WHERE ' . $this->db->sql_in_set('post_id', $close_report_posts);
						$this->db->sql_query($sql);

						if (!empty($close_report_topics))
						{
							$sql = 'UPDATE ' . $this->tables['topics'] . '
								SET topic_reported = 0
								WHERE ' . $this->db->sql_in_set('topic_id', $close_report_topics) . '
									OR ' . $this->db->sql_in_set('topic_moved_id', $close_report_topics);
							$this->db->sql_query($sql);
						}
					}
				}

				$this->db->sql_transaction('commit');
			}
			unset($close_report_posts, $close_report_topics);

			foreach ($reports as $report)
			{
				if ($pm)
				{
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_PM_REPORT_' . strtoupper($action) . 'D', false, [
						'forum_id'	=> 0,
						'topic_id'	=> 0,
						$post_info[$report['pm_id']]['message_subject'],
					]);

					$this->notification_manager->delete_notifications('notification.type.report_pm', $report['pm_id']);
				}
				else
				{
					$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_REPORT_' . strtoupper($action) . 'D', false, [
						'forum_id'	=> (int) $post_info[$report['post_id']]['forum_id'],
						'topic_id'	=> (int) $post_info[$report['post_id']]['topic_id'],
						'post_id'	=> (int) $report['post_id'],
						$post_info[$report['post_id']]['post_subject'],
					]);

					$this->notification_manager->delete_notifications('notification.type.report_post', $report['post_id']);
				}
			}

			// Notify reporters
			if (!empty($notify_reporters))
			{
				foreach ($notify_reporters as $report_id => $reporter)
				{
					if ($reporter['user_id'] == ANONYMOUS)
					{
						continue;
					}

					$post_id = $reporter[$id_column];

					if ($pm)
					{
						$this->notification_manager->add_notifications('notification.type.report_pm_closed', array_merge($post_info[$post_id], [
							'reporter'			=> (int) $reporter['user_id'],
							'closer_id'			=> (int) $this->user->data['user_id'],
							'from_user_id'		=> (int) $post_info[$post_id]['author_id'],
						]));
					}
					else
					{
						$this->notification_manager->add_notifications('notification.type.report_post_closed', array_merge($post_info[$post_id], [
							'reporter'			=> (int) $reporter['user_id'],
							'closer_id'			=> (int) $this->user->data['user_id'],
						]));
					}
				}
			}

			if (!$pm)
			{
				foreach ($post_info as $post)
				{
					$forum_ids[(int) $post['forum_id']] = (int) $post['forum_id'];
					$topic_ids[(int) $post['topic_id']] = (int) $post['topic_id'];
				}
			}

			unset($notify_reporters, $post_info, $reports);

			$success_msg = count($report_id_list) === 1 ? "{$pm_prefix}REPORT_" . strtoupper($action) . 'D_SUCCESS' : "{$pm_prefix}REPORTS_" . strtoupper($action) . 'D_SUCCESS';
		}
		else
		{
			confirm_box(false, $this->lang->lang(strtoupper($action) . "_{$pm_prefix}REPORT" . (count($report_id_list) === 1 ? '' : 'S') . '_CONFIRM'), $s_hidden_fields);
		}

		$redirect = $this->request->variable('redirect', "index.$this->php_ext");
		$redirect = reapply_sid($redirect);

		if (!$success_msg)
		{
			redirect($redirect);
		}
		else
		{
			meta_refresh(3, $redirect);

			$return_forum = '';
			$return_topic = '';

			if (!$pm)
			{
				if (count($forum_ids) === 1)
				{
					$return_forum = $this->lang->lang('RETURN_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", 'f=' . current($forum_ids)) . '">', '</a>') . '<br /><br />';
				}

				if (count($topic_ids) === 1)
				{
					$return_topic = $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 't=' . current($topic_ids) . '&amp;f=' . current($forum_ids)) . '">', '</a>') . '<br /><br />';
				}
			}

			trigger_error($this->lang->lang($success_msg) . '<br /><br />' . $return_forum . $return_topic . $this->lang->lang('RETURN_PAGE', "<a href=\"$redirect\">", '</a>'));
		}
	}
}
