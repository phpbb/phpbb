<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : viewtopic.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$auth->acl($user->data);

// Initial var setup
$forum_id	= (isset($_GET['f'])) ? max(intval($_GET['f']), 0) : 0;
$topic_id	= (isset($_GET['t'])) ? max(intval($_GET['t']), 0) : 0;
$post_id	= (isset($_GET['p'])) ? max(intval($_GET['p']), 0) : 0;
$voted_id	= (isset($_POST['vote_id'])) ? array_map('intval', $_POST['vote_id']) : 0;

$start		= (isset($_GET['start'])) ? max(intval($_GET['start']), 0) : 0;
$view		= (isset($_GET['view'])) ? htmlspecialchars($_GET['view']) : false;
$rate		= (isset($_GET['rate'])) ? intval($_GET['rate']) : false;
$sort_days	= (!empty($_REQUEST['st'])) ? max(intval($_REQUEST['st']), 0) : 0;
$sort_key	= (!empty($_REQUEST['sk'])) ? htmlspecialchars($_REQUEST['sk']) : 't';
$sort_dir	= (!empty($_REQUEST['sd'])) ? htmlspecialchars($_REQUEST['sd']) : 'a';
$update		= (isset($_POST['update'])) ? true : false;

$hilit_words = (isset($_GET['hilit'])) ? urldecode($_GET['hilit']) : false;
$tracking_topics = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();

// Do we have a topic or post id?
if (!$topic_id && !$post_id)
{
	trigger_error('NO_TOPIC');
}

// Find topic id if user requested a newer or older topic
$unread_post_id = '';
if ($view && !$post_id)
{
	if ($view == 'unread')
	{
		if ($user->data['user_id'] != ANONYMOUS)
		{
			if ($config['load_db_lastread'])
			{
				switch (SQL_LAYER)
				{
					case 'oracle':
						break;

					default:
						$sql_lastread = 'LEFT JOIN ' . TOPICS_TRACK_TABLE . ' tt ON (tt.user_id = ' . $user->data['user_id'] . ' 
							AND tt.topic_id = p.topic_id)';
						$sql_unread_time = ' tt.mark_time OR tt.mark_time IS NULL';
				}
			}
			else
			{
				$sql_unread_time = base_convert(max($tracking_topics[$forum_id]), 36, 10);
				$sql_unread_time = max($sql_unread_time, $user->data['session_last_visit']);
			}

			$sql = 'SELECT p.post_id
				FROM (' . POSTS_TABLE . " p 
				$sql_lastread, " . TOPICS_TABLE . " t)
				WHERE t.topic_id = $topic_id
					AND p.topic_id = t.topic_id 
					" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND p.post_approved = 1') . " 
					AND (p.post_time > $sql_unread_time
						OR p.post_id = t.topic_last_post_id)
				ORDER BY p.post_time ASC";
			$result = $db->sql_query_limit($sql, 1);

			if (!($row = $db->sql_fetchrow($result)))
			{
				// Setup user environment so we can process lang string
				$user->setup();

				meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id");
				$message = $user->lang['NO_UNREAD_POSTS'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id\">", '</a>');
				trigger_error($message);
			}
			$db->sql_freeresult($result);

			$unread_post_id = $post_id = $row['post_id'];
		}
	}
	else if ($view == 'next' || $view == 'previous')
	{
		$sql_condition = ($view == 'next') ? '>' : '<';
		$sql_ordering = ($view == 'next') ? 'ASC' : 'DESC';

		$sql = 'SELECT t.topic_id
			FROM ' . TOPICS_TABLE . ' t, ' . TOPICS_TABLE . " t2
			WHERE t2.topic_id = $topic_id
				AND t.forum_id = t2.forum_id
				" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND t.topic_approved = 1') . " 
				AND t.topic_last_post_time $sql_condition t2.topic_last_post_time
			ORDER BY t.topic_last_post_time $sql_ordering";
		$result = $db->sql_query_limit($sql, 1);

		if (!($row = $db->sql_fetchrow($result)))
		{
			$message = ($view == 'next') ? 'NO_NEWER_TOPICS' : 'NO_OLDER_TOPICS';
			trigger_error($message);
		}
		else
		{
			$topic_id = $row['topic_id'];
		}
	}
}

// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
$join_sql_table = (!$post_id) ? '' : ', ' . POSTS_TABLE . ' p, ' . POSTS_TABLE . ' p2 ';
if (!$post_id)
{
	$join_sql = "t.topic_id = $topic_id";
}
else
{
	if ($auth->acl_get('m_approve', $forum_id))
	{
		$join_sql = (!$post_id) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
	}
	else
	{
		$join_sql = (!$post_id) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND p.post_approved = 1 AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_approved = 1 AND p2.post_id <= $post_id";
	}
}
$extra_fields = (!$post_id)  ? '' : ', COUNT(p2.post_id) AS prev_posts';
$order_sql = (!$post_id) ? '' : 'GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.poll_max_options, t.poll_start, t.poll_length, t.poll_title, f.forum_name, f.forum_desc, f.forum_parents, f.parent_id, f.left_id, f.right_id, f.forum_status, f.forum_id, f.forum_style, f.forum_password ORDER BY p.post_id ASC';

if ($user->data['user_id'] != ANONYMOUS)
{
	switch (SQL_LAYER)
	{
		case 'oracle':
			// TODO
			break;

		default:
			$extra_fields .= ', tw.notify_status';
			$join_sql_table .= ' LEFT JOIN ' . TOPICS_WATCH_TABLE . ' tw ON (tw.user_id = ' . $user->data['user_id'] . ' 
				AND t.topic_id = tw.topic_id)';
	}
}

// Join to forum table on topic forum_id unless topic forum_id is zero
// whereupon we join on the forum_id passed as a parameter ... this
// is done so navigation, forum name, etc. remain consistent with where
// user clicked to view a global topic
$sql = 'SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_attachment, t.topic_status, t.topic_approved, ' . (($auth->acl_get('m_approve')) ? 't.topic_replies_real AS topic_replies' : 't.topic_replies') . ', t.topic_last_post_id, t.topic_time, t.topic_type, t.poll_max_options, t.poll_start, t.poll_length, t.poll_title, f.forum_name, f.forum_desc, f.forum_parents, f.parent_id, f.left_id, f.right_id, f.forum_status, f.forum_id, f.forum_style, f.forum_password' . $extra_fields . '
	FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f' . $join_sql_table . "
	WHERE $join_sql
		AND (f.forum_id = t.forum_id
			" . ((!$forum_id) ? '' : 'OR (t.topic_type = ' . POST_GLOBAL . " AND f.forum_id = $forum_id)") . "
			)
		$order_sql";
$result = $db->sql_query($sql);

if (!($topic_data = $db->sql_fetchrow($result)))
{
	trigger_error('NO_TOPIC');
}

