<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session
$user->session_begin();
$auth->acl($user->data);

// Start initial var setup
$forum_id	= request_var('f', 0);
$mark_read	= request_var('mark', '');
$start		= request_var('start', 0);

$sort_days	= request_var('st', ((!empty($user->data['user_topic_show_days'])) ? $user->data['user_topic_show_days'] : 0));
$sort_key	= request_var('sk', ((!empty($user->data['user_topic_sortby_type'])) ? $user->data['user_topic_sortby_type'] : 't'));
$sort_dir	= request_var('sd', ((!empty($user->data['user_topic_sortby_dir'])) ? $user->data['user_topic_sortby_dir'] : 'd'));

// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
if (!$forum_id)
{
	trigger_error('NO_FORUM');
}

// Grab appropriate forum data
if ($config['load_db_lastread'] && $user->data['is_registered'])
{
	$sql_lastread = 'LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . '
		AND ft.forum_id = f.forum_id)';
	$lastread_select = ', ft.mark_time';
}
else
{
	$sql_lastread = $lastread_select = '';
	$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();
}

if ($user->data['is_registered'])
{
	$sql_from = ($sql_lastread) ? '((' . FORUMS_TABLE . ' f LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON (fw.forum_id = f.forum_id AND fw.user_id = ' . $user->data['user_id'] . ")) $sql_lastread)" : '(' . FORUMS_TABLE . ' f LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON (fw.forum_id = f.forum_id AND fw.user_id = ' . $user->data['user_id'] . '))';
	$lastread_select .= ', fw.notify_status';
}
else
{
	$sql_from = FORUMS_TABLE . ' f';
}

$sql = "SELECT f.* $lastread_select
	FROM $sql_from
	WHERE f.forum_id = $forum_id";
$result = $db->sql_query($sql);

if (!($forum_data = $db->sql_fetchrow($result)))
{
	trigger_error('NO_FORUM');
}
$db->sql_freeresult($result);

// Is this forum a link? ... User got here either because the
// number of clicks is being tracked or they guessed the id
if ($forum_data['forum_link'])
{
	// Does it have click tracking enabled?
	if ($forum_data['forum_flags'] & 1)
	{
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET forum_posts = forum_posts + 1
			WHERE forum_id = ' . $forum_id;
		$db->sql_query($sql);
	}

	redirect(str_replace('&amp;', '&', $forum_data['forum_link']));
}

// Configure style, language, etc.
$user->setup('viewforum', $forum_data['forum_style']);

// Forum is passworded ... check whether access has been granted to this
// user this session, if not show login box
if ($forum_data['forum_password'])
{
	login_forum_box($forum_data);
}

// Redirect to login upon emailed notification links
if (isset($_GET['e']) && !$user->data['is_registered'])
{
	login_box('', $user->lang['LOGIN_NOTIFY_FORUM']);
}

// Permissions check
if (!$auth->acl_get('f_read', $forum_id))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error($user->lang['SORRY_AUTH_READ']);
	}

	login_box('', $user->lang['LOGIN_VIEWFORUM']);
}

// Build navigation links
generate_forum_nav($forum_data);

// Forum Rules
generate_forum_rules($forum_data);

// Do we have subforums?
$active_forum_ary = $moderators = array();

if ($forum_data['left_id'] != $forum_data['right_id'] - 1)
{
	list($active_forum_ary, $moderators) = display_forums($forum_data, $config['load_moderators'], $config['load_moderators']);
}
else
{
	$template->assign_var('S_HAS_SUBFORUM', false);
	get_moderators($moderators, $forum_id);
}

