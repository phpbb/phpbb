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
 * @param \phpbb\event\dispatcher $phpbb_dispatcher
 * @param array $sort_by_sql
 * @param $sort_key
 * @param $direction
 * @param $forum_data
 * @param array $active_forum_ary
 * @param $forum_id
 * @param $db
 * @param $sql_approved
 * @param $sql_limit_time
 * @param $store_reverse
 * @param $sql_limit
 * @param $sql_start
 * @param array $topic_list
 * @return array
 */
function query_topic_ids(\phpbb\event\dispatcher $phpbb_dispatcher, array $sort_by_sql, $sort_key, $direction, $forum_data, array $active_forum_ary, $forum_id, $db, $sql_approved, $sql_limit_time, $store_reverse, $sql_limit, $sql_start, array $topic_list)
{
	/**
	 * Modify the topics sort ordering if needed
	 *
	 * @event core.viewforum_modify_sort_direction
	 * @var string    direction    Topics sort order
	 * @since 3.2.5-RC1
	 */
	$vars = array(
		'direction',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_sort_direction', compact($vars)));

	if (is_array($sort_by_sql[$sort_key]))
	{
		$sql_sort_order = implode(' ' . $direction . ', ', $sort_by_sql[$sort_key]) . ' ' . $direction;
	}
	else
	{
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . $direction;
	}

	if ($forum_data['forum_type'] == FORUM_POST || !count($active_forum_ary))
	{
		$sql_where = 't.forum_id = ' . $forum_id;
	}
	else if (empty($active_forum_ary['exclude_forum_id']))
	{
		$sql_where = $db->sql_in_set('t.forum_id', $active_forum_ary['forum_id']);
	}
	else
	{
		$get_forum_ids = array_diff($active_forum_ary['forum_id'], $active_forum_ary['exclude_forum_id']);
		$sql_where = (count($get_forum_ids)) ? $db->sql_in_set('t.forum_id', $get_forum_ids) : 't.forum_id = ' . $forum_id;
	}

	// Grab just the sorted topic ids
	$sql_ary = array(
		'SELECT' => 't.topic_id',
		'FROM' => array(
			TOPICS_TABLE => 't',
		),
		'WHERE' => "$sql_where
		AND t.topic_type IN (" . POST_NORMAL . ', ' . POST_STICKY . ")
		$sql_approved
		$sql_limit_time",
		'ORDER_BY' => 't.topic_type ' . ((!$store_reverse) ? 'DESC' : 'ASC') . ', ' . $sql_sort_order,
	);

	/**
	 * Event to modify the SQL query before the topic ids data is retrieved
	 *
	 * @event core.viewforum_get_topic_ids_data
	 * @var    array    forum_data        Data about the forum
	 * @var    array    sql_ary            SQL query array to get the topic ids data
	 * @var    string    sql_approved    Topic visibility SQL string
	 * @var    int        sql_limit        Number of records to select
	 * @var    string    sql_limit_time    SQL string to limit topic_last_post_time data
	 * @var    array    sql_sort_order    SQL sorting string
	 * @var    int        sql_start        Offset point to start selection from
	 * @var    string    sql_where        SQL WHERE clause string
	 * @var    bool    store_reverse    Flag indicating if we select from the late pages
	 *
	 * @since 3.1.0-RC4
	 *
	 * @changed 3.1.3 Added forum_data
	 */
	$vars = array(
		'forum_data',
		'sql_ary',
		'sql_approved',
		'sql_limit',
		'sql_limit_time',
		'sql_sort_order',
		'sql_start',
		'sql_where',
		'store_reverse',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewforum_get_topic_ids_data', compact($vars)));

	$sql = $db->sql_build_query('SELECT', $sql_ary);
	$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

	while ($row = $db->sql_fetchrow($result))
	{
		$topic_list[] = (int) $row['topic_id'];
	}
	$db->sql_freeresult($result);
	return $topic_list;
}

/**
 * @param $config
 * @param $start
 * @param $topics_count
 * @param $sort_dir
 * @param \phpbb\pagination $pagination
 * @param array $announcement_list
 * @return array
 */
function sql_compute_limits($config, $start, $topics_count, $sort_dir, \phpbb\pagination $pagination, array $announcement_list)
{
	$store_reverse = false;
	$sql_limit = $config['topics_per_page'];
	if ($start > $topics_count / 2)
	{
		$store_reverse = true;

		// Select the sort order
		$direction = (($sort_dir == 'd') ? 'ASC' : 'DESC');

		$sql_limit = $pagination->reverse_limit($start, $sql_limit, $topics_count - count($announcement_list));
		$sql_start = $pagination->reverse_start($start, $sql_limit, $topics_count - count($announcement_list));
	}
	else
	{
		// Select the sort order
		$direction = (($sort_dir == 'd') ? 'DESC' : 'ASC');
		$sql_start = $start;
	}
	return array($store_reverse, $sql_limit, $direction, $sql_start);
}

/**
 * @param $sort_days
 * @param $forum_id
 * @param \phpbb\content_visibility $phpbb_content_visibility
 * @param \phpbb\event\dispatcher $phpbb_dispatcher
 * @param $db
 * @param \phpbb\template\template $template
 * @param int $start
 * @return array
 */
function get_topic_count_by_time_frame($sort_days, $forum_id, \phpbb\content_visibility $phpbb_content_visibility, \phpbb\event\dispatcher $phpbb_dispatcher, $db, \phpbb\template\template $template, $start)
{
	$min_post_time = time() - ($sort_days * 86400);

	$sql_array = array(
		'SELECT' => 'COUNT(t.topic_id) AS num_topics',
		'FROM' => array(
			TOPICS_TABLE => 't',
		),
		'WHERE' => 't.forum_id = ' . $forum_id . '
			AND (t.topic_last_post_time >= ' . $min_post_time . '
				OR t.topic_type = ' . POST_ANNOUNCE . '
				OR t.topic_type = ' . POST_GLOBAL . ')
			AND ' . $phpbb_content_visibility->get_visibility_sql('topic', $forum_id, 't.'),
	);

	/**
	 * Modify the sort data SQL query for getting additional fields if needed
	 *
	 * @event core.viewforum_modify_sort_data_sql
	 * @var int        forum_id        The forum_id whose topics are being listed
	 * @var int        start            Variable containing start for pagination
	 * @var int        sort_days        The oldest topic displayable in elapsed days
	 * @var string    sort_key        The sorting by. It is one of the first character of (in low case):
	 *                                Author, Post time, Replies, Subject, Views
	 * @var string    sort_dir        Either "a" for ascending or "d" for descending
	 * @var array    sql_array        The SQL array to get the data of all topics
	 * @since 3.1.9-RC1
	 */
	$vars = array(
		'forum_id',
		'start',
		'sort_days',
		'sort_key',
		'sort_dir',
		'sql_array',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_sort_data_sql', compact($vars)));

	$result = $db->sql_query($db->sql_build_query('SELECT', $sql_array));
	$topics_count = (int) $db->sql_fetchfield('num_topics');
	$db->sql_freeresult($result);

	if (isset($_POST['sort']))
	{
		$start = 0;
	}
	$sql_limit_time = "AND t.topic_last_post_time >= $min_post_time";

	// Make sure we have information about day selection ready
	$template->assign_var('S_SORT_DAYS', true);
	return [$topics_count, $start, $sql_limit_time];
}

/**
 * @param $config
 * @param array $forum_data
 * @param $auth
 * @param $forum_id
 * @param \phpbb\user $user
 * @param $start
 * @return array
 */
function forum_subscription_information($config, array $forum_data, $auth, $forum_id, \phpbb\user $user, $start)
{
	// Forum rules and subscription info
	$s_watching_forum = array(
		'link' => '',
		'link_toggle' => '',
		'title' => '',
		'title_toggle' => '',
		'is_watching' => false,
	);

	if ($config['allow_forum_notify'] && $forum_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_subscribe', $forum_id) || $user->data['user_id'] == ANONYMOUS))
	{
		$notify_status = (isset($forum_data['notify_status'])) ? $forum_data['notify_status'] : NULL;
		watch_topic_forum('forum', $s_watching_forum, $user->data['user_id'], $forum_id, 0, $notify_status, $start, $forum_data['forum_name']);
	}
	return array($s_watching_forum, $forum_data);
}

/**
 * @param $mark_read
 * @param $request
 * @param $forum_id
 * @param $phpbb_root_path
 * @param $phpEx
 * @param \phpbb\user $user
 * @param $config
 */
function mark_topics_read($request, $forum_id, $phpbb_root_path, $phpEx, \phpbb\user $user, $config)
{
	$token = $request->variable('hash', '');
	if (check_link_hash($token, 'global'))
	{
		markread('topics', array($forum_id), false, $request->variable('mark_time', 0));
	}
	$redirect_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id);
	meta_refresh(3, $redirect_url);

	if ($request->is_ajax())
	{
		// Tell the ajax script what language vars and URL need to be replaced
		$data = array(
			'NO_UNREAD_POSTS'	=> $user->lang['NO_UNREAD_POSTS'],
			'UNREAD_POSTS'		=> $user->lang['UNREAD_POSTS'],
			'U_MARK_TOPICS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . "&f=$forum_id&mark=topics&mark_time=" . time(), false) : '',
			'MESSAGE_TITLE'		=> $user->lang['INFORMATION'],
			'MESSAGE_TEXT'		=> $user->lang['TOPICS_MARKED']
		);
		$json_response = new \phpbb\json_response();
		$json_response->send($data);
	}

	trigger_error($user->lang['TOPICS_MARKED'] . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . $redirect_url . '">', '</a>'));
}

/**
 * @param $config
 * @param $phpbb_container
 * @param array $forum_data
 * @param \phpbb\template\template $template
 */
function run_cron_jobs($config, $phpbb_container, array $forum_data, \phpbb\template\template $template)
{
	// Do the forum Prune thang - cron type job ...
	if (!$config['use_system_cron'])
	{
		/* @var $cron \phpbb\cron\manager */
		$cron = $phpbb_container->get('cron.manager');

		$task = $cron->find_task('cron.task.core.prune_forum');
		$task->set_forum_data($forum_data);

		if ($task->is_ready())
		{
			$url = $task->get_url();
			$template->assign_var('RUN_CRON_TASK', '<img src="' . $url . '" width="1" height="1" alt="cron" />');
		}
		else
		{
			// See if we should prune the shadow topics instead
			$task = $cron->find_task('cron.task.core.prune_shadow_topics');
			$task->set_forum_data($forum_data);

			if ($task->is_ready())
			{
				$url = $task->get_url();
				$template->assign_var('RUN_CRON_TASK', '<img src="' . $url . '" width="1" height="1" alt="cron" />');
			}
		}
	}
}

/**
 * @param \phpbb\template\template $template
 * @param array $moderators
 * @param $forum_id
 * @param \phpbb\user $user
 * @param array $forum_data
 * @param $post_alt
 * @param $auth
 * @param $s_display_active
 * @param $s_sort_dir
 * @param $s_sort_key
 * @param $s_limit_days
 * @param array $active_forum_ary
 * @param array $s_watching_forum
 * @param $phpbb_root_path
 * @param $phpEx
 * @param $start
 * @param $config
 * @param array $s_search_hidden_fields
 * @param $u_sort_param
 */
function render_general(\phpbb\template\template $template, array $moderators, $forum_id, \phpbb\user $user, array $forum_data, $post_alt, $auth, $s_display_active, $s_sort_dir, $s_sort_key, $s_limit_days, array $active_forum_ary, array $s_watching_forum, $phpbb_root_path, $phpEx, $start, $config, array $s_search_hidden_fields, $u_sort_param)
{
	$template->assign_vars(array(
		'MODERATORS' => (!empty($moderators[$forum_id])) ? implode($user->lang['COMMA_SEPARATOR'], $moderators[$forum_id]) : '',

		'POST_IMG' => ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', $post_alt) : $user->img('button_topic_new', $post_alt),
		'NEWEST_POST_IMG' => $user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
		'LAST_POST_IMG' => $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
		'FOLDER_IMG' => $user->img('topic_read', 'NO_UNREAD_POSTS'),
		'FOLDER_UNREAD_IMG' => $user->img('topic_unread', 'UNREAD_POSTS'),
		'FOLDER_HOT_IMG' => $user->img('topic_read_hot', 'NO_UNREAD_POSTS_HOT'),
		'FOLDER_HOT_UNREAD_IMG' => $user->img('topic_unread_hot', 'UNREAD_POSTS_HOT'),
		'FOLDER_LOCKED_IMG' => $user->img('topic_read_locked', 'NO_UNREAD_POSTS_LOCKED'),
		'FOLDER_LOCKED_UNREAD_IMG' => $user->img('topic_unread_locked', 'UNREAD_POSTS_LOCKED'),
		'FOLDER_STICKY_IMG' => $user->img('sticky_read', 'POST_STICKY'),
		'FOLDER_STICKY_UNREAD_IMG' => $user->img('sticky_unread', 'POST_STICKY'),
		'FOLDER_ANNOUNCE_IMG' => $user->img('announce_read', 'POST_ANNOUNCEMENT'),
		'FOLDER_ANNOUNCE_UNREAD_IMG' => $user->img('announce_unread', 'POST_ANNOUNCEMENT'),
		'FOLDER_MOVED_IMG' => $user->img('topic_moved', 'TOPIC_MOVED'),
		'REPORTED_IMG' => $user->img('icon_topic_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG' => $user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
		'DELETED_IMG' => $user->img('icon_topic_deleted', 'TOPIC_DELETED'),
		'POLL_IMG' => $user->img('icon_topic_poll', 'TOPIC_POLL'),
		'GOTO_PAGE_IMG' => $user->img('icon_post_target', 'GOTO_PAGE'),

		'L_NO_TOPICS' => ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->lang['POST_FORUM_LOCKED'] : $user->lang['NO_TOPICS'],

		'S_DISPLAY_POST_INFO' => ($forum_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS)) ? true : false,

		'S_IS_POSTABLE' => ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'S_USER_CAN_POST' => ($auth->acl_get('f_post', $forum_id)) ? true : false,
		'S_DISPLAY_ACTIVE' => $s_display_active,
		'S_SELECT_SORT_DIR' => $s_sort_dir,
		'S_SELECT_SORT_KEY' => $s_sort_key,
		'S_SELECT_SORT_DAYS' => $s_limit_days,
		'S_TOPIC_ICONS' => ($s_display_active && count($active_forum_ary)) ? max($active_forum_ary['enable_icons']) : (($forum_data['enable_icons']) ? true : false),
		'U_WATCH_FORUM_LINK' => $s_watching_forum['link'],
		'U_WATCH_FORUM_TOGGLE' => $s_watching_forum['link_toggle'],
		'S_WATCH_FORUM_TITLE' => $s_watching_forum['title'],
		'S_WATCH_FORUM_TOGGLE' => $s_watching_forum['title_toggle'],
		'S_WATCHING_FORUM' => $s_watching_forum['is_watching'],
		'S_FORUM_ACTION' => append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")),
		'S_DISPLAY_SEARCHBOX' => ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id) && $config['load_search']) ? true : false,
		'S_SEARCHBOX_ACTION' => append_sid("{$phpbb_root_path}search.$phpEx"),
		'S_SEARCH_LOCAL_HIDDEN_FIELDS' => build_hidden_fields($s_search_hidden_fields),
		'S_SINGLE_MODERATOR' => (!empty($moderators[$forum_id]) && count($moderators[$forum_id]) > 1) ? false : true,
		'S_IS_LOCKED' => ($forum_data['forum_status'] == ITEM_LOCKED) ? true : false,
		'S_VIEWFORUM' => true,

		'U_MCP' => ($auth->acl_get('m_', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "f=$forum_id&amp;i=main&amp;mode=forum_view", true, $user->session_id) : '',
		'U_POST_NEW_TOPIC' => ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=post&amp;f=' . $forum_id) : '',
		'U_VIEW_FORUM' => append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($start == 0) ? '' : "&amp;start=$start")),
		'U_CANONICAL' => generate_board_url() . '/' . append_sid("viewforum.$phpEx", "f=$forum_id" . (($start) ? "&amp;start=$start" : ''), true, ''),
		'U_MARK_TOPICS' => ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . "&amp;f=$forum_id&amp;mark=topics&amp;mark_time=" . time()) : '',
	));
}