// Extract the data
extract($topic_data);

// Setup look and feel
$user->setup(false, $forum_style);

if (!$topic_approved && !$auth->acl_get('m_approve', $forum_id))
{
	trigger_error('NO_TOPIC');
}

// Start auth check
if (!$auth->acl_get('f_read', $forum_id))
{
	if ($user->data['user_id'] != ANONYMOUS)
	{
		trigger_error($user->lang['SORRY_AUTH_READ']);
	}

	login_box(preg_replace('#.*?([a-z]+?\.' . $phpEx . '.*?)$#i', '\1', htmlspecialchars($_SERVER['REQUEST_URI'])), '', $user->lang['LOGIN_VIEWFORUM']);
}

// Forum is passworded ... check whether access has been granted to this
// user this session, if not show login box
if ($forum_password)
{
	login_forum_box($topic_data);
}

// Not final in the slightest! Far too simplistic
if ($rate)
{
	// Check for rating count for previous X time

	// Grab existing rating for this post, if it exists
	$sql = 'SELECT * 
		FROM ' . RATINGS_TABLE . ' 
		WHERE user_id = ' . $user->data['user_id'] . "
			AND post_id = $post_id";
	$result = $db->sql_query($sql);

	switch ($_GET['rate'])
	{
		case 'good':
			$rate = 1;
			break;
		case 'bad':
			$rate = -1;
			break;
	}

	$updated = ($row = $db->sql_fetchrow($result)) ? true : false;
	$db->sql_freeresult($result);

	// Insert rating if appropriate
	$sql = (!$updated) ? 'INSERT INTO ' . RATINGS_TABLE . ' (user_id, post_id, rating, rating_time) VALUES (' . $user->data['user_id'] . ", $post_id, $rate, " . time() . ')' : 'UPDATE ' . RATINGS_TABLE . " SET rating = $rate, rating_time = " . time() . " WHERE post_id = $post_id AND user_id = " . $user->data['user_id'];
	$db->sql_query($sql);

	// Rating sum and count past thirty days
	$sql = 'SELECT p.poster_id, SUM(r.rating) AS rated, COUNT(r.rating) as total_ratings
		FROM ' . RATINGS_TABLE . ' r, ' . POSTS_TABLE . ' p, ' . POSTS_TABLE . " p2  
		WHERE p2.post_id = $post_id
			AND p.poster_id = p2.poster_id  
			AND p.post_time > " . (time() - (30 * 86400)) . ' 
			AND r.post_id = p.post_id 
			AND r.user_id <> p2.poster_id 
		GROUP BY p.poster_id';
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	// Rating sum and count since first post
	$sql = 'SELECT p.poster_id, SUM(r.rating) AS rated, COUNT(r.rating) as total_ratings
		FROM ' . RATINGS_TABLE . ' r, ' . POSTS_TABLE . ' p, ' . POSTS_TABLE . " p2  
		WHERE p2.post_id = $post_id 
			AND p.poster_id = p2.poster_id 
			AND p.post_time < " . (time() - (30 * 86400)) . ' 
			AND r.post_id = p.post_id 
			AND r.user_id <> p2.poster_id 
		GROUP BY p.poster_id';
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$total_ratings = $row['total_ratings'];
	$historic_rating = ($row['rated'] / $row['total_ratings']) * 0.30;

	$total_ratings += $row['total_ratings'];
	$thirty_day_rating = ($row['rated'] / $row['total_ratings']) * 0.50;

	if ($total_ratings > $config['min_ratings'])
	{
		// Post count and reg date for this user
		$sql = 'SELECT user_id, user_regdate, user_posts 
			FROM ' . USERS_TABLE . ' 
			WHERE user_id = ' . $row['poster_id'];
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$post_count_rating = ($row['user_posts'] / $config['num_posts']) * 0.1;
		$day_rating = (($row['user_regdate'] > $config['board_startdate']) ? $config['board_startdate'] / $row['user_regdate'] : 1) * 0.1;
		$poster_id = $row['user_id'];

		// Number of rated posts by this user
//		$sql = 'SELECT COUNT(DISTINCT(p.post_id)) AS rated_posts
//			FROM ' . RATINGS_TABLE . ' r , ' . POSTS_TABLE . " p 
//			WHERE p.poster_id = $poster_id  
//				AND r.post_id = p.post_id
//				AND r.user_id <> $poster_id";
//		$result = $db->sql_query($sql);

//		$row = $db->sql_fetchrow($result);
//		$db->sql_freeresult($result);

		$karma = ($historic_rating + $thirty_day_rating + $day_rating + $post_count_rating) * 5;
		$karma = ($karma < 0) ? floor($karma) : (($karma > 0) ? ceil($karma) : 0);

		$sql = 'UPDATE ' . USERS_TABLE . "
			SET user_karma = $karma 
			WHERE user_id = $poster_id";
//		$db->sql_query($sql);
	}

	meta_refresh(3, "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$post_id#$post_id");
	$message = ($updated) ? $user->lang['RATING_UPDATED'] : $user->lang['RATING_ADDED'];
	$message = $message . '<br /><br />' . sprintf($user->lang['RETURN_POST'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;p=$post_id#$post_id\">", '</a>');
	trigger_error($message);
}



// What is start equal to?
if (!empty($post_id))
{
	$start = floor(($prev_posts - 1) / $config['posts_per_page']) * $config['posts_per_page'];
}


// Fill extension informations, if this topic has attachments
$extensions = array();
if ($topic_attachment)
{
	obtain_attach_extensions($extensions);
}


// Are we watching this topic?
$s_watching_topic = $s_watching_topic_img = '';
if ($config['email_enable'] && $config['allow_topic_notify'])
{
	watch_topic_forum('topic', $s_watching_topic, $s_watching_topic_img, $user->data['user_id'], $topic_id, $notify_status);
}


// Post ordering options
$limit_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);

$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
$sort_by_sql = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'p.post_subject');

$s_limit_days = $s_sort_key = $s_sort_dir = $u_sort_param = '';
gen_sort_selects($limit_days, $sort_by_text, $sort_days, $sort_key, $sort_dir, $s_limit_days, $s_sort_key, $s_sort_dir, $u_sort_param);

// Obtain correct post count and ordering SQL if user has
// requested anything different
if ($sort_days)
{
	$min_post_time = time() - ($sort_days * 86400);

	$sql = 'SELECT COUNT(post_id) AS num_posts
		FROM ' . POSTS_TABLE . "
		WHERE topic_id = $topic_id
			AND post_time >= $min_post_time
		" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND p.post_approved = 1');
	$result = $db->sql_query($sql);

	if (isset($_POST['sort']))
	{
		$start = 0;
	}
	$total_posts = ($row = $db->sql_fetchrow($result)) ? $row['num_posts'] : 0;
	$limit_posts_time = "AND p.post_time >= $min_post_time ";
}
else
{
	$total_posts = $topic_replies + 1;
	$limit_posts_time = '';
}

