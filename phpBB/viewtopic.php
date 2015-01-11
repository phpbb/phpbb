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
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

// Initial var setup
$forum_id	= request_var('f', 0);
$topic_id	= request_var('t', 0);
$post_id	= request_var('p', 0);
$voted_id	= request_var('vote_id', array('' => 0));

$voted_id = (sizeof($voted_id) > 1) ? array_unique($voted_id) : $voted_id;


$start		= request_var('start', 0);
$view		= request_var('view', '');

$default_sort_days	= (!empty($user->data['user_post_show_days'])) ? $user->data['user_post_show_days'] : 0;
$default_sort_key	= (!empty($user->data['user_post_sortby_type'])) ? $user->data['user_post_sortby_type'] : 't';
$default_sort_dir	= (!empty($user->data['user_post_sortby_dir'])) ? $user->data['user_post_sortby_dir'] : 'a';

$sort_days	= request_var('st', $default_sort_days);
$sort_key	= request_var('sk', $default_sort_key);
$sort_dir	= request_var('sd', $default_sort_dir);

$update		= request_var('update', false);

$pagination = $phpbb_container->get('pagination');

$s_can_vote = false;
/**
* @todo normalize?
*/
$hilit_words	= request_var('hilit', '', true);

// Do we have a topic or post id?
if (!$topic_id && !$post_id)
{
	trigger_error('NO_TOPIC');
}

$phpbb_content_visibility = $phpbb_container->get('content.visibility');

// Find topic id if user requested a newer or older topic
if ($view && !$post_id)
{
	if (!$forum_id)
	{
		$sql = 'SELECT forum_id
			FROM ' . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
		$result = $db->sql_query($sql);
		$forum_id = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);

		if (!$forum_id)
		{
			trigger_error('NO_TOPIC');
		}
	}

	if ($view == 'unread')
	{
		// Get topic tracking info
		$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_id);
		$topic_last_read = (isset($topic_tracking_info[$topic_id])) ? $topic_tracking_info[$topic_id] : 0;

		$sql = 'SELECT post_id, topic_id, forum_id
			FROM ' . POSTS_TABLE . "
			WHERE topic_id = $topic_id
				AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id) . "
				AND post_time > $topic_last_read
				AND forum_id = $forum_id
			ORDER BY post_time ASC, post_id ASC";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$sql = 'SELECT topic_last_post_id as post_id, topic_id, forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		if (!$row)
		{
			// Setup user environment so we can process lang string
			$user->setup('viewtopic');

			trigger_error('NO_TOPIC');
		}

		$post_id = $row['post_id'];
		$topic_id = $row['topic_id'];
	}
	else if ($view == 'next' || $view == 'previous')
	{
		$sql_condition = ($view == 'next') ? '>' : '<';
		$sql_ordering = ($view == 'next') ? 'ASC' : 'DESC';

		$sql = 'SELECT forum_id, topic_last_post_time
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . $topic_id;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$user->setup('viewtopic');
			// OK, the topic doesn't exist. This error message is not helpful, but technically correct.
			trigger_error(($view == 'next') ? 'NO_NEWER_TOPICS' : 'NO_OLDER_TOPICS');
		}
		else
		{
			$sql = 'SELECT topic_id, forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE forum_id = ' . $row['forum_id'] . "
					AND topic_moved_id = 0
					AND topic_last_post_time $sql_condition {$row['topic_last_post_time']}
					AND " . $phpbb_content_visibility->get_visibility_sql('topic', $row['forum_id']) . "
				ORDER BY topic_last_post_time $sql_ordering, topic_last_post_id $sql_ordering";
			$result = $db->sql_query_limit($sql, 1);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$row)
			{
				$sql = 'SELECT forum_style
					FROM ' . FORUMS_TABLE . "
					WHERE forum_id = $forum_id";
				$result = $db->sql_query($sql);
				$forum_style = (int) $db->sql_fetchfield('forum_style');
				$db->sql_freeresult($result);

				$user->setup('viewtopic', $forum_style);
				trigger_error(($view == 'next') ? 'NO_NEWER_TOPICS' : 'NO_OLDER_TOPICS');
			}
			else
			{
				$topic_id = $row['topic_id'];
				$forum_id = $row['forum_id'];
			}
		}
	}

	if (isset($row) && $row['forum_id'])
	{
		$forum_id = $row['forum_id'];
	}
}

// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
$sql_array = array(
	'SELECT'	=> 't.*, f.*',

	'FROM'		=> array(FORUMS_TABLE => 'f'),
);

// The FROM-Order is quite important here, else t.* columns can not be correctly bound.
if ($post_id)
{
	$sql_array['SELECT'] .= ', p.post_visibility, p.post_time, p.post_id';
	$sql_array['FROM'][POSTS_TABLE] = 'p';
}

// Topics table need to be the last in the chain
$sql_array['FROM'][TOPICS_TABLE] = 't';

if ($user->data['is_registered'])
{
	$sql_array['SELECT'] .= ', tw.notify_status';
	$sql_array['LEFT_JOIN'] = array();

	$sql_array['LEFT_JOIN'][] = array(
		'FROM'	=> array(TOPICS_WATCH_TABLE => 'tw'),
		'ON'	=> 'tw.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tw.topic_id'
	);

	if ($config['allow_bookmarks'])
	{
		$sql_array['SELECT'] .= ', bm.topic_id as bookmarked';
		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(BOOKMARKS_TABLE => 'bm'),
			'ON'	=> 'bm.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = bm.topic_id'
		);
	}

	if ($config['load_db_lastread'])
	{
		$sql_array['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
			'ON'	=> 'tt.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tt.topic_id'
		);

		$sql_array['LEFT_JOIN'][] = array(
			'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
			'ON'	=> 'ft.user_id = ' . $user->data['user_id'] . ' AND t.forum_id = ft.forum_id'
		);
	}
}

if (!$post_id)
{
	$sql_array['WHERE'] = "t.topic_id = $topic_id";
}
else
{
	$sql_array['WHERE'] = "p.post_id = $post_id AND t.topic_id = p.topic_id";
}

$sql_array['WHERE'] .= ' AND f.forum_id = t.forum_id';

$sql = $db->sql_build_query('SELECT', $sql_array);
$result = $db->sql_query($sql);
$topic_data = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

// link to unapproved post or incorrect link
if (!$topic_data)
{
	// If post_id was submitted, we try at least to display the topic as a last resort...
	if ($post_id && $topic_id)
	{
		redirect(append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t=$topic_id" . (($forum_id) ? "&amp;f=$forum_id" : '')));
	}

	trigger_error('NO_TOPIC');
}

$forum_id = (int) $topic_data['forum_id'];

// Now we know the forum_id and can check the permissions
if ($topic_data['topic_visibility'] != ITEM_APPROVED && !$auth->acl_get('m_approve', $forum_id))
{
	trigger_error('NO_TOPIC');
}

// This is for determining where we are (page)
if ($post_id)
{
	// are we where we are supposed to be?
	if (($topic_data['post_visibility'] == ITEM_UNAPPROVED || $topic_data['post_visibility'] == ITEM_REAPPROVE) && !$auth->acl_get('m_approve', $topic_data['forum_id']))
	{
		// If post_id was submitted, we try at least to display the topic as a last resort...
		if ($topic_id)
		{
			redirect(append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t=$topic_id" . (($forum_id) ? "&amp;f=$forum_id" : '')));
		}

		trigger_error('NO_TOPIC');
	}
	if ($post_id == $topic_data['topic_first_post_id'] || $post_id == $topic_data['topic_last_post_id'])
	{
		$check_sort = ($post_id == $topic_data['topic_first_post_id']) ? 'd' : 'a';

		if ($sort_dir == $check_sort)
		{
			$topic_data['prev_posts'] = $phpbb_content_visibility->get_count('topic_posts', $topic_data, $forum_id) - 1;
		}
		else
		{
			$topic_data['prev_posts'] = 0;
		}
	}
	else
	{
		$sql = 'SELECT COUNT(p.post_id) AS prev_posts
			FROM ' . POSTS_TABLE . " p
			WHERE p.topic_id = {$topic_data['topic_id']}
				AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id, 'p.');

		if ($sort_dir == 'd')
		{
			$sql .= " AND (p.post_time > {$topic_data['post_time']} OR (p.post_time = {$topic_data['post_time']} AND p.post_id >= {$topic_data['post_id']}))";
		}
		else
		{
			$sql .= " AND (p.post_time < {$topic_data['post_time']} OR (p.post_time = {$topic_data['post_time']} AND p.post_id <= {$topic_data['post_id']}))";
		}

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$topic_data['prev_posts'] = $row['prev_posts'] - 1;
	}
}

$topic_id = (int) $topic_data['topic_id'];
$topic_replies = $phpbb_content_visibility->get_count('topic_posts', $topic_data, $forum_id) - 1;

// Check sticky/announcement time limit
if (($topic_data['topic_type'] == POST_STICKY || $topic_data['topic_type'] == POST_ANNOUNCE) && $topic_data['topic_time_limit'] && ($topic_data['topic_time'] + $topic_data['topic_time_limit']) < time())
{
	$sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_type = ' . POST_NORMAL . ', topic_time_limit = 0
		WHERE topic_id = ' . $topic_id;
	$db->sql_query($sql);

	$topic_data['topic_type'] = POST_NORMAL;
	$topic_data['topic_time_limit'] = 0;
}

// Setup look and feel
$user->setup('viewtopic', $topic_data['forum_style']);

$overrides_f_read_check = false;
$overrides_forum_password_check = false;
$topic_tracking_info = isset($topic_tracking_info) ? $topic_tracking_info : null;

/**
* Event to apply extra permissions and to override original phpBB's f_read permission and forum password check
* on viewtopic access
*
* @event core.viewtopic_before_f_read_check
* @var	int		forum_id						The forum id from where the topic belongs
* @var	int		topic_id						The id of the topic the user tries to access
* @var	int		post_id							The id of the post the user tries to start viewing at.
*												It may be 0 for none given.
* @var	array	topic_data						All the information from the topic and forum tables for this topic
* 												It includes posts information if post_id is not 0
* @var	bool	overrides_f_read_check			Set true to remove f_read check afterwards
* @var	bool	overrides_forum_password_check	Set true to remove forum_password check afterwards
* @var	array	topic_tracking_info				Information upon calling get_topic_tracking()
*												Set it to NULL to allow auto-filling later.
*												Set it to an array to override original data.
* @since 3.1.3-RC1
*/
$vars = array(
	'forum_id',
	'topic_id',
	'post_id',
	'topic_data',
	'overrides_f_read_check',
	'overrides_forum_password_check',
	'topic_tracking_info',
);
extract($phpbb_dispatcher->trigger_event('core.viewtopic_before_f_read_check', compact($vars)));

// Start auth check
if (!$overrides_f_read_check && !$auth->acl_get('f_read', $forum_id))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error('SORRY_AUTH_READ');
	}

	login_box('', $user->lang['LOGIN_VIEWFORUM']);
}

// Forum is passworded ... check whether access has been granted to this
// user this session, if not show login box
if (!$overrides_forum_password_check && $topic_data['forum_password'])
{
	login_forum_box($topic_data);
}