/**
 * @param $db
 * @param array $shadow_topic_list
 * @param \phpbb\event\dispatcher $phpbb_dispatcher
 * @param array $rowset
 * @param array $topic_list
 * @param int $topics_count
 * @param $auth
 * @return array
 */
function update_shadow_topic_information($db, array $shadow_topic_list, \phpbb\event\dispatcher $phpbb_dispatcher, array $rowset, array $topic_list, int $topics_count, $auth): array
{
// SQL array for obtaining shadow topics
	$sql_array = array(
		'SELECT' => 't.*',
		'FROM' => array(
			TOPICS_TABLE => 't'
		),
		'WHERE' => $db->sql_in_set('t.topic_id', array_keys($shadow_topic_list)),
	);

	/**
	 * Event to modify the SQL query before the shadowtopic data is retrieved
	 *
	 * @event core.viewforum_get_shadowtopic_data
	 * @var    array    sql_array        SQL array to get the data of any shadowtopics
	 * @since 3.1.0-a1
	 */
	$vars = array('sql_array');
	extract($phpbb_dispatcher->trigger_event('core.viewforum_get_shadowtopic_data', compact($vars)));

	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$orig_topic_id = $shadow_topic_list[$row['topic_id']];

		// If the shadow topic is already listed within the rowset (happens for active topics for example), then do not include it...
		if (isset($rowset[$row['topic_id']]))
		{
			// We need to remove any trace regarding this topic. :)
			unset($rowset[$orig_topic_id]);
			unset($topic_list[array_search($orig_topic_id, $topic_list)]);
			$topics_count--;

			continue;
		}

		// Do not include those topics the user has no permission to access
		if (!$auth->acl_gets('f_read', 'f_list_topics', $row['forum_id']))
		{
			// We need to remove any trace regarding this topic. :)
			unset($rowset[$orig_topic_id]);
			unset($topic_list[array_search($orig_topic_id, $topic_list)]);
			$topics_count--;

			continue;
		}

		// We want to retain some values
		$row = array_merge($row, array(
			'topic_moved_id' => $rowset[$orig_topic_id]['topic_moved_id'],
			'topic_status' => $rowset[$orig_topic_id]['topic_status'],
			'topic_type' => $rowset[$orig_topic_id]['topic_type'],
			'topic_title' => $rowset[$orig_topic_id]['topic_title'],
		));

		// Shadow topics are never reported
		$row['topic_reported'] = 0;

		$rowset[$orig_topic_id] = $row;
	}
	$db->sql_freeresult($result);
	return array($vars, $rowset, $topic_list, $topics_count);
}