// Select the sort order
$sort_order = $sort_by_sql[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

// Grab ranks
$ranks = array();
obtain_ranks($ranks);

// Grab icons
$icons = array();
obtain_icons($icons);

// Was a highlight request part of the URI?
$highlight_match = $highlight = '';
if ($hilit_words)
{
	foreach (explode(' ', trim($hilit_words)) as $word)
	{
		if (trim($word))
		{
			$highlight_match .= (($highlight_match != '') ? '|' : '') . str_replace('\*', '\w*?', preg_quote($word, '#'));
		}
	}

	$highlight = htmlspecialchars(urlencode($hilit_words));
}

// Forum rules listing
$s_forum_rules = '';
gen_forum_rules('topic', $forum_id);

// Quick mod tools
$topic_mod = '';
$topic_mod .= ($auth->acl_get('m_lock', $forum_id)) ? (($topic_status == ITEM_UNLOCKED) ? '<option value="lock">' . $user->lang['LOCK_TOPIC'] . '</option>' : '<option value="unlock">' . $user->lang['UNLOCK_TOPIC'] . '</option>') : '';
$topic_mod .= ($auth->acl_get('m_delete', $forum_id)) ? '<option value="delete_topic">' . $user->lang['DELETE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_move', $forum_id)) ? '<option value="move">' . $user->lang['MOVE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_split', $forum_id)) ? '<option value="split">' . $user->lang['SPLIT_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_merge', $forum_id)) ? '<option value="merge">' . $user->lang['MERGE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_', $forum_id)) ? '<option value="fork">' . $user->lang['FORK_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_', $forum_id) && $topic_type != POST_NORMAL) ? '<option value="make_normal">' . $user->lang['MAKE_NORMAL'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('f_sticky', $forum_id) && $topic_type != POST_STICKY) ? '<option value="make_sticky">' . $user->lang['MAKE_STICKY'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('f_announce', $forum_id) && $topic_type != POST_ANNOUNCE) ? '<option value="make_announce">' . $user->lang['MAKE_ANNOUNCE'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('f_announce', $forum_id) && $topic_type != POST_GLOBAL) ? '<option value="make_global">' . $user->lang['MAKE_GLOBAL'] . '</option>' : '';
$topic_mod .= ($auth->acl_get('m_', $forum_id)) ? '<option value="viewlogs">' . $user->lang['VIEW_TOPIC_LOGS'] . '</option>' : '';

// If we've got a hightlight set pass it on to pagination.
$pagination_url = "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;" . (($highlight_match) ? "&amp;hilit=$highlight" : '');
$pagination = generate_pagination($pagination_url, $total_posts, $config['posts_per_page'], $start);

// Grab censored words
$censors = array();
obtain_word_list($censors);

// Navigation links
generate_forum_nav($topic_data);

// Moderators
$forum_moderators = array();
get_moderators($forum_moderators, $forum_id);