// Redirect to login upon emailed notification links if user is not logged in.
if (isset($_GET['e']) && $user->data['user_id'] == ANONYMOUS)
{
	login_box(build_url('e') . '#unread', $user->lang['LOGIN_NOTIFY_TOPIC']);
}

// What is start equal to?
if ($post_id)
{
	$start = floor(($topic_data['prev_posts']) / $config['posts_per_page']) * $config['posts_per_page'];
}

// Get topic tracking info
if (!isset($topic_tracking_info))
{
	$topic_tracking_info = array();

	// Get topic tracking info
	if ($config['load_db_lastread'] && $user->data['is_registered'])
	{
		$tmp_topic_data = array($topic_id => $topic_data);
		$topic_tracking_info = get_topic_tracking($forum_id, $topic_id, $tmp_topic_data, array($forum_id => $topic_data['forum_mark_time']));
		unset($tmp_topic_data);
	}
	else if ($config['load_anon_lastread'] || $user->data['is_registered'])
	{
		$topic_tracking_info = get_complete_topic_tracking($forum_id, $topic_id);
	}
}

// Post ordering options
$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']);

$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
$sort_by_sql = array('a' => array('u.username_clean', 'p.post_id'), 't' => array('p.post_time', 'p.post_id'), 's' => array('p.post_subject', 'p.post_id'));
$join_user_sql = array('a' => true, 't' => false, 's' => false);

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';

gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param, $default_sort_days, $default_sort_key, $default_sort_dir);

// Obtain correct post count and ordering SQL if user has
// requested anything different
if ($sort_days)
{
	$min_post_time = time() - ($sort_days * 86400);

	$sql = 'SELECT COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . "
		WHERE topic_id = $topic_id
			AND post_time >= $min_post_time
				AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id);
	$result = $db->sql_query($sql);
	$total_posts = (int) $db->sql_fetchfield('num_posts');
	$db->sql_freeresult($result);

	$limit_posts_time = "AND p.post_time >= $min_post_time ";

	if (isset($_POST['sort']))
	{
		$start = 0;
	}
}
else
{
	$total_posts = $topic_replies + 1;
	$limit_posts_time = '';
}

// Was a highlight request part of the URI?
$highlight_match = $highlight = '';
if ($hilit_words)
{
	$highlight_match = phpbb_clean_search_string($hilit_words);
	$highlight = urlencode($highlight_match);
	$highlight_match = str_replace('\*', '\w+?', preg_quote($highlight_match, '#'));
	$highlight_match = preg_replace('#(?<=^|\s)\\\\w\*\?(?=\s|$)#', '\w+?', $highlight_match);
	$highlight_match = str_replace(' ', '|', $highlight_match);
}

// Make sure $start is set to the last page if it exceeds the amount
$start = $pagination->validate_start($start, $config['posts_per_page'], $total_posts);

// General Viewtopic URL for return links
$viewtopic_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($highlight_match) ? "&amp;hilit=$highlight" : ''));

// Are we watching this topic?
$s_watching_topic = array(
	'link'			=> '',
	'link_toggle'	=> '',
	'title'			=> '',
	'title_toggle'	=> '',
	'is_watching'	=> false,
);

if ($config['allow_topic_notify'])
{
	$notify_status = (isset($topic_data['notify_status'])) ? $topic_data['notify_status'] : null;
	watch_topic_forum('topic', $s_watching_topic, $user->data['user_id'], $forum_id, $topic_id, $notify_status, $start, $topic_data['topic_title']);

	// Reset forum notification if forum notify is set
	if ($config['allow_forum_notify'] && $auth->acl_get('f_subscribe', $forum_id))
	{
		$s_watching_forum = $s_watching_topic;
		watch_topic_forum('forum', $s_watching_forum, $user->data['user_id'], $forum_id, 0);
	}
}

// Bookmarks
if ($config['allow_bookmarks'] && $user->data['is_registered'] && request_var('bookmark', 0))
{
	if (check_link_hash(request_var('hash', ''), "topic_$topic_id"))
	{
		if (!$topic_data['bookmarked'])
		{
			$sql = 'INSERT INTO ' . BOOKMARKS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
				'user_id'	=> $user->data['user_id'],
				'topic_id'	=> $topic_id,
			));
			$db->sql_query($sql);
		}
		else
		{
			$sql = 'DELETE FROM ' . BOOKMARKS_TABLE . "
				WHERE user_id = {$user->data['user_id']}
					AND topic_id = $topic_id";
			$db->sql_query($sql);
		}
		$message = (($topic_data['bookmarked']) ? $user->lang['BOOKMARK_REMOVED'] : $user->lang['BOOKMARK_ADDED']);

		if (!$request->is_ajax())
		{
			$message .= '<br /><br />' . $user->lang('RETURN_TOPIC', '<a href="' . $viewtopic_url . '">', '</a>');
		}
	}
	else
	{
		$message = $user->lang['BOOKMARK_ERR'];

		if (!$request->is_ajax())
		{
			$message .= '<br /><br />' . $user->lang('RETURN_TOPIC', '<a href="' . $viewtopic_url . '">', '</a>');
		}
	}
	meta_refresh(3, $viewtopic_url);

	trigger_error($message);
}

// Grab ranks
$ranks = $cache->obtain_ranks();

// Grab icons
$icons = $cache->obtain_icons();

// Grab extensions
$extensions = array();
if ($topic_data['topic_attachment'])
{
	$extensions = $cache->obtain_attach_extensions($forum_id);
}

// Forum rules listing
$s_forum_rules = '';
gen_forum_auth_level('topic', $forum_id, $topic_data['forum_status']);

// Quick mod tools
$allow_change_type = ($auth->acl_get('m_', $forum_id) || ($user->data['is_registered'] && $user->data['user_id'] == $topic_data['topic_poster'])) ? true : false;

$s_quickmod_action = append_sid(
	"{$phpbb_root_path}mcp.$phpEx",
	array(
		'f'	=> $forum_id,
		't'	=> $topic_id,
		'start'		=> $start,
		'quickmod'	=> 1,
		'redirect'	=> urlencode(str_replace('&amp;', '&', $viewtopic_url)),
	),
	true,
	$user->session_id
);

$quickmod_array = array(
//	'key'			=> array('LANG_KEY', $userHasPermissions),

	'lock'					=> array('LOCK_TOPIC', ($topic_data['topic_status'] == ITEM_UNLOCKED) && ($auth->acl_get('m_lock', $forum_id) || ($auth->acl_get('f_user_lock', $forum_id) && $user->data['is_registered'] && $user->data['user_id'] == $topic_data['topic_poster']))),
	'unlock'				=> array('UNLOCK_TOPIC', ($topic_data['topic_status'] != ITEM_UNLOCKED) && ($auth->acl_get('m_lock', $forum_id))),
	'delete_topic'		=> array('DELETE_TOPIC', ($auth->acl_get('m_delete', $forum_id) || (($topic_data['topic_visibility'] != ITEM_DELETED) && $auth->acl_get('m_softdelete', $forum_id)))),
	'restore_topic'		=> array('RESTORE_TOPIC', (($topic_data['topic_visibility'] == ITEM_DELETED) && $auth->acl_get('m_approve', $forum_id))),
	'move'					=> array('MOVE_TOPIC', $auth->acl_get('m_move', $forum_id) && $topic_data['topic_status'] != ITEM_MOVED),
	'split'					=> array('SPLIT_TOPIC', $auth->acl_get('m_split', $forum_id)),
	'merge'					=> array('MERGE_POSTS', $auth->acl_get('m_merge', $forum_id)),
	'merge_topic'		=> array('MERGE_TOPIC', $auth->acl_get('m_merge', $forum_id)),
	'fork'					=> array('FORK_TOPIC', $auth->acl_get('m_move', $forum_id)),
	'make_normal'		=> array('MAKE_NORMAL', ($allow_change_type && $auth->acl_gets('f_sticky', 'f_announce', $forum_id) && $topic_data['topic_type'] != POST_NORMAL)),
	'make_sticky'		=> array('MAKE_STICKY', ($allow_change_type && $auth->acl_get('f_sticky', $forum_id) && $topic_data['topic_type'] != POST_STICKY)),
	'make_announce'	=> array('MAKE_ANNOUNCE', ($allow_change_type && $auth->acl_get('f_announce', $forum_id) && $topic_data['topic_type'] != POST_ANNOUNCE)),
	'make_global'		=> array('MAKE_GLOBAL', ($allow_change_type && $auth->acl_get('f_announce', $forum_id) && $topic_data['topic_type'] != POST_GLOBAL)),
	'topic_logs'			=> array('VIEW_TOPIC_LOGS', $auth->acl_get('m_', $forum_id)),
);

foreach($quickmod_array as $option => $qm_ary)
{
	if (!empty($qm_ary[1]))
	{
		phpbb_add_quickmod_option($s_quickmod_action, $option, $qm_ary[0]);
	}
}

// Navigation links
generate_forum_nav($topic_data);

// Forum Rules
generate_forum_rules($topic_data);

// Moderators
$forum_moderators = array();
if ($config['load_moderators'])
{
	get_moderators($forum_moderators, $forum_id);
}

// This is only used for print view so ...
$server_path = (!$view) ? $phpbb_root_path : generate_board_url() . '/';

// Replace naughty words in title
$topic_data['topic_title'] = censor_text($topic_data['topic_title']);

$s_search_hidden_fields = array(
	't' => $topic_id,
	'sf' => 'msgonly',
);
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

// If we've got a hightlight set pass it on to pagination.
$base_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : '') . (($highlight_match) ? "&amp;hilit=$highlight" : ''));

/**
* Event to modify data before template variables are being assigned
*
* @event core.viewtopic_assign_template_vars_before
* @var	string	base_url			URL to be passed to generate pagination
* @var	int		forum_id			Forum ID
* @var	int		post_id				Post ID
* @var	array	quickmod_array		Array with quick moderation options data
* @var	int		start				Pagination information
* @var	array	topic_data			Array with topic data
* @var	int		topic_id			Topic ID
* @var	array	topic_tracking_info	Array with topic tracking data
* @var	int		total_posts			Topic total posts count
* @var	string	viewtopic_url		URL to the topic page
* @since 3.1.0-RC4
* @change 3.1.2-RC1 Added viewtopic_url
*/
$vars = array(
	'base_url',
	'forum_id',
	'post_id',
	'quickmod_array',
	'start',
	'topic_data',
	'topic_id',
	'topic_tracking_info',
	'total_posts',
	'viewtopic_url',
);
extract($phpbb_dispatcher->trigger_event('core.viewtopic_assign_template_vars_before', compact($vars)));

$pagination->generate_template_pagination($base_url, 'pagination', 'start', $total_posts, $config['posts_per_page'], $start);