/**
 * @param $auth
 * @param array $sql_array
 * @param $forum_id
 * @param $db
 * @param \phpbb\event\dispatcher $phpbb_dispatcher
 * @param \phpbb\content_visibility $phpbb_content_visibility
 * @param array $rowset
 * @param array $announcement_list
 * @param int $topics_count
 * @param array $global_announce_forums
 * @return array
 */
function get_announcements($auth, array $sql_array, $forum_id, $db, \phpbb\event\dispatcher $phpbb_dispatcher, \phpbb\content_visibility $phpbb_content_visibility, array $rowset, array $announcement_list, int $topics_count, array $global_announce_forums): array
{
// Get global announcement forums
	$g_forum_ary = $auth->acl_getf('f_read', true);
	$g_forum_ary = array_unique(array_keys($g_forum_ary));

	$sql_anounce_array['LEFT_JOIN'] = $sql_array['LEFT_JOIN'];
	$sql_anounce_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TABLE => 'f'), 'ON' => 'f.forum_id = t.forum_id');
	$sql_anounce_array['SELECT'] = $sql_array['SELECT'] . ', f.forum_name';

	// Obtain announcements ... removed sort ordering, sort by time in all cases
	$sql_ary = array(
		'SELECT' => $sql_anounce_array['SELECT'],
		'FROM' => $sql_array['FROM'],
		'LEFT_JOIN' => $sql_anounce_array['LEFT_JOIN'],

		'WHERE' => '(t.forum_id = ' . $forum_id . '
				AND t.topic_type = ' . POST_ANNOUNCE . ') OR
			(' . $db->sql_in_set('t.forum_id', $g_forum_ary, false, true) . '
				AND t.topic_type = ' . POST_GLOBAL . ')',

		'ORDER_BY' => 't.topic_time DESC',
	);

	/**
	 * Event to modify the SQL query before the announcement topic ids data is retrieved
	 *
	 * @event core.viewforum_get_announcement_topic_ids_data
	 * @var    array    forum_data            Data about the forum
	 * @var    array    g_forum_ary            Global announcement forums array
	 * @var    array    sql_anounce_array    SQL announcement array
	 * @var    array    sql_ary                SQL query array to get the announcement topic ids data
	 * @var    int        forum_id            The forum ID
	 *
	 * @since 3.1.10-RC1
	 */
	$vars = array(
		'forum_data',
		'g_forum_ary',
		'sql_anounce_array',
		'sql_ary',
		'forum_id',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewforum_get_announcement_topic_ids_data', compact($vars)));

	$sql = $db->sql_build_query('SELECT', $sql_ary);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (!$phpbb_content_visibility->is_visible('topic', $row['forum_id'], $row))
		{
			// Do not display announcements that are waiting for approval or soft deleted.
			continue;
		}

		$rowset[$row['topic_id']] = $row;
		$announcement_list[] = $row['topic_id'];

		if ($forum_id != $row['forum_id'])
		{
			$topics_count++;
			$global_announce_forums[] = $row['forum_id'];
		}
	}
	$db->sql_freeresult($result);
	return array($vars, $sql, $result, $row, $rowset, $announcement_list, $topics_count, $global_announce_forums);
}