// This is only used for print view so ...
$server_path = (!$view) ? '' : (($config['cookie_secure']) ? 'https://' : 'http://') . trim($config['server_name']) . (($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/') . trim($config['script_path']) . '/';

// Replace naughty words in title
if (sizeof($censors))
{
	$topic_title = preg_replace($censors['match'], $censors['replace'], $topic_title);
}

// Send vars to template
$template->assign_vars(array(
	'FORUM_ID' 		=> $forum_id,
    'FORUM_NAME' 	=> $forum_name,
	'FORUM_DESC'	=> $forum_desc,
    'TOPIC_ID' 		=> $topic_id,
    'TOPIC_TITLE' 	=> $topic_title,
	'PAGINATION' 	=> $pagination,
	'PAGE_NUMBER' 	=> on_page($total_posts, $config['posts_per_page'], $start),
	'TOTAL_POSTS'	=> ($total_posts == 1) ? $user->lang['VIEW_TOPIC_POST'] : sprintf($user->lang['VIEW_TOPIC_POSTS'], $total_posts), 
	'MCP' 			=> ($auth->acl_get('m_', $forum_id)) ? sprintf($user->lang['MCP'], "<a href=\"mcp.$phpEx?sid=" . $user->session_id . "&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start&amp;$u_sort_param&amp;posts_per_page=" . $config['posts_per_page'] . '">', '</a>') : '',
	'MODERATORS'	=> (sizeof($forum_moderators[$forum_id])) ? implode(', ', $forum_moderators[$forum_id]) : '',

	'POST_IMG' 			=> ($forum_status == ITEM_LOCKED) ? $user->img('btn_locked', $user->lang['FORUM_LOCKED']) : $user->img('btn_post', $user->lang['POST_NEW_TOPIC']),
	'QUOTE_IMG' 		=> $user->img('btn_quote', $user->lang['QUOTE_POST']),
	'REPLY_IMG'			=> ($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) ? $user->img('btn_locked', $user->lang['TOPIC_LOCKED']) : $user->img('btn_reply', $user->lang['REPLY_TO_TOPIC']),
	'EDIT_IMG' 			=> $user->img('btn_edit', $user->lang['EDIT_POST']),
	'DELETE_IMG' 		=> $user->img('btn_delete', $user->lang['DELETE_POST']),
	'IP_IMG' 			=> $user->img('btn_ip', $user->lang['VIEW_IP']),
	'PROFILE_IMG'		=> $user->img('btn_profile', $user->lang['READ_PROFILE']), 
	'SEARCH_IMG' 		=> $user->img('btn_search', $user->lang['SEARCH_USER_POSTS']),
	'PM_IMG' 			=> $user->img('btn_pm', $user->lang['SEND_PRIVATE_MESSAGE']),
	'EMAIL_IMG' 		=> $user->img('btn_email', $user->lang['SEND_EMAIL']),
	'WWW_IMG' 			=> $user->img('btn_www', $user->lang['VISIT_WEBSITE']),
	'ICQ_IMG' 			=> $user->img('btn_icq', $user->lang['ICQ']),
	'AIM_IMG' 			=> $user->img('btn_aim', $user->lang['AIM']),
	'MSN_IMG' 			=> $user->img('btn_msnm', $user->lang['MSNM']),
	'YIM_IMG' 			=> $user->img('btn_yim', $user->lang['YIM']) ,
	'JABBER_IMG'		=> $user->img('btn_jabber', $user->lang['JABBER']) ,
	'REPORT_IMG'		=> $user->img('btn_report', $user->lang['REPORT_POST']),
	'REPORTED_IMG'		=> $user->img('icon_reported', $user->lang['POST_BEEN_REPORTED']),
	'UNAPPROVED_IMG'	=> $user->img('icon_unapproved', $user->lang['POST_NOT_BEEN_APPROVED']),

	'S_SELECT_SORT_DIR' 	=> $s_sort_dir,
	'S_SELECT_SORT_KEY' 	=> $s_sort_key,
	'S_SELECT_SORT_DAYS' 	=> $s_limit_days,
	'S_TOPIC_ACTION' 		=> "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start",
	'S_TOPIC_MOD' 			=> ($topic_mod != '') ? '<select name="mode">' . $topic_mod . '</select>' : '',
	'S_MOD_ACTION' 			=> "mcp.$phpEx?sid=" . $user->session_id . "&amp;t=$topic_id&amp;quickmod=1",

	'S_WATCH_TOPIC' 		=> $s_watching_topic, 
	'S_DISPLAY_SEARCHBOX'	=> ($auth->acl_get('f_search', $forum_id)) ? true : false, 
	'S_SEARCHBOX_ACTION'	=> "search.$phpEx$SID&amp;f=$forum_id", 

	'U_TOPIC'				=> "{$server_path}viewtopic.$phpEx?f=$forum_id&amp;t=$topic_id",
	'U_FORUM'				=> $server_path,
	'U_VIEW_UNREAD_POST'	=> "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;view=unread#unread",
	'U_VIEW_TOPIC' 			=> "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;start=$start&amp;$u_sort_param&amp;hilit=$highlight",
	'U_VIEW_FORUM' 			=> "viewforum.$phpEx$SID&amp;f=$forum_id",
	'U_VIEW_OLDER_TOPIC'	=> "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;view=previous",
	'U_VIEW_NEWER_TOPIC'	=> "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;view=next",
	'U_PRINT_TOPIC'			=> ($auth->acl_get('f_print', $forum_id)) ? "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id&amp;$u_sort_param&amp;view=print" : '',
	'U_EMAIL_TOPIC'			=> ($auth->acl_get('f_email', $forum_id) && $config['email_enable']) ? "memberlist.$phpEx$SID&amp;mode=email&amp;t=$topic_id" : '', 

	'U_POST_NEW_TOPIC' 		=> "posting.$phpEx$SID&amp;mode=post&amp;f=$forum_id",
	'U_POST_REPLY_TOPIC' 	=> "posting.$phpEx$SID&amp;mode=reply&amp;f=$forum_id&amp;t=$topic_id")
);

// Does this topic contain a poll?
if (!empty($poll_start))
{
	$sql = 'SELECT *
		FROM ' . POLL_OPTIONS_TABLE . '
		WHERE topic_id = ' . $topic_id . '
		ORDER BY poll_option_id';
	$result = $db->sql_query($sql);

	$poll_info = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$poll_info[] = $row;
	}
	$db->sql_freeresult($result);

	$cur_voted_id = array();
	if ($user->data['user_id'] != ANONYMOUS)
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
		// the need to delete cookies to mess with results. We could get 
		// a little more clever by time limiting based on ip's but ultimately
		// it can be overcome without great difficulty.
		if (isset($_COOKIE[$config['cookie_name'] . '_poll_' . $topic_id]))
		{
			$cur_voted_id = explode(',', $_COOKIE[$config['cookie_name'] . '_poll_' . $topic_id]);
		}
	}

	$s_can_vote = (((!sizeof($cur_voted_id) && $auth->acl_get('f_vote', $forum_id)) || 
		$auth->acl_get('f_votechg', $forum_id)) && 
		(($poll_length != 0 && $poll_start + $poll_length > time()) || $poll_length == 0) &&
		$topic_status != ITEM_LOCKED && 
		$forum_status != ITEM_LOCKED) ? true : false;
	$s_display_results = (!$s_can_vote || ($s_can_vote && $voted_id) || $view = 'viewpoll') ? true : false;

	if ($update && $s_can_vote)
	{
		if (!sizeof($voted_id) || sizeof($voted_id) > $poll_max_options)
		{
			meta_refresh(5, "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id");

			$message = (!sizeof($voted_id)) ? 'NO_VOTE_OPTION' : 'TOO_MANY_VOTE_OPTIONS';
			$message = $user->lang[$message] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id\">", '</a>');
			trigger_error($message);
		}

		foreach ($voted_id as $option)
		{
			if (in_array($option, $cur_voted_id))
			{
				continue;
			}

			$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . " 
				SET poll_option_total = poll_option_total + 1 
				WHERE poll_option_id = $option 
					AND topic_id = $topic_id";
			$db->sql_query($sql);

			if ($user->data['user_id'] != ANONYMOUS)
			{
				$sql = 'INSERT INTO  ' . POLL_VOTES_TABLE . " (topic_id, poll_option_id, vote_user_id, vote_user_ip) 
					VALUES ($topic_id, $option, " . $user->data['user_id'] . ", '$user->ip')";
				$db->sql_query($sql);
			}
		}

		foreach ($cur_voted_id as $option)
		{
			if (!in_array($option, $voted_id))
			{
				$sql = 'UPDATE ' . POLL_OPTIONS_TABLE . " 
					SET poll_option_total = poll_option_total - 1 
					WHERE poll_option_id = $option 
						AND topic_id = $topic_id";
				$db->sql_query($sql);

				if ($user->data['user_id'] != ANONYMOUS)
				{
					$sql = 'DELETE FROM ' . POLL_VOTES_TABLE . " 
						WHERE topic_id = $topic_id
							AND poll_option_id = $option 
							AND vote_user_id = " . $user->data['user_id'];
					$db->sql_query($sql);
				}
			}
		}

		if ($user->data['user_id'] == ANONYMOUS)
		{
			setcookie($config['cookie_name'] . '_poll_' . $topic_id, implode(',', $voted_id), time() + 31536000, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
		}

		$sql = 'UPDATE ' . TOPICS_TABLE . ' 
			SET poll_last_vote = ' . time() . ', topic_last_post_time = ' . time() . "  
			WHERE topic_id = $topic_id";
		$db->sql_query($sql);

		meta_refresh(5, "viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id");

		$message = $user->lang['VOTE_SUBMITTED'] . '<br /><br />' . sprintf($user->lang['RETURN_TOPIC'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=$topic_id\">", '</a>');
		trigger_error($message);
	}

	$poll_total = 0;
	foreach ($poll_info as $poll_option)
	{
		$poll_total += $poll_option['poll_option_total'];
	}

	foreach ($poll_info as $poll_option)
	{
		$poll_option['poll_option_text'] = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $poll_option['poll_option_text']) : $poll_option['poll_option_text'];
		$option_pct = ($poll_total > 0) ? $poll_option['poll_option_total'] / $poll_total : 0;
		$option_pct_txt = sprintf("%.1d%%", ($option_pct * 100));

		$template->assign_block_vars('poll_option', array(
			'POLL_OPTION_ID' 		=> $poll_option['poll_option_id'],
			'POLL_OPTION_CAPTION' 	=> $poll_option['poll_option_text'],
			'POLL_OPTION_RESULT' 	=> $poll_option['poll_option_total'],
			'POLL_OPTION_PERCENT' 	=> $option_pct_txt,
			'POLL_OPTION_IMG' 		=> $user->img('poll_center', $option_pct_txt, round($option_pct * 250), true), 
			'POLL_OPTION_VOTED'		=> (in_array($poll_option['poll_option_id'], $cur_voted_id)) ? true : false)
		);
	}

	$template->assign_vars(array(
		'POLL_QUESTION'		=> (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $poll_title) : $poll_title,
		'TOTAL_VOTES' 		=> $poll_total,
		'POLL_LEFT_CAP_IMG'	=> $user->img('poll_left'),
		'POLL_RIGHT_CAP_IMG'=> $user->img('poll_right'),

		'L_MAX_VOTES'		=> ($poll_max_options == 1) ? $user->lang['MAX_OPTION_SELECT'] : sprintf($user->lang['MAX_OPTIONS_SELECT'], $poll_max_options), 
		'L_POLL_LENGTH'		=> ($poll_length) ? sprintf($user->lang['POLL_RUN_TILL'], $user->format_date($poll_length + $poll_start)) : '', 

		'S_HAS_POLL'		=> true, 
		'S_CAN_VOTE'		=> $s_can_vote, 
		'S_DISPLAY_RESULTS'	=> $s_display_results,
		'S_IS_MULTI_CHOICE'	=> ($poll_max_options > 1) ? true : false, 
		'S_POLL_ACTION'		=> "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;$u_sort_param",

		'U_VIEW_RESULTS'	=> "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;$u_sort_param&amp;view=viewpoll")
	);

	unset($poll_info);
	unset($voted_id);
}