// Output forum listing if it is postable
if ($forum_data['forum_type'] == FORUM_POST || ($forum_data['forum_flags'] & 16))
{
	// Handle marking posts
	if ($mark_read == 'topics')
	{
		markread('topics', $forum_id);

		meta_refresh(3, "viewforum.$phpEx$SID&amp;f=$forum_id");

		$message = $user->lang['TOPICS_MARKED'] . '<br /><br />' . sprintf($user->lang['RETURN_FORUM'], '<a href="' . "viewforum.$phpEx$SID&amp;f=$forum_id" . '">', '</a> ');
		trigger_error($message);
	}

	// Is a forum specific topic count required?
	if ($forum_data['forum_topics_per_page'])
	{
		$config['topics_per_page'] = $forum_data['forum_topics_per_page'];
	}

	// Do the forum Prune thang - cron type job ...
	if ($forum_data['prune_next'] < time() && $forum_data['enable_prune'])
	{
		$template->assign_var('RUN_CRON_TASK', '<img src="' . $phpbb_root_path . 'cron.' . $phpEx . '?cron_type=prune_forum&amp;f=' . $forum_id . '" width="1" height="1" />');
	}

	// Forum rules amd subscription info
	$s_watching_forum = $s_watching_forum_img = array();
	$s_watching_forum['link'] = $s_watching_forum['title'] = '';
	if (($config['email_enable'] || $config['jab_enable']) && $config['allow_forum_notify'] && $auth->acl_get('f_subscribe', $forum_id))
	{
		$notify_status = (isset($forum_data['notify_status'])) ? $forum_data['notify_status'] : NULL;
		watch_topic_forum('forum', $s_watching_forum, $s_watching_forum_img, $user->data['user_id'], $forum_id, $notify_status);
	}

	$s_forum_rules = '';
	gen_forum_auth_level('forum', $forum_id);

	// Topic ordering options
	$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);

	$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
	$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

	$sort_key = (!in_array($sort_key, array('a', 't', 'r', 's', 'v'))) ? 't' : $sort_key;

	$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
	gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

	// Limit topics to certain time frame, obtain correct topic count
	if ($sort_days)
	{
		$min_post_time = time() - ($sort_days * 86400);

		$sql = 'SELECT COUNT(topic_id) AS num_topics
			FROM ' . TOPICS_TABLE . "
			WHERE forum_id = $forum_id
				AND topic_type NOT IN (" . POST_ANNOUNCE . ', ' . POST_GLOBAL . ")
				AND topic_last_post_time >= $min_post_time
			" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND topic_approved = 1');
		$result = $db->sql_query($sql);

		if (isset($_POST['sort']))
		{
			$start = 0;
		}
		$topics_count = ($row = $db->sql_fetchrow($result)) ? $row['num_topics'] : 0;
		$sql_limit_time = "AND t.topic_last_post_time >= $min_post_time";
	}
	else
	{
		if ($auth->acl_get('m_approve', $forum_id))
		{
			$topics_count = ($forum_data['forum_topics_real']) ? $forum_data['forum_topics_real'] : 1;
		}
		else
		{
			$topics_count = ($forum_data['forum_topics']) ? $forum_data['forum_topics'] : 1;
		}

		$sql_limit_time = '';
	}

	// Basic pagewide vars
	$post_alt = ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->lang['FORUM_LOCKED'] : $user->lang['POST_NEW_TOPIC'];

	$template->assign_vars(array(
		'PAGINATION'	=> generate_pagination("{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id&amp;$u_sort_param", $topics_count, $config['topics_per_page'], $start),
		'PAGE_NUMBER'	=> on_page($topics_count, $config['topics_per_page'], $start),
		'TOTAL_TOPICS'	=> ($forum_data['forum_flags'] & 16) ? false : (($topics_count == 1) ? $user->lang['VIEW_FORUM_TOPIC'] : sprintf($user->lang['VIEW_FORUM_TOPICS'], $topics_count)),
		'MODERATORS'	=> (!empty($moderators[$forum_id])) ? implode(', ', $moderators[$forum_id]) : '',

		'POST_IMG' 				=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->img('btn_locked', $post_alt) : $user->img('btn_post', $post_alt),
		'NEWEST_POST_IMG'		=> $user->img('icon_post_newest', 'VIEW_NEWEST_POST'),
		'LAST_POST_IMG' 		=> $user->img('icon_post_latest', 'VIEW_LATEST_POST'),
		'FOLDER_IMG' 			=> $user->img('folder', 'NO_NEW_POSTS'),
		'FOLDER_NEW_IMG' 		=> $user->img('folder_new', 'NEW_POSTS'),
		'FOLDER_HOT_IMG' 		=> $user->img('folder_hot', 'NO_NEW_POSTS_HOT'),
		'FOLDER_HOT_NEW_IMG'	=> $user->img('folder_hot_new', 'NEW_POSTS_HOT'),
		'FOLDER_LOCKED_IMG' 	=> $user->img('folder_locked', 'NO_NEW_POSTS_LOCKED'),
		'FOLDER_LOCKED_NEW_IMG' => $user->img('folder_locked_new', 'NEW_POSTS_LOCKED'),
		'FOLDER_STICKY_IMG' 	=> $user->img('folder_sticky', 'POST_STICKY'),
		'FOLDER_STICKY_NEW_IMG' => $user->img('folder_sticky_new', 'POST_STICKY'),
		'FOLDER_ANNOUNCE_IMG' 	=> $user->img('folder_announce', 'POST_ANNOUNCEMENT'),
		'FOLDER_ANNOUNCE_NEW_IMG'=> $user->img('folder_announce_new', 'POST_ANNOUNCEMENT'),
		'FOLDER_MOVED_IMG'		=> $user->img('folder_moved', 'TOPIC_MOVED'),

		'REPORTED_IMG'			=> $user->img('icon_reported', 'TOPIC_REPORTED'),
		'UNAPPROVED_IMG'		=> $user->img('icon_unapproved', 'TOPIC_UNAPPROVED'),

		'GOTO_PAGE_IMG'			=> $user->img('icon_post', 'GOTO_PAGE'),

		'L_NO_TOPICS' 			=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->lang['POST_FORUM_LOCKED'] : $user->lang['NO_TOPICS'],

		'S_IS_POSTABLE'			=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'S_DISPLAY_ACTIVE'		=> ($forum_data['forum_type'] == FORUM_CAT && ($forum_data['forum_flags'] & 16)) ? true : false,
		'S_SELECT_SORT_DIR'		=> $s_sort_dir,
		'S_SELECT_SORT_KEY'		=> $s_sort_key,
		'S_SELECT_SORT_DAYS'	=> $s_limit_days,
		'S_TOPIC_ICONS'			=> ($forum_data['forum_type'] == FORUM_CAT && sizeof($active_forum_ary) && ($forum_data['forum_flags'] & 16)) ? max($active_forum_ary['enable_icons']) : (($forum_data['enable_icons']) ? true : false),
		'S_WATCH_FORUM_LINK'	=> $s_watching_forum['link'],
		'S_WATCH_FORUM_TITLE'	=> $s_watching_forum['title'],
		'S_FORUM_ACTION' 		=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id&amp;start=$start",
		'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('f_search', $forum_id)) ? true : false,
		'S_SEARCHBOX_ACTION'	=> "{$phpbb_root_path}search.$phpEx$SID&amp;search_forum[]=$forum_id",

		'U_MCP' 			=> ($auth->acl_gets('m_', $forum_id)) ? "{$phpbb_root_path}mcp.$phpEx?sid=$user->session_id&amp;f=$forum_id&amp;i=main&amp;mode=forum_view" : '',
		'U_POST_NEW_TOPIC'	=> "{$phpbb_root_path}posting.$phpEx$SID&amp;mode=post&amp;f=$forum_id",
		'U_VIEW_FORUM'		=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id&amp;$u_sort_param&amp;start=$start",
		'U_MARK_TOPICS' 	=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$forum_id&amp;mark=topics")
	);

	// Grab icons
	$icons = array();
	$cache->obtain_icons($icons);

	// Grab all topic data
	$rowset = $announcement_list = $topic_list = $global_announce_list = array();

	$sql_from = TOPICS_TABLE . ' t ';
	$sql_approved = ($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND t.topic_approved = 1';
	$sql_select = '';

	if ($user->data['is_registered'])
	{
		if ($config['load_db_track'])
		{
			$sql_from .= ' LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp ON (tp.user_id = ' . $user->data['user_id'] . ' 
				AND t.topic_id = tp.topic_id)';
			$sql_select .= ', tp.topic_posted';
		}

		if ($config['load_db_lastread'])
		{
			$sql_from .= ' LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . $user->data['user_id'] . '
				AND t.topic_id = tt.topic_id)';
			$sql_select .= ', tt.mark_time';
		}
	}

	if ($forum_data['forum_type'] == FORUM_POST)
	{
		// Obtain announcements ... removed sort ordering, sort by time in all cases
		$sql = "SELECT t.* $sql_select
			FROM $sql_from
			WHERE t.forum_id IN ($forum_id, 0)
				AND t.topic_type IN (" . POST_ANNOUNCE . ', ' . POST_GLOBAL . ')
			ORDER BY t.topic_time DESC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$rowset[$row['topic_id']] = $row;
			$announcement_list[] = $row['topic_id'];

			if ($row['topic_type'] == POST_GLOBAL)
			{
				$global_announce_list[$row['topic_id']] = true;
			}
		}
		$db->sql_freeresult($result);
	}

	// If the user is trying to reach late pages, start searching from the end
	$store_reverse = false;
	$sql_limit = $config['topics_per_page'];
	if ($start > $topics_count / 2)
	{
		$store_reverse = true;

		if ($start + $config['topics_per_page'] > $topics_count)
		{
			$sql_limit = min($config['topics_per_page'], max(1, $topics_count - $start));
		}

		// Select the sort order
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'ASC' : 'DESC');
		$sql_start = max(0, $topics_count - $sql_limit - $start);
	}
	else
	{
		// Select the sort order
		$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');
		$sql_start = $start;
	}

	// Obtain other topics
	$sql_where = ($forum_data['forum_type'] == FORUM_POST || !sizeof($active_forum_ary)) ? "= $forum_id" : 'IN (' . implode(', ', $active_forum_ary['forum_id']) . ')';

	$sql = "SELECT t.* $sql_select
		FROM $sql_from
		WHERE t.forum_id $sql_where
			AND t.topic_type NOT IN (" . POST_ANNOUNCE . ', ' . POST_GLOBAL . ")
			$sql_approved
			$sql_limit_time
		ORDER BY t.topic_type " . ((!$store_reverse) ? 'DESC' : 'ASC') . ', ' . $sql_sort_order;
	$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

	while ($row = $db->sql_fetchrow($result))
	{
		$rowset[$row['topic_id']] = $row;
		$topic_list[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	$topic_list = ($store_reverse) ? array_merge($announcement_list, array_reverse($topic_list)) : array_merge($announcement_list, $topic_list);
	$topic_tracking_info = $tracking_topics = array();

	// Okay, lets dump out the page ...
	if (sizeof($topic_list))
	{
		$mark_forum_read = true;

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$topic_tracking_info = get_topic_tracking($forum_id, $topic_list, $rowset, array($forum_id => $forum_data['mark_time']), $global_announce_list);
			$mark_time_forum = (!empty($forum_data['mark_time'])) ? $forum_data['mark_time'] : $user->data['user_lastmark'];
		}
		else
		{
			$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_list, $global_announce_list);

			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
			}
			$mark_time_forum = (isset($tracking_topics['f'][$forum_id])) ? base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'] : $user->data['user_lastmark'];
		}

		$s_type_switch = 0;
		foreach ($topic_list as $topic_id)
		{
			$row = &$rowset[$topic_id];

			// This will allow the style designer to output a different header
			// or even seperate the list of announcements from sticky and normal
			// topics
			$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

			// Replies
			$replies = ($auth->acl_get('m_approve', $forum_id)) ? $row['topic_replies_real'] : $row['topic_replies'];

			if ($row['topic_status'] == ITEM_MOVED)
			{
				$topic_id = $row['topic_moved_id'];
			}

			$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

			// Get folder img, topic status/type related informations
			$folder_img = $folder_alt = $topic_type = '';
			topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

			// Generate all the URIs ...
			$view_topic_url = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=" . (($row['forum_id']) ? $row['forum_id'] : $forum_id) . "&amp;t=$topic_id";

			// Send vars to template
			$template->assign_block_vars('topicrow', array(
				'FORUM_ID' 			=> $forum_id,
				'TOPIC_ID' 			=> $topic_id,
				'TOPIC_AUTHOR' 		=> topic_topic_author($row),
				'FIRST_POST_TIME' 	=> $user->format_date($row['topic_time']),
				'LAST_POST_TIME'	=> $user->format_date($row['topic_last_post_time']),
				'LAST_VIEW_TIME'	=> $user->format_date($row['topic_last_view_time']),
				'LAST_POST_AUTHOR' 	=> ($row['topic_last_poster_name'] != '') ? $row['topic_last_poster_name'] : $user->lang['GUEST'],
				'PAGINATION' 		=> topic_generate_pagination($replies, $view_topic_url),
				'REPLIES' 			=> $replies,
				'VIEWS' 			=> $row['topic_views'],
				'TOPIC_TITLE' 		=> censor_text($row['topic_title']),
				'TOPIC_TYPE' 		=> $topic_type,

				'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
				'TOPIC_FOLDER_IMG_SRC' 	=> $user->img($folder_img, $folder_alt, false, '', 'src'),
				'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
				'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
				'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
				'ATTACH_ICON_IMG'		=> ($auth->acl_gets('f_download', 'u_download', $forum_id) && $row['topic_attachment']) ? $user->img('icon_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',

				'S_TOPIC_TYPE'			=> $row['topic_type'],
				'S_USER_POSTED'			=> (isset($row['topic_posted']) && $row['topic_posted']) ? true : false,
				'S_UNREAD_TOPIC'		=> $unread_topic,

				'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_gets('m_', $forum_id)) ? true : false,
				'S_TOPIC_UNAPPROVED'	=> (!$row['topic_approved'] && $auth->acl_gets('m_approve', $forum_id)) ? true : false,

				'U_NEWEST_POST'		=> $view_topic_url . '&amp;view=unread#unread',
				'U_LAST_POST'		=> $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#' . $row['topic_last_post_id'],
				'U_LAST_POST_AUTHOR'=> ($row['topic_last_poster_id'] != ANONYMOUS && $row['topic_last_poster_id']) ? "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['topic_last_poster_id']}" : '',
				'U_VIEW_TOPIC'		=> $view_topic_url,
				'U_MCP_REPORT'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;mode=reports&amp;t=$topic_id",
				'U_MCP_QUEUE'		=> "{$phpbb_root_path}mcp.$phpEx?sid={$user->session_id}&amp;i=queue&amp;mode=approve_details&amp;t=$topic_id",

				'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test)
			);

			$s_type_switch = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

			if ($unread_topic)
			{
				$mark_forum_read = false;
			}

			unset($rowset[$topic_id]);
		}
	}

	// This is rather a fudge but it's the best I can think of without requiring information
	// on all topics (as we do in 2.0.x). It looks for unread or new topics, if it doesn't find
	// any it updates the forum last read cookie. This requires that the user visit the forum
	// after reading a topic
	if ($forum_data['forum_type'] == FORUM_POST && sizeof($topic_list) && $mark_forum_read)
	{
		// Make sure there are not additional topics unread
		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			if ($mark_time_forum >= $forum_data['forum_last_post_time'])
			{
				$row = true;
			}
			else
			{
				$sql = 'SELECT t.forum_id FROM ' . TOPICS_TABLE . ' t
					LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . $user->data['user_id'] . ' AND tt.topic_id = t.topic_id)
					WHERE t.forum_id = ' . $forum_id . '
						AND t.topic_last_post_time > ' . $mark_time_forum . '
						AND tt.topic_id IS NULL
					GROUP BY t.forum_id';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
			}
		}
		else
		{
			// Get information from cookie
			$row = false;
			
			if (!isset($tracking_topics['tf'][$forum_id]))
			{
				// We do not need to mark read, this has happened before. Therefore setting this to true
				$row = true;
			}
			else
			{
				$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . '
					WHERE forum_id = ' . $forum_id . '
						AND topic_last_post_time > ' . $mark_time_forum;
				$result = $db->sql_query($sql);
				
				$check_forum = $tracking_topics['tf'][$forum_id];
				$unread = false;
				while ($row = $db->sql_fetchrow($result))
				{
					if (!in_array(base_convert($row['topic_id'], 10, 36), array_keys($check_forum)))
					{
						$unread = true;
						break;
					}
				}
				$db->sql_freeresult($result);

				$row = $unread;
			}
		}

		if (!$row)
		{
			markread('topics', $forum_id);
		}
	}
}

// Dump out the page header and load viewforum template
page_header($user->lang['VIEW_FORUM'] . ' - ' . $forum_data['forum_name']);

$template->set_filenames(array(
	'body' => 'viewforum_body.html')
);
make_jumpbox("viewforum.$phpEx$SID", $forum_id);

page_footer();

?>