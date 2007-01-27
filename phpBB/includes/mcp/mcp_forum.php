<?php
/** 
*
* @package mcp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* MCP Forum View
*/
function mcp_forum_view($id, $mode, $action, $forum_info)
{
	global $template, $db, $user, $auth, $cache, $module;
	global $phpEx, $phpbb_root_path, $config;

	$user->add_lang('viewtopic');

	include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);

	$url = append_sid("{$phpbb_root_path}mcp.$phpEx?" . extra_url());

	if ($action == 'merge_select')
	{
		// Fixes a "bug" that makes forum_view use the same ordering as topic_view
		unset($_POST['sk'], $_POST['sd'], $_REQUEST['sk'], $_REQUEST['sd']);
	}

	$forum_id		= $forum_info['forum_id'];
	$start			= request_var('start', 0);
	$topic_id_list	= request_var('topic_id_list', array(0));
	$post_id_list	= request_var('post_id_list', array(0));
	$topic_id		= request_var('t', 0);

	// Resync Topics
	if ($action == 'resync')
	{
		$topic_ids = request_var('topic_id_list', array(0));

		mcp_resync_topics($topic_ids);
	}

	$selected_ids = '';
	if (sizeof($post_id_list))
	{
		foreach ($post_id_list as $num => $post_id)
		{
			$selected_ids .= '&amp;post_id_list[' . $num . ']=' . $post_id;
		}
	}

	make_jumpbox($url . "&amp;i=$id&amp;action=$action&amp;mode=$mode", $forum_id . (($action == 'merge_select') ? $selected_ids : ''), false, 'm_');

	$topics_per_page = ($forum_info['forum_topics_per_page']) ? $forum_info['forum_topics_per_page'] : $config['topics_per_page'];

	$sort_days = $total = 0;
	$sort_key = $sort_dir = '';
	$sort_by_sql = $sort_order_sql = array();
	mcp_sorting('viewforum', $sort_days, $sort_key, $sort_dir, $sort_by_sql, $sort_order_sql, $total, $forum_id);

	$forum_topics = ($total == -1) ? $forum_info['forum_topics'] : $total;
	$limit_time_sql = ($sort_days) ? 'AND t.topic_last_post_time >= ' . (time() - ($sort_days * 86400)) : '';

	$template->assign_vars(array(
		'FORUM_NAME'			=> $forum_info['forum_name'],
		'FORUM_DESCRIPTION'		=> generate_text_for_display($forum_info['forum_desc'], $forum_info['forum_desc_uid'], $forum_info['forum_desc_bitfield'], $forum_info['forum_desc_options']),

		'REPORTED_IMG'			=> $user->img('icon_topic_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'		=> $user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
		'LAST_POST_IMG'			=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		'NEWEST_POST_IMG'		=> $user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),

		'S_CAN_REPORT'			=> $auth->acl_get('m_report', $forum_id),
		'S_CAN_DELETE'			=> $auth->acl_get('m_delete', $forum_id),
		'S_CAN_MOVE'			=> $auth->acl_get('m_move', $forum_id),
		'S_CAN_FORK'			=> $auth->acl_get('m_', $forum_id),
		'S_CAN_LOCK'			=> $auth->acl_get('m_lock', $forum_id),
		'S_CAN_SYNC'			=> $auth->acl_get('m_', $forum_id),
		'S_CAN_APPROVE'			=> $auth->acl_get('m_approve', $forum_id),
		'S_MERGE_SELECT'		=> ($action == 'merge_select') ? true : false,
		'S_CAN_MAKE_NORMAL'		=> $auth->acl_gets('f_sticky', 'f_announce', $forum_id),
		'S_CAN_MAKE_STICKY'		=> $auth->acl_get('f_sticky', $forum_id),
		'S_CAN_MAKE_ANNOUNCE'	=> $auth->acl_get('f_announce', $forum_id),

		'U_VIEW_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id),
		'U_VIEW_FORUM_LOGS'		=> ($auth->acl_gets('a_', 'm_', $forum_id) && $module->loaded('logs')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=logs&amp;mode=forum_logs&amp;f=' . $forum_id) : '',

		'S_MCP_ACTION'			=> $url . "&amp;i=$id&amp;mode=$mode&amp;start=$start" . (($action == 'merge_select') ? $selected_ids : ''),

		'PAGINATION'			=> generate_pagination($url . "&amp;i=$id&amp;action=$action&amp;mode=$mode" . (($action == 'merge_select') ? $selected_ids : ''), $forum_topics, $topics_per_page, $start),
		'PAGE_NUMBER'			=> on_page($forum_topics, $topics_per_page, $start),
		'TOTAL'					=> $forum_topics)
	);

	// Grab icons
	$icons = $cache->obtain_icons();

	$topic_rows = array();

	if ($config['load_db_lastread'])
	{
		$read_tracking_join = ' LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id'] . ')';
		$read_tracking_select = ', tt.mark_time';
	}
	else
	{
		$read_tracking_join = $read_tracking_select = '';
	}

	$sql = "SELECT t.*$read_tracking_select
		FROM " . TOPICS_TABLE . " t $read_tracking_join
		WHERE (t.forum_id = $forum_id OR t.forum_id = 0)
			" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND t.topic_approved = 1') . "
			$limit_time_sql
		ORDER BY t.topic_type DESC, $sort_order_sql";
	$result = $db->sql_query_limit($sql, $topics_per_page, $start);

	while ($row = $db->sql_fetchrow($result))
	{
		$topic_rows[$row['topic_id']] = $row;
		$topic_list[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	$topic_tracking_info = array();
	// Get topic tracking info
	if ($config['load_db_lastread'])
	{
		$topic_tracking_info = get_topic_tracking($forum_id, $topic_list, $topic_rows, array($forum_id => $forum_info['mark_time']), array());
	}
	else
	{
		$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_list, array());
	}

	foreach ($topic_rows as $topic_id => $row)
	{
		$topic_title = '';

		$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];

		if ($row['topic_status'] == ITEM_MOVED)
		{
			$unread_topic = false;
		}
		else
		{
			$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
		}

		// Get folder img, topic status/type related information
		$folder_img = $folder_alt = $topic_type = '';
		topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

		$topic_title = censor_text($row['topic_title']);

		$topic_unapproved = (!$row['topic_approved'] && $auth->acl_get('m_approve', $row['forum_id'])) ? true : false;
		$posts_unapproved = ($row['topic_approved'] && $row['topic_replies'] < $row['topic_replies_real'] && $auth->acl_get('m_approve', $row['forum_id'])) ? true : false;
		$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? $url . '&amp;i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . '&amp;t=' . $row['topic_id'] : '';

		$topic_row = array(
			'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
			'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_SRC'	=> $user->img($folder_img, $folder_alt, false, '', 'src'),
			'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
			'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
			'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
			'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',

			'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'U_TOPIC_AUTHOR'			=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),

			'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'U_LAST_POST_AUTHOR'		=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

			'TOPIC_TYPE'		=> $topic_type,
			'TOPIC_TITLE'		=> $topic_title,
			'REPLIES'			=> ($auth->acl_get('m_approve', $row['forum_id'])) ? $row['topic_replies_real'] : $row['topic_replies'],
			'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
			'FIRST_POST_TIME'	=> $user->format_date($row['topic_time']),
			'LAST_POST_SUBJECT'	=> $row['topic_last_post_subject'],
			'LAST_VIEW_TIME'	=> $user->format_date($row['topic_last_view_time']),

			'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_get('m_report', $row['forum_id'])) ? true : false,
			'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
			'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
			'S_UNREAD_TOPIC'		=> $unread_topic,
		);

		if ($row['topic_status'] == ITEM_MOVED)
		{
			$topic_row = array_merge($topic_row, array(
				'U_VIEW_TOPIC'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t={$row['topic_moved_id']}"),
				'U_DELETE_TOPIC'	=> ($auth->acl_get('m_delete', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;f=$forum_id&amp;topic_id_list[]={$row['topic_id']}&amp;mode=forum_view&amp;action=delete_topic") : '',
				'S_MOVED_TOPIC'		=> true,
				'TOPIC_ID'			=> $row['topic_moved_id'],
			));
		}
		else
		{
			$topic_row = array_merge($topic_row, array(
				'U_VIEW_TOPIC'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=$id&amp;f=$forum_id&amp;t={$row['topic_id']}&amp;mode=topic_view"),

				'S_SELECT_TOPIC'	=> ($action == 'merge_select' && $row['topic_id'] != $topic_id) ? true : false,
				'U_SELECT_TOPIC'	=> $url . "&amp;i=$id&amp;mode=topic_view&amp;action=merge&amp;to_topic_id=" . $row['topic_id'] . $selected_ids,
				'U_MCP_QUEUE'		=> $u_mcp_queue,
				'U_MCP_REPORT'		=> ($auth->acl_get('m_report', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=main&amp;mode=topic_view&amp;t=' . $row['topic_id'] . '&amp;action=reports') : '',
				'TOPIC_ID'			=> $row['topic_id'],
				'S_TOPIC_CHECKED'	=> ($topic_id_list && in_array($row['topic_id'], $topic_id_list)) ? true : false,
			));
		}

		$template->assign_block_vars('topicrow', $topic_row);
	}
	unset($topic_rows);
}

/**
* Resync topics
*/
function mcp_resync_topics($topic_ids)
{
	global $auth, $db, $template, $phpEx, $user, $phpbb_root_path;

	if (!sizeof($topic_ids))
	{
		trigger_error($user->lang['NO_TOPIC_SELECTED']);
	}

	if (!check_ids($topic_ids, TOPICS_TABLE, 'topic_id', array('m_')))
	{
		return;
	}

	// Sync everything and perform extra checks separately
	sync('topic_reported', 'topic_id', $topic_ids, false, true);
	sync('topic_attachment', 'topic_id', $topic_ids, false, true);
	sync('topic', 'topic_id', $topic_ids, true, false);

	$sql = 'SELECT topic_id, forum_id, topic_title
		FROM ' . TOPICS_TABLE . '
		WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
	$result = $db->sql_query($sql);

	// Log this action
	while ($row = $db->sql_fetchrow($result))
	{
		add_log('mod', $row['forum_id'], $row['topic_id'], 'LOG_TOPIC_RESYNC', $row['topic_title']);
	}
	$db->sql_freeresult($result);

	$msg = (sizeof($topic_ids) == 1) ? $user->lang['TOPIC_RESYNC_SUCCESS'] : $user->lang['TOPICS_RESYNC_SUCCESS'];

	$redirect = request_var('redirect', $user->data['session_page']);

	meta_refresh(3, $redirect);
	trigger_error($msg . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));

	return;
}

?>