// Container for user details, only process once
$user_cache = $id_cache = $attachments = $attach_list = $rowset = $update_count = array();
$has_attachments = $display_notice = FALSE;
$force_encoding = '';
$bbcode_bitfield = $i = $i_total = 0;

// If the user is trying to reach the second half of the topic, fetch it starting from the end
$store_reverse = FALSE;
$limit = $config['posts_per_page'];

if ($start > $total_posts / 2)
{
	$store_reverse = TRUE;

	if ($start + $config['posts_per_page'] > $total_posts)
	{
		$limit = min($config['posts_per_page'], max(0, $total_posts - $start));
	}

	$sort_order = preg_replace('/(ASC|DESC)/e', "('\$1' == 'ASC') ? 'DESC' : 'ASC'", $sort_order);
	$start = max(0, $total_posts - $limit - $start);
}

// Go ahead and pull all data for this topic
$sql = 'SELECT u.username, u.user_id, u.user_colour, u.user_posts, u.user_from, u.user_karma, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_jabber, u.user_regdate, u.user_msnm, u.user_allow_viewemail, u.user_allow_viewonline, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_sig_bbcode_bitfield, u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, p.*
	FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . " u 
	WHERE p.topic_id = $topic_id
		" . (($auth->acl_get('m_approve', $forum_id)) ? '' : 'AND p.post_approved = 1') . "
		$limit_posts_time
		AND u.user_id = p.poster_id
	ORDER BY $sort_order";
$result = $db->sql_query_limit($sql, $limit, $start);

if (!$row = $db->sql_fetchrow($result))
{
	trigger_error($user->lang['NO_TOPIC']);
}

