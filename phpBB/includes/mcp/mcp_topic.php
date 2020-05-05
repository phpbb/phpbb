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

/**
* View topic in MCP
*/
function mcp_topic_view($id, $mode, $action)
{
	global $phpEx, $phpbb_root_path, $config, $request;
	global $template, $db, $user, $auth, $phpbb_container, $phpbb_dispatcher;

	$url = append_sid("{$phpbb_root_path}mcp.$phpEx?" . phpbb_extra_url());

	/* @var $pagination \phpbb\pagination */
	$pagination = $phpbb_container->get('pagination');
	$user->add_lang('viewtopic');

	$topic_id = $request->variable('t', 0);
	$topic_info = phpbb_get_topic_data(array($topic_id), false, true);

	if (!count($topic_info))
	{
		trigger_error('TOPIC_NOT_EXIST');
	}

	$topic_info = $topic_info[$topic_id];

	// Set up some vars
	$icon_id		= $request->variable('icon', 0);
	$subject		= $request->variable('subject', '', true);
	$start			= $request->variable('start', 0);
	$sort_days_old	= $request->variable('st_old', 0);
	$forum_id		= $request->variable('f', 0);
	$to_topic_id	= $request->variable('to_topic_id', 0);
	$to_forum_id	= $request->variable('to_forum_id', 0);
	$sort			= isset($_POST['sort']) ? true : false;
	$submitted_id_list	= $request->variable('post_ids', array(0));
	$checked_ids = $post_id_list = $request->variable('post_id_list', array(0));

	// Resync Topic?
	if ($action == 'resync')
	{
		if (!function_exists('mcp_resync_topics'))
		{
			include($phpbb_root_path . 'includes/mcp/mcp_forum.' . $phpEx);
		}
		mcp_resync_topics(array($topic_id));
	}

	// Split Topic?
	if ($action == 'split_all' || $action == 'split_beyond')
	{
		if (!$sort)
		{
			split_topic($action, $topic_id, $to_forum_id, $subject);
		}
		$action = 'split';
	}

	// Merge Posts?
	if ($action == 'merge_posts')
	{
		if (!$sort)
		{
			merge_posts($topic_id, $to_topic_id);
		}
		$action = 'merge';
	}

	if ($action == 'split' && !$subject)
	{
		$subject = $topic_info['topic_title'];
	}

	// Restore or pprove posts?
	if (($action == 'restore' || $action == 'approve') && $auth->acl_get('m_approve', $topic_info['forum_id']))
	{
		if (!class_exists('mcp_queue'))
		{
			include($phpbb_root_path . 'includes/mcp/mcp_queue.' . $phpEx);
		}

		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

		if (!count($post_id_list))
		{
			trigger_error('NO_POST_SELECTED');
		}

		if (!$sort)
		{
			mcp_queue::approve_posts($action, $post_id_list, $id, $mode);
		}
	}

	// Jumpbox, sort selects and that kind of things
	make_jumpbox($url . "&amp;i=$id&amp;mode=forum_view", $topic_info['forum_id'], false, 'm_', true);
	$where_sql = ($action == 'reports') ? 'WHERE post_reported = 1 AND ' : 'WHERE';

	$sort_days = $total = 0;
	$sort_key = $sort_dir = '';
	$sort_by_sql = $sort_order_sql = array();
	phpbb_mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $topic_info['forum_id'], $topic_id, $where_sql);

	/* @var $phpbb_content_visibility \phpbb\content_visibility */
	$phpbb_content_visibility = $phpbb_container->get('content.visibility');
	$limit_time_sql = ($sort_days) ? 'AND p.post_time >= ' . (time() - ($sort_days * 86400)) : '';

	if ($total == -1)
	{
		$total = $phpbb_content_visibility->get_count('topic_posts', $topic_info, $topic_info['forum_id']);
	}

	$posts_per_page = max(0, $request->variable('posts_per_page', intval($config['posts_per_page'])));
	if ($posts_per_page == 0)
	{
		$posts_per_page = $total;
	}

	if ((!empty($sort_days_old) && $sort_days_old != $sort_days) || $total <= $posts_per_page)
	{
		$start = 0;
	}
	$start = $pagination->validate_start($start, $posts_per_page, $total);

	$sql_where = (($action == 'reports') ? 'p.post_reported = 1 AND ' : '') . '
			p.topic_id = ' . $topic_id . '
			AND ' .	$phpbb_content_visibility->get_visibility_sql('post', $topic_info['forum_id'], 'p.') . '
			AND p.poster_id = u.user_id ' .
			$limit_time_sql;

	$sql_ary = array(
		'SELECT'	=> 'u.username, u.username_clean, u.user_colour, p.*',
		'FROM'		=> array(
			POSTS_TABLE		=> 'p',
			USERS_TABLE		=> 'u'
		),
		'LEFT_JOIN'	=> array(),
		'WHERE'		=> $sql_where,
		'ORDER_BY'	=> $sort_order_sql,
	);

	/**
	* Event to modify the SQL query before the MCP topic review posts is queried
	*
	* @event core.mcp_topic_modify_sql_ary
	* @var	array	sql_ary		The SQL array to get the data of the MCP topic review posts
	* @since 3.2.8-RC1
	*/
	$vars = array('sql_ary');
	extract($phpbb_dispatcher->trigger_event('core.mcp_topic_modify_sql_ary', compact($vars)));

	$sql = $db->sql_build_query('SELECT', $sql_ary);
	unset($sql_ary);

	$result = $db->sql_query_limit($sql, $posts_per_page, $start);

	$rowset = $post_id_list = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[] = $row;
		$post_id_list[] = $row['post_id'];
	}
	$db->sql_freeresult($result);

	// Get topic tracking info
	if ($config['load_db_lastread'])
	{
		$tmp_topic_data = array($topic_id => $topic_info);
		$topic_tracking_info = get_topic_tracking($topic_info['forum_id'], $topic_id, $tmp_topic_data, array($topic_info['forum_id'] => $topic_info['forum_mark_time']));
		unset($tmp_topic_data);
	}
	else
	{
		$topic_tracking_info = get_complete_topic_tracking($topic_info['forum_id'], $topic_id);
	}

	$has_unapproved_posts = $has_deleted_posts = false;

	// Grab extensions
	$attachments = array();
	if ($topic_info['topic_attachment'] && count($post_id_list))
	{
		// Get attachments...
		if ($auth->acl_get('u_download') && $auth->acl_get('f_download', $topic_info['forum_id']))
		{
			$sql = 'SELECT *
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_msg_id', $post_id_list) . '
					AND in_message = 0
				ORDER BY filetime DESC, post_msg_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$attachments[$row['post_msg_id']][] = $row;
			}
			$db->sql_freeresult($result);
		}
	}

	/**
	* Event to modify the post data for the MCP topic review before assigning the posts
	*
	* @event core.mcp_topic_modify_post_data
	* @var	array	attachments		List of attachments post_id => array of attachments
	* @var	int		forum_id		The forum ID we are currently in
	* @var	int		id				ID of the tab we are displaying
	* @var	string	mode			Mode of the MCP page we are displaying
	* @var	array	post_id_list	Array with post ids we are going to display
	* @var	array	rowset			Array with the posts data
	* @var	int		topic_id		The topic ID we are currently reviewing
	* @since 3.1.7-RC1
	*/
	$vars = array(
		'attachments',
		'forum_id',
		'id',
		'mode',
		'post_id_list',
		'rowset',
		'topic_id',
	);
	extract($phpbb_dispatcher->trigger_event('core.mcp_topic_modify_post_data', compact($vars)));

	foreach ($rowset as $current_row_number => $row)
	{
		$message = $row['post_text'];
		$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : $topic_info['topic_title'];

		$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$message = generate_text_for_display($message, $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, false);

		if (!empty($attachments[$row['post_id']]))
		{
			$update_count = array();
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

		$post_row = array(
			'POST_AUTHOR_FULL'		=> get_username_string('full', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
			'POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
			'POST_AUTHOR'			=> get_username_string('username', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),
			'U_POST_AUTHOR'			=> get_username_string('profile', $row['poster_id'], $row['username'], $row['user_colour'], $row['post_username']),

			'POST_DATE'		=> $user->format_date($row['post_time']),
			'POST_SUBJECT'	=> $post_subject,
			'MESSAGE'		=> $message,
			'POST_ID'		=> $row['post_id'],
			'RETURN_TOPIC'	=> sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $topic_id) . '">', '</a>'),

			'MINI_POST_IMG'			=> ($post_unread) ? $user->img('icon_post_target_unread', 'UNREAD_POST') : $user->img('icon_post_target', 'POST'),

			'S_POST_REPORTED'	=> ($row['post_reported'] && $auth->acl_get('m_report', $topic_info['forum_id'])),
			'S_POST_UNAPPROVED'	=> (($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) && $auth->acl_get('m_approve', $topic_info['forum_id'])),
			'S_POST_DELETED'	=> ($row['post_visibility'] == ITEM_DELETED && $auth->acl_get('m_approve', $topic_info['forum_id'])),
			'S_CHECKED'			=> (($submitted_id_list && !in_array(intval($row['post_id']), $submitted_id_list)) || in_array(intval($row['post_id']), $checked_ids)) ? true : false,
			'S_HAS_ATTACHMENTS'	=> (!empty($attachments[$row['post_id']])) ? true : false,

			'U_POST_DETAILS'	=> "$url&amp;i=$id&amp;p={$row['post_id']}&amp;mode=post_details" . (($forum_id) ? "&amp;f=$forum_id" : ''),
			'U_MCP_APPROVE'		=> ($auth->acl_get('m_approve', $topic_info['forum_id'])) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $topic_info['forum_id'] . '&amp;p=' . $row['post_id']) : '',
			'U_MCP_REPORT'		=> ($auth->acl_get('m_report', $topic_info['forum_id'])) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $topic_info['forum_id'] . '&amp;p=' . $row['post_id']) : '',
		);

		/**
		* Event to modify the template data block for topic reviews in the MCP
		*
		* @event core.mcp_topic_review_modify_row
		* @var	int		id					ID of the tab we are displaying
		* @var	string	mode				Mode of the MCP page we are displaying
		* @var	int		topic_id			The topic ID we are currently reviewing
		* @var	int		forum_id			The forum ID we are currently in
		* @var	int		start				Start item of this page
		* @var	int		current_row_number	Number of the post on this page
		* @var	array	post_row			Template block array of the current post
		* @var	array	row					Array with original post and user data
		* @var	array	topic_info			Array with topic data
		* @var	int		total				Total posts count
		* @since 3.1.4-RC1
		*/
		$vars = array(
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
		);
		extract($phpbb_dispatcher->trigger_event('core.mcp_topic_review_modify_row', compact($vars)));

		$template->assign_block_vars('postrow', $post_row);

		// Display not already displayed Attachments for this post, we already parsed them. ;)
		if (!empty($attachments[$row['post_id']]))
		{
			foreach ($attachments[$row['post_id']] as $attachment)
			{
				$template->assign_block_vars('postrow.attachment', array(
					'DISPLAY_ATTACHMENT'	=> $attachment)
				);
			}
		}

		unset($rowset[$current_row_number]);
	}

	// Display topic icons for split topic
	$s_topic_icons = false;

	if ($auth->acl_gets('m_split', 'm_merge', (int) $topic_info['forum_id']))
	{
		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		$s_topic_icons = posting_gen_topic_icons('', $icon_id);

		// Has the user selected a topic for merge?
		if ($to_topic_id)
		{
			$to_topic_info = phpbb_get_topic_data(array($to_topic_id), 'm_merge');

			if (!count($to_topic_info))
			{
				$to_topic_id = 0;
			}
			else
			{
				$to_topic_info = $to_topic_info[$to_topic_id];

				if (!$to_topic_info['enable_icons'] || $auth->acl_get('!f_icons', $topic_info['forum_id']))
				{
					$s_topic_icons = false;
				}
			}
		}
	}

	$s_hidden_fields = build_hidden_fields(array(
		'st_old'	=> $sort_days,
		'post_ids'	=> $post_id_list,
	));

	$base_url = append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;t={$topic_info['topic_id']}&amp;mode=$mode&amp;action=$action&amp;to_topic_id=$to_topic_id&amp;posts_per_page=$posts_per_page&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir");
	if ($posts_per_page)
	{
		$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $posts_per_page, $start);
	}

	$template->assign_vars(array(
		'TOPIC_TITLE'		=> $topic_info['topic_title'],
		'U_VIEW_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $topic_info['forum_id'] . '&amp;t=' . $topic_info['topic_id']),

		'TO_TOPIC_ID'		=> $to_topic_id,
		'TO_TOPIC_INFO'		=> ($to_topic_id) ? sprintf($user->lang['YOU_SELECTED_TOPIC'], $to_topic_id, '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $to_topic_info['forum_id'] . '&amp;t=' . $to_topic_id) . '">' . $to_topic_info['topic_title'] . '</a>') : '',

		'SPLIT_SUBJECT'		=> $subject,
		'POSTS_PER_PAGE'	=> $posts_per_page,
		'ACTION'			=> $action,

		'REPORTED_IMG'		=> $user->img('icon_topic_reported', 'POST_REPORTED'),
		'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
		'DELETED_IMG'		=> $user->img('icon_topic_deleted', 'POST_DELETED_RESTORE'),
		'INFO_IMG'			=> $user->img('icon_post_info', 'VIEW_INFO'),

		'S_MCP_ACTION'		=> "$url&amp;i=$id&amp;mode=$mode&amp;action=$action&amp;start=$start",
		'S_FORUM_SELECT'	=> ($to_forum_id) ? make_forum_select($to_forum_id, false, false, true, true, true) : make_forum_select($topic_info['forum_id'], false, false, true, true, true),
		'S_CAN_SPLIT'		=> ($auth->acl_get('m_split', $topic_info['forum_id'])) ? true : false,
		'S_CAN_MERGE'		=> ($auth->acl_get('m_merge', $topic_info['forum_id'])) ? true : false,
		'S_CAN_DELETE'		=> ($auth->acl_get('m_delete', $topic_info['forum_id'])) ? true : false,
		'S_CAN_APPROVE'		=> ($has_unapproved_posts && $auth->acl_get('m_approve', $topic_info['forum_id'])) ? true : false,
		'S_CAN_RESTORE'		=> ($has_deleted_posts && $auth->acl_get('m_approve', $topic_info['forum_id'])) ? true : false,
		'S_CAN_LOCK'		=> ($auth->acl_get('m_lock', $topic_info['forum_id'])) ? true : false,
		'S_CAN_REPORT'		=> ($auth->acl_get('m_report', $topic_info['forum_id'])) ? true : false,
		'S_CAN_SYNC'		=> $auth->acl_get('m_', $topic_info['forum_id']),
		'S_REPORT_VIEW'		=> ($action == 'reports') ? true : false,
		'S_MERGE_VIEW'		=> ($action == 'merge') ? true : false,
		'S_SPLIT_VIEW'		=> ($action == 'split') ? true : false,

		'S_HIDDEN_FIELDS'	=> $s_hidden_fields,

		'S_SHOW_TOPIC_ICONS'	=> $s_topic_icons,
		'S_TOPIC_ICON'			=> $icon_id,

		'U_SELECT_TOPIC'	=> "$url&amp;i=$id&amp;mode=forum_view&amp;action=merge_select" . (($forum_id) ? "&amp;f=$forum_id" : ''),

		'RETURN_TOPIC'		=> sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f={$topic_info['forum_id']}&amp;t={$topic_info['topic_id']}&amp;start=$start") . '">', '</a>'),
		'RETURN_FORUM'		=> sprintf($user->lang['RETURN_FORUM'], '<a href="' . append_sid("{$phpbb_root_path}viewforum.$phpEx", "f={$topic_info['forum_id']}&amp;start=$start") . '">', '</a>'),

		'TOTAL_POSTS'		=> $user->lang('VIEW_TOPIC_POSTS', (int) $total),
	));
}