/**
 * @param $forum_data
 * @param array $forum_tracking_info
 * @param $forum_id
 * @param array $global_announce_forums
 * @param $db
 * @param \phpbb\user $user
 * @return array
 */
function get_fourm_tracking_info_for_announcements($forum_data, array $forum_tracking_info, $forum_id, array $global_announce_forums, $db, \phpbb\user $user): array
{
	$forum_tracking_info[$forum_id] = $forum_data['mark_time'];

	if (!empty($global_announce_forums))
	{
		$sql = 'SELECT forum_id, mark_time
			FROM ' . FORUMS_TRACK_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $global_announce_forums) . '
				AND user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_tracking_info[$row['forum_id']] = $row['mark_time'];
		}
		$db->sql_freeresult($result);
	}
	return array($forum_tracking_info);
}

/**
 * @param array $sql_array
 * @param $db
 * @param array $topic_list
 * @param array $shadow_topic_list
 * @param array $rowset
 * @return array
 */
function query_topics(array $sql_array, $db, array $topic_list, array $shadow_topic_list, array $rowset): array
{
	// SQL array for obtaining topics/stickies
	$sql_array = array(
		'SELECT' => $sql_array['SELECT'],
		'FROM' => $sql_array['FROM'],
		'LEFT_JOIN' => $sql_array['LEFT_JOIN'],

		'WHERE' => $db->sql_in_set('t.topic_id', $topic_list),
	);

	// If store_reverse, then first obtain topics, then stickies, else the other way around...
	// Funnily enough you typically save one query if going from the last page to the middle (store_reverse) because
	// the number of stickies are not known
	$sql = $db->sql_build_query('SELECT', $sql_array);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['topic_status'] == ITEM_MOVED)
		{
			$shadow_topic_list[$row['topic_moved_id']] = $row['topic_id'];
		}

		$rowset[$row['topic_id']] = $row;
	}
	$db->sql_freeresult($result);
	return array($shadow_topic_list, $rowset);
}

