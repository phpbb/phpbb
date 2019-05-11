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

class topic
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\mcp\controller\forum */
	protected $mcp_forum;

	/** @var \phpbb\mcp\controller\queue */
	protected $mcp_queue;

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
	 * @param \phpbb\auth\auth					$auth				Auth object
	 * @param \phpbb\config\config				$config				Config object
	 * @param \phpbb\content_visibility			$content_visibility	Content visibility object
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\event\dispatcher			$dispatcher			Event dispatcher object
	 * @param \phpbb\language\language			$lang				Language object
	 * @param \phpbb\log\log					$log				Log object
	 * @param \phpbb\mcp\controller\forum		$mcp_forum			MCP Forum controller object
	 * @param \phpbb\mcp\controller\queue		$mcp_queue			MCP Queue controller object
	 * @param \phpbb\pagination					$pagination			Pagination object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template			$template			Template object
	 * @param \phpbb\user						$user				User object
	 * @param string							$root_path			phpBB root path
	 * @param string							$php_ext			php File extension
	 * @param array								$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\content_visibility $content_visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		forum $mcp_forum,
		queue $mcp_queue,
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
		$this->content_visibility	= $content_visibility;
		$this->db					= $db;
		$this->dispatcher			= $dispatcher;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->mcp_forum			= $mcp_forum;
		$this->mcp_queue			= $mcp_queue;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
		$this->tables				= $tables;
	}

	public function view($id, $mode, $action)
	{
		$this->lang->add_lang('viewtopic');

		$url = append_sid("{$this->root_path}mcp.$this->php_ext?" . phpbb_extra_url());

		$topic_id = $this->request->variable('t', 0);
		$topic_info = phpbb_get_topic_data([$topic_id], false, true);

		if (empty($topic_info))
		{
			trigger_error('TOPIC_NOT_EXIST');
		}

		$topic_info = $topic_info[$topic_id];

		// Set up some vars
		$icon_id		= $this->request->variable('icon', 0);
		$subject		= $this->request->variable('subject', '', true);
		$start			= $this->request->variable('start', 0);
		$sort_days_old	= $this->request->variable('st_old', 0);
		$forum_id		= $this->request->variable('f', 0);
		$to_topic_id	= $this->request->variable('to_topic_id', 0);
		$to_forum_id	= $this->request->variable('to_forum_id', 0);
		$sort			= $this->request->is_set_post('sort');
		$submitted_id_list	= $this->request->variable('post_ids', [0]);
		$checked_ids = $post_id_list = $this->request->variable('post_id_list', [0]);

		// Resync Topic?
		if ($action === 'resync')
		{
			$this->mcp_forum->resync_topics([$topic_id]);
		}

		// Split Topic?
		if ($action === 'split_all' || $action === 'split_beyond')
		{
			if (!$sort)
			{
				$this->split_topic($action, $topic_id, $to_forum_id, $subject);
			}

			$action = 'split';
		}

		// Merge Posts?
		if ($action === 'merge_posts')
		{
			if (!$sort)
			{
				$this->merge_posts($topic_id, $to_topic_id);
			}

			$action = 'merge';
		}

		if ($action === 'split' && !$subject)
		{
			$subject = $topic_info['topic_title'];
		}

		// Restore or approve posts?
		if (($action === 'restore' || $action === 'approve') && $this->auth->acl_get('m_approve', $topic_info['forum_id']))
		{
			include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);
			include_once($this->root_path . 'includes/functions_messenger.' . $this->php_ext);

			if (empty($post_id_list))
			{
				trigger_error('NO_POST_SELECTED');
			}

			if (!$sort)
			{
				$this->mcp_queue->approve_posts($action, $post_id_list, $id, $mode);
			}
		}

		// Jumpbox, sort selects and that kind of things
		make_jumpbox($url . "&amp;i=$id&amp;mode=forum_view", $topic_info['forum_id'], false, 'm_', true);
		$where_sql = ($action === 'reports') ? 'WHERE post_reported = 1 AND ' : 'WHERE';

		$sort_days = $total = 0;
		$sort_key = $sort_dir = '';
		$sort_by_sql = $sort_order_sql = [];
		phpbb_mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $topic_info['forum_id'], $topic_id, $where_sql);

		$limit_time_sql = ($sort_days) ? 'AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';

		if ($total === -1)
		{
			$total = $this->content_visibility->get_count('topic_posts', $topic_info, $topic_info['forum_id']);
		}

		$posts_per_page = max(0, $this->request->variable('posts_per_page', intval($this->config['posts_per_page'])));
		if ($posts_per_page == 0)
		{
			$posts_per_page = $total;
		}

		if ((!empty($sort_days_old) && $sort_days_old != $sort_days) || $total <= $posts_per_page)
		{
			$start = 0;
		}

		$start = $this->pagination->validate_start($start, $posts_per_page, $total);

		$rowset = $post_id_list = [];

		$sql = 'SELECT u.username, u.username_clean, u.user_colour, p.*
			FROM ' . $this->tables['posts'] . ' p,
				' . $this->tables['users'] . ' u
			WHERE ' . ($action === 'reports' ? 'p.post_reported = 1 AND ' : '') . '
				p.topic_id = ' . (int) $topic_id . '
				AND ' .	$this->content_visibility->get_visibility_sql('post', $topic_info['forum_id'], 'p.') . '
				AND p.poster_id = u.user_id 
				' . $limit_time_sql . '
			ORDER BY ' . $sort_order_sql;
		$result = $this->db->sql_query_limit($sql, $posts_per_page, $start);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[] = $row;
			$post_id_list[] = (int) $row['post_id'];
		}
		$this->db->sql_freeresult($result);

		// Get topic tracking info
		if ($this->config['load_db_lastread'])
		{
			$tmp_topic_data = [$topic_id => $topic_info];
			$topic_tracking_info = get_topic_tracking($topic_info['forum_id'], $topic_id, $tmp_topic_data, [$topic_info['forum_id'] => $topic_info['forum_mark_time']]);
			unset($tmp_topic_data);
		}
		else
		{
			$topic_tracking_info = get_complete_topic_tracking($topic_info['forum_id'], $topic_id);
		}

		$has_unapproved_posts = $has_deleted_posts = false;

		// Grab extensions
		$attachments = [];
		if ($topic_info['topic_attachment'] && !empty($post_id_list))
		{
			// Get attachments...
			if ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $topic_info['forum_id']))
			{
				$sql = 'SELECT *
					FROM ' . $this->tables['attachments'] . '
					WHERE ' . $this->db->sql_in_set('post_msg_id', $post_id_list) . '
						AND in_message = 0
					ORDER BY filetime DESC, post_msg_id ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$attachments[(int) $row['post_msg_id']][] = $row;
				}
				$this->db->sql_freeresult($result);
			}
		}

		/**
		 * Event to modify the post data for the MCP topic review before assigning the posts
		 *
		 * @event core.mcp_topic_modify_post_data
		 * @var	int		id				ID of the tab we are displaying
		 * @var	string	mode			Mode of the MCP page we are displaying
		 * @var	int		forum_id		The forum ID we are currently in
		 * @var	int		topic_id		The topic ID we are currently reviewing
		 * @var	array	post_id_list	Array with post ids we are going to display
		 * @var	array	rowset			Array with the posts data
		 * @var	array	attachments		List of attachments post_id => array of attachments
		 * @since 3.1.7-RC1
		 */
		$vars = [
			'attachments',
			'forum_id',
			'id',
			'mode',
			'post_id_list',
			'rowset',
			'topic_id',
		];
		extract($this->dispatcher->trigger_event('core.mcp_topic_modify_post_data', compact($vars)));

		foreach ($rowset as $i => $row)
		{
			$message = $row['post_text'];
			$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : $topic_info['topic_title'];

			$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
			$message = generate_text_for_display($message, $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, false);

			if (!empty($attachments[$row['post_id']]))
			{
				$update_count = [];
				parse_attachments($topic_info['forum_id'], $message, $attachments[$row['post_id']], $update_count);
			}

			if ($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE)
			{
				$has_unapproved_posts = true;
			}

			if ($row['post_visibility'] == ITEM_DELETED)
			{
				$has_deleted_posts = true;
			}

			$post_unread = (isset($topic_tracking_info[$topic_id]) && $row['post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

			$post_row = [
				'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
				'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
				'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
				'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),

				'MESSAGE'				=> $message,
				'POST_ID'				=> (int) $row['post_id'],
				'POST_DATE'				=> $this->user->format_date($row['post_time']),
				'POST_SUBJECT'			=> $post_subject,
				'RETURN_TOPIC'			=> $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 't=' . $topic_id) . '">', '</a>'),

				'MINI_POST_IMG'			=> $post_unread ? $this->user->img('icon_post_target_unread', 'UNREAD_POST') : $this->user->img('icon_post_target', 'POST'),

				'S_POST_REPORTED'		=> (bool) ($row['post_reported'] && $this->auth->acl_get('m_report', $topic_info['forum_id'])),
				'S_POST_UNAPPROVED'		=> (bool) (($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) && $this->auth->acl_get('m_approve', $topic_info['forum_id'])),
				'S_POST_DELETED'		=> (bool) ($row['post_visibility'] == ITEM_DELETED && $this->auth->acl_get('m_approve', $topic_info['forum_id'])),
				'S_CHECKED'				=> (bool) (($submitted_id_list && !in_array(intval($row['post_id']), $submitted_id_list)) || in_array(intval($row['post_id']), $checked_ids)),
				'S_HAS_ATTACHMENTS'		=> (bool) !empty($attachments[$row['post_id']]),

				'U_POST_DETAILS'		=> "$url&amp;i=$id&amp;p={$row['post_id']}&amp;mode=post_details" . ($forum_id ? "&amp;f=$forum_id" : ''),
				'U_MCP_APPROVE'			=> $this->auth->acl_get('m_approve', $topic_info['forum_id']) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=queue&amp;mode=approve_details&amp;f=' . $topic_info['forum_id'] . '&amp;p=' . $row['post_id']) : '',
				'U_MCP_REPORT'			=> $this->auth->acl_get('m_report', $topic_info['forum_id']) ? append_sid("{$this->root_path}mcp.$this->php_ext", 'i=reports&amp;mode=report_details&amp;f=' . $topic_info['forum_id'] . '&amp;p=' . $row['post_id']) : '',
			];

			/**
			 * Event to modify the template data block for topic reviews in the MCP
			 *
			 * @event core.mcp_topic_review_modify_row
			 * @var	int		id					ID of the tab we are displaying
			 * @var	string	mode				Mode of the MCP page we are displaying
			 * @var	int		start				Start item of this page
			 * @var	int		total				Total posts count
			 * @var	int		forum_id			The forum ID we are currently in
			 * @var	int		topic_id			The topic ID we are currently reviewing
			 * @var	array	topic_info			Array with topic data
			 * @var	array	row					Array with original post and user data
			 * @var	array	post_row			Template block array of the current post
			 * @var	int		current_row_number	Number of the post on this page
			 * @since 3.1.4-RC1
			 */
			$vars = [
				'id',
				'mode',
				'topic_id',
				'forum_id',
				'start',
				'current_row_number',
				'post_row',
				'row',
				'topic_info',
				'total',
			];
			extract($this->dispatcher->trigger_event('core.mcp_topic_review_modify_row', compact($vars)));

			$this->template->assign_block_vars('postrow', $post_row);

			// Display not already displayed Attachments for this post, we already parsed them. ;)
			if (!empty($attachments[$row['post_id']]))
			{
				foreach ($attachments[$row['post_id']] as $attachment)
				{
					$this->template->assign_block_vars('postrow.attachment', ['DISPLAY_ATTACHMENT' => $attachment]);
				}
			}

			unset($rowset[$i]);
		}

		// Display topic icons for split topic
		$s_topic_icons = false;
		$to_topic_info = [];

		if ($this->auth->acl_gets('m_split', 'm_merge', (int) $topic_info['forum_id']))
		{
			include_once($this->root_path . 'includes/functions_posting.' . $this->php_ext);
			$s_topic_icons = posting_gen_topic_icons('', $icon_id);

			// Has the user selected a topic for merge?
			if ($to_topic_id)
			{
				$to_topic_info = phpbb_get_topic_data([$to_topic_id], 'm_merge');

				if (empty($to_topic_info))
				{
					$to_topic_id = 0;
				}
				else
				{
					$to_topic_info = $to_topic_info[$to_topic_id];

					if (!$to_topic_info['enable_icons'] || $this->auth->acl_get('!f_icons', $topic_info['forum_id']))
					{
						$s_topic_icons = false;
					}
				}
			}
		}

		$s_hidden_fields = build_hidden_fields([
			'st_old'	=> $sort_days,
			'post_ids'	=> $post_id_list,
		]);

		$base_url = append_sid("{$this->root_path}mcp.$this->php_ext", "i=$id&amp;t={$topic_info['topic_id']}&amp;mode=$mode&amp;action=$action&amp;to_topic_id=$to_topic_id&amp;posts_per_page=$posts_per_page&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir");
		if ($posts_per_page)
		{
			$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $posts_per_page, $start);
		}

		$this->template->assign_vars([
			'TOPIC_TITLE'		=> $topic_info['topic_title'],
			'U_VIEW_TOPIC'		=> append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $topic_info['forum_id'] . '&amp;t=' . $topic_info['topic_id']),

			'TO_TOPIC_ID'		=> $to_topic_id,
			'TO_TOPIC_INFO'		=> $to_topic_id ? $this->lang->lang('YOU_SELECTED_TOPIC', $to_topic_id, '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $to_topic_info['forum_id'] . '&amp;t=' . $to_topic_id) . '">' . $to_topic_info['topic_title'] . '</a>') : '',

			'SPLIT_SUBJECT'		=> $subject,
			'POSTS_PER_PAGE'	=> $posts_per_page,
			'ACTION'			=> $action,

			'REPORTED_IMG'		=> $this->user->img('icon_topic_reported', 'POST_REPORTED'),
			'UNAPPROVED_IMG'	=> $this->user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
			'DELETED_IMG'		=> $this->user->img('icon_topic_deleted', 'POST_DELETED_RESTORE'),
			'INFO_IMG'			=> $this->user->img('icon_post_info', 'VIEW_INFO'),

			'S_MCP_ACTION'		=> "$url&amp;i=$id&amp;mode=$mode&amp;action=$action&amp;start=$start",
			'S_FORUM_SELECT'	=> $to_forum_id ? make_forum_select($to_forum_id, false, false, true, true, true) : make_forum_select($topic_info['forum_id'], false, false, true, true, true),

			'S_CAN_SPLIT'		=> (bool) $this->auth->acl_get('m_split', $topic_info['forum_id']),
			'S_CAN_MERGE'		=> (bool) $this->auth->acl_get('m_merge', $topic_info['forum_id']),
			'S_CAN_DELETE'		=> (bool) $this->auth->acl_get('m_delete', $topic_info['forum_id']),
			'S_CAN_APPROVE'		=> (bool) ($this->auth->acl_get('m_approve', $topic_info['forum_id']) && $has_unapproved_posts),
			'S_CAN_RESTORE'		=> (bool) ($this->auth->acl_get('m_approve', $topic_info['forum_id']) && $has_deleted_posts),
			'S_CAN_LOCK'		=> (bool) $this->auth->acl_get('m_lock', $topic_info['forum_id']),
			'S_CAN_REPORT'		=> (bool) $this->auth->acl_get('m_report', $topic_info['forum_id']),
			'S_CAN_SYNC'		=> (bool) $this->auth->acl_get('m_', $topic_info['forum_id']),
			'S_REPORT_VIEW'		=> $action === 'reports',
			'S_MERGE_VIEW'		=> $action === 'merge',
			'S_SPLIT_VIEW'		=> $action === 'split',

			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,

			'S_SHOW_TOPIC_ICONS'	=> $s_topic_icons,
			'S_TOPIC_ICON'			=> $icon_id,

			'U_SELECT_TOPIC'	=> "$url&amp;i=$id&amp;mode=forum_view&amp;action=merge_select" . ($forum_id ? "&amp;f=$forum_id" : ''),

			'RETURN_TOPIC'		=> $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", "f={$topic_info['forum_id']}&amp;t={$topic_info['topic_id']}&amp;start=$start") . '">', '</a>'),
			'RETURN_FORUM'		=> $this->lang->lang('RETURN_FORUM', '<a href="' . append_sid("{$this->root_path}viewforum.$this->php_ext", "f={$topic_info['forum_id']}&amp;start=$start") . '">', '</a>'),

			'TOTAL_POSTS'		=> $this->lang->lang('VIEW_TOPIC_POSTS', (int) $total),
		]);
	}

	/**
	 * Split topic.
	 *
	 * @param string	$action			The action (split_all|split_beyond)
	 * @param int		$topic_id		The topic identifier
	 * @param int		$to_forum_id	The forum identifier
	 * @param string	$subject		The new topic subject
	 * @return void
	 */
	protected function split_topic($action, $topic_id, $to_forum_id, $subject)
	{
		$post_id_list	= $this->request->variable('post_id_list', [0]);
		$forum_id		= $this->request->variable('forum_id', 0);
		$start			= $this->request->variable('start', 0);

		if (empty($post_id_list))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_POST_SELECTED'));
			return;
		}

		if (!phpbb_check_ids($post_id_list, $this->tables['posts'], 'post_id', ['m_split']))
		{
			return;
		}

		$post_id = $post_id_list[0];
		$post_info = phpbb_get_post_data([$post_id]);

		if (empty($post_info))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_POST_SELECTED'));
			return;
		}

		$post_info = $post_info[$post_id];
		$subject = trim($subject);

		// Make some tests
		if (!$subject)
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('EMPTY_SUBJECT'));
			return;
		}

		if ($to_forum_id <= 0)
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_DESTINATION_FORUM'));
			return;
		}

		$forum_info = phpbb_get_forum_data([$to_forum_id], 'f_post');

		if (empty($forum_info))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('USER_CANNOT_POST'));
			return;
		}

		$forum_info = $forum_info[$to_forum_id];

		if ($forum_info['forum_type'] != FORUM_POST)
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('FORUM_NOT_POSTABLE'));
			return;
		}

		$redirect = $this->request->variable('redirect', build_url(['quickmod']));

		$s_hidden_fields = build_hidden_fields([
			'i'				=> 'main',
			'f'				=> $forum_id,
			't'				=> $topic_id,
			'mode'			=> 'topic_view',
			'start'			=> $start,
			'action'		=> $action,
			'subject'		=> $subject,
			'redirect'		=> $redirect,
			'to_forum_id'	=> $to_forum_id,
			'post_id_list'	=> $post_id_list,
			'icon'			=> $this->request->variable('icon', 0),
		]);

		if (confirm_box(true))
		{
			if ($action === 'split_beyond')
			{
				$sort_days = $total = 0;
				$sort_key = $sort_dir = '';
				$sort_by_sql = $sort_order_sql = [];
				phpbb_mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id, $topic_id);

				$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

				$store = false;
				$post_id_list = [];

				if ($sort_order_sql[0] === 'u')
				{
					$sql = 'SELECT p.post_id, p.forum_id, p.post_visibility
						FROM ' . $this->tables['posts'] . ' p, 
							' . $this->tables['users'] . ' u
						WHERE p.topic_id = ' . (int) $topic_id . "
							AND p.poster_id = u.user_id
							$limit_time_sql
						ORDER BY $sort_order_sql";
				}
				else
				{
					$sql = 'SELECT p.post_id, p.forum_id, p.post_visibility
						FROM ' . $this->tables['posts'] . ' p
						WHERE p.topic_id = ' . (int) $topic_id . "
							$limit_time_sql
						ORDER BY $sort_order_sql";
				}
				$result = $this->db->sql_query_limit($sql, 0, $start);
				while ($row = $this->db->sql_fetchrow($result))
				{
					// If split from selected post (split_beyond), we split the unapproved items too.
					if (($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) && !$this->auth->acl_get('m_approve', $row['forum_id']))
					{
//					continue;		@todo why is this commented out?
					}

					// Start to store post_ids as soon as we see the first post that was selected
					if ($row['post_id'] == $post_id)
					{
						$store = true;
					}

					if ($store)
					{
						$post_id_list[] = (int) $row['post_id'];
					}
				}
				$this->db->sql_freeresult($result);
			}

			if (empty($post_id_list))
			{
				trigger_error('NO_POST_SELECTED');
			}

			$icon_id = $this->request->variable('icon', 0);

			$sql_ary = [
				'icon_id'			=> (int) $icon_id,
				'forum_id'			=> (int) $to_forum_id,
				'topic_title'		=> $subject,
				'topic_visibility'	=> 1,
			];

			$sql = 'INSERT INTO ' . $this->tables['topics'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->db->sql_query($sql);

			$to_topic_id = $this->db->sql_nextid();
			move_posts($post_id_list, $to_topic_id);

			$topic_info = phpbb_get_topic_data([$topic_id]);
			$topic_info = $topic_info[$topic_id];

			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_SPLIT_DESTINATION', false, [
				'forum_id' => (int) $to_forum_id,
				'topic_id' => (int) $to_topic_id,
				$subject,
			]);
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_SPLIT_SOURCE', false, [
				'forum_id' => (int) $forum_id,
				'topic_id' => (int) $topic_id,
				$topic_info['topic_title'],
			]);

			// Change topic title of first post
			$sql = 'UPDATE ' . $this->tables['posts'] . "
				SET post_subject = '" . $this->db->sql_escape($subject) . "'
				WHERE post_id = " . (int) $post_id_list[0];
			$this->db->sql_query($sql);

			// Grab data for first post in split topic
			$sql_array = [
				'SELECT'	=> 'p.post_id, p.forum_id, p.poster_id, p.post_text, f.enable_indexing',
				'FROM'		=> [$this->tables['posts'] => 'p'],
				'LEFT_JOIN'	=> [
					[
						'FROM'	=> [$this->tables['forums'] => 'f'],
						'ON'	=> 'p.forum_id = f.forum_id',
					],
				],
				'WHERE'		=> 'post_id = ' . (int) $post_id_list[0],
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			$first_post_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			// Index first post as if it were edited
			if ($first_post_data['enable_indexing'])
			{
				// Select the search method and do some additional checks to ensure it can actually be utilised
				$search_type = $this->config['search_type'];

				if (!class_exists($search_type))
				{
					trigger_error('NO_SUCH_SEARCH_MODULE');
				}

				$error = false;

				/** @var \phpbb\search\fulltext_mysql $search		@todo Search interface?? */
				$search = new $search_type($error, $this->root_path, $this->php_ext, $this->auth, $this->config, $this->db, $this->user, $this->dispatcher);

				if ($error)
				{
					trigger_error($error);
				}

				$search->index('edit', $first_post_data['post_id'], $first_post_data['post_text'], $subject, $first_post_data['poster_id'], $first_post_data['forum_id']);
			}

			// Copy topic subscriptions to new topic
			$sql_ary = [];

			$sql = 'SELECT user_id, notify_status
				FROM ' . $this->tables['topics_watch'] . '
				WHERE topic_id = ' . (int) $topic_id;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_ary[] = [
					'topic_id'		=> (int) $to_topic_id,
					'user_id'		=> (int) $row['user_id'],
					'notify_status'	=> (int) $row['notify_status'],
				];
			}
			$this->db->sql_freeresult($result);

			if (!empty($sql_ary))
			{
				$this->db->sql_multi_insert($this->tables['topics_watch'], $sql_ary);
			}

			// Copy bookmarks to new topic
			$sql_ary = [];

			$sql = 'SELECT user_id
				FROM ' . $this->tables['bookmarks'] . '
				WHERE topic_id = ' . (int) $topic_id;
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$sql_ary[] = [
					'topic_id'		=> (int) $to_topic_id,
					'user_id'		=> (int) $row['user_id'],
				];
			}
			$this->db->sql_freeresult($result);

			if (!empty($sql_ary))
			{
				$this->db->sql_multi_insert($this->tables['bookmarks'], $sql_ary);
			}

			$success_msg = 'TOPIC_SPLIT_SUCCESS';

			// Update forum statistics
			$this->config->increment('num_topics', 1, false);

			// Link back to both topics
			$return_link = $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']) . '">', '</a>') . '<br /><br />' . $this->lang->lang('RETURN_NEW_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $to_forum_id . '&amp;t=' . $to_topic_id) . '">', '</a>');
			$redirect = $this->request->variable('redirect', "{$this->root_path}viewtopic.$this->php_ext?f=$to_forum_id&amp;t=$to_topic_id");
			$redirect = reapply_sid($redirect);

			meta_refresh(3, $redirect);
			trigger_error($this->lang->lang($success_msg) . '<br /><br />' . $return_link);
		}
		else
		{
			confirm_box(false, ($action === 'split_all') ? 'SPLIT_TOPIC_ALL' : 'SPLIT_TOPIC_BEYOND', $s_hidden_fields);
		}
	}

	/**
	 * Merge selected posts into selected topic.
	 *
	 * @param int		$topic_id		The "from" topic identifier
	 * @param int		$to_topic_id	The "to" topic identifier
	 * @return void
	 */
	protected function merge_posts($topic_id, $to_topic_id)
	{
		if (!$to_topic_id)
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_FINAL_TOPIC_SELECTED'));
			return;
		}

		$sync_topics = [$topic_id, $to_topic_id];

		$topic_data = phpbb_get_topic_data($sync_topics, 'm_merge');

		if (empty($topic_data) || empty($topic_data[$to_topic_id]))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_FINAL_TOPIC_SELECTED'));
			return;
		}

		$sync_forums = [];
		foreach ($topic_data as $data)
		{
			$sync_forums[(int) $data['forum_id']] = (int) $data['forum_id'];
		}

		$topic_data = $topic_data[$to_topic_id];

		$post_id_list	= $this->request->variable('post_id_list', [0]);
		$start			= $this->request->variable('start', 0);

		if (empty($post_id_list))
		{
			$this->template->assign_var('MESSAGE', $this->lang->lang('NO_POST_SELECTED'));
			return;
		}

		if (!phpbb_check_ids($post_id_list, $this->tables['posts'], 'post_id', ['m_merge']))
		{
			return;
		}

		$redirect = $this->request->variable('redirect', build_url(['quickmod']));

		$s_hidden_fields = build_hidden_fields([
			'i'				=> 'main',
			't'				=> $topic_id,
			'mode'			=> 'topic_view',
			'action'		=> 'merge_posts',
			'start'			=> $start,
			'redirect'		=> $redirect,
			'to_topic_id'	=> $to_topic_id,
			'post_id_list'	=> $post_id_list,
		]);

		$return_link = '';

		if (confirm_box(true))
		{
			$to_forum_id = (int) $topic_data['forum_id'];

			move_posts($post_id_list, $to_topic_id, false);

			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MERGE', false, [
				'forum_id' => (int) $to_forum_id,
				'topic_id' => (int) $to_topic_id,
				$topic_data['topic_title'],
			]);

			// Message and return links
			$success_msg = 'POSTS_MERGED_SUCCESS';

			// Does the original topic still exist? If yes, link back to it
			$sql = 'SELECT forum_id
				FROM ' . $this->tables['posts'] . '
				WHERE topic_id = ' . (int) $topic_id;
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				$return_link .= $this->lang->lang('RETURN_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $row['forum_id'] . '&amp;t=' . $topic_id) . '">', '</a>');
			}
			else
			{
				if (!function_exists('phpbb_update_rows_avoiding_duplicates_notify_status'))
				{
					include($this->root_path . 'includes/functions_database_helper.' . $this->php_ext);
				}

				// If the topic no longer exist, we will update the topic watch table.
				phpbb_update_rows_avoiding_duplicates_notify_status($this->db, $this->tables['topics_watch'], 'topic_id', [$topic_id], $to_topic_id);

				// If the topic no longer exist, we will update the bookmarks table.
				phpbb_update_rows_avoiding_duplicates($this->db, $this->tables['bookmarks'], 'topic_id', [$topic_id], $to_topic_id);
			}

			// Re-sync the topics and forums because the auto-sync was deactivated in the call of move_posts()
			sync('topic_reported', 'topic_id', $sync_topics);
			sync('topic_attachment', 'topic_id', $sync_topics);
			sync('topic', 'topic_id', $sync_topics, true);
			sync('forum', 'forum_id', $sync_forums, true, true);

			// Link to the new topic
			$return_link .= ($return_link ? '<br /><br />' : '') . $this->lang->lang('RETURN_NEW_TOPIC', '<a href="' . append_sid("{$this->root_path}viewtopic.$this->php_ext", 'f=' . $to_forum_id . '&amp;t=' . $to_topic_id) . '">', '</a>');
			$redirect = $this->request->variable('redirect', "{$this->root_path}viewtopic.$this->php_ext?f=$to_forum_id&amp;t=$to_topic_id");
			$redirect = reapply_sid($redirect);

			/**
			 * Perform additional actions after merging posts.
			 *
			 * @event core.mcp_topics_merge_posts_after
			 * @var	int		topic_id		The topic ID from which posts are being moved
			 * @var	int		to_topic_id		The topic ID to which posts are being moved
			 * @since 3.1.11-RC1
			 */
			$vars = [
				'topic_id',
				'to_topic_id',
			];
			extract($this->dispatcher->trigger_event('core.mcp_topics_merge_posts_after', compact($vars)));

			meta_refresh(3, $redirect);
			trigger_error($this->lang->lang($success_msg) . '<br /><br />' . $return_link);
		}
		else
		{
			confirm_box(false, 'MERGE_POSTS', $s_hidden_fields);
		}
	}
}