/**
* Split topic
*/
function split_topic($action, $topic_id, $to_forum_id, $subject)
{
	global $db, $template, $user, $phpEx, $phpbb_root_path, $auth, $config, $phpbb_log, $request, $phpbb_dispatcher;

	$post_id_list	= $request->variable('post_id_list', array(0));
	$forum_id		= $request->variable('forum_id', 0);
	$start			= $request->variable('start', 0);

	if (!count($post_id_list))
	{
		$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
		return;
	}

	if (!phpbb_check_ids($post_id_list, POSTS_TABLE, 'post_id', array('m_split')))
	{
		return;
	}

	$post_id = $post_id_list[0];
	$post_info = phpbb_get_post_data(array($post_id));

	if (!count($post_info))
	{
		$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
		return;
	}

	$post_info = $post_info[$post_id];
	$subject = trim($subject);

	/**
	 * Replace Emojis and other 4bit UTF-8 chars not allowed by MySQL to UCR/NCR.
	 * Using their Numeric Character Reference's Hexadecimal notation.
	 */
	$subject = utf8_encode_ucr($subject);

	// Make some tests
	if (!$subject)
	{
		$template->assign_var('MESSAGE', $user->lang['EMPTY_SUBJECT']);
		return;
	}

	if ($to_forum_id <= 0)
	{
		$template->assign_var('MESSAGE', $user->lang['NO_DESTINATION_FORUM']);
		return;
	}

	$forum_info = phpbb_get_forum_data(array($to_forum_id), 'f_post');

	if (!count($forum_info))
	{
		$template->assign_var('MESSAGE', $user->lang['USER_CANNOT_POST']);
		return;
	}

	$forum_info = $forum_info[$to_forum_id];

	if ($forum_info['forum_type'] != FORUM_POST)
	{
		$template->assign_var('MESSAGE', $user->lang['FORUM_NOT_POSTABLE']);
		return;
	}

	$redirect = $request->variable('redirect', build_url(array('quickmod')));

	$s_hidden_fields = build_hidden_fields(array(
		'i'				=> 'main',
		'post_id_list'	=> $post_id_list,
		'f'				=> $forum_id,
		'mode'			=> 'topic_view',
		'start'			=> $start,
		'action'		=> $action,
		't'				=> $topic_id,
		'redirect'		=> $redirect,
		'subject'		=> $subject,
		'to_forum_id'	=> $to_forum_id,
		'icon'			=> $request->variable('icon', 0))
	);

	if (confirm_box(true))
	{
		if ($action == 'split_beyond')
		{
			$sort_days = $total = 0;
			$sort_key = $sort_dir = '';
			$sort_by_sql = $sort_order_sql = array();
			phpbb_mcp_sorting('viewtopic', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id, $topic_id);

			$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

			if ($sort_order_sql[0] == 'u')
			{
				$sql = 'SELECT p.post_id, p.forum_id, p.post_visibility
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u
					WHERE p.topic_id = $topic_id
						AND p.poster_id = u.user_id
						$limit_time_sql
					ORDER BY $sort_order_sql";
			}
			else
			{
				$sql = 'SELECT p.post_id, p.forum_id, p.post_visibility
					FROM ' . POSTS_TABLE . " p
					WHERE p.topic_id = $topic_id
						$limit_time_sql
					ORDER BY $sort_order_sql";
			}
			$result = $db->sql_query_limit($sql, 0, $start);

			$store = false;
			$post_id_list = array();
			while ($row = $db->sql_fetchrow($result))
			{
				// If split from selected post (split_beyond), we split the unapproved items too.
				if (($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) && !$auth->acl_get('m_approve', $row['forum_id']))
				{
//					continue;
				}

				// Start to store post_ids as soon as we see the first post that was selected
				if ($row['post_id'] == $post_id)
				{
					$store = true;
				}

				if ($store)
				{
					$post_id_list[] = $row['post_id'];
				}
			}
			$db->sql_freeresult($result);
		}

		if (!count($post_id_list))
		{
			trigger_error('NO_POST_SELECTED');
		}

		$icon_id = $request->variable('icon', 0);

		$sql_ary = array(
			'forum_id'			=> $to_forum_id,
			'topic_title'		=> $subject,
			'icon_id'			=> $icon_id,
			'topic_visibility'	=> 1
		);

		$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);

		$to_topic_id = $db->sql_nextid();
		move_posts($post_id_list, $to_topic_id);

		$topic_info = phpbb_get_topic_data(array($topic_id));
		$topic_info = $topic_info[$topic_id];

		$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_SPLIT_DESTINATION', false, array(
			'forum_id' => $to_forum_id,
			'topic_id' => $to_topic_id,
			$subject
		));
		$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_SPLIT_SOURCE', false, array(
			'forum_id' => $forum_id,
			'topic_id' => $topic_id,
			$topic_info['topic_title']
		));

		// Change topic title of first post
		$sql = 'UPDATE ' . POSTS_TABLE . "
			SET post_subject = '" . $db->sql_escape($subject) . "'
			WHERE post_id = {$post_id_list[0]}";
		$db->sql_query($sql);

		// Grab data for first post in split topic
		$sql_array = array(
			'SELECT'  => 'p.post_id, p.forum_id, p.poster_id, p.post_text, f.enable_indexing',
			'FROM' => array(
				POSTS_TABLE => 'p',
			),
			'LEFT_JOIN' => array(
				array(
					'FROM' => array(FORUMS_TABLE => 'f'),
					'ON' => 'p.forum_id = f.forum_id',
				)
			),
			'WHERE' => "post_id = {$post_id_list[0]}",
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$first_post_data = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Index first post as if it were edited
		if ($first_post_data['enable_indexing'])
		{
			// Select the search method and do some additional checks to ensure it can actually be utilised
			$search_type = $config['search_type'];

			if (!class_exists($search_type))
			{
				trigger_error('NO_SUCH_SEARCH_MODULE');
			}

			$error = false;
			$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

			if ($error)
			{
				trigger_error($error);
			}

			$search->index('edit', $first_post_data['post_id'], $first_post_data['post_text'], $subject, $first_post_data['poster_id'], $first_post_data['forum_id']);
		}

		// Copy topic subscriptions to new topic
		$sql = 'SELECT user_id, notify_status
			FROM ' . TOPICS_WATCH_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);

		$sql_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary[] = array(
				'topic_id'		=> (int) $to_topic_id,
				'user_id'		=> (int) $row['user_id'],
				'notify_status'	=> (int) $row['notify_status'],
			);
		}
		$db->sql_freeresult($result);

		if (count($sql_ary))
		{
			$db->sql_multi_insert(TOPICS_WATCH_TABLE, $sql_ary);
		}

		// Copy bookmarks to new topic
		$sql = 'SELECT user_id
			FROM ' . BOOKMARKS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);

		$sql_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_ary[] = array(
				'topic_id'		=> (int) $to_topic_id,
				'user_id'		=> (int) $row['user_id'],
			);
		}
		$db->sql_freeresult($result);

		if (count($sql_ary))
		{
			$db->sql_multi_insert(BOOKMARKS_TABLE, $sql_ary);
		}

		$success_msg = 'TOPIC_SPLIT_SUCCESS';

		// Update forum statistics
		$config->increment('num_topics', 1, false);

		// Link back to both topics
		$return_link = sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $post_info['forum_id'] . '&amp;t=' . $post_info['topic_id']) . '">', '</a>') . '<br /><br />' . sprintf($user->lang['RETURN_NEW_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $to_forum_id . '&amp;t=' . $to_topic_id) . '">', '</a>');
		$redirect = $request->variable('redirect', "{$phpbb_root_path}viewtopic.$phpEx?f=$to_forum_id&amp;t=$to_topic_id");
		$redirect = reapply_sid($redirect);

		meta_refresh(3, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . $return_link);
	}
	else
	{
		confirm_box(false, ($action == 'split_all') ? 'SPLIT_TOPIC_ALL' : 'SPLIT_TOPIC_BEYOND', $s_hidden_fields);
	}
}