// Send vars to template
$template->assign_vars(array(
	'FORUM_ID' 		=> $forum_id,
	'FORUM_NAME' 	=> $topic_data['forum_name'],
	'FORUM_DESC'	=> generate_text_for_display($topic_data['forum_desc'], $topic_data['forum_desc_uid'], $topic_data['forum_desc_bitfield'], $topic_data['forum_desc_options']),
	'TOPIC_ID' 		=> $topic_id,
	'TOPIC_TITLE' 	=> $topic_data['topic_title'],
	'TOPIC_POSTER'	=> $topic_data['topic_poster'],

	'TOPIC_AUTHOR_FULL'		=> get_username_string('full', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
	'TOPIC_AUTHOR_COLOUR'	=> get_username_string('colour', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),
	'TOPIC_AUTHOR'			=> get_username_string('username', $topic_data['topic_poster'], $topic_data['topic_first_poster_name'], $topic_data['topic_first_poster_colour']),

	'TOTAL_POSTS'	=> $user->lang('VIEW_TOPIC_POSTS', (int) $total_posts),
	'U_MCP' 		=> ($auth->acl_get('m_', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=topic_view&amp;f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start") . ((strlen($u_sort_param)) ? "&amp;$u_sort_param" : ''), true, $user->session_id) : '',
	'MODERATORS'	=> (isset($forum_moderators[$forum_id]) && sizeof($forum_moderators[$forum_id])) ? implode($user->lang['COMMA_SEPARATOR'], $forum_moderators[$forum_id]) : '',

	'POST_IMG' 			=> ($topic_data['forum_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'FORUM_LOCKED') : $user->img('button_topic_new', 'POST_NEW_TOPIC'),
	'QUOTE_IMG' 		=> $user->img('icon_post_quote', 'REPLY_WITH_QUOTE'),
	'REPLY_IMG'			=> ($topic_data['forum_status'] == ITEM_LOCKED || $topic_data['topic_status'] == ITEM_LOCKED) ? $user->img('button_topic_locked', 'TOPIC_LOCKED') : $user->img('button_topic_reply', 'REPLY_TO_TOPIC'),
	'EDIT_IMG' 			=> $user->img('icon_post_edit', 'EDIT_POST'),
	'DELETE_IMG' 		=> $user->img('icon_post_delete', 'DELETE_POST'),
	'DELETED_IMG'		=> $user->img('icon_topic_deleted', 'POST_DELETED_RESTORE'),
	'INFO_IMG' 			=> $user->img('icon_post_info', 'VIEW_INFO'),
	'PROFILE_IMG'		=> $user->img('icon_user_profile', 'READ_PROFILE'),
	'SEARCH_IMG' 		=> $user->img('icon_user_search', 'SEARCH_USER_POSTS'),
	'PM_IMG' 			=> $user->img('icon_contact_pm', 'SEND_PRIVATE_MESSAGE'),
	'EMAIL_IMG' 		=> $user->img('icon_contact_email', 'SEND_EMAIL'),
	'JABBER_IMG'		=> $user->img('icon_contact_jabber', 'JABBER') ,
	'REPORT_IMG'		=> $user->img('icon_post_report', 'REPORT_POST'),
	'REPORTED_IMG'		=> $user->img('icon_topic_reported', 'POST_REPORTED'),
	'UNAPPROVED_IMG'	=> $user->img('icon_topic_unapproved', 'POST_UNAPPROVED'),
	'WARN_IMG'			=> $user->img('icon_user_warn', 'WARN_USER'),

	'S_IS_LOCKED'			=> ($topic_data['topic_status'] == ITEM_UNLOCKED && $topic_data['forum_status'] == ITEM_UNLOCKED) ? false : true,
	'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
	'S_SELECT_SORT_KEY' 	=> $s_sort_key,
	'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
	'S_SINGLE_MODERATOR'	=> (!empty($forum_moderators[$forum_id]) && sizeof($forum_moderators[$forum_id]) > 1) ? false : true,
	'S_TOPIC_ACTION' 		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start")),
	'S_MOD_ACTION' 			=> $s_quickmod_action,

	'L_RETURN_TO_FORUM'		=> $user->lang('RETURN_TO', $topic_data['forum_name']),
	'S_VIEWTOPIC'			=> true,
	'S_UNREAD_VIEW'			=> $view == 'unread',
	'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('u_search') && $auth->acl_get('f_search', $forum_id) && $config['load_search']) ? true : false,
	'S_SEARCHBOX_ACTION'	=> append_sid("{$phpbb_root_path}search.$phpEx"),
	'S_SEARCH_LOCAL_HIDDEN_FIELDS'	=> build_hidden_fields($s_search_hidden_fields),

	'S_DISPLAY_POST_INFO'	=> ($topic_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS)) ? true : false,
	'S_DISPLAY_REPLY_INFO'	=> ($topic_data['forum_type'] == FORUM_POST && ($auth->acl_get('f_reply', $forum_id) || $user->data['user_id'] == ANONYMOUS)) ? true : false,
	'S_ENABLE_FEEDS_TOPIC'	=> ($config['feed_topic'] && !phpbb_optionget(FORUM_OPTION_FEED_EXCLUDE, $topic_data['forum_options'])) ? true : false,

	'U_TOPIC'				=> "{$server_path}viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id",
	'U_FORUM'				=> $server_path,
	'U_VIEW_TOPIC' 			=> $viewtopic_url,
	'U_CANONICAL'			=> generate_board_url() . '/' . append_sid("viewtopic.$phpEx", "t=$topic_id" . (($start) ? "&amp;start=$start" : ''), true, ''),
	'U_VIEW_FORUM' 			=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $forum_id),
	'U_VIEW_OLDER_TOPIC'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;view=previous"),
	'U_VIEW_NEWER_TOPIC'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;view=next"),
	'U_PRINT_TOPIC'			=> ($auth->acl_get('f_print', $forum_id)) ? $viewtopic_url . '&amp;view=print' : '',
	'U_EMAIL_TOPIC'			=> ($auth->acl_get('f_email', $forum_id) && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;t=$topic_id") : '',

	'U_WATCH_TOPIC'			=> $s_watching_topic['link'],
	'U_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['link_toggle'],
	'S_WATCH_TOPIC_TITLE'	=> $s_watching_topic['title'],
	'S_WATCH_TOPIC_TOGGLE'	=> $s_watching_topic['title_toggle'],
	'S_WATCHING_TOPIC'		=> $s_watching_topic['is_watching'],

	'U_BOOKMARK_TOPIC'		=> ($user->data['is_registered'] && $config['allow_bookmarks']) ? $viewtopic_url . '&amp;bookmark=1&amp;hash=' . generate_link_hash("topic_$topic_id") : '',
	'S_BOOKMARK_TOPIC'		=> ($user->data['is_registered'] && $config['allow_bookmarks'] && $topic_data['bookmarked']) ? $user->lang['BOOKMARK_TOPIC_REMOVE'] : $user->lang['BOOKMARK_TOPIC'],
	'S_BOOKMARK_TOGGLE'		=> (!$user->data['is_registered'] || !$config['allow_bookmarks'] || !$topic_data['bookmarked']) ? $user->lang['BOOKMARK_TOPIC_REMOVE'] : $user->lang['BOOKMARK_TOPIC'],
	'S_BOOKMARKED_TOPIC'	=> ($user->data['is_registered'] && $config['allow_bookmarks'] && $topic_data['bookmarked']) ? true : false,

	'U_POST_NEW_TOPIC' 		=> ($auth->acl_get('f_post', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=post&amp;f=$forum_id") : '',
	'U_POST_REPLY_TOPIC' 	=> ($auth->acl_get('f_reply', $forum_id) || $user->data['user_id'] == ANONYMOUS) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=reply&amp;f=$forum_id&amp;t=$topic_id") : '',
	'U_BUMP_TOPIC'			=> (bump_topic_allowed($forum_id, $topic_data['topic_bumped'], $topic_data['topic_last_post_time'], $topic_data['topic_poster'], $topic_data['topic_last_poster_id'])) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=bump&amp;f=$forum_id&amp;t=$topic_id&amp;hash=" . generate_link_hash("topic_$topic_id")) : '')
);

// Does this topic contain a poll?
if (!empty($topic_data['poll_start']))
{
	$sql = 'SELECT o.*, p.bbcode_bitfield, p.bbcode_uid
		FROM ' . POLL_OPTIONS_TABLE . ' o, ' . POSTS_TABLE . " p
		WHERE o.topic_id = $topic_id
			AND p.post_id = {$topic_data['topic_first_post_id']}
			AND p.topic_id = o.topic_id
		ORDER BY o.poll_option_id";
	$result = $db->sql_query($sql);

	$poll_info = $vote_counts = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$poll_info[] = $row;
		$option_id = (int) $row['poll_option_id'];
		$vote_counts[$option_id] = (int) $row['poll_option_total'];
	}
	$db->sql_freeresult($result);

	$cur_voted_id = array();
	if ($user->data['is_registered'])
	{
		$sql = 'SELECT poll_option_id
			FROM ' . POLL_VOTES_TABLE . '
			WHERE topic_id = ' . $topic_id . '
				AND vote_user_id = ' . $user->data['user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$cur_voted_id[] = $row['poll_option_id'];
		}
		$db->sql_freeresult($result);
	}
	else
	{
		// Cookie based guest tracking ... I don't like this but hum ho
		// it's oft requested. This relies on "nice" users who don't feel
		// the need to delete cookies to mess with results.
		if ($request->is_set($config['cookie_name'] . '_poll_' . $topic_id, \phpbb\request\request_interface::COOKIE))
		{
			$cur_voted_id = explode(',', $request->variable($config['cookie_name'] . '_poll_' . $topic_id, '', true, \phpbb\request\request_interface::COOKIE));
			$cur_voted_id = array_map('intval', $cur_voted_id);
		}
	}

	// Can not vote at all if no vote permission
	$s_can_vote = ($auth->acl_get('f_vote', $forum_id) &&
		(($topic_data['poll_length'] != 0 && $topic_data['poll_start'] + $topic_data['poll_length'] > time()) || $topic_data['poll_length'] == 0) &&
		$topic_data['topic_status'] != ITEM_LOCKED &&
		$topic_data['forum_status'] != ITEM_LOCKED &&
		(!sizeof($cur_voted_id) ||
		($auth->acl_get('f_votechg', $forum_id) && $topic_data['poll_vote_change']))) ? true : false;
	$s_display_results = (!$s_can_vote || ($s_can_vote && sizeof($cur_voted_id)) || $view == 'viewpoll') ? true : false;

	if ($update && $s_can_vote)
	{

		if (!sizeof($voted_id) || sizeof($voted_id) > $topic_data['poll_max_options'] || in_array(VOTE_CONVERTED, $cur_voted_id) || !check_form_key('posting'))
		{
			$redirect_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start"));

			meta_refresh(5, $redirect_url);
			if (!sizeof($voted_id))
			{
				$message = 'NO_VOTE_OPTION';
			}
			else if (sizeof($voted_id) > $topic_data['poll_max_options'])
			{
				$message = 'TOO_MANY_VOTE_OPTIONS';
			}
			else if (in_array(VOTE_CONVERTED, $cur_voted_id))
			{
				$message = 'VOTE_CONVERTED';
			}
			else
			{
				$message = 'FORM_INVALID';
			}

			$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');
			trigger_error($message);
		}

		foreach ($voted_id as $option)
		{
			if (in_array($option, $cur_voted_id))
			{
				continue;
			}

			$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
				SET poll_option_total = poll_option_total + 1
				WHERE poll_option_id = ' . (int) $option . '
					AND topic_id = ' . (int) $topic_id;
			$db->sql_query($sql);

			$vote_counts[$option]++;

			if ($user->data['is_registered'])
			{
				$sql_ary = array(
					'topic_id'			=> (int) $topic_id,
					'poll_option_id'	=> (int) $option,
					'vote_user_id'		=> (int) $user->data['user_id'],
					'vote_user_ip'		=> (string) $user->ip,
				);

				$sql = 'INSERT INTO ' . POLL_VOTES_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				$db->sql_query($sql);
			}
		}

		foreach ($cur_voted_id as $option)
		{
			if (!in_array($option, $voted_id))
			{
				$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . '
					SET poll_option_total = poll_option_total - 1
					WHERE poll_option_id = ' . (int) $option . '
						AND topic_id = ' . (int) $topic_id;
				$db->sql_query($sql);

				$vote_counts[$option]--;

				if ($user->data['is_registered'])
				{
					$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . '
						WHERE topic_id = ' . (int) $topic_id . '
							AND poll_option_id = ' . (int) $option . '
							AND vote_user_id = ' . (int) $user->data['user_id'];
					$db->sql_query($sql);
				}
			}
		}

		if ($user->data['user_id'] == ANONYMOUS && !$user->data['is_bot'])
		{
			$user->set_cookie('poll_' . $topic_id, implode(',', $voted_id), time() + 31536000);
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . '
			SET poll_last_vote = ' . time() . "
			WHERE topic_id = $topic_id";
		//, topic_last_post_time = ' . time() . " -- for bumping topics with new votes, ignore for now
		$db->sql_query($sql);

		$redirect_url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id" . (($start == 0) ? '' : "&amp;start=$start"));
		$message = $user->lang['VOTE_SUBMITTED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');

		if ($request->is_ajax())
		{
			// Filter out invalid options
			$valid_user_votes = array_intersect(array_keys($vote_counts), $voted_id);

			$data = array(
				'NO_VOTES'			=> $user->lang['NO_VOTES'],
				'success'			=> true,
				'user_votes'		=> array_flip($valid_user_votes),
				'vote_counts'		=> $vote_counts,
				'total_votes'		=> array_sum($vote_counts),
				'can_vote'			=> !sizeof($valid_user_votes) || ($auth->acl_get('f_votechg', $forum_id) && $topic_data['poll_vote_change']),
			);
			$json_response = new \phpbb\json_response();
			$json_response->send($data);
		}

		meta_refresh(5, $redirect_url);
		trigger_error($message);
	}

	$poll_total = 0;
	$poll_most = 0;
	foreach ($poll_info as $poll_option)
	{
		$poll_total += $poll_option['poll_option_total'];
		$poll_most = ($poll_option['poll_option_total'] >= $poll_most) ? $poll_option['poll_option_total'] : $poll_most;
	}

	$parse_flags = ($poll_info[0]['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;

	for ($i = 0, $size = sizeof($poll_info); $i < $size; $i++)
	{
		$poll_info[$i]['poll_option_text'] = generate_text_for_display($poll_info[$i]['poll_option_text'], $poll_info[$i]['bbcode_uid'], $poll_option['bbcode_bitfield'], $parse_flags, true);
	}

	$topic_data['poll_title'] = generate_text_for_display($topic_data['poll_title'], $poll_info[0]['bbcode_uid'], $poll_info[0]['bbcode_bitfield'], $parse_flags, true);

	foreach ($poll_info as $poll_option)
	{
		$option_pct = ($poll_total > 0) ? $poll_option['poll_option_total'] / $poll_total : 0;
		$option_pct_txt = sprintf("%.1d%%", round($option_pct * 100));
		$option_pct_rel = ($poll_most > 0) ? $poll_option['poll_option_total'] / $poll_most : 0;
		$option_pct_rel_txt = sprintf("%.1d%%", round($option_pct_rel * 100));
		$option_most_votes = ($poll_option['poll_option_total'] > 0 && $poll_option['poll_option_total'] == $poll_most) ? true : false;

		$template->assign_block_vars('poll_option', array(
			'POLL_OPTION_ID' 			=> $poll_option['poll_option_id'],
			'POLL_OPTION_CAPTION' 		=> $poll_option['poll_option_text'],
			'POLL_OPTION_RESULT' 		=> $poll_option['poll_option_total'],
			'POLL_OPTION_PERCENT' 		=> $option_pct_txt,
			'POLL_OPTION_PERCENT_REL' 	=> $option_pct_rel_txt,
			'POLL_OPTION_PCT'			=> round($option_pct * 100),
			'POLL_OPTION_WIDTH'     	=> round($option_pct * 250),
			'POLL_OPTION_VOTED'			=> (in_array($poll_option['poll_option_id'], $cur_voted_id)) ? true : false,
			'POLL_OPTION_MOST_VOTES'	=> $option_most_votes,
		));
	}

	$poll_end = $topic_data['poll_length'] + $topic_data['poll_start'];

	$template->assign_vars(array(
		'POLL_QUESTION'		=> $topic_data['poll_title'],
		'TOTAL_VOTES' 		=> $poll_total,
		'POLL_LEFT_CAP_IMG'	=> $user->img('poll_left'),
		'POLL_RIGHT_CAP_IMG'=> $user->img('poll_right'),

		'L_MAX_VOTES'		=> $user->lang('MAX_OPTIONS_SELECT', (int) $topic_data['poll_max_options']),
		'L_POLL_LENGTH'		=> ($topic_data['poll_length']) ? sprintf($user->lang[($poll_end > time()) ? 'POLL_RUN_TILL' : 'POLL_ENDED_AT'], $user->format_date($poll_end)) : '',

		'S_HAS_POLL'		=> true,
		'S_CAN_VOTE'		=> $s_can_vote,
		'S_DISPLAY_RESULTS'	=> $s_display_results,
		'S_IS_MULTI_CHOICE'	=> ($topic_data['poll_max_options'] > 1) ? true : false,
		'S_POLL_ACTION'		=> $viewtopic_url,

		'U_VIEW_RESULTS'	=> $viewtopic_url . '&amp;view=viewpoll',
	));

	unset($poll_end, $poll_info, $voted_id);
}

// If the user is trying to reach the second half of the topic, fetch it starting from the end
$store_reverse = false;
$sql_limit = $config['posts_per_page'];
$sql_sort_order = $direction = '';

if ($start > $total_posts / 2)
{
	$store_reverse = true;

	// Select the sort order
	$direction = (($sort_dir == 'd') ? 'ASC' : 'DESC');

	$sql_limit = $pagination->reverse_limit($start, $sql_limit, $total_posts);
	$sql_start = $pagination->reverse_start($start, $sql_limit, $total_posts);
}
else
{
	// Select the sort order
	$direction = (($sort_dir == 'd') ? 'DESC' : 'ASC');
	$sql_start = $start;
}

if (is_array($sort_by_sql[$sort_key]))
{
	$sql_sort_order = implode(' ' . $direction . ', ', $sort_by_sql[$sort_key]) . ' ' . $direction;
}
else
{
	$sql_sort_order = $sort_by_sql[$sort_key] . ' ' . $direction;
}

// Container for user details, only process once
$post_list = $user_cache = $id_cache = $attachments = $attach_list = $rowset = $update_count = $post_edit_list = $post_delete_list = array();
$has_unapproved_attachments = $has_approved_attachments = $display_notice = false;
$bbcode_bitfield = '';
$i = $i_total = 0;

// Go ahead and pull all data for this topic
$sql = 'SELECT p.post_id
	FROM ' . POSTS_TABLE . ' p' . (($join_user_sql[$sort_key]) ? ', ' . USERS_TABLE . ' u': '') . "
	WHERE p.topic_id = $topic_id
		AND " . $phpbb_content_visibility->get_visibility_sql('post', $forum_id, 'p.') . "
		" . (($join_user_sql[$sort_key]) ? 'AND u.user_id = p.poster_id': '') . "
		$limit_posts_time
	ORDER BY $sql_sort_order";
$result = $db->sql_query_limit($sql, $sql_limit, $sql_start);

$i = ($store_reverse) ? $sql_limit - 1 : 0;
while ($row = $db->sql_fetchrow($result))
{
	$post_list[$i] = (int) $row['post_id'];
	($store_reverse) ? $i-- : $i++;
}
$db->sql_freeresult($result);

if (!sizeof($post_list))
{
	if ($sort_days)
	{
		trigger_error('NO_POSTS_TIME_FRAME');
	}
	else
	{
		trigger_error('NO_TOPIC');
	}
}

// Holding maximum post time for marking topic read
// We need to grab it because we do reverse ordering sometimes
$max_post_time = 0;

$sql_ary = array(
	'SELECT'	=> 'u.*, z.friend, z.foe, p.*',

	'FROM'		=> array(
		USERS_TABLE		=> 'u',
		POSTS_TABLE		=> 'p',
	),

	'LEFT_JOIN'	=> array(
		array(
			'FROM'	=> array(ZEBRA_TABLE => 'z'),
			'ON'	=> 'z.user_id = ' . $user->data['user_id'] . ' AND z.zebra_id = p.poster_id',
		),
	),

	'WHERE'		=> $db->sql_in_set('p.post_id', $post_list) . '
		AND u.user_id = p.poster_id',
);

/**
* Event to modify the SQL query before the post and poster data is retrieved
*
* @event core.viewtopic_get_post_data
* @var	int		forum_id	Forum ID
* @var	int		topic_id	Topic ID
* @var	array	topic_data	Array with topic data
* @var	array	post_list	Array with post_ids we are going to retrieve
* @var	int		sort_days	Display posts of previous x days
* @var	string	sort_key	Key the posts are sorted by
* @var	string	sort_dir	Direction the posts are sorted by
* @var	int		start		Pagination information
* @var	array	sql_ary		The SQL array to get the data of posts and posters
* @since 3.1.0-a1
* @change 3.1.0-a2 Added vars forum_id, topic_id, topic_data, post_list, sort_days, sort_key, sort_dir, start
*/
$vars = array(
	'forum_id',
	'topic_id',
	'topic_data',
	'post_list',
	'sort_days',
	'sort_key',
	'sort_dir',
	'start',
	'sql_ary',
);
extract($phpbb_dispatcher->trigger_event('core.viewtopic_get_post_data', compact($vars)));

$sql = $db->sql_build_query('SELECT', $sql_ary);
$result = $db->sql_query($sql);

$now = $user->create_datetime();
$now = phpbb_gmgetdate($now->getTimestamp() + $now->getOffset());

// Posts are stored in the $rowset array while $attach_list, $user_cache
// and the global bbcode_bitfield are built
while ($row = $db->sql_fetchrow($result))
{
	// Set max_post_time
	if ($row['post_time'] > $max_post_time)
	{
		$max_post_time = $row['post_time'];
	}

	$poster_id = (int) $row['poster_id'];

	// Does post have an attachment? If so, add it to the list
	if ($row['post_attachment'] && $config['allow_attachments'])
	{
		$attach_list[] = (int) $row['post_id'];

		if ($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE)
		{
			$has_unapproved_attachments = true;
		}
		else if ($row['post_visibility'] == ITEM_APPROVED)
		{
			$has_approved_attachments = true;
		}
	}

	$rowset_data = array(
		'hide_post'			=> (($row['foe'] || $row['post_visibility'] == ITEM_DELETED) && ($view != 'show' || $post_id != $row['post_id'])) ? true : false,

		'post_id'			=> $row['post_id'],
		'post_time'			=> $row['post_time'],
		'user_id'			=> $row['user_id'],
		'username'			=> $row['username'],
		'user_colour'		=> $row['user_colour'],
		'topic_id'			=> $row['topic_id'],
		'forum_id'			=> $row['forum_id'],
		'post_subject'		=> $row['post_subject'],
		'post_edit_count'	=> $row['post_edit_count'],
		'post_edit_time'	=> $row['post_edit_time'],
		'post_edit_reason'	=> $row['post_edit_reason'],
		'post_edit_user'	=> $row['post_edit_user'],
		'post_edit_locked'	=> $row['post_edit_locked'],
		'post_delete_time'	=> $row['post_delete_time'],
		'post_delete_reason'=> $row['post_delete_reason'],
		'post_delete_user'	=> $row['post_delete_user'],

		// Make sure the icon actually exists
		'icon_id'			=> (isset($icons[$row['icon_id']]['img'], $icons[$row['icon_id']]['height'], $icons[$row['icon_id']]['width'])) ? $row['icon_id'] : 0,
		'post_attachment'	=> $row['post_attachment'],
		'post_visibility'	=> $row['post_visibility'],
		'post_reported'		=> $row['post_reported'],
		'post_username'		=> $row['post_username'],
		'post_text'			=> $row['post_text'],
		'bbcode_uid'		=> $row['bbcode_uid'],
		'bbcode_bitfield'	=> $row['bbcode_bitfield'],
		'enable_smilies'	=> $row['enable_smilies'],
		'enable_sig'		=> $row['enable_sig'],
		'friend'			=> $row['friend'],
		'foe'				=> $row['foe'],
	);

	/**
	* Modify the post rowset containing data to be displayed with posts
	*
	* @event core.viewtopic_post_rowset_data
	* @var	array	rowset_data	Array with the rowset data for this post
	* @var	array	row			Array with original user and post data
	* @since 3.1.0-a1
	*/
	$vars = array('rowset_data', 'row');
	extract($phpbb_dispatcher->trigger_event('core.viewtopic_post_rowset_data', compact($vars)));

	$rowset[$row['post_id']] = $rowset_data;

	// Define the global bbcode bitfield, will be used to load bbcodes
	$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['bbcode_bitfield']);

	// Is a signature attached? Are we going to display it?
	if ($row['enable_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
	{
		$bbcode_bitfield = $bbcode_bitfield | base64_decode($row['user_sig_bbcode_bitfield']);
	}

	// Cache various user specific data ... so we don't have to recompute
	// this each time the same user appears on this page
	if (!isset($user_cache[$poster_id]))
	{
		if ($poster_id == ANONYMOUS)
		{
			$user_cache_data = array(
				'user_type'		=> USER_IGNORE,
				'joined'		=> '',
				'posts'			=> '',

				'sig'					=> '',
				'sig_bbcode_uid'		=> '',
				'sig_bbcode_bitfield'	=> '',

				'online'			=> false,
				'avatar'			=> ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',
				'sig'				=> '',
				'pm'				=> '',
				'email'				=> '',
				'jabber'			=> '',
				'search'			=> '',
				'age'				=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'contact_user'		=> '',

				'warnings'			=> 0,
				'allow_pm'			=> 0,
			);

			/**
			* Modify the guest user's data displayed with the posts
			*
			* @event core.viewtopic_cache_guest_data
			* @var	array	user_cache_data	Array with the user's data
			* @var	int		poster_id		Poster's user id
			* @var	array	row				Array with original user and post data
			* @since 3.1.0-a1
			*/
			$vars = array('user_cache_data', 'poster_id', 'row');
			extract($phpbb_dispatcher->trigger_event('core.viewtopic_cache_guest_data', compact($vars)));

			$user_cache[$poster_id] = $user_cache_data;

			$user_rank_data = phpbb_get_user_rank($row, false);
			$user_cache[$poster_id]['rank_title'] = $user_rank_data['title'];
			$user_cache[$poster_id]['rank_image'] = $user_rank_data['img'];
			$user_cache[$poster_id]['rank_image_src'] = $user_rank_data['img_src'];
		}
		else
		{
			$user_sig = '';

			// We add the signature to every posters entry because enable_sig is post dependent
			if ($row['user_sig'] && $config['allow_sig'] && $user->optionget('viewsigs'))
			{
				$user_sig = $row['user_sig'];
			}

			$id_cache[] = $poster_id;

			$user_cache_data = array(
				'user_type'					=> $row['user_type'],
				'user_inactive_reason'		=> $row['user_inactive_reason'],

				'joined'		=> $user->format_date($row['user_regdate']),
				'posts'			=> $row['user_posts'],
				'warnings'		=> (isset($row['user_warnings'])) ? $row['user_warnings'] : 0,

				'sig'					=> $user_sig,
				'sig_bbcode_uid'		=> (!empty($row['user_sig_bbcode_uid'])) ? $row['user_sig_bbcode_uid'] : '',
				'sig_bbcode_bitfield'	=> (!empty($row['user_sig_bbcode_bitfield'])) ? $row['user_sig_bbcode_bitfield'] : '',

				'viewonline'	=> $row['user_allow_viewonline'],
				'allow_pm'		=> $row['user_allow_pm'],

				'avatar'		=> ($user->optionget('viewavatars')) ? phpbb_get_user_avatar($row) : '',
				'age'			=> '',

				'rank_title'		=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',

				'username'			=> $row['username'],
				'user_colour'		=> $row['user_colour'],
				'contact_user' 		=> $user->lang('CONTACT_USER', get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['username'])),

				'online'		=> false,
				'jabber'		=> ($config['jab_enable'] && $row['user_jabber'] && $auth->acl_get('u_sendim')) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=contact&amp;action=jabber&amp;u=$poster_id") : '',
				'search'		=> ($config['load_search'] && $auth->acl_get('u_search')) ? append_sid("{$phpbb_root_path}search.$phpEx", "author_id=$poster_id&amp;sr=posts") : '',

				'author_full'		=> get_username_string('full', $poster_id, $row['username'], $row['user_colour']),
				'author_colour'		=> get_username_string('colour', $poster_id, $row['username'], $row['user_colour']),
				'author_username'	=> get_username_string('username', $poster_id, $row['username'], $row['user_colour']),
				'author_profile'	=> get_username_string('profile', $poster_id, $row['username'], $row['user_colour']),
			);

			/**
			* Modify the users' data displayed with their posts
			*
			* @event core.viewtopic_cache_user_data
			* @var	array	user_cache_data	Array with the user's data
			* @var	int		poster_id		Poster's user id
			* @var	array	row				Array with original user and post data
			* @since 3.1.0-a1
			*/
			$vars = array('user_cache_data', 'poster_id', 'row');
			extract($phpbb_dispatcher->trigger_event('core.viewtopic_cache_user_data', compact($vars)));

			$user_cache[$poster_id] = $user_cache_data;

			$user_rank_data = phpbb_get_user_rank($row, $row['user_posts']);
			$user_cache[$poster_id]['rank_title'] = $user_rank_data['title'];
			$user_cache[$poster_id]['rank_image'] = $user_rank_data['img'];
			$user_cache[$poster_id]['rank_image_src'] = $user_rank_data['img_src'];

			if ((!empty($row['user_allow_viewemail']) && $auth->acl_get('u_sendemail')) || $auth->acl_get('a_email'))
			{
				$user_cache[$poster_id]['email'] = ($config['board_email_form'] && $config['email_enable']) ? append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=email&amp;u=$poster_id") : (($config['board_hide_emails'] && !$auth->acl_get('a_email')) ? '' : 'mailto:' . $row['user_email']);
			}
			else
			{
				$user_cache[$poster_id]['email'] = '';
			}

			if ($config['allow_birthdays'] && !empty($row['user_birthday']))
			{
				list($bday_day, $bday_month, $bday_year) = array_map('intval', explode('-', $row['user_birthday']));

				if ($bday_year)
				{
					$diff = $now['mon'] - $bday_month;
					if ($diff == 0)
					{
						$diff = ($now['mday'] - $bday_day < 0) ? 1 : 0;
					}
					else
					{
						$diff = ($diff < 0) ? 1 : 0;
					}

					$user_cache[$poster_id]['age'] = (int) ($now['year'] - $bday_year - $diff);
				}
			}
		}
	}
}
$db->sql_freeresult($result);

// Load custom profile fields
if ($config['load_cpf_viewtopic'])
{
	$cp = $phpbb_container->get('profilefields.manager');

	// Grab all profile fields from users in id cache for later use - similar to the poster cache
	$profile_fields_tmp = $cp->grab_profile_fields_data($id_cache);

	// filter out fields not to be displayed on viewtopic. Yes, it's a hack, but this shouldn't break any MODs.
	$profile_fields_cache = array();
	foreach ($profile_fields_tmp as $profile_user_id => $profile_fields)
	{
		$profile_fields_cache[$profile_user_id] = array();
		foreach ($profile_fields as $used_ident => $profile_field)
		{
			if ($profile_field['data']['field_show_on_vt'])
			{
				$profile_fields_cache[$profile_user_id][$used_ident] = $profile_field;
			}
		}
	}
	unset($profile_fields_tmp);
}

// Generate online information for user
if ($config['load_onlinetrack'] && sizeof($id_cache))
{
	$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_viewonline) AS viewonline
		FROM ' . SESSIONS_TABLE . '
		WHERE ' . $db->sql_in_set('session_user_id', $id_cache) . '
		GROUP BY session_user_id';
	$result = $db->sql_query($sql);

	$update_time = $config['load_online_time'] * 60;
	while ($row = $db->sql_fetchrow($result))
	{
		$user_cache[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
	}
	$db->sql_freeresult($result);
}
unset($id_cache);

// Pull attachment data
if (sizeof($attach_list))
{
	if ($auth->acl_get('u_download') && $auth->acl_get('f_download', $forum_id))
	{
		$sql = 'SELECT *
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $attach_list) . '
				AND in_message = 0
			ORDER BY filetime DESC, post_msg_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[$row['post_msg_id']][] = $row;
		}
		$db->sql_freeresult($result);

		// No attachments exist, but post table thinks they do so go ahead and reset post_attach flags
		if (!sizeof($attachments))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 0
				WHERE ' . $db->sql_in_set('post_id', $attach_list);
			$db->sql_query($sql);

			// We need to update the topic indicator too if the complete topic is now without an attachment
			if (sizeof($rowset) != $total_posts)
			{
				// Not all posts are displayed so we query the db to find if there's any attachment for this topic
				$sql = 'SELECT a.post_msg_id as post_id
					FROM ' . ATTACHMENTS_TABLE . ' a, ' . POSTS_TABLE . " p
					WHERE p.topic_id = $topic_id
						AND p.post_visibility = " . ITEM_APPROVED . '
						AND p.topic_id = a.topic_id';
				$result = $db->sql_query_limit($sql, 1);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . "
						SET topic_attachment = 0
						WHERE topic_id = $topic_id";
					$db->sql_query($sql);
				}
			}
			else
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . "
					SET topic_attachment = 0
					WHERE topic_id = $topic_id";
				$db->sql_query($sql);
			}
		}
		else if ($has_approved_attachments && !$topic_data['topic_attachment'])
		{
			// Topic has approved attachments but its flag is wrong
			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_attachment = 1
				WHERE topic_id = $topic_id";
			$db->sql_query($sql);

			$topic_data['topic_attachment'] = 1;
		}
		else if ($has_unapproved_attachments && !$topic_data['topic_attachment'])
		{
			// Topic has only unapproved attachments but we have the right to see and download them
			$topic_data['topic_attachment'] = 1;
		}
	}
	else
	{
		$display_notice = true;
	}
}

// Instantiate BBCode if need be
if ($bbcode_bitfield !== '')
{
	$bbcode = new bbcode(base64_encode($bbcode_bitfield));
}

// Get the list of users who can receive private messages
$can_receive_pm_list = $auth->acl_get_list(array_keys($user_cache), 'u_readpm');
$can_receive_pm_list = (empty($can_receive_pm_list) || !isset($can_receive_pm_list[0]['u_readpm'])) ? array() : $can_receive_pm_list[0]['u_readpm'];

// Get the list of permanently banned users
$permanently_banned_users = phpbb_get_banned_user_ids(array_keys($user_cache), false);

$i_total = sizeof($rowset) - 1;
$prev_post_id = '';

$template->assign_vars(array(
	'S_HAS_ATTACHMENTS' => $topic_data['topic_attachment'],
	'S_NUM_POSTS' => sizeof($post_list))
);

/**
* Event to modify the post, poster and attachment data before assigning the posts
*
* @event core.viewtopic_modify_post_data
* @var	int		forum_id	Forum ID
* @var	int		topic_id	Topic ID
* @var	array	topic_data	Array with topic data
* @var	array	post_list	Array with post_ids we are going to display
* @var	array	rowset		Array with post_id => post data
* @var	array	user_cache	Array with prepared user data
* @var	int		start		Pagination information
* @var	int		sort_days	Display posts of previous x days
* @var	string	sort_key	Key the posts are sorted by
* @var	string	sort_dir	Direction the posts are sorted by
* @var	bool	display_notice				Shall we display a notice instead of attachments
* @var	bool	has_approved_attachments	Does the topic have approved attachments
* @var	array	attachments					List of attachments post_id => array of attachments
* @var	array	permanently_banned_users	List of permanently banned users
* @var	array	can_receive_pm_list			Array with posters that can receive pms
* @since 3.1.0-RC3
*/
$vars = array(
	'forum_id',
	'topic_id',
	'topic_data',
	'post_list',
	'rowset',
	'user_cache',
	'sort_days',
	'sort_key',
	'sort_dir',
	'start',
	'permanently_banned_users',
	'can_receive_pm_list',
	'display_notice',
	'has_approved_attachments',
	'attachments',
);
extract($phpbb_dispatcher->trigger_event('core.viewtopic_modify_post_data', compact($vars)));

// Output the posts
$first_unread = $post_unread = false;
for ($i = 0, $end = sizeof($post_list); $i < $end; ++$i)
{
	// A non-existing rowset only happens if there was no user present for the entered poster_id
	// This could be a broken posts table.
	if (!isset($rowset[$post_list[$i]]))
	{
		continue;
	}

	$row = $rowset[$post_list[$i]];
	$poster_id = $row['user_id'];

	// End signature parsing, only if needed
	if ($user_cache[$poster_id]['sig'] && $row['enable_sig'] && empty($user_cache[$poster_id]['sig_parsed']))
	{
		$parse_flags = ($user_cache[$poster_id]['sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
		$user_cache[$poster_id]['sig'] = generate_text_for_display($user_cache[$poster_id]['sig'], $user_cache[$poster_id]['sig_bbcode_uid'], $user_cache[$poster_id]['sig_bbcode_bitfield'],  $parse_flags, true);
	}

	// Parse the message and subject
	$parse_flags = ($row['bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
	$message = generate_text_for_display($row['post_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $parse_flags, true);

	if (!empty($attachments[$row['post_id']]))
	{
		parse_attachments($forum_id, $message, $attachments[$row['post_id']], $update_count);
	}

	// Replace naughty words such as farty pants
	$row['post_subject'] = censor_text($row['post_subject']);

	// Highlight active words (primarily for search)
	if ($highlight_match)
	{
		$message = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $message);
		$row['post_subject'] = preg_replace('#(?!<.*)(?<!\w)(' . $highlight_match . ')(?!\w|[^<>]*(?:</s(?:cript|tyle))?>)#is', '<span class="posthilit">\1</span>', $row['post_subject']);
	}

	// Editing information
	if (($row['post_edit_count'] && $config['display_last_edited']) || $row['post_edit_reason'])
	{
		// Get usernames for all following posts if not already stored
		if (!sizeof($post_edit_list) && ($row['post_edit_reason'] || ($row['post_edit_user'] && !isset($user_cache[$row['post_edit_user']]))))
		{
			// Remove all post_ids already parsed (we do not have to check them)
			$post_storage_list = (!$store_reverse) ? array_slice($post_list, $i) : array_slice(array_reverse($post_list), $i);

			$sql = 'SELECT DISTINCT u.user_id, u.username, u.user_colour
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE ' . $db->sql_in_set('p.post_id', $post_storage_list) . '
					AND p.post_edit_count <> 0
					AND p.post_edit_user <> 0
					AND p.post_edit_user = u.user_id';
			$result2 = $db->sql_query($sql);
			while ($user_edit_row = $db->sql_fetchrow($result2))
			{
				$post_edit_list[$user_edit_row['user_id']] = $user_edit_row;
			}
			$db->sql_freeresult($result2);

			unset($post_storage_list);
		}

		if ($row['post_edit_reason'])
		{
			// User having edited the post also being the post author?
			if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
			{
				$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
			}
			else
			{
				$display_username = get_username_string('full', $row['post_edit_user'], $post_edit_list[$row['post_edit_user']]['username'], $post_edit_list[$row['post_edit_user']]['user_colour']);
			}

			$l_edited_by = $user->lang('EDITED_TIMES_TOTAL', (int) $row['post_edit_count'], $display_username, $user->format_date($row['post_edit_time'], false, true));
		}
		else
		{
			if ($row['post_edit_user'] && !isset($user_cache[$row['post_edit_user']]))
			{
				$user_cache[$row['post_edit_user']] = $post_edit_list[$row['post_edit_user']];
			}

			// User having edited the post also being the post author?
			if (!$row['post_edit_user'] || $row['post_edit_user'] == $poster_id)
			{
				$display_username = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);
			}
			else
			{
				$display_username = get_username_string('full', $row['post_edit_user'], $user_cache[$row['post_edit_user']]['username'], $user_cache[$row['post_edit_user']]['user_colour']);
			}

			$l_edited_by = $user->lang('EDITED_TIMES_TOTAL', (int) $row['post_edit_count'], $display_username, $user->format_date($row['post_edit_time'], false, true));
		}
	}
	else
	{
		$l_edited_by = '';
	}

	// Deleting information
	if ($row['post_visibility'] == ITEM_DELETED && $row['post_delete_user'])
	{
		// Get usernames for all following posts if not already stored
		if (!sizeof($post_delete_list) && ($row['post_delete_reason'] || ($row['post_delete_user'] && !isset($user_cache[$row['post_delete_user']]))))
		{
			// Remove all post_ids already parsed (we do not have to check them)
			$post_storage_list = (!$store_reverse) ? array_slice($post_list, $i) : array_slice(array_reverse($post_list), $i);

			$sql = 'SELECT DISTINCT u.user_id, u.username, u.user_colour
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE ' . $db->sql_in_set('p.post_id', $post_storage_list) . '
					AND p.post_delete_user <> 0
					AND p.post_delete_user = u.user_id';
			$result2 = $db->sql_query($sql);
			while ($user_delete_row = $db->sql_fetchrow($result2))
			{
				$post_delete_list[$user_delete_row['user_id']] = $user_delete_row;
			}
			$db->sql_freeresult($result2);

			unset($post_storage_list);
		}

		if ($row['post_delete_user'] && !isset($user_cache[$row['post_delete_user']]))
		{
			$user_cache[$row['post_delete_user']] = $post_delete_list[$row['post_delete_user']];
		}

		$display_postername = get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']);

		// User having deleted the post also being the post author?
		if (!$row['post_delete_user'] || $row['post_delete_user'] == $poster_id)
		{
			$display_username = $display_postername;
		}
		else
		{
			$display_username = get_username_string('full', $row['post_delete_user'], $user_cache[$row['post_delete_user']]['username'], $user_cache[$row['post_delete_user']]['user_colour']);
		}

		if ($row['post_delete_reason'])
		{
			$l_deleted_message = $user->lang('POST_DELETED_BY_REASON', $display_postername, $display_username, $user->format_date($row['post_delete_time'], false, true), $row['post_delete_reason']);
		}
		else
		{
			$l_deleted_message = $user->lang('POST_DELETED_BY', $display_postername, $display_username, $user->format_date($row['post_delete_time'], false, true));
		}
		$l_deleted_by = $user->lang('DELETED_INFORMATION', $display_username, $user->format_date($row['post_delete_time'], false, true));
	}
	else
	{
		$l_deleted_by = $l_deleted_message = '';
	}

	// Bump information
	if ($topic_data['topic_bumped'] && $row['post_id'] == $topic_data['topic_last_post_id'] && isset($user_cache[$topic_data['topic_bumper']]) )
	{
		// It is safe to grab the username from the user cache array, we are at the last
		// post and only the topic poster and last poster are allowed to bump.
		// Admins and mods are bound to the above rules too...
		$l_bumped_by = sprintf($user->lang['BUMPED_BY'], $user_cache[$topic_data['topic_bumper']]['username'], $user->format_date($topic_data['topic_last_post_time'], false, true));
	}
	else
	{
		$l_bumped_by = '';
	}

	$cp_row = array();

	//
	if ($config['load_cpf_viewtopic'])
	{
		$cp_row = (isset($profile_fields_cache[$poster_id])) ? $cp->generate_profile_fields_template_data($profile_fields_cache[$poster_id]) : array();
	}

	$post_unread = (isset($topic_tracking_info[$topic_id]) && $row['post_time'] > $topic_tracking_info[$topic_id]) ? true : false;

	$s_first_unread = false;
	if (!$first_unread && $post_unread)
	{
		$s_first_unread = $first_unread = true;
	}

	$force_edit_allowed = $force_delete_allowed = false;

	$s_cannot_edit = !$auth->acl_get('f_edit', $forum_id) || $user->data['user_id'] != $poster_id;
	$s_cannot_edit_time = $config['edit_time'] && $row['post_time'] <= time() - ($config['edit_time'] * 60);
	$s_cannot_edit_locked = $topic_data['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'];

	$s_cannot_delete = $user->data['user_id'] != $poster_id || (
			!$auth->acl_get('f_delete', $forum_id) &&
			(!$auth->acl_get('f_softdelete', $forum_id) || $row['post_visibility'] == ITEM_DELETED)
	);
	$s_cannot_delete_lastpost = $topic_data['topic_last_post_id'] != $row['post_id'];
	$s_cannot_delete_time = $config['delete_time'] && $row['post_time'] <= time() - ($config['delete_time'] * 60);
	// we do not want to allow removal of the last post if a moderator locked it!
	$s_cannot_delete_locked = $topic_data['topic_status'] == ITEM_LOCKED || $row['post_edit_locked'];

	/**
	* This event allows you to modify the conditions for the "can edit post" and "can delete post" checks
	*
	* @event core.viewtopic_modify_post_action_conditions
	* @var	array	row			Array with post data
	* @var	array	topic_data	Array with topic data
	* @var	bool	force_edit_allowed		Allow the user to edit the post (all permissions and conditions are ignored)
	* @var	bool	s_cannot_edit			User can not edit the post because it's not his
	* @var	bool	s_cannot_edit_locked	User can not edit the post because it's locked
	* @var	bool	s_cannot_edit_time		User can not edit the post because edit_time has passed
	* @var	bool	force_delete_allowed		Allow the user to delete the post (all permissions and conditions are ignored)
	* @var	bool	s_cannot_delete				User can not delete the post because it's not his
	* @var	bool	s_cannot_delete_lastpost	User can not delete the post because it's not the last post of the topic
	* @var	bool	s_cannot_delete_locked		User can not delete the post because it's locked
	* @var	bool	s_cannot_delete_time		User can not delete the post because edit_time has passed
	* @since 3.1.0-b4
	*/
	$vars = array(
		'row',
		'topic_data',
		'force_edit_allowed',
		's_cannot_edit',
		's_cannot_edit_locked',
		's_cannot_edit_time',
		'force_delete_allowed',
		's_cannot_delete',
		's_cannot_delete_lastpost',
		's_cannot_delete_locked',
		's_cannot_delete_time',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewtopic_modify_post_action_conditions', compact($vars)));

	$edit_allowed = $force_edit_allowed || ($user->data['is_registered'] && ($auth->acl_get('m_edit', $forum_id) || (
		!$s_cannot_edit &&
		!$s_cannot_edit_time &&
		!$s_cannot_edit_locked
	)));

	$quote_allowed = $auth->acl_get('m_edit', $forum_id) || ($topic_data['topic_status'] != ITEM_LOCKED &&
		($user->data['user_id'] == ANONYMOUS || $auth->acl_get('f_reply', $forum_id))
	);

	$delete_allowed = $force_delete_allowed || ($user->data['is_registered'] && (
		($auth->acl_get('m_delete', $forum_id) || ($auth->acl_get('m_softdelete', $forum_id) && $row['post_visibility'] != ITEM_DELETED)) ||
		(!$s_cannot_delete && !$s_cannot_delete_lastpost && !$s_cannot_delete_time && !$s_cannot_delete_locked)
	));

	// Can this user receive a Private Message?
	$can_receive_pm = (
		// They must be a "normal" user
		$user_cache[$poster_id]['user_type'] != USER_IGNORE &&

		// They must not be deactivated by the administrator
		($user_cache[$poster_id]['user_type'] != USER_INACTIVE || $user_cache[$poster_id]['user_inactive_reason'] != INACTIVE_MANUAL) &&

		// They must be able to read PMs
		in_array($poster_id, $can_receive_pm_list) &&

		// They must not be permanently banned
		!in_array($poster_id, $permanently_banned_users) &&

		// They must allow users to contact via PM
		(($auth->acl_gets('a_', 'm_') || $auth->acl_getf_global('m_')) || $user_cache[$poster_id]['allow_pm'])
	);

	$u_pm = '';

	if ($config['allow_privmsg'] && $auth->acl_get('u_sendpm') && $can_receive_pm)
	{
		$u_pm = append_sid("{$phpbb_root_path}ucp.$phpEx", 'i=pm&amp;mode=compose&amp;action=quotepost&amp;p=' . $row['post_id']);
	}

	//
	$post_row = array(
		'POST_AUTHOR_FULL'		=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_full'] : get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'POST_AUTHOR_COLOUR'	=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_colour'] : get_username_string('colour', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'POST_AUTHOR'			=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_username'] : get_username_string('username', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),
		'U_POST_AUTHOR'			=> ($poster_id != ANONYMOUS) ? $user_cache[$poster_id]['author_profile'] : get_username_string('profile', $poster_id, $row['username'], $row['user_colour'], $row['post_username']),

		'RANK_TITLE'		=> $user_cache[$poster_id]['rank_title'],
		'RANK_IMG'			=> $user_cache[$poster_id]['rank_image'],
		'RANK_IMG_SRC'		=> $user_cache[$poster_id]['rank_image_src'],
		'POSTER_JOINED'		=> $user_cache[$poster_id]['joined'],
		'POSTER_POSTS'		=> $user_cache[$poster_id]['posts'],
		'POSTER_AVATAR'		=> $user_cache[$poster_id]['avatar'],
		'POSTER_WARNINGS'	=> $auth->acl_get('m_warn') ? $user_cache[$poster_id]['warnings'] : '',
		'POSTER_AGE'		=> $user_cache[$poster_id]['age'],
		'CONTACT_USER'		=> $user_cache[$poster_id]['contact_user'],

		'POST_DATE'			=> $user->format_date($row['post_time'], false, ($view == 'print') ? true : false),
		'POST_SUBJECT'		=> $row['post_subject'],
		'MESSAGE'			=> $message,
		'SIGNATURE'			=> ($row['enable_sig']) ? $user_cache[$poster_id]['sig'] : '',
		'EDITED_MESSAGE'	=> $l_edited_by,
		'EDIT_REASON'		=> $row['post_edit_reason'],
		'DELETED_MESSAGE'	=> $l_deleted_by,
		'DELETE_REASON'		=> $row['post_delete_reason'],
		'BUMPED_MESSAGE'	=> $l_bumped_by,

		'MINI_POST_IMG'			=> ($post_unread) ? $user->img('icon_post_target_unread', 'UNREAD_POST') : $user->img('icon_post_target', 'POST'),
		'POST_ICON_IMG'			=> ($topic_data['enable_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['img'] : '',
		'POST_ICON_IMG_WIDTH'	=> ($topic_data['enable_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['width'] : '',
		'POST_ICON_IMG_HEIGHT'	=> ($topic_data['enable_icons'] && !empty($row['icon_id'])) ? $icons[$row['icon_id']]['height'] : '',
		'ONLINE_IMG'			=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : (($user_cache[$poster_id]['online']) ? $user->img('icon_user_online', 'ONLINE') : $user->img('icon_user_offline', 'OFFLINE')),
		'S_ONLINE'				=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? false : (($user_cache[$poster_id]['online']) ? true : false),

		'U_EDIT'			=> ($edit_allowed) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=edit&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
		'U_QUOTE'			=> ($quote_allowed) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=quote&amp;f=$forum_id&amp;p={$row['post_id']}") : '',
		'U_INFO'			=> ($auth->acl_get('m_info', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", "i=main&amp;mode=post_details&amp;f=$forum_id&amp;p=" . $row['post_id'], true, $user->session_id) : '',
		'U_DELETE'			=> ($delete_allowed) ? append_sid("{$phpbb_root_path}posting.$phpEx", "mode=delete&amp;f=$forum_id&amp;p={$row['post_id']}") : '',

		'U_SEARCH'		=> $user_cache[$poster_id]['search'],
		'U_PM'			=> $u_pm,
		'U_EMAIL'		=> $user_cache[$poster_id]['email'],
		'U_JABBER'		=> $user_cache[$poster_id]['jabber'],

		'U_APPROVE_ACTION'		=> append_sid("{$phpbb_root_path}mcp.$phpEx", "i=queue&amp;p={$row['post_id']}&amp;f=$forum_id&amp;redirect=" . urlencode(str_replace('&amp;', '&', $viewtopic_url . '&amp;p=' . $row['post_id'] . '#p' . $row['post_id']))),
		'U_REPORT'			=> ($auth->acl_get('f_report', $forum_id)) ? append_sid("{$phpbb_root_path}report.$phpEx", 'f=' . $forum_id . '&amp;p=' . $row['post_id']) : '',
		'U_MCP_REPORT'		=> ($auth->acl_get('m_report', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=reports&amp;mode=report_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
		'U_MCP_APPROVE'		=> ($auth->acl_get('m_approve', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=approve_details&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
		'U_MCP_RESTORE'		=> ($auth->acl_get('m_approve', $forum_id)) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=queue&amp;mode=' . (($topic_data['topic_visibility'] != ITEM_DELETED) ? 'deleted_posts' : 'deleted_topics') . '&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',
		'U_MINI_POST'		=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'p=' . $row['post_id']) . '#p' . $row['post_id'],
		'U_NEXT_POST_ID'	=> ($i < $i_total && isset($rowset[$post_list[$i + 1]])) ? $rowset[$post_list[$i + 1]]['post_id'] : '',
		'U_PREV_POST_ID'	=> $prev_post_id,
		'U_NOTES'			=> ($auth->acl_getf_global('m_')) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=notes&amp;mode=user_notes&amp;u=' . $poster_id, true, $user->session_id) : '',
		'U_WARN'			=> ($auth->acl_get('m_warn') && $poster_id != $user->data['user_id'] && $poster_id != ANONYMOUS) ? append_sid("{$phpbb_root_path}mcp.$phpEx", 'i=warn&amp;mode=warn_post&amp;f=' . $forum_id . '&amp;p=' . $row['post_id'], true, $user->session_id) : '',

		'POST_ID'			=> $row['post_id'],
		'POST_NUMBER'		=> $i + $start + 1,
		'POSTER_ID'			=> $poster_id,

		'S_HAS_ATTACHMENTS'	=> (!empty($attachments[$row['post_id']])) ? true : false,
		'S_MULTIPLE_ATTACHMENTS'	=> !empty($attachments[$row['post_id']]) && sizeof($attachments[$row['post_id']]) > 1,
		'S_POST_UNAPPROVED'	=> ($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE) ? true : false,
		'S_POST_DELETED'	=> ($row['post_visibility'] == ITEM_DELETED) ? true : false,
		'L_POST_DELETED_MESSAGE'	=> $l_deleted_message,
		'S_POST_REPORTED'	=> ($row['post_reported'] && $auth->acl_get('m_report', $forum_id)) ? true : false,
		'S_DISPLAY_NOTICE'	=> $display_notice && $row['post_attachment'],
		'S_FRIEND'			=> ($row['friend']) ? true : false,
		'S_UNREAD_POST'		=> $post_unread,
		'S_FIRST_UNREAD'	=> $s_first_unread,
		'S_CUSTOM_FIELDS'	=> (isset($cp_row['row']) && sizeof($cp_row['row'])) ? true : false,
		'S_TOPIC_POSTER'	=> ($topic_data['topic_poster'] == $poster_id) ? true : false,

		'S_IGNORE_POST'		=> ($row['foe']) ? true : false,
		'L_IGNORE_POST'		=> ($row['foe']) ? sprintf($user->lang['POST_BY_FOE'], get_username_string('full', $poster_id, $row['username'], $row['user_colour'], $row['post_username'])) : '',
		'S_POST_HIDDEN'		=> $row['hide_post'],
		'L_POST_DISPLAY'	=> ($row['hide_post']) ? $user->lang('POST_DISPLAY', '<a class="display_post" data-post-id="' . $row['post_id'] . '" href="' . $viewtopic_url . "&amp;p={$row['post_id']}&amp;view=show#p{$row['post_id']}" . '">', '</a>') : '',
	);

	$user_poster_data = $user_cache[$poster_id];

	$current_row_number = $i;

	/**
	* Modify the posts template block
	*
	* @event core.viewtopic_modify_post_row
	* @var	int		start				Start item of this page
	* @var	int		current_row_number	Number of the post on this page
	* @var	int		end					Number of posts on this page
	* @var	int		total_posts			Total posts count
	* @var	int		poster_id			Post author id
	* @var	array	row					Array with original post and user data
	* @var	array	cp_row				Custom profile field data of the poster
	* @var	array	attachments			List of attachments
	* @var	array	user_poster_data	Poster's data from user cache
	* @var	array	post_row			Template block array of the post
	* @var	array	topic_data			Array with topic data
	* @since 3.1.0-a1
	* @change 3.1.0-a3 Added vars start, current_row_number, end, attachments
	* @change 3.1.0-b3 Added topic_data array, total_posts
	* @change 3.1.0-RC3 Added poster_id
	*/
	$vars = array(
		'start',
		'current_row_number',
		'end',
		'total_posts',
		'poster_id',
		'row',
		'cp_row',
		'attachments',
		'user_poster_data',
		'post_row',
		'topic_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewtopic_modify_post_row', compact($vars)));

	$i = $current_row_number;

	if (isset($cp_row['row']) && sizeof($cp_row['row']))
	{
		$post_row = array_merge($post_row, $cp_row['row']);
	}

	// Dump vars into template
	$template->assign_block_vars('postrow', $post_row);

	$contact_fields = array(
		array(
			'ID'		=> 'pm',
			'NAME' 		=> $user->lang['SEND_PRIVATE_MESSAGE'],
			'U_CONTACT'	=> $u_pm,
		),
		array(
			'ID'		=> 'email',
			'NAME'		=> $user->lang['SEND_EMAIL'],
			'U_CONTACT'	=> $user_cache[$poster_id]['email'],
		),
		array(
			'ID'		=> 'jabber',
			'NAME'		=> $user->lang['JABBER'],
			'U_CONTACT'	=> $user_cache[$poster_id]['jabber'],
		),
	);

	foreach ($contact_fields as $field)
	{
		if ($field['U_CONTACT'])
		{
			$template->assign_block_vars('postrow.contact', $field);
		}
	}

	if (!empty($cp_row['blockrow']))
	{
		foreach ($cp_row['blockrow'] as $field_data)
		{
			$template->assign_block_vars('postrow.custom_fields', $field_data);

			if ($field_data['S_PROFILE_CONTACT'])
			{
				$template->assign_block_vars('postrow.contact', array(
					'ID'		=> $field_data['PROFILE_FIELD_IDENT'],
					'NAME'		=> $field_data['PROFILE_FIELD_NAME'],
					'U_CONTACT'	=> $field_data['PROFILE_FIELD_CONTACT'],
				));
			}
		}
	}

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

	$current_row_number = $i;

	/**
	* Event after the post data has been assigned to the template
	*
	* @event core.viewtopic_post_row_after
	* @var	int		start				Start item of this page
	* @var	int		current_row_number	Number of the post on this page
	* @var	int		end					Number of posts on this page
	* @var	int		total_posts			Total posts count
	* @var	array	row					Array with original post and user data
	* @var	array	cp_row				Custom profile field data of the poster
	* @var	array	attachments			List of attachments
	* @var	array	user_poster_data	Poster's data from user cache
	* @var	array	post_row			Template block array of the post
	* @var	array	topic_data			Array with topic data
	* @since 3.1.0-a3
	* @change 3.1.0-b3 Added topic_data array, total_posts
	*/
	$vars = array(
		'start',
		'current_row_number',
		'end',
		'total_posts',
		'row',
		'cp_row',
		'attachments',
		'user_poster_data',
		'post_row',
		'topic_data',
	);
	extract($phpbb_dispatcher->trigger_event('core.viewtopic_post_row_after', compact($vars)));

	$i = $current_row_number;

	$prev_post_id = $row['post_id'];

	unset($rowset[$post_list[$i]]);
	unset($attachments[$row['post_id']]);
}
unset($rowset, $user_cache);

// Update topic view and if necessary attachment view counters ... but only for humans and if this is the first 'page view'
if (isset($user->data['session_page']) && !$user->data['is_bot'] && (strpos($user->data['session_page'], '&t=' . $topic_id) === false || isset($user->data['session_created'])))
{
	$sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_views = topic_views + 1, topic_last_view_time = ' . time() . "
		WHERE topic_id = $topic_id";
	$db->sql_query($sql);

	// Update the attachment download counts
	if (sizeof($update_count))
	{
		$sql = 'UPDATE ' . ATTACHMENTS_TABLE . '
			SET download_count = download_count + 1
			WHERE ' . $db->sql_in_set('attach_id', array_unique($update_count));
		$db->sql_query($sql);
	}
}

// Only mark topic if it's currently unread. Also make sure we do not set topic tracking back if earlier pages are viewed.
if (isset($topic_tracking_info[$topic_id]) && $topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id] && $max_post_time > $topic_tracking_info[$topic_id])
{
	markread('topic', $forum_id, $topic_id, $max_post_time);

	// Update forum info
	$all_marked_read = update_forum_tracking_info($forum_id, $topic_data['forum_last_post_time'], (isset($topic_data['forum_mark_time'])) ? $topic_data['forum_mark_time'] : false, false);
}
else
{
	$all_marked_read = true;
}

// If there are absolutely no more unread posts in this forum
// and unread posts shown, we can safely show the #unread link
if ($all_marked_read)
{
	if ($post_unread)
	{
		$template->assign_vars(array(
			'U_VIEW_UNREAD_POST'	=> '#unread',
		));
	}
	else if (isset($topic_tracking_info[$topic_id]) && $topic_data['topic_last_post_time'] > $topic_tracking_info[$topic_id])
	{
		$template->assign_vars(array(
			'U_VIEW_UNREAD_POST'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;view=unread") . '#unread',
		));
	}
}
else if (!$all_marked_read)
{
	$last_page = ((floor($start / $config['posts_per_page']) + 1) == max(ceil($total_posts / $config['posts_per_page']), 1)) ? true : false;

	// What can happen is that we are at the last displayed page. If so, we also display the #unread link based in $post_unread
	if ($last_page && $post_unread)
	{
		$template->assign_vars(array(
			'U_VIEW_UNREAD_POST'	=> '#unread',
		));
	}
	else if (!$last_page)
	{
		$template->assign_vars(array(
			'U_VIEW_UNREAD_POST'	=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", "f=$forum_id&amp;t=$topic_id&amp;view=unread") . '#unread',
		));
	}
}

// let's set up quick_reply
$s_quick_reply = false;
if ($user->data['is_registered'] && $config['allow_quick_reply'] && ($topic_data['forum_flags'] & FORUM_FLAG_QUICK_REPLY) && $auth->acl_get('f_reply', $forum_id))
{
	// Quick reply enabled forum
	$s_quick_reply = (($topic_data['forum_status'] == ITEM_UNLOCKED && $topic_data['topic_status'] == ITEM_UNLOCKED) || $auth->acl_get('m_edit', $forum_id)) ? true : false;
}

if ($s_can_vote || $s_quick_reply)
{
	add_form_key('posting');

	if ($s_quick_reply)
	{
		$s_attach_sig	= $config['allow_sig'] && $user->optionget('attachsig') && $auth->acl_get('f_sigs', $forum_id) && $auth->acl_get('u_sig');
		$s_smilies		= $config['allow_smilies'] && $user->optionget('smilies') && $auth->acl_get('f_smilies', $forum_id);
		$s_bbcode		= $config['allow_bbcode'] && $user->optionget('bbcode') && $auth->acl_get('f_bbcode', $forum_id);
		$s_notify		= $config['allow_topic_notify'] && ($user->data['user_notify'] || $s_watching_topic['is_watching']);

		$qr_hidden_fields = array(
			'topic_cur_post_id'		=> (int) $topic_data['topic_last_post_id'],
			'lastclick'				=> (int) time(),
			'topic_id'				=> (int) $topic_data['topic_id'],
			'forum_id'				=> (int) $forum_id,
		);

		// Originally we use checkboxes and check with isset(), so we only provide them if they would be checked
		(!$s_bbcode)					? $qr_hidden_fields['disable_bbcode'] = 1		: true;
		(!$s_smilies)					? $qr_hidden_fields['disable_smilies'] = 1		: true;
		(!$config['allow_post_links'])	? $qr_hidden_fields['disable_magic_url'] = 1	: true;
		($s_attach_sig)					? $qr_hidden_fields['attach_sig'] = 1			: true;
		($s_notify)						? $qr_hidden_fields['notify'] = 1				: true;
		($topic_data['topic_status'] == ITEM_LOCKED) ? $qr_hidden_fields['lock_topic'] = 1 : true;

		$template->assign_vars(array(
			'S_QUICK_REPLY'			=> true,
			'U_QR_ACTION'			=> append_sid("{$phpbb_root_path}posting.$phpEx", "mode=reply&amp;f=$forum_id&amp;t=$topic_id"),
			'QR_HIDDEN_FIELDS'		=> build_hidden_fields($qr_hidden_fields),
			'SUBJECT'				=> 'Re: ' . censor_text($topic_data['topic_title']),
		));
	}
}
// now I have the urge to wash my hands :(


// We overwrite $_REQUEST['f'] if there is no forum specified
// to be able to display the correct online list.
// One downside is that the user currently viewing this topic/post is not taken into account.
if (!request_var('f', 0))
{
	$request->overwrite('f', $forum_id);
}

// We need to do the same with the topic_id. See #53025.
if (!request_var('t', 0) && !empty($topic_id))
{
	$request->overwrite('t', $topic_id);
}

$page_title = $topic_data['topic_title'] . ($start ? ' - ' . sprintf($user->lang['PAGE_TITLE_NUMBER'], $pagination->get_on_page($config['posts_per_page'], $start)) : '');

/**
* You can use this event to modify the page title of the viewtopic page
*
* @event core.viewtopic_modify_page_title
* @var	string	page_title		Title of the viewtopic page
* @var	array	topic_data		Array with topic data
* @var	int		forum_id		Forum ID of the topic
* @var	int		start			Start offset used to calculate the page
* @var	array	post_list		Array with post_ids we are going to display
* @since 3.1.0-a1
* @change 3.1.0-RC4 Added post_list var
*/
$vars = array('page_title', 'topic_data', 'forum_id', 'start', 'post_list');
extract($phpbb_dispatcher->trigger_event('core.viewtopic_modify_page_title', compact($vars)));

// Output the page
page_header($page_title, true, $forum_id);

$template->set_filenames(array(
	'body' => ($view == 'print') ? 'viewtopic_print.html' : 'viewtopic_body.html')
);
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"), $forum_id);

page_footer();