/**
 * @param \phpbb\event\dispatcher $phpbb_dispatcher
 * @param \phpbb\content_visibility $phpbb_content_visibility
 * @param $forum_id
 * @param \phpbb\user $user
 * @param $config
 * @param bool $s_display_active
 * @param array $active_forum_ary
 * @return array
 */
function get_topic_query(\phpbb\event\dispatcher $phpbb_dispatcher, \phpbb\content_visibility $phpbb_content_visibility, $forum_id, \phpbb\user $user, $config, bool $s_display_active, array $active_forum_ary): array
{
	$sql_array = array(
		'SELECT' => 't.*',
		'FROM' => array(
			TOPICS_TABLE => 't'
		),
		'LEFT_JOIN' => array(),
	);

	/**
	 * Event to modify the SQL query before the topic data is retrieved
	 *
	 * It may also be used to override the above assigned template vars
	 *
	 * @event core.viewforum_get_topic_data
	 * @var    array    forum_data            Array with forum data
	 * @var    array    sql_array            The SQL array to get the data of all topics
	 * @var    int        forum_id            The forum_id whose topics are being listed
	 * @var    int        topics_count        The total number of topics for display
	 * @var    int        sort_days            The oldest topic displayable in elapsed days
	 * @var    string    sort_key            The sorting by. It is one of the first character of (in low case):
	 *                                    Author, Post time, Replies, Subject, Views
	 * @var    string    sort_dir            Either "a" for ascending or "d" for descending
	 * @since 3.1.0-a1
	 * @changed 3.1.0-RC4 Added forum_data var
	 * @changed 3.1.4-RC1 Added forum_id, topics_count, sort_days, sort_key and sort_dir vars
	 * @changed 3.1.9-RC1 Fix types of properties
	 */
	$vars = array(
		'forum_data',
		'sql_array',
		'forum_id',
		'topics_count',
		'sort_days',
		'sort_key',
		'sort_dir',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewforum_get_topic_data', compact($vars)));

	$sql_approved = ' AND ' . $phpbb_content_visibility->get_visibility_sql('topic', $forum_id, 't.');

	if ($user->data['is_registered'])
	{
		if ($config['load_db_track'])
		{
			$sql_array['LEFT_JOIN'][] = array('FROM' => array(TOPICS_POSTED_TABLE => 'tp'), 'ON' => 'tp.topic_id = t.topic_id AND tp.user_id = ' . $user->data['user_id']);
			$sql_array['SELECT'] .= ', tp.topic_posted';
		}

		if ($config['load_db_lastread'])
		{
			$sql_array['LEFT_JOIN'][] = array('FROM' => array(TOPICS_TRACK_TABLE => 'tt'), 'ON' => 'tt.topic_id = t.topic_id AND tt.user_id = ' . $user->data['user_id']);
			$sql_array['SELECT'] .= ', tt.mark_time';

			if ($s_display_active && count($active_forum_ary))
			{
				$sql_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TRACK_TABLE => 'ft'), 'ON' => 'ft.forum_id = t.forum_id AND ft.user_id = ' . $user->data['user_id']);
				$sql_array['SELECT'] .= ', ft.mark_time AS forum_mark_time';
			}
		}
	}
	return array($sql_array, $vars, $sql_approved);
}

