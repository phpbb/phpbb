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
include($phpbb_root_path . 'includes/functions_viewforum.' . $phpEx);

/** @var \phpbb\auth\auth $auth */
/** @var \phpbb\request\request_interface $request */

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

/** @var \phpbb\forum\data\forum_repository $forum_repository */
$forum_repository = $phpbb_container->get('forum.data.repository');

/** @var \phpbb\forum\view\viewforum_renderer $viewforum_renderer */
$viewforum_renderer = $phpbb_container->get('forum.view.renderer');

/** @var \phpbb\topic\view\topic_list_renderer $topic_list_renderer */
$topic_list_renderer = $phpbb_container->get('topic.view.topic_list_renderer');

/** @var \phpbb\topic\data\topic_repository $topic_repository */
$topic_repository = $phpbb_container->get('topic.data.repository');

// Check if the user has actually sent a forum ID with his/her request
// If not give them a nice error page.
if (!$forum_id)
{
	trigger_error('NO_FORUM');
}

$forum_data = $forum_repository->get_forum_by_id(
	$forum_id,
	$config['load_db_lastread'] && $user->data['is_registered'],
	$user->data['is_registered'],
	$user->data['user_id']);

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
		$forum_repository->increment_forum_link_click_count($forum_id);
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
	$viewforum_renderer->set_has_no_subforums();
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
$viewforum_renderer->set_viewforum_url($forum_id, $start);

// Not postable forum or showing active topics?
if (!($forum_data['forum_type'] == FORUM_POST || (($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) && $forum_data['forum_type'] == FORUM_CAT)))
{
	page_footer();
}

// Ok, if someone has only list-access, we only display the forum list.
// We also make this circumstance available to the template in case we want to display a notice. ;)
if (!$auth->acl_gets('f_read', 'f_list_topics', $forum_id))
{
	$viewforum_renderer->set_has_no_read_access();
	page_footer();
}

// Handle marking posts
if ($mark_read == 'topics')
{
	mark_topics_read($request, $forum_id, $phpbb_root_path, $phpEx, $user, $config);
}

run_cron_jobs($config, $phpbb_container, $forum_data, $template);

list($s_watching_forum, $forum_data) = forum_subscription_information($config, $forum_data, $auth, $forum_id, $user, $start);

$s_forum_rules = '';
gen_forum_auth_level('forum', $forum_id, $forum_data['forum_status']);

// Topic ordering options
list($sort_by_sql, $u_sort_param, $s_sort_dir, $s_sort_key, $s_limit_days, $limit_days, $sort_days, $sort_key, $sort_dir) = viewforum_figure_out_sorting($user, $auth, $forum_id, $phpbb_dispatcher, $sort_days, $sort_key, $sort_dir, $default_sort_days, $default_sort_key, $default_sort_dir);

// Limit topics to certain time frame, obtain correct topic count
if ($sort_days)
{
	list($topics_count, $start, $sql_limit_time) = get_topic_count_by_time_frame($sort_days, $forum_id, $phpbb_content_visibility, $phpbb_dispatcher, $db, $template, $start);
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

$viewforum_renderer->render_general_information($forum_data, $moderators, $active_forum_ary, $s_watching_forum, $s_search_hidden_fields, $s_sort_dir, $s_sort_key, $s_limit_days, $start, $u_sort_param);

// Grab icons
$icons = $cache->obtain_icons();

// Grab all topic data
$rowset = $announcement_list = $topic_list = $global_announce_forums = array();

list($sql_array, $sql_approved) = $topic_repository->build_base_query($forum_id, $s_display_active, $active_forum_ary);

if ($forum_data['forum_type'] == FORUM_POST)
{
	list($rowset, $announcement_list, $global_announce_forums) = $topic_repository->get_announcement_topics($forum_id, $sql_array);
	$topics_count += count($global_announce_forums);
}

$forum_tracking_info = array();

if ($user->data['is_registered'] && $config['load_db_lastread'])
{
	list($forum_tracking_info) = get_fourm_tracking_info_for_announcements($forum_data, $forum_tracking_info, $forum_id, $global_announce_forums, $db, $user);
}

// If the user is trying to reach late pages, start searching from the end
list($store_reverse, $sql_limit, $direction, $sql_start) = sql_compute_limits($config, $start, $topics_count, $sort_dir, $pagination, $announcement_list);

$topic_list = query_topic_ids($phpbb_dispatcher, $sort_by_sql, $sort_key, $direction, $forum_data, $active_forum_ary, $forum_id, $db, $sql_approved, $sql_limit_time, $store_reverse, $sql_limit, $sql_start, $topic_list);

// For storing shadow topics
$shadow_topic_list = array();

if (count($topic_list))
{
	list($shadow_topic_list, $rowset) = query_topics($sql_array, $db, $topic_list, $shadow_topic_list, $rowset);
}

// If we have some shadow topics, update the rowset to reflect their topic information
if (count($shadow_topic_list))
{
	list($vars, $rowset, $topic_list, $topics_count) = update_shadow_topic_information($db, $shadow_topic_list, $phpbb_dispatcher, $rowset, $topic_list, $topics_count, $auth);
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

$viewforum_renderer->set_topic_count(
	($s_display_active) ? false : $user->lang('VIEW_FORUM_TOPICS', (int) $total_topic_count)
);

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
	list($mark_forum_read, $mark_time_forum, $forum_data) = $topic_list_renderer->render_topic_list($rowset, $forum_tracking_info, $config, $user, $topic_tracking_info, $s_display_active, $forum_data, $tracking_topics, $forum_id, $topic_list, $phpbb_content_visibility, $auth, $phpbb_root_path, $phpEx, $icons, $phpbb_dispatcher, $template, $pagination);
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