// Posts are stored in the $rowset array while $attach_list, $user_cache
// and the global bbcode_bitfield are built
do
{
	$poster_id = $row['poster_id'];
	$poster	= ($poster_id == ANONYMOUS) ? ((!empty($row['post_username'])) ? $row['post_username'] : $user->lang['GUEST']) : $row['username'];

	if ($row['user_karma'] < $user->data['user_min_karma'] && (!$view || $view != 'karma' || $post_id != $row['post_id']))
	{
		$rowset[] = array(
			'below_karma'	=> TRUE,
			'post_id'		=> $row['post_id'], 
			'poster'		=> $poster,
			'user_karma'	=> $row['user_karma']
		);

		continue;
	}

	// Does post have an attachment? If so, add it to the list
	if ($row['post_attachment'] && $config['allow_attachments'])
	{
		$attach_list[] = $row['post_id'];
	
		if ($row['post_approved'])
		{
			$has_attachments = TRUE;
		}
	}

	if ($store_reverse)
	{
		array_unshift($rowset, array(
			'post_id'			=> $row['post_id'],
			'post_time'			=> $row['post_time'],
			'poster'			=> ($row['user_colour']) ? '<span style="color:#' . $row['user_colour'] . '">' . $poster . '</span>' : $poster,
			'user_id'			=> $row['user_id'],
			'topic_id'			=> $row['topic_id'],
			'forum_id'			=> $row['forum_id'],
			'post_subject'		=> $row['post_subject'],
			'post_edit_count'	=> $row['post_edit_count'],
			'post_edit_time'	=> $row['post_edit_time'],
			'icon_id'			=> $row['icon_id'],
			'post_attachment'	=> $row['post_attachment'],
			'post_approved'		=> $row['post_approved'],
			'post_reported'		=> $row['post_reported'],
			'post_text'			=> $row['post_text'],
			'post_encoding'		=> $row['post_encoding'],
			'bbcode_uid'		=> $row['bbcode_uid'],
			'bbcode_bitfield'	=> $row['bbcode_bitfield'],
			'enable_html'		=> $row['enable_html'],
			'enable_smilies'	=> $row['enable_smilies'],
			'enable_sig'		=> $row['enable_sig']
		));
	}
	else
	{
		$rowset[] = array(
			'post_id'			=> $row['post_id'],
			'post_time'			=> $row['post_time'],
			'poster'			=> ($row['user_colour']) ? '<span style="color:#' . $row['user_colour'] . '">' . $poster . '</span>' : $poster,
			'user_id'			=> $row['user_id'],
			'topic_id'			=> $row['topic_id'],
			'forum_id'			=> $row['forum_id'],
			'post_subject'		=> $row['post_subject'],
			'post_edit_count'	=> $row['post_edit_count'],
			'post_edit_time'	=> $row['post_edit_time'],
			'icon_id'			=> $row['icon_id'],
			'post_attachment'	=> $row['post_attachment'],
			'post_approved'		=> $row['post_approved'],
			'post_reported'		=> $row['post_reported'],
			'post_text'			=> $row['post_text'],
			'post_encoding'		=> $row['post_encoding'],
			'bbcode_uid'		=> $row['bbcode_uid'],
			'bbcode_bitfield'	=> $row['bbcode_bitfield'],
			'enable_html'		=> $row['enable_html'],
			'enable_smilies'	=> $row['enable_smilies'],
			'enable_sig'		=> $row['enable_sig']
		);
	}

	// Define the global bbcode bitfield, will be used to load bbcodes
	$bbcode_bitfield |= $row['bbcode_bitfield'];

	// Cache various user specific data ... so we don't have to recompute
	// this each time the same user appears on this page
	if (!isset($user_cache[$poster_id]))
	{
		if ($poster_id == ANONYMOUS)
		{
			$user_cache[$poster_id] = array(
				'joined'		=>	'',
				'posts'			=>	'',
				'from'			=>	'',
				'avatar'		=>	'',
				'rank_title'	=>	'',
				'rank_image'	=>	'',
				'sig'			=>	'',
				'posts'			=>	'',
				'profile'		=>	'',
				'pm'			=>	'',
				'email'			=>	'',
				'www'			=>	'',
				'icq_status_img'=>	'',
				'icq'			=>	'',
				'aim'			=>	'',
				'msn'			=>	'',
				'search'		=>	''
			);
		}
		else
		{
			$user_sig = '';
			if ($row['user_sig'] && $config['allow_sig'] && $user->data['user_viewsigs'])
			{
				$user_sig = $row['user_sig'];
				$bbcode_bitfield |= $row['user_sig_bbcode_bitfield'];
			}

			$id_cache[] = $poster_id;
			$user_cache[$poster_id] = array(
				'joined'		=> $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']),
				'posts'			=> (!empty($row['user_posts'])) ? $row['user_posts'] : '',
				'from'			=> (!empty($row['user_from'])) ? $row['user_from'] : '',
				'karma'			=> (!empty($row['user_karma'])) ? $row['user_karma'] : 0, 
				'karma_img'		=> '<img src="images/karma' . $row['user_karma'] . '.gif" alt="' . $user->lang['KARMA_LEVEL'] . ': ' . $user->lang['KARMA'][$row['user_karma']] . '" title="' . $user->lang['KARMA_LEVEL'] . ': ' .  $user->lang['KARMA'][$row['user_karma']] . '" />',  

				'sig'					=> $user_sig,
				'sig_bbcode_uid'		=> (!empty($row['user_sig_bbcode_uid'])) ? $row['user_sig_bbcode_uid']  : '',
				'sig_bbcode_bitfield'	=> (!empty($row['user_sig_bbcode_bitfield'])) ? $row['user_sig_bbcode_bitfield']  : '',

				'viewonline'	=> $row['user_allow_viewonline'], 

				'avatar'		=> '',

				'profile'		=> "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id",
				'pm'			=> "ucp.$phpEx$SID&amp;mode=message&amp;action=send&amp;u=$poster_id",
				'www'			=> $row['user_website'],
				'aim'			=> ($row['user_aim']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=aim&amp;u=$poster_id" : '',
				'msn'			=> ($row['user_msnm']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=msnm&amp;u=$poster_id" : '',
				'yim'			=> ($row['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&.src=pg' : '',
				'jabber'		=> ($row['user_jabber']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=jabber&amp;u=$poster_id" : '',
				'search'		=> ($auth->acl_get('u_search')) ? "search.$phpEx$SID&amp;search_author=" . urlencode($row['username']) .'&amp;showresults=posts' : ''

			);

			if ($row['user_avatar'] && $user->data['user_viewavatars'])
			{
				$avatar_img = '';
				switch ($row['user_avatar_type'])
				{
					case AVATAR_UPLOAD:
						$avatar_img = $config['avatar_path'] . '/';
						break;
					case AVATAR_GALLERY:
						$avatar_img = $config['avatar_gallery_path'] . '/';
						break;
				}
				$avatar_img .= $row['user_avatar'];

				$user_cache[$poster_id]['avatar'] = '<img src="' . $avatar_img . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />';
			}

			if (!empty($row['user_rank']))
			{
				$user_cache[$poster_id]['rank_title'] = $ranks['special'][$row['user_rank']]['rank_title'];
				$user_cache[$poster_id]['rank_image'] = (!empty($ranks['special'][$row['user_rank']]['rank_image'])) ? '<img src="' . $config['ranks_path'] . '/' . $ranks['special'][$row['user_rank']]['rank_image'] . '" border="0" alt="' . $ranks['special'][$row['user_rank']]['rank_title'] . '" title="' . $ranks['special'][$row['user_rank']]['rank_title'] . '" /><br />' : '';
			}
			else
			{
				foreach ($ranks['normal'] as $rank)
				{
					if ($row['user_posts'] >= $rank['rank_min'])
					{
						$user_cache[$poster_id]['rank_title'] = $rank['rank_title'];
						$user_cache[$poster_id]['rank_image'] = (!empty($rank['rank_image'])) ? '<img src="' . $config['ranks_path'] . '/' . $rank['rank_image'] . '" border="0" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" /><br />' : '';
						break;
					}
				}
			}

			if (!empty($row['user_allow_viewemail']) || $auth->acl_get('a_email'))
			{
				$user_cache[$poster_id]['email'] = ($config['board_email_form'] && $config['email_enable']) ? "memberlist.$phpEx$SID&amp;mode=email&amp;u=" . $poster_id : 'mailto:' . $row['user_email'];
			}
			else
			{
				$user_cache[$poster_id]['email'] = '';
			}

			if (!empty($row['user_icq']))
			{
				$user_cache[$poster_id]['icq'] =  "memberlist.$phpEx$SID&amp;mode=contact&amp;action=icq&amp;u=$poster_id";
				$user_cache[$poster_id]['icq_status_img'] = '<img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" border="0" />';
			}
			else
			{
				$user_cache[$poster_id]['icq_status_img'] = '';
				$user_cache[$poster_id]['icq'] = '';
			}
		}
	}
}
while ($row = $db->sql_fetchrow($result));
$db->sql_freeresult($result);

// Generate online information for user
if ($config['load_onlinetrack'] && sizeof($id_cache))
{
	$sql = 'SELECT session_user_id, MAX(session_time) as online_time, MIN(session_allow_viewonline) AS viewonline 
		FROM ' . SESSIONS_TABLE . ' 
		WHERE session_user_id IN (' . implode(', ', $id_cache) . ')
		GROUP BY session_user_id';
	$result = $db->sql_query($sql);

	$update_time = $config['load_online_time'] * 60;
	while ($row = $db->sql_fetchrow($result))
	{
		$user_cache[$row['session_user_id']]['online'] = (time() - $update_time < $row['online_time'] && (($row['viewonline'] && $user_cache[$row['session_user_id']]['viewonline']) || $auth->acl_get('u_viewonline'))) ? true : false;
	}
}
unset($id_cache);

// Pull attachment data
if (count($attach_list))
{
	if ($auth->acl_get('f_download', $forum_id))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

		$sql = 'SELECT a.post_id, d.* 
			FROM ' . ATTACHMENTS_TABLE . ' a, ' . ATTACHMENTS_DESC_TABLE . ' d
			WHERE a.post_id IN (' . implode(', ', $attach_list) . ')
				AND a.attach_id = d.attach_id
			ORDER BY d.filetime ' . ((!$config['display_order']) ? 'DESC' : 'ASC') . ', a.post_id ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$attachments[$row['post_id']][] = $row;
		}
		$db->sql_freeresult($result);

		// No attachments exist, but post table thinks they do so go ahead and reset post_attach flags
		if (!count($attachments))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . ' 
				SET post_attachment = 0 
				WHERE post_id IN (' . implode(', ', $attach_list) . ')';
			$db->sql_query($sql);

			// We need to update the topic indicator too if the complete topic is now without an attachment
			if (count($rowset) != $total_posts)
			{
				// Not all posts are displayed so we query the db to find if there's any attachment for this topic
				$sql = 'SELECT a.post_id
					FROM ' . ATTACHMENTS_TABLE . ' a, ' . POSTS_TABLE . " p
					WHERE p.topic_id = $topic_id
						AND p.post_approved = 1
						AND p.post_id = a.post_id";
				$result = $db->sql_query_limit($sql, 1);

				if (!$db->sql_fetchrow($result))
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
		elseif ($has_attachments && !$topic_data['topic_attachment'])
		{
			// Topic has approved attachments but its flag is wrong
			$sql = 'UPDATE ' . TOPICS_TABLE . " 
				SET topic_attachment = 1 
				WHERE topic_id = $topic_id";
			$db->sql_query($sql);
		}
	}
	else
	{
		$display_notice = TRUE;
	}
}

// Instantiate BBCode if need be
if ($bbcode_bitfield)
{
	include($phpbb_root_path . 'includes/bbcode.'.$phpEx);
	$bbcode = new bbcode($bbcode_bitfield);
}

$i_total = sizeof($rowset) - 1;
$prev_post_id = '';

// Output the posts
foreach ($rowset as $i => $row)
{
	$poster_id = $row['user_id'];

	// Three situations can prevent a post being display:
	// i)   The posters karma is below the minimum of the user 
	// ii)  The poster is on the users ignore list
	// iii) The post was made in a codepage different from the users
	if (!empty($row['below_karma']))
	{
		$template->assign_block_vars('postrow', array(
			'S_IGNORE_POST' => true, 
			'S_ROW_COUNT'	=> $i,

			'L_IGNORE_POST' => sprintf($user->lang['POST_BELOW_KARMA'], $row['poster'], $row['user_karma'], "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;p=" . $row['post_id'] . '&amp;view=karma#' . $row['post_id'] . '">', '</a>'))
		);

		continue;
	}
	else if ($row['post_encoding'] != $user->lang['ENCODING'])
	{
		if ($view == 'encoding' && $post_id == $row['post_id'])
		{
			$force_encoding = $row['post_encoding'];
		}
		else
		{
			$template->assign_block_vars('postrow', array(
				'S_IGNORE_POST'	=> true, 
				'S_ROW_COUNT'	=> $i,

				'L_IGNORE_POST'	=> sprintf($user->lang['POST_ENCODING'], $row['poster'], '<a href="viewtopic.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;view=encoding#' . $row['post_id'] . '">', '</a>'))
			);

			continue;
		}
	}

	// End signature parsing, only if needed
	if ($row['enable_sig'] && $user_cache[$poster_id]['sig'] && empty($user_cache['sig_parsed']))
	{
		$user_cache[$poster_id]['sig'] = ($config['enable_smilies']) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $user_cache[$poster_id]['sig']) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $user_cache[$poster_id]['sig']);

		if ($user_cache[$poster_id]['sig_bbcode_bitfield'])
		{
			$bbcode->bbcode_second_pass($user_cache[$poster_id]['sig'], $user_cache[$poster_id]['sig_bbcode_uid'], $user_cache[$poster_id]['sig_bbcode_bitfield']);
		}

		if (count($censors))
		{
			$user_cache[$poster_id]['sig'] = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $user_cache[$poster_id]['sig'] . '<'), 1, -1));
		}

		$user_cache[$poster_id]['sig'] = str_replace("\n", '<br />', $user_cache[$poster_id]['sig']);
		$user_cache[$poster_id]['sig_parsed'] = TRUE;
	}

	// Parse the message and subject
	$message = $row['post_text'];

	// If the board has HTML off but the post has HTML on then we process it, else leave it alone
	if (!$auth->acl_get('f_html', $forum_id))
	{
		if ($row['enable_html'] && $auth->acl_get('f_bbcode', $forum_id))
		{
			$message = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $message);
		}
	}

	// Second parse bbcode here
	if ($row['bbcode_bitfield'])
	{
		$bbcode->bbcode_second_pass($message, $row['bbcode_uid'], $row['bbcode_bitfield']);
	}

	// If we allow users to disable display of emoticons
	// we'll need an appropriate check and preg_replace here
	$message = (empty($config['allow_smilies']) || !$user->data['user_viewsmilies']) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);

	// Highlight active words (primarily for search)
	if ($highlight_match)
	{
		// This was shamelessly 'borrowed' from volker at multiartstudio dot de
		// via php.net's annotated manual
		$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace('#\b(" . $highlight_match . ")\b#i', '<span class=\"posthilit\">\\\\1</span>', '\\0')", '>' . $message . '<'), 1, -1));
	}

	// Replace naughty words such as farty pants
	if (sizeof($censors))
	{
		$row['post_subject'] = preg_replace($censors['match'], $censors['replace'], $row['post_subject']);
		$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $message . '<'), 1, -1));
	}

	$message = str_replace("\n", '<br />', $message);

	// Editing information
	if (!empty($row['post_edit_count']) && $config['display_last_edited'])
	{
		$l_edit_time_total = ($row['post_edit_count'] == 1) ? $user->lang['EDITED_TIME_TOTAL'] : $user->lang['EDITED_TIMES_TOTAL'];

		$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, $row['poster'], $user->format_date($row['post_edit_time']), $row['post_edit_count']);
	}
	else
	{
		$l_edited_by = '';
	}

	// Dump vars into template
	$template->assign_block_vars('postrow', array(
		'POSTER_NAME' 	=> $row['poster'],
		'POSTER_RANK' 	=> $user_cache[$poster_id]['rank_title'],
		'RANK_IMAGE' 	=> $user_cache[$poster_id]['rank_image'],
		'POSTER_JOINED' => $user_cache[$poster_id]['joined'],
		'POSTER_POSTS' 	=> $user_cache[$poster_id]['posts'],
		'POSTER_FROM' 	=> $user_cache[$poster_id]['from'],
		'POSTER_AVATAR' => $user_cache[$poster_id]['avatar'],
		'POSTER_KARMA'	=> $user_cache[$poster_id]['karma'], 

		'POST_DATE' 	=> $user->format_date($row['post_time']),
		'POST_SUBJECT' 	=> $row['post_subject'],
		'MESSAGE' 		=> $message,
		'SIGNATURE' 	=> ($row['enable_sig']) ? $user_cache[$poster_id]['sig'] : '',
		'EDITED_MESSAGE'=> $l_edited_by,

		'MINI_POST_IMG' => ($row['post_time'] > $user->data['user_lastvisit'] && $row['post_time'] > $topic_last_read && $user->data['user_id'] != ANONYMOUS) ? $user->img('icon_post_new', $user->lang['NEW_POST']) : $user->img('icon_post', $user->lang['POST']),
		'POST_ICON_IMG' => (!empty($row['icon_id'])) ? '<img src="' . $config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',
		'ICQ_STATUS_IMG'	=> $user_cache[$poster_id]['icq_status_img'],
		'KARMA_IMG'			=> $user_cache[$poster_id]['karma_img'], 
		'ONLINE_IMG'		=> ($poster_id == ANONYMOUS || !$config['load_onlinetrack']) ? '' : (($user_cache[$poster_id]['online']) ? $user->img('btn_online', $user->lang['ONLINE']) : $user->img('btn_offline', $user->lang['OFFLINE'])), 

		'U_EDIT' 			=> (($user->data['user_id'] == $poster_id && $auth->acl_get('f_edit', $forum_id) && ($row['post_time'] > time() - $config['edit_time'] || !$config['edit_time'])) || $auth->acl_get('m_edit', $forum_id)) ? "posting.$phpEx$SID&amp;mode=edit&amp;f=$forum_id&amp;p=" . $row['post_id'] : '',
		'U_QUOTE' 			=> ($auth->acl_get('f_quote', $forum_id)) ? "posting.$phpEx$SID&amp;mode=quote&amp;f=$forum_id&amp;p=" . $row['post_id'] : '', 
		'U_IP' 				=> ($auth->acl_get('m_ip', $forum_id)) ? "mcp.$phpEx?sid=" . $user->session_id . "&amp;mode=post_details&amp;p=" . $row['post_id'] . "&amp;t=$topic_id#ip" : '',
		'U_DELETE' 			=> (($user->data['user_id'] == $poster_id && $auth->acl_get('f_delete', $forum_id) && $topic_data['topic_last_post_id'] == $row['post_id'] && ($row['post_time'] > time() - $config['edit_time'] || !$config['edit_time'])) || $auth->acl_get('m_delete', $forum_id)) ? "posting.$phpEx$SID&amp;mode=delete&amp;f=$forum_id&amp;p=" . $row['post_id'] : '',

		'U_PROFILE' 		=> $user_cache[$poster_id]['profile'],
		'U_SEARCH' 			=> $user_cache[$poster_id]['search'],
		'U_PM' 				=> $user_cache[$poster_id]['pm'],
		'U_EMAIL' 			=> $user_cache[$poster_id]['email'],
		'U_WWW' 			=> $user_cache[$poster_id]['www'],
		'U_ICQ' 			=> $user_cache[$poster_id]['icq'],
		'U_AIM' 			=> $user_cache[$poster_id]['aim'],
		'U_MSN' 			=> $user_cache[$poster_id]['msn'],
		'U_YIM' 			=> $user_cache[$poster_id]['yim'],
		'U_JABBER'			=> $user_cache[$poster_id]['jabber'], 

		'U_RATE_GOOD'		=> "viewtopic.$phpEx$SID&amp;rate=good&amp;p=" . $row['post_id'], 
		'U_RATE_BAD'		=> "viewtopic.$phpEx$SID&amp;rate=bad&amp;p=" . $row['post_id'], 
		'U_REPORT'			=> "report.$phpEx$SID&amp;p=" . $row['post_id'],
		'U_MCP_REPORT'		=> ($auth->acl_get('f_report', $forum_id)) ? "mcp.$phpEx$SID&amp;mode=post_details&amp;p=" . $row['post_id'] : '',
		'U_MCP_APPROVE'		=> "mcp.$phpEx$SID&amp;mode=approve&amp;p=" . $row['post_id'],
		'U_MCP_DETAILS'	=>	"mcp.$phpEx$SID&amp;mode=post_details&amp;p=" . $row['post_id'],
		'U_MINI_POST'		=> "viewtopic.$phpEx$SID&amp;p=" . $row['post_id'] . '#' . $row['post_id'],
		'U_POST_ID' 		=> ($unread_post_id == $row['post_id']) ? 'unread' : $row['post_id'],
		'U_NEXT_POST_ID'	=> ($i < $i_total) ? $rowset[$i + 1]['post_id'] : '', 
		'U_PREV_POST_ID'	=> $prev_post_id, 

		'S_ROW_COUNT'		=> $i,
		'S_CAN_RATE'		=> ($auth->acl_get('f_rate', $forum_id) && $row['post_approved'] && !$row['post_reported'] && $poster_id != $user->data['user_id'] && $poster_id != ANONYMOUS) ? true : false, 
		'S_HAS_ATTACHMENTS' => (!empty($attachments[$row['post_id']])) ? TRUE : FALSE,
		'S_POST_UNAPPROVED'	=> ($row['post_approved']) ? FALSE : TRUE,
		'S_POST_REPORTED'	=> ($row['post_reported'] && $auth->acl_get('m_', $forum_id)) ? TRUE : FALSE,
		'S_DISPLAY_NOTICE'	=> $display_notice && $row['post_attachment'])
	);

	// Process Attachments for this post
	if (sizeof($attachments[$row['post_id']]))
	{
		display_attachments($attachments[$row['post_id']], $update_count);
	}

	$prev_post_id = $row['post_id'];

	unset($rowset[$i]);
	unset($attachments[$row['post_id']]);
}
unset($rowset);
unset($user_cache);