/**
 * @param \phpbb\user $user
 * @param $auth
 * @param $forum_id
 * @param \phpbb\event\dispatcher $phpbb_dispatcher
 * @param $sort_days
 * @param $sort_key
 * @param $sort_dir
 * @param int $default_sort_days
 * @param string $default_sort_key
 * @param string $default_sort_dir
 * @return array
 */
function viewforum_figure_out_sorting(\phpbb\user $user, $auth, $forum_id, \phpbb\event\dispatcher $phpbb_dispatcher, $sort_days, $sort_key, $sort_dir, int $default_sort_days, string $default_sort_key, string $default_sort_dir): array
{
	$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);
	$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
	$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => array('t.topic_last_post_time', 't.topic_last_post_id'), 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted' : 't.topic_posts_approved'), 's' => 'LOWER(t.topic_title)', 'v' => 't.topic_views');

	/**
	 * Modify the topic ordering if needed
	 *
	 * @event core.viewforum_modify_topic_ordering
	 * @var array    sort_by_text    Topic ordering options
	 * @var array    sort_by_sql        Topic orderings options SQL equivalent
	 * @since 3.2.5-RC1
	 */
	$vars = array(
		'sort_by_text',
		'sort_by_sql',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_topic_ordering', compact($vars)));

	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);
	return array($sort_by_sql, $vars, $u_sort_param, $s_sort_dir, $s_sort_key, $s_limit_days, $limit_days, $sort_days, $sort_key, $sort_dir);
}

/**
 * @param $forum_id
 * @param $db
 */
function increment_forum_link_count($forum_id, $db): void
{
	$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET forum_posts_approved = forum_posts_approved + 1
			WHERE forum_id = ' . $forum_id;
	$db->sql_query($sql);
}
