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
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session
$user->session_begin();
$auth->acl($user->data);

// Start initial var setup
$forum_id	= $request->variable('f', 0);
$mark_read	= $request->variable('mark', '');
$start		= $request->variable('start', 0);

$default_sort_days	= (!empty($user->data['user_topic_show_days'])) ? $user->data['user_topic_show_days'] : 0;
$default_sort_key	= (!empty($user->data['user_topic_sortby_type'])) ? $user->data['user_topic_sortby_type'] : 't';
$default_sort_dir	= (!empty($user->data['user_topic_sortby_dir'])) ? $user->data['user_topic_sortby_dir'] : 'd';

$sort_days	= $request->variable('st', $default_sort_days);
$sort_key	= $request->variable('sk', $default_sort_key);
$sort_dir	= $request->variable('sd', $default_sort_dir);

/* @var $pagination \phpbb\pagination */
$pagination = $phpbb_container->get('pagination');

// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
if (!$forum_id)
{
	trigger_error('NO_FORUM');
}

$sql_from = FORUMS_TABLE . ' f';
$lastread_select = '';

// Grab appropriate forum data
if ($config['load_db_lastread'] && $user->data['is_registered'])
{
	$sql_from .= ' LEFT JOIN ' . FORUMS_TRACK_TABLE . ' ft ON (ft.user_id = ' . $user->data['user_id'] . '
		AND ft.forum_id = f.forum_id)';
	$lastread_select .= ', ft.mark_time';
}

if ($user->data['is_registered'])
{
	$sql_from .= ' LEFT JOIN ' . FORUMS_WATCH_TABLE . ' fw ON (fw.forum_id = f.forum_id AND fw.user_id = ' . $user->data['user_id'] . ')';
	$lastread_select .= ', fw.notify_status';
}

$sql = "SELECT f.* $lastread_select
	FROM $sql_from
	WHERE f.forum_id = $forum_id";
$result = $db->sql_query($sql);
$forum_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$forum_data)
{
	trigger_error('NO_FORUM');
}


// Configure style, language, etc.
$user->setup('viewforum', $forum_data['forum_style']);

// Redirect to login upon emailed notification links
if (isset($_GET['e']) && !$user->data['is_registered'])
{
	login_box('', $user->lang['LOGIN_NOTIFY_FORUM']);
}

// Permissions check
if (!$auth->acl_gets('f_list', 'f_list_topics', 'f_read', $forum_id) || ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'] && !$auth->acl_get('f_read', $forum_id)))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		send_status_line(403, 'Forbidden');
		trigger_error('SORRY_AUTH_READ');
	}

	login_box('', $user->lang['LOGIN_VIEWFORUM']);
}

// Forum is passworded ... check whether access has been granted to this
// user this session, if not show login box
if ($forum_data['forum_password'])
{
	login_forum_box($forum_data);
}

// Is this forum a link? ... User got here either because the
// number of clicks is being tracked or they guessed the id
if ($forum_data['forum_type'] == FORUM_LINK && $forum_data['forum_link'])
{
	// Does it have click tracking enabled?
	if ($forum_data['forum_flags'] & FORUM_FLAG_LINK_TRACK)
	{
		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET forum_posts_approved = forum_posts_approved + 1
			WHERE forum_id = ' . $forum_id;
		$db->sql_query($sql);
	}

	// We redirect to the url. The third parameter indicates that external redirects are allowed.
	redirect($forum_data['forum_link'], false, true);
	return;
}

// Build navigation links
generate_forum_nav($forum_data);

// Forum Rules
if ($auth->acl_get('f_read', $forum_id))
{
	generate_forum_rules($forum_data);
}

// Do we have subforums?
$active_forum_ary = $moderators = array();

if ($forum_data['left_id'] != $forum_data['right_id'] - 1)
{
	list($active_forum_ary, $moderators) = display_forums($forum_data, $config['load_moderators'], $config['load_moderators']);
}
else
{
	$template->assign_var('S_HAS_SUBFORUM', false);
	if ($config['load_moderators'])
	{
		get_moderators($moderators, $forum_id);
	}
}

// Is a forum specific topic count required?
if ($forum_data['forum_topics_per_page'])
{
	$config['topics_per_page'] = $forum_data['forum_topics_per_page'];
}