/**
* Merge selected posts into selected topic
*/
function merge_posts($topic_id, $to_topic_id)
{
	global $db, $template, $user, $phpEx, $phpbb_root_path, $phpbb_log, $request, $phpbb_dispatcher;

	if (!$to_topic_id)
	{
		$template->assign_var('MESSAGE', $user->lang['NO_FINAL_TOPIC_SELECTED']);
		return;
	}

	$sync_topics = array($topic_id, $to_topic_id);

	$topic_data = phpbb_get_topic_data($sync_topics, 'm_merge');

	if (!count($topic_data) || empty($topic_data[$to_topic_id]))
	{
		$template->assign_var('MESSAGE', $user->lang['NO_FINAL_TOPIC_SELECTED']);
		return;
	}

	$sync_forums = array();
	foreach ($topic_data as $data)
	{
		$sync_forums[$data['forum_id']] = $data['forum_id'];
	}

	$topic_data = $topic_data[$to_topic_id];

	$post_id_list	= $request->variable('post_id_list', array(0));
	$start			= $request->variable('start', 0);

	if (!count($post_id_list))
	{
		$template->assign_var('MESSAGE', $user->lang['NO_POST_SELECTED']);
		return;
	}

	if (!phpbb_check_ids($post_id_list, POSTS_TABLE, 'post_id', array('m_merge')))
	{
		return;
	}

	$redirect = $request->variable('redirect', build_url(array('quickmod')));

	$s_hidden_fields = build_hidden_fields(array(
		'i'				=> 'main',
		'post_id_list'	=> $post_id_list,
		'to_topic_id'	=> $to_topic_id,
		'mode'			=> 'topic_view',
		'action'		=> 'merge_posts',
		'start'			=> $start,
		'redirect'		=> $redirect,
		't'				=> $topic_id)
	);
	$return_link = '';

	if (confirm_box(true))
	{
		$to_forum_id = $topic_data['forum_id'];

		move_posts($post_id_list, $to_topic_id, false);

		$phpbb_log->add('mod', $user->data['user_id'], $user->ip, 'LOG_MERGE', false, array(
			'forum_id' => $to_forum_id,
			'topic_id' => $to_topic_id,
			$topic_data['topic_title']
		));

		// Message and return links
		$success_msg = 'POSTS_MERGED_SUCCESS';

		// Does the original topic still exist? If yes, link back to it
		$sql = 'SELECT forum_id
			FROM ' . POSTS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$return_link .= sprintf($user->lang['RETURN_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $row['forum_id'] . '&amp;t=' . $topic_id) . '">', '</a>');
		}
		else
		{
			if (!function_exists('phpbb_update_rows_avoiding_duplicates_notify_status'))
			{
				include($phpbb_root_path . 'includes/functions_database_helper.' . $phpEx);
			}

			// If the topic no longer exist, we will update the topic watch table.
			phpbb_update_rows_avoiding_duplicates_notify_status($db, TOPICS_WATCH_TABLE, 'topic_id', array($topic_id), $to_topic_id);

			// If the topic no longer exist, we will update the bookmarks table.
			phpbb_update_rows_avoiding_duplicates($db, BOOKMARKS_TABLE, 'topic_id', array($topic_id), $to_topic_id);
		}

		// Re-sync the topics and forums because the auto-sync was deactivated in the call of move_posts()
		sync('topic_reported', 'topic_id', $sync_topics);
		sync('topic_attachment', 'topic_id', $sync_topics);
		sync('topic', 'topic_id', $sync_topics, true);
		sync('forum', 'forum_id', $sync_forums, true, true);

		// Link to the new topic
		$return_link .= (($return_link) ? '<br /><br />' : '') . sprintf($user->lang['RETURN_NEW_TOPIC'], '<a href="' . append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $to_forum_id . '&amp;t=' . $to_topic_id) . '">', '</a>');
		$redirect = $request->variable('redirect', "{$phpbb_root_path}viewtopic.$phpEx?f=$to_forum_id&amp;t=$to_topic_id");
		$redirect = reapply_sid($redirect);

		/**
		 * Perform additional actions after merging posts.
		 *
		 * @event core.mcp_topics_merge_posts_after
		 * @var	int		topic_id		The topic ID from which posts are being moved
		 * @var	int		to_topic_id		The topic ID to which posts are being moved
		 * @since 3.1.11-RC1
		 */
		$vars = array(
			'topic_id',
			'to_topic_id',
		);
		extract($phpbb_dispatcher->trigger_event('core.mcp_topics_merge_posts_after', compact($vars)));

		meta_refresh(3, $redirect);
		trigger_error($user->lang[$success_msg] . '<br /><br />' . $return_link);
	}
	else
	{
		confirm_box(false, 'MERGE_POSTS', $s_hidden_fields);
	}
}