// Update topic view and if necessary attachment view counters ... but only
// if this is the first 'page view'
if (!preg_match("#&t=$topic_id#", $user->data['session_page']))
{
	$sql = 'UPDATE ' . TOPICS_TABLE . '
		SET topic_views = topic_views + 1, topic_last_view_time = ' . time() . "
		WHERE topic_id = $topic_id";
	$db->sql_query($sql);

	// Update the attachment download counts
	if (sizeof($update_count))
	{
		$sql = 'UPDATE ' . ATTACHMENTS_DESC_TABLE . ' 
			SET download_count = download_count + 1 
			WHERE attach_id IN (' . implode(', ', array_unique($update_count)) . ')';
		$db->sql_query($sql);
	}
}

// Mark topics read
$mark_forum_id = ($topic_type == POST_GLOBAL) ? 0 : $forum_id;
markread('topic', $mark_forum_id, $topic_id, $row['post_time']);

// Change encoding if appropriate
if ($force_encoding != '')
{
	$user->lang['ENCODING'] = $force_encoding;
}

// Output the page
page_header($user->lang['VIEW_TOPIC'] .' - ' . $topic_title);

$template->set_filenames(array(
	'body' => ($view == 'print') ? 'viewtopic_print.html' : 'viewtopic_body.html')
);
make_jumpbox('viewforum.'.$phpEx, $forum_id);

page_footer();

?>