/* @var $phpbb_content_visibility \phpbb\content_visibility */
$phpbb_content_visibility = $phpbb_container->get('content.visibility');

// Dump out the page header and load viewforum template
$topics_count = $phpbb_content_visibility->get_count('forum_topics', $forum_data, $forum_id);
$start = $pagination->validate_start($start, $config['topics_per_page'], $topics_count);

$page_title = $forum_data['forum_name'] . ($start ? ' - ' . $user->lang('PAGE_TITLE_NUMBER', $pagination->get_on_page($config['topics_per_page'], $start)) : '');

/**
* You can use this event to modify the page title of the viewforum page
*
* @event core.viewforum_modify_page_title
* @var	string	page_title		Title of the viewforum page
* @var	array	forum_data		Array with forum data
* @var	int		forum_id		The forum ID
* @var	int		start			Start offset used to calculate the page
* @since 3.2.2-RC1
*/
$vars = array('page_title', 'forum_data', 'forum_id', 'start');
extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_page_title', compact($vars)));

page_header($page_title, true, $forum_id);

$template->set_filenames(array(
	'body' => 'viewforum_body.html')
);

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"), $forum_id);

$template->assign_vars(array(
	'U_VIEW_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")),
));

// Not postable forum or showing active topics?
if (!($forum_data['forum_type'] == FORUM_POST || (($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) && $forum_data['forum_type'] == FORUM_CAT)))
{
	page_footer();
}

// Ok, if someone has only list-access, we only display the forum list.
// We also make this circumstance available to the template in case we want to display a notice. ;)
if (!$auth->acl_gets('f_read', 'f_list_topics', $forum_id))
{
	$template->assign_vars(array(
		'S_NO_READ_ACCESS'		=> true,
	));

	page_footer();
}

// Handle marking posts
if ($mark_read == 'topics')
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

// Forum rules and subscription info
$s_watching_forum = array(
	'link'			=> '',
	'link_toggle'	=> '',
	'title'			=> '',
	'title_toggle'	=> '',
	'is_watching'	=> false,
);

if ($config['allow_forum_notify'] && $forum_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_subscribe', $forum_id) || $user->data['user_id'] == ANONYMOUS))
{
	$notify_status = (isset($forum_data['notify_status'])) ? $forum_data['notify_status'] : NULL;
	watch_topic_forum('forum', $s_watching_forum, $user->data['user_id'], $forum_id, 0, $notify_status, $start, $forum_data['forum_name']);
}

$s_forum_rules = '';
gen_forum_auth_level('forum', $forum_id, $forum_data['forum_status']);

// Topic ordering options
$limit_days = array(0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => array('t.topic_last_post_time', 't.topic_last_post_id'), 'r' => (($auth->acl_get('m_approve', $forum_id)) ? 't.topic_posts_approved + t.topic_posts_unapproved + t.topic_posts_softdeleted' : 't.topic_posts_approved'), 's' => 'LOWER(t.topic_title)', 'v' => 't.topic_views');

/**
 * Modify the topic ordering if needed
 *
 * @event core.viewforum_modify_topic_ordering
 * @var array	sort_by_text	Topic ordering options
 * @var array	sort_by_sql		Topic orderings options SQL equivalent
 * @since 3.2.5-RC1
 */
$vars = array(
	'sort_by_text',
	'sort_by_sql',
);
extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_topic_ordering', compact($vars)));

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

// Limit topics to certain time frame, obtain correct topic count
if ($sort_days)
{
	$min_post_time = time() - ($sort_days * 86400);

	$sql_array = array(
		'SELECT'	=> 'COUNT(t.topic_id) AS num_topics',
		'FROM'		=> array(
			TOPICS_TABLE	=> 't',
		),
		'WHERE'		=> 't.forum_id = ' . $forum_id . '
			AND (t.topic_last_post_time >= ' . $min_post_time . '
				OR t.topic_type = ' . POST_ANNOUNCE . '
				OR t.topic_type = ' . POST_GLOBAL . ')
			AND ' . $phpbb_content_visibility->get_visibility_sql('topic', $forum_id, 't.'),
	);

	/**
	* Modify the sort data SQL query for getting additional fields if needed
	*
	* @event core.viewforum_modify_sort_data_sql
	* @var int		forum_id		The forum_id whose topics are being listed
	* @var int		start			Variable containing start for pagination
	* @var int		sort_days		The oldest topic displayable in elapsed days
	* @var string	sort_key		The sorting by. It is one of the first character of (in low case):
	*								Author, Post time, Replies, Subject, Views
	* @var string	sort_dir		Either "a" for ascending or "d" for descending
	* @var array	sql_array		The SQL array to get the data of all topics
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
}
else
{
	$sql_limit_time = '';
}

// Basic pagewide vars
$post_alt = ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->lang['FORUM_LOCKED'] : $user->lang['POST_NEW_TOPIC'];

// Display active topics?
$s_display_active = ($forum_data['forum_type'] == FORUM_CAT && ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS)) ? true : false;

$s_search_hidden_fields = array('fid' => array($forum_id));
if ($_SID)
{
	$s_search_hidden_fields['sid'] = $_SID;
}

if (!empty($_EXTRA_URL))
{
	foreach ($_EXTRA_URL as $url_param)
	{
		$url_param = explode('=', $url_param, 2);
		$s_search_hidden_fields[$url_param[0]] = $url_param[1];
	}
}

$template->assign_vars(array(
	'MODERATORS'	=> (!empty($moderators[$forum_id])) ? implode($user->lang['COMMA_SEPARATOR'], $moderators[$forum_id]) : '',

	'POST_IMG'					=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', $post_alt) : $user->img('button_topic_new', $post_alt),
	'NEWEST_POST_IMG'			=> $user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
	'LAST_POST_IMG'				=> $user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
	'FOLDER_IMG'				=> $user->img('topic_read', 'NO_UNREAD_POSTS'),
	'FOLDER_UNREAD_IMG'			=> $user->img('topic_unread', 'UNREAD_POSTS'),
	'FOLDER_HOT_IMG'			=> $user->img('topic_read_hot', 'NO_UNREAD_POSTS_HOT'),
	'FOLDER_HOT_UNREAD_IMG'		=> $user->img('topic_unread_hot', 'UNREAD_POSTS_HOT'),
	'FOLDER_LOCKED_IMG'			=> $user->img('topic_read_locked', 'NO_UNREAD_POSTS_LOCKED'),
	'FOLDER_LOCKED_UNREAD_IMG'	=> $user->img('topic_unread_locked', 'UNREAD_POSTS_LOCKED'),
	'FOLDER_STICKY_IMG'			=> $user->img('sticky_read', 'POST_STICKY'),
	'FOLDER_STICKY_UNREAD_IMG'	=> $user->img('sticky_unread', 'POST_STICKY'),
	'FOLDER_ANNOUNCE_IMG'		=> $user->img('announce_read', 'POST_ANNOUNCEMENT'),
	'FOLDER_ANNOUNCE_UNREAD_IMG'=> $user->img('announce_unread', 'POST_ANNOUNCEMENT'),
	'FOLDER_MOVED_IMG'			=> $user->img('topic_moved', 'TOPIC_MOVED'),
	'REPORTED_IMG'				=> $user->img('icon_topic_reported', 'TOPIC_REPORTED'),
	'UNAPPROVED_IMG'			=> $user->img('icon_topic_unapproved', 'TOPIC_UNAPPROVED'),
	'DELETED_IMG'				=> $user->img('icon_topic_deleted', 'TOPIC_DELETED'),
	'POLL_IMG'					=> $user->img('icon_topic_poll', 'TOPIC_POLL'),
	'GOTO_PAGE_IMG'				=> $user->img('icon_post_target', 'GOTO_PAGE'),

	'L_NO_TOPICS' 			=> ($forum_data['forum_status'] == ITEM_LOCKED) ? $user->lang['POST_FORUM_LOCKED'] : $user->lang['NO_TOPICS'],

	'S_DISPLAY_POST_INFO'	=> ($forum_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS)) ? true : false,

	'S_IS_POSTABLE'			=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
	'S_USER_CAN_POST'		=> ($auth->acl_get('f_post', $forum_id)) ? true : false,
	'S_DISPLAY_ACTIVE'		=> $s_display_active,
	'S_SELECT_SORT_DIR'		=> $s_sort_dir,
	'S_SELECT_SORT_KEY'		=> $s_sort_key,
	'S_SELECT_SORT_DAYS'	=> $s_limit_days,
	'S_TOPIC_ICONS'			=> ($s_display_active && count($active_forum_ary)) ? max($active_forum_ary['enable_icons']) : (($forum_data['enable_icons']) ? true : false),
	'U_WATCH_FORUM_LINK'	=> $s_watching_forum['link'],
	'U_WATCH_FORUM_TOGGLE'	=> $s_watching_forum['link_toggle'],
	'S_WATCH_FORUM_TITLE'	=> $s_watching_forum['title'],
	'S_WATCH_FORUM_TOGGLE'	=> $s_watching_forum['title_toggle'],
	'S_WATCHING_FORUM'		=> $s_watching_forum['is_watching'],
	'S_FORUM_ACTION'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . (($start == 0) ? '' : "&amp;start=$start")),
	'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id) && $config['load_search']) ? true : false,
	'S_SEARCHBOX_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx"),
	'S_SEARCH_LOCAL_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),
	'S_SINGLE_MODERATOR'	=> (!empty($moderators[$forum_id]) && count($moderators[$forum_id]) > 1) ? false : true,
	'S_IS_LOCKED'			=> ($forum_data['forum_status'] == ITEM_LOCKED) ? true : false,
	'S_VIEWFORUM'			=> true,

	'U_MCP'				=> ($auth->acl_get('m_', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "f=$forum_id&amp;i=main&amp;mode=forum_view", true, $user->session_id) : '',
	'U_POST_NEW_TOPIC'	=> ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", 'mode=post&amp;f=' . $forum_id) : '',
	'U_VIEW_FORUM'		=> append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($start == 0) ? '' : "&amp;start=$start")),
	'U_CANONICAL'		=> generate_board_url() . '/' . append_sid("viewforum.$phpEx", "f=$forum_id" . (($start) ? "&amp;start=$start" : ''), true, ''),
	'U_MARK_TOPICS'		=> ($user->data['is_registered'] || $config['load_anon_lastread']) ? append_sid("{$phpbb_root_path}viewforum.$phpEx", 'hash=' . generate_link_hash('global') . "&amp;f=$forum_id&amp;mark=topics&amp;mark_time=" . time()) : '',
));

// Grab icons
$icons = $cache->obtain_icons();

// Grab all topic data
$rowset = $announcement_list = $topic_list = $global_announce_forums = array();

$sql_array = array(
	'SELECT'	=> 't.*',
	'FROM'		=> array(
		TOPICS_TABLE		=> 't'
	),
	'LEFT_JOIN'	=> array(),
);

/**
* Event to modify the SQL query before the topic data is retrieved
*
* It may also be used to override the above assigned template vars
*
* @event core.viewforum_get_topic_data
* @var	array	forum_data			Array with forum data
* @var	array	sql_array			The SQL array to get the data of all topics
* @var	int		forum_id			The forum_id whose topics are being listed
* @var	int		topics_count		The total number of topics for display
* @var	int		sort_days			The oldest topic displayable in elapsed days
* @var	string	sort_key			The sorting by. It is one of the first character of (in low case):
*									Author, Post time, Replies, Subject, Views
* @var	string	sort_dir			Either "a" for ascending or "d" for descending
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

if ($forum_data['forum_type'] == FORUM_POST)
{
	// Get global announcement forums
	$g_forum_ary = $auth->acl_getf('f_read', true);
	$g_forum_ary = array_unique(array_keys($g_forum_ary));

	$sql_anounce_array['LEFT_JOIN'] = $sql_array['LEFT_JOIN'];
	$sql_anounce_array['LEFT_JOIN'][] = array('FROM' => array(FORUMS_TABLE => 'f'), 'ON' => 'f.forum_id = t.forum_id');
	$sql_anounce_array['SELECT'] = $sql_array['SELECT'] . ', f.forum_name';

	// Obtain announcements ... removed sort ordering, sort by time in all cases
	$sql_ary = array(
		'SELECT'	=> $sql_anounce_array['SELECT'],
		'FROM'		=> $sql_array['FROM'],
		'LEFT_JOIN'	=> $sql_anounce_array['LEFT_JOIN'],

		'WHERE'		=> '(t.forum_id = ' . $forum_id . '
				AND t.topic_type = ' . POST_ANNOUNCE . ') OR
			(' . $db->sql_in_set('t.forum_id', $g_forum_ary, false, true) . '
				AND t.topic_type = ' . POST_GLOBAL . ')',

		'ORDER_BY'	=> 't.topic_time DESC',
	);

	/**
	* Event to modify the SQL query before the announcement topic ids data is retrieved
	*
	* @event core.viewforum_get_announcement_topic_ids_data
	* @var	array	forum_data			Data about the forum
	* @var	array	g_forum_ary			Global announcement forums array
	* @var	array	sql_anounce_array	SQL announcement array
	* @var	array	sql_ary				SQL query array to get the announcement topic ids data
	* @var	int		forum_id			The forum ID
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
}

$forum_tracking_info = array();

if ($user->data['is_registered'] && $config['load_db_lastread'])
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
}

// If the user is trying to reach late pages, start searching from the end
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

/**
 * Modify the topics sort ordering if needed
 *
 * @event core.viewforum_modify_sort_direction
 * @var string	direction	Topics sort order
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
	'SELECT'	=> 't.topic_id',
	'FROM'		=> array(
		TOPICS_TABLE => 't',
	),
	'WHERE'		=> "$sql_where
		AND t.topic_type IN (" . POST_NORMAL . ', ' . POST_STICKY . ")
		$sql_approved
		$sql_limit_time",
	'ORDER_BY'	=> 't.topic_type ' . ((!$store_reverse) ? 'DESC' : 'ASC') . ', ' . $sql_sort_order,
);

/**
* Event to modify the SQL query before the topic ids data is retrieved
*
* @event core.viewforum_get_topic_ids_data
* @var	array	forum_data		Data about the forum
* @var	array	sql_ary			SQL query array to get the topic ids data
* @var	string	sql_approved	Topic visibility SQL string
* @var	int		sql_limit		Number of records to select
* @var	string	sql_limit_time	SQL string to limit topic_last_post_time data
* @var	array	sql_sort_order	SQL sorting string
* @var	int		sql_start		Offset point to start selection from
* @var	string	sql_where		SQL WHERE clause string
* @var	bool	store_reverse	Flag indicating if we select from the late pages
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

// For storing shadow topics
$shadow_topic_list = array();

if (count($topic_list))
{
	// SQL array for obtaining topics/stickies
	$sql_array = array(
		'SELECT'		=> $sql_array['SELECT'],
		'FROM'			=> $sql_array['FROM'],
		'LEFT_JOIN'		=> $sql_array['LEFT_JOIN'],
		'WHERE'			=> $db->sql_in_set('t.topic_id', $topic_list),
	);

	/**
	* Event to modify the SQL query before obtaining topics/stickies
	*
	* @event core.viewforum_modify_topic_list_sql
	* @var	int		forum_id			The forum ID
	* @var	array	forum_data			Data about the forum
	* @var	array	topic_list			Topic ids array
	* @var	array	sql_array			SQL query array for obtaining topics/stickies
	*
	* @since 3.2.10-RC1
	* @since 3.3.1-RC1
	*/
	$vars = [
		'forum_id',
		'forum_data',
		'topic_list',
		'sql_array',
	];
	extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_topic_list_sql', compact($vars)));

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
}

// If we have some shadow topics, update the rowset to reflect their topic information
if (count($shadow_topic_list))
{
	// SQL array for obtaining shadow topics
	$sql_array = array(
		'SELECT'	=> 't.*',
		'FROM'		=> array(
			TOPICS_TABLE		=> 't'
		),
		'WHERE'		=> $db->sql_in_set('t.topic_id', array_keys($shadow_topic_list)),
	);

	/**
	* Event to modify the SQL query before the shadowtopic data is retrieved
	*
	* @event core.viewforum_get_shadowtopic_data
	* @var	array	sql_array		SQL array to get the data of any shadowtopics
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
			'topic_moved_id'	=> $rowset[$orig_topic_id]['topic_moved_id'],
			'topic_status'		=> $rowset[$orig_topic_id]['topic_status'],
			'topic_type'		=> $rowset[$orig_topic_id]['topic_type'],
			'topic_title'		=> $rowset[$orig_topic_id]['topic_title'],
		));

		// Shadow topics are never reported
		$row['topic_reported'] = 0;

		$rowset[$orig_topic_id] = $row;
	}
	$db->sql_freeresult($result);
}
unset($shadow_topic_list);

// Ok, adjust topics count for active topics list
if ($s_display_active)
{
	$topics_count = 1;
}

// We need to remove the global announcements from the forums total topic count,
// otherwise the number is different from the one on the forum list
$total_topic_count = $topics_count - count($announcement_list);

$base_url = append_sid("{$phpbb_root_path}viewforum.$phpEx", "f=$forum_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : ''));
$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_topic_count, $config['topics_per_page'], $start);

$template->assign_vars(array(
	'TOTAL_TOPICS'	=> ($s_display_active) ? false : $user->lang('VIEW_FORUM_TOPICS', (int) $total_topic_count),
));

$topic_list = ($store_reverse) ? array_merge($announcement_list, array_reverse($topic_list)) : array_merge($announcement_list, $topic_list);
$topic_tracking_info = $tracking_topics = array();

/**
* Modify topics data before we display the viewforum page
*
* @event core.viewforum_modify_topics_data
* @var	array	topic_list			Array with current viewforum page topic ids
* @var	array	rowset				Array with topics data (in topic_id => topic_data format)
* @var	int		total_topic_count	Forum's total topic count
* @var	int		forum_id			Forum identifier
* @since 3.1.0-b3
* @changed 3.1.11-RC1 Added forum_id
*/
$vars = array('topic_list', 'rowset', 'total_topic_count', 'forum_id');
extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_topics_data', compact($vars)));

// Okay, lets dump out the page ...
if (count($topic_list))
{
	$mark_forum_read = true;
	$mark_time_forum = 0;

	// Generate topic forum list...
	$topic_forum_list = array();
	foreach ($rowset as $t_id => $row)
	{
		if (isset($forum_tracking_info[$row['forum_id']]))
		{
			$row['forum_mark_time'] = $forum_tracking_info[$row['forum_id']];
		}

		$topic_forum_list[$row['forum_id']]['forum_mark_time'] = ($config['load_db_lastread'] && $user->data['is_registered'] && isset($row['forum_mark_time'])) ? $row['forum_mark_time'] : 0;
		$topic_forum_list[$row['forum_id']]['topics'][] = (int) $t_id;
	}

	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		foreach ($topic_forum_list as $f_id => $topic_row)
		{
			$topic_tracking_info += get_topic_tracking($f_id, $topic_row['topics'], $rowset, array($f_id => $topic_row['forum_mark_time']));
		}
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		foreach ($topic_forum_list as $f_id => $topic_row)
		{
			$topic_tracking_info += get_complete_topic_tracking($f_id, $topic_row['topics']);
		}
	}

	unset($topic_forum_list);

	if (!$s_display_active)
	{
		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			$mark_time_forum = (!empty($forum_data['mark_time'])) ? $forum_data['mark_time'] : $user->data['user_lastmark'];
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			if (!$user->data['is_registered'])
			{
				$user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int) (base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate']) : 0;
			}
			$mark_time_forum = (isset($tracking_topics['f'][$forum_id])) ? (int) (base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate']) : $user->data['user_lastmark'];
		}
	}

	$s_type_switch = 0;
	foreach ($topic_list as $topic_id)
	{
		$row = &$rowset[$topic_id];

		$topic_forum_id = ($row['forum_id']) ? (int) $row['forum_id'] : $forum_id;

		// This will allow the style designer to output a different header
		// or even separate the list of announcements from sticky and normal topics
		$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

		// Replies
		$replies = $phpbb_content_visibility->get_count('topic_posts', $row, $topic_forum_id) - 1;

		if ($row['topic_status'] == ITEM_MOVED)
		{
			$topic_id = $row['topic_moved_id'];
			$unread_topic = false;
		}
		else
		{
			$unread_topic = (isset($topic_tracking_info[$topic_id]) && $row['topic_last_post_time'] > $topic_tracking_info[$topic_id]) ? true : false;
		}

		// Get folder img, topic status/type related information
		$folder_img = $folder_alt = $topic_type = '';
		topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

		// Generate all the URIs ...
		$view_topic_url_params = 'f=' . $row['forum_id'] . '&amp;t=' . $topic_id;
		$view_topic_url = $auth->acl_get('f_read', $forum_id) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params) : false;

		$topic_unapproved = (($row['topic_visibility'] == ITEM_UNAPPROVED || $row['topic_visibility'] == ITEM_REAPPROVE) && $auth->acl_get('m_approve', $row['forum_id']));
		$posts_unapproved = ($row['topic_visibility'] == ITEM_APPROVED && $row['topic_posts_unapproved'] && $auth->acl_get('m_approve', $row['forum_id']));
		$topic_deleted = $row['topic_visibility'] == ITEM_DELETED;

		$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $user->session_id) : '';
		$u_mcp_queue = (!$u_mcp_queue && $topic_deleted) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=deleted_topics&amp;t=' . $topic_id, true, $user->session_id) : $u_mcp_queue;

		// Send vars to template
		$topic_row = array(
			'FORUM_ID'					=> $row['forum_id'],
			'TOPIC_ID'					=> $topic_id,
			'TOPIC_AUTHOR'				=> get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_COLOUR'		=> get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'TOPIC_AUTHOR_FULL'			=> get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'FIRST_POST_TIME'			=> $user->format_date($row['topic_time']),
			'FIRST_POST_TIME_RFC3339'	=> gmdate(DATE_RFC3339, $row['topic_time']),
			'LAST_POST_SUBJECT'			=> censor_text($row['topic_last_post_subject']),
			'LAST_POST_TIME'			=> $user->format_date($row['topic_last_post_time']),
			'LAST_POST_TIME_RFC3339'	=> gmdate(DATE_RFC3339, $row['topic_last_post_time']),
			'LAST_VIEW_TIME'			=> $user->format_date($row['topic_last_view_time']),
			'LAST_VIEW_TIME_RFC3339'	=> gmdate(DATE_RFC3339, $row['topic_last_view_time']),
			'LAST_POST_AUTHOR'			=> get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_COLOUR'	=> get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'LAST_POST_AUTHOR_FULL'		=> get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

			'REPLIES'			=> $replies,
			'VIEWS'				=> $row['topic_views'],
			'TOPIC_TITLE'		=> censor_text($row['topic_title']),
			'TOPIC_TYPE'		=> $topic_type,
			'FORUM_NAME'		=> (isset($row['forum_name'])) ? $row['forum_name'] : $forum_data['forum_name'],

			'TOPIC_IMG_STYLE'		=> $folder_img,
			'TOPIC_FOLDER_IMG'		=> $user->img($folder_img, $folder_alt),
			'TOPIC_FOLDER_IMG_ALT'	=> $user->lang[$folder_alt],

			'TOPIC_ICON_IMG'		=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
			'TOPIC_ICON_IMG_WIDTH'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
			'TOPIC_ICON_IMG_HEIGHT'	=> (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
			'ATTACH_ICON_IMG'		=> ($auth->acl_get('u_download') && $auth->acl_get('f_download', $row['forum_id']) && $row['topic_attachment']) ? $user->img('icon_topic_attach', $user->lang['TOTAL_ATTACHMENTS']) : '',
			'UNAPPROVED_IMG'		=> ($topic_unapproved || $posts_unapproved) ? $user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',

			'S_TOPIC_TYPE'			=> $row['topic_type'],
			'S_USER_POSTED'			=> (isset($row['topic_posted']) && $row['topic_posted']) ? true : false,
			'S_UNREAD_TOPIC'		=> $unread_topic,
			'S_TOPIC_REPORTED'		=> (!empty($row['topic_reported']) && $auth->acl_get('m_report', $row['forum_id'])) ? true : false,
			'S_TOPIC_UNAPPROVED'	=> $topic_unapproved,
			'S_POSTS_UNAPPROVED'	=> $posts_unapproved,
			'S_TOPIC_DELETED'		=> $topic_deleted,
			'S_HAS_POLL'			=> ($row['poll_start']) ? true : false,
			'S_POST_ANNOUNCE'		=> ($row['topic_type'] == POST_ANNOUNCE) ? true : false,
			'S_POST_GLOBAL'			=> ($row['topic_type'] == POST_GLOBAL) ? true : false,
			'S_POST_STICKY'			=> ($row['topic_type'] == POST_STICKY) ? true : false,
			'S_TOPIC_LOCKED'		=> ($row['topic_status'] == ITEM_LOCKED) ? true : false,
			'S_TOPIC_MOVED'			=> ($row['topic_status'] == ITEM_MOVED) ? true : false,

			'U_NEWEST_POST'			=> $auth->acl_get('f_read', $forum_id) ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params . '&amp;view=unread') . '#unread' : false,
			'U_LAST_POST'			=> $auth->acl_get('f_read', $forum_id)  ? append_sid("{$phpbb_root_path}viewtopic.$phpEx", $view_topic_url_params . '&amp;p=' . $row['topic_last_post_id']) . '#p' . $row['topic_last_post_id'] : false,
			'U_LAST_POST_AUTHOR'	=> get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
			'U_TOPIC_AUTHOR'		=> get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
			'U_VIEW_TOPIC'			=> $view_topic_url,
			'U_VIEW_FORUM'			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $row['forum_id']),
			'U_MCP_REPORT'			=> append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=reports&amp;f=' . $row['forum_id'] . '&amp;t=' . $topic_id, true, $user->session_id),
			'U_MCP_QUEUE'			=> $u_mcp_queue,

			'S_TOPIC_TYPE_SWITCH'	=> ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test,
		);

		/**
		* Modify the topic data before it is assigned to the template
		*
		* @event core.viewforum_modify_topicrow
		* @var	array	row					Array with topic data
		* @var	array	topic_row			Template array with topic data
		* @var	bool	s_type_switch		Flag indicating if the topic type is [global] announcement
		* @var	bool	s_type_switch_test	Flag indicating if the test topic type is [global] announcement
		* @since 3.1.0-a1
		*
		* @changed 3.1.10-RC1 Added s_type_switch, s_type_switch_test
		*/
		$vars = array('row', 'topic_row', 's_type_switch', 's_type_switch_test');
		extract($phpbb_dispatcher->trigger_event('core.viewforum_modify_topicrow', compact($vars)));

		$template->assign_block_vars('topicrow', $topic_row);

		$pagination->generate_template_pagination($view_topic_url, 'topicrow.pagination', 'start', $replies + 1, $config['posts_per_page'], 1, true, true);

		$s_type_switch = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

		/**
		* Event after the topic data has been assigned to the template
		*
		* @event core.viewforum_topic_row_after
		* @var	array	row				Array with the topic data
		* @var	array	rowset			Array with topics data (in topic_id => topic_data format)
		* @var	bool	s_type_switch	Flag indicating if the topic type is [global] announcement
		* @var	int		topic_id		The topic ID
		* @var	array	topic_list		Array with current viewforum page topic ids
		* @var	array	topic_row		Template array with topic data
		* @since 3.1.3-RC1
		*/
		$vars = array(
			'row',
			'rowset',
			's_type_switch',
			'topic_id',
			'topic_list',
			'topic_row',
		);
		extract($phpbb_dispatcher->trigger_event('core.viewforum_topic_row_after', compact($vars)));

		if ($unread_topic)
		{
			$mark_forum_read = false;
		}

		unset($rowset[$topic_id]);
	}
}

/**
* This event is to perform additional actions on viewforum page
*
* @event core.viewforum_generate_page_after
* @var	array	forum_data	Array with the forum data
* @since 3.2.2-RC1
*/
$vars = array('forum_data');
extract($phpbb_dispatcher->trigger_event('core.viewforum_generate_page_after', compact($vars)));

// This is rather a fudge but it's the best I can think of without requiring information
// on all topics (as we do in 2.0.x). It looks for unread or new topics, if it doesn't find
// any it updates the forum last read cookie. This requires that the user visit the forum
// after reading a topic
if ($forum_data['forum_type'] == FORUM_POST && count($topic_list) && $mark_forum_read)
{
	update_forum_tracking_info($forum_id, $forum_data['forum_last_post_time'], false, $mark_time_forum);
}

page_footer();
