<?php
/***************************************************************************
 *                               viewtopic.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

// Start initial var setup
$topic_id = (isset($_GET['t'])) ? intval($_GET['t']) : 0;
$post_id = (isset($_GET['p'])) ? intval($_GET['p']) : 0;
$start = (isset($_GET['start'])) ? intval($_GET['start']) : 0;

if (empty($topic_id) && empty($post_id))
{
	trigger_error('Topic_post_not_exist');
}

// Start session management
$user->start();
// End session management



// Find topic id if user requested a newer or older topic
if (isset($_GET['view']) && empty($post_id))
{
	if ($_GET['view'] == 'newest')
	{
		if ($user->session_id)
		{
			$sql = "SELECT p.post_id
				FROM " . POSTS_TABLE . " p, " . SESSIONS_TABLE . " s,  " . USERS_TABLE . " u
				WHERE s.session_id = '$user->session_id'
					AND u.user_id = s.session_user_id
					AND p.topic_id = $topic_id
					AND p.post_approved = 1
					AND p.post_time >= u.user_lastvisit
				ORDER BY p.post_time ASC";
			$result = $db->sql_query_limit($sql, 1);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error('No_new_posts_last_visit');
			}

			$post_id = $row['post_id'];
			$newest_post_id = $post_id;
			//redirect("viewtopic.$phpEx$SID&p=$post_id#$post_id");
		}

		redirect("index.$phpEx");
	}
	else if ($_GET['view'] == 'next' || $_GET['view'] == 'previous')
	{
		$sql_condition = ($_GET['view'] == 'next') ? '>' : '<';
		$sql_ordering = ($_GET['view'] == 'next') ? 'ASC' : 'DESC';

		$sql = "SELECT t.topic_id
			FROM " . TOPICS_TABLE . " t, " . TOPICS_TABLE . " t2
			WHERE t2.topic_id = $topic_id
				AND t.forum_id = t2.forum_id
				AND t.topic_last_post_time $sql_condition t2.topic_last_post_time
			ORDER BY t.topic_last_post_time $sql_ordering";
		$result = $db->sql_query_limit($sql, 1);

		if (!($row = $db->sql_fetchrow($result)))
		{
			$message = ($_GET['view'] == 'next') ? 'No_newer_topics' : 'No_older_topics';
			trigger_error($message);
		}
		else
		{
			$topic_id = $row['topic_id'];
		}
	}
}



// Look at this query ... perhaps a re-think? Perhaps store topic ids rather
// than last/first post ids and have a redirect at the top of this page
// for latest post, newest post for a given topic_id?

// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
$join_sql_table = (!$post_id) ? '' : ', ' . POSTS_TABLE . ' p, ' . POSTS_TABLE . ' p2 ';
$join_sql = (!$post_id) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND p.post_approved = " . TRUE . " AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_approved = " . TRUE . " AND p2.post_id <= $post_id";
$extra_fields = (!$post_id)  ? '' : ", COUNT(p2.post_id) AS prev_posts";
$order_sql = (!$post_id) ? '' : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, f.forum_name, f.forum_desc, f.forum_parents, f.parent_id, f.left_id, f.right_id, f.forum_status, f.forum_id, f.forum_style ORDER BY p.post_id ASC";

if ($user->data['user_id'] != ANONYMOUS)
{
	switch (SQL_LAYER)
	{
		//TODO
		case 'oracle':
		break;

		default:
			$extra_fields .= ', tw.notify_status';
			$join_sql_table .= ' LEFT JOIN ' . TOPICS_WATCH_TABLE . ' tw ON tw.user_id = ' . $user->data['user_id'] . ' AND t.topic_id = tw.topic_id';
	}
}

$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.poll_start, t.poll_length, t.poll_title, f.forum_name, f.forum_desc, f.forum_parents, f.parent_id, f.left_id, f.right_id, f.forum_status, f.forum_id, f.forum_style" . $extra_fields . "
	FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $join_sql_table . "
	WHERE $join_sql
		AND f.forum_id = t.forum_id
		$order_sql";
$result = $db->sql_query($sql);

if (!$topic_data = $db->sql_fetchrow($result))
{
	trigger_error('Topic_post_not_exist');
}
extract($topic_data);



// Configure style, language, etc.
$user->setup(false, intval($forum_style));
$auth->acl($user->data, intval($forum_id));
// End configure

// Start auth check
if (!$auth->acl_gets('f_read', 'm_', 'a_', intval($forum_id)))
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		$redirect = (isset($post_id)) ? "p=$post_id" : "t=$topic_id";
		$redirect .= (isset($start)) ? "&start=$start" : '';
		redirect('login.' . $phpEx . $SID . '&redirect=viewtopic.' . $phpEx . '&' . $redirect);
	}

	trigger_error($user->lang['Sorry_auth_read']);
}
// End auth check




if (!empty($post_id))
{
	$start = floor(($prev_posts - 1) / $config['posts_per_page']) * $config['posts_per_page'];
}

$s_watching_topic = '';
$s_watching_topic_img = '';
watch_topic_forum('topic', $s_watching_topic, $s_watching_topic_img, $user->data['user_id'], $topic_id, $notify_status);




// Post ordering options
$previous_days = array(0 => $user->lang['ALL_POSTS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 's' => $user->lang['SUBJECT']);
$sort_by = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'pt.post_subject');

if (isset($_POST['sort']))
{
	if (!empty($_POST['sort_days']))
	{
		$sort_days = (!empty($_POST['sort_days'])) ? intval($_POST['sort_days']) : intval($_GET['sort_days']);
		$min_post_time = time() - ($sort_days * 86400);

		$sql = "SELECT COUNT(post_id) AS num_posts
			FROM " . POSTS_TABLE . "
			WHERE topic_id = $topic_id
				AND post_time >= $min_post_time
				AND post_approved = " . TRUE;
		$result = $db->sql_query($sql);

		$start = 0;
		$topic_replies = ($row = $db->sql_fetchrow($result)) ? $row['num_posts'] : 0;
		$limit_posts_time = "AND p.post_time >= $min_post_time ";
	}
	else
	{
		$topic_replies++;
	}

	$sort_key = (isset($_POST['sort_key'])) ? $_POST['sort_key'] : $_GET['sort_key'];
	$sort_dir = (isset($_POST['sort_dir'])) ? $_POST['sort_dir'] : $_GET['sort_dir'];
}
else
{
	$topic_replies++;
	$limit_posts_time = '';

	$sort_days = 0;
	$sort_key = 't';
	$sort_dir = 'a';
}

$sort_order = $sort_by[$sort_key] . ' ' . (($sort_dir == 'd') ? 'DESC' : 'ASC');

$select_sort_days = '<select name="sort_days">';
foreach ($previous_days as $day => $text)
{
	$selected = ($sort_days == $day) ? ' selected="selected"' : '';
	$select_sort_days .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
}
$select_sort_days .= '</select>';

$select_sort = '<select name="sort_key">';
foreach ($sort_by_text as $key => $text)
{
	$selected = ($sort_key == $key) ? ' selected="selected"' : '';
	$select_sort .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
}
$select_sort .= '</select>';

$select_sort_dir = '<select name="sort_dir">';
$select_sort_dir .= ($sort_dir == 'a') ? '<option value="a" selected="selected">' . $user->lang['ASCENDING'] . '</option><option value="d">' . $user->lang['DESCENDING'] . '</option>' : '<option value="a">' . $user->lang['ASCENDING'] . '</option><option value="d" selected="selected">' . $user->lang['DESCENDING'] . '</option>';
$select_sort_dir .= '</select>';

$select_post_days = '<select name="postdays">';
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($post_days == $previous_days[$i]) ? ' selected="selected"' : '';
	$select_post_days .= '<option value="' . $previous_days[$i] . '"' . $selected . '>' . $previous_days_text[$i] . '</option>';
}
$select_post_days .= '</select>';



$sql = "SELECT *
	FROM " . RANKS_TABLE;
$result = $db->sql_query($sql);

$ranksrow = array();
while ($row = $db->sql_fetchrow($result))
{
	$ranksrow[] = $row;
}
$db->sql_freeresult($result);



// Grab icons
$icons = array();
obtain_icons($icons);



// Was a highlight request part of the URI?
$highlight_match = $highlight = '';
if (isset($_GET['highlight']))
{
	// Split words and phrases
	$words = explode(' ', trim(htmlspecialchars(urldecode($_GET['highlight']))));

	foreach ($words as $word)
	{
		if (trim($word) != '')
		{
			$highlight_match .= (($highlight_match != '') ? '|' : '') . str_replace('*', '\w*', preg_quote($word, '#'));
		}
	}
	unset($words);

	$highlight = urlencode($_GET['highlight']);
}



// Quick mod tools
$s_forum_rules = '';
get_forum_rules('topic', $s_forum_rules, $forum_id);

$topic_mod = '';
$topic_mod .= ($auth->acl_gets('m_lock', 'a_', $forum_id)) ? ((intval($topic_status) == ITEM_UNLOCKED) ? '<option value="lock">' . $user->lang['LOCK_TOPIC'] . '</option>' : '<option value="unlock">' . $user->lang['UNLOCK_TOPIC'] . '</option>') : '';
$topic_mod .= ($auth->acl_gets('m_delete', 'a_', $forum_id)) ? '<option value="delete">' . $user->lang['DELETE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_gets('m_move', 'a_', $forum_id)) ? '<option value="move">' . $user->lang['MOVE_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_gets('m_split', 'a_', $forum_id)) ? '<option value="split">' . $user->lang['SPLIT_TOPIC'] . '</option>' : '';
$topic_mod .= ($auth->acl_gets('m_merge', 'a_', $forum_id)) ? '<option value="merge">' . $user->lang['MERGE_TOPIC'] . '</option>' : '';

// If we've got a hightlight set pass it on to pagination.
$pagination = ($highlight_match) ? generate_pagination("viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=$highlight", $topic_replies, $config['posts_per_page'], $start) : generate_pagination("viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $topic_replies, $config['posts_per_page'], $start);

// Post, reply and other URL generation for
// templating vars
$new_topic_url = 'posting.' . $phpEx . $SID . '&amp;mode=post&amp;f=' . $forum_id;
$reply_topic_url = 'posting.' . $phpEx . $SID . '&amp;mode=reply&amp;f=' . $forum_id . '&amp;t=' . $topic_id;
$view_forum_url = 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id;
$view_prev_topic_url = 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '&amp;view=previous';
$view_next_topic_url = 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '&amp;view=next';

$reply_img = ($forum_status == ITEM_LOCKED || $topic_status == ITEM_LOCKED) ? $user->img('reply_locked', $user->lang['Topic_locked']) : $user->img('reply_new', $user->lang['Reply_to_topic']);
$post_img = ($forum_status == ITEM_LOCKED) ? $user->img('post_locked', $user->lang['Forum_locked']) : $user->img('post_new', $user->lang['Post_new_topic']);



// Set a cookie for this topic
if ($user->data['user_id'] != ANONYMOUS)
{
	$mark_topics = (isset($_COOKIE[$config['cookie_name'] . '_t'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_t'])) : array();

	$mark_topics[$forum_id][$topic_id] = 0;
	setcookie($config['cookie_name'] . '_t', serialize($mark_topics), 0, $config['cookie_path'], $config['cookie_domain'], $config['cookie_secure']);
}




// Grab censored words
$censors = array();
obtain_word_list($censors);

// Replace naughty words in title
if (sizeof($censors))
{
	$topic_title = preg_replace($censors['match'], $censors['replace'], $topic_title);
}


// Navigation links
generate_forum_nav($topic_data);


// Moderators
$forum_moderators = array();
get_moderators($forum_moderators, $forum_id);



// This is only used for print view so ...
$server_path = (($config['cookie_secure']) ? 'https://' : 'http://' ) . trim($config['server_name']) . (($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/') . trim($config['script_path']) . '/';



// Send vars to template
$template->assign_vars(array(
	'FORUM_ID' 		=> $forum_id,
    'FORUM_NAME' 	=> $forum_name,
	'FORUM_DESC'	=> strip_tags($forum_desc),
    'TOPIC_ID' 		=> $topic_id,
    'TOPIC_TITLE' 	=> $topic_title,
	'PAGINATION' 	=> $pagination,
	'PAGE_NUMBER' 	=> on_page($topic_replies, $config['posts_per_page'], $start),
	'MCP' 			=> ($auth->acl_gets('m_', 'a_', $forum_id)) ? sprintf($user->lang['MCP'], '<a href="mcp.' . $phpEx . '?sid=' . $user->session_id . '&amp;f=' . $forum_id . '">', '</a>') : '',
	'MODERATORS'	=> (sizeof($forum_moderators[$forum_id])) ? implode(', ', $forum_moderators[$forum_id]) : $user->lang['NONE'],

	'POST_IMG' 	=> $post_img,
	'REPLY_IMG' => $reply_img,

	'S_TOPIC_LINK' 			=> 't',
	'S_SELECT_SORT_DIR' 	=> $select_sort_dir,
	'S_SELECT_SORT_KEY' 	=> $select_sort,
	'S_SELECT_SORT_DAYS' 	=> $select_sort_days,
	'S_SELECT_RATING' 		=> $rating,
	'S_TOPIC_ACTION' 		=> "viewtopic.$phpEx$SID&amp;t=" . $topic_id . "&amp;start=$start",
	'S_AUTH_LIST' 			=> $s_forum_rules,
	'S_TOPIC_MOD' 			=> ( $topic_mod != '' ) ? '<select name="mode">' . $topic_mod . '</select>' : '',
	'S_MOD_ACTION' 			=> "mcp.$phpEx?sid=" . $user->session_id . "&amp;t=$topic_id&amp;quickmod=1",
	'S_WATCH_TOPIC' 		=> $s_watching_topic,

	'U_TOPIC'				=> $server_path . 'viewtopic.' . $phpEx  . '?t=' . $topic_id,
	'U_FORUM'				=> $server_path,

	'U_VIEW_TOPIC' 			=> "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;start=$start&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=$highlight",
	'U_VIEW_FORUM' 			=> $view_forum_url,
	'U_VIEW_OLDER_TOPIC'	=> $view_prev_topic_url,
	'U_VIEW_NEWER_TOPIC'	=> $view_next_topic_url,
	'U_PRINT_TOPIC'			=> "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;start=$start&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=$highlight&amp;view=print",
	'U_POST_NEW_TOPIC' 		=> $new_topic_url,
	'U_POST_REPLY_TOPIC' 	=> $reply_topic_url)
);



// Mozilla navigation bar
$nav_links['prev'] = array(
	'url' => $view_prev_topic_url,
	'title' => $user->lang['View_previous_topic']
);
$nav_links['next'] = array(
	'url' => $view_next_topic_url,
	'title' => $user->lang['View_next_topic']
);
$nav_links['up'] = array(
	'url' => $view_forum_url,
	'title' => $forum_name
);





// Does this topic contain a poll?
if (!empty($poll_start))
{
	$sql = "SELECT *
		FROM " . POLL_OPTIONS_TABLE . "
		WHERE topic_id = $topic_id
		ORDER BY poll_option_id";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$poll_info[] = $row;
	}
	$db->sql_freeresult($result);

	$sql = "SELECT poll_option_id
		FROM " . POLL_VOTES_TABLE . "
		WHERE topic_id = $topic_id
			AND vote_user_id = " . $user->data['user_id'];
	$result = $db->sql_query($sql);

	$voted_id = ($row = $db->sql_fetchrow($result)) ? $row['poll_option_id'] : false;
	$db->sql_freeresult($result);

	$display_results = ($voted_id || ($poll_length != 0 && $poll_start + $poll_length < time()) || $_GET['vote'] == 'viewresult' || !$auth->acl_gets('f_vote', 'm_', 'a_', $forum_id) || $topic_status == ITEM_LOCKED || $forum_status == ITEM_LOCKED) ? true : false;

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
			'POLL_OPTION_PERCENT' 	=> $vote_percent,
			'POLL_OPTION_IMG' 		=> $user->img('poll_center', $option_pct_txt, round($option_pct * $user->theme['poll_length']), true))
		);
	}

	$poll_title = (sizeof($censors)) ? preg_replace($censors['match'], $censors['replace'], $poll_title) : $poll_title;

	$template->assign_vars(array(
		'POLL_QUESTION'		=> $poll_title,
		'TOTAL_VOTES' 		=> $poll_total,
		'POLL_LEFT_CAP_IMG'	=> $user->img('poll_left'),
		'POLL_RIGHT_CAP_IMG'=> $user->img('poll_right'),

		'S_HAS_POLL_OPTIONS'=> !$display_results,
		'S_HAS_POLL_DISPLAY'=> $display_results,
		'S_POLL_ACTION'		=>	"viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$poster_order",

		'L_SUBMIT_VOTE'	=> $user->lang['Submit_vote'],
		'L_VIEW_RESULTS'=> $user->lang['View_results'],
		'L_TOTAL_VOTES' => $user->lang['Total_votes'],

		'U_VIEW_RESULTS' => "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;vote=viewresult")
	);
}



// TEMP TEMP TEMP TEMP
$rating = '';
for ($i = 0; $i < 6; $i++)
{
	$rating .= (($rating != '') ? '&nbsp;' : '') . '<a href="viewtopic.' . $phpEx . $SID . '&amp;p=??&amp;rate=' . $i . '">' . $i . '</a>';
}
// TEMP TEMP TEMP TEMP



// Container for user details, only process once
$user_cache = $attach_list = array();
$i = 0;

// Go ahead and pull all data for this topic
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_karma, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_avatar, u.user_avatar_type, p.*, pt.post_text, pt.post_subject, pt.bbcode_uid
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id
		AND p.post_approved = " . TRUE . "
		AND pt.post_id = p.post_id
		$limit_posts_time
		AND u.user_id = p.poster_id
	ORDER BY $sort_order";
$result = $db->sql_query_limit($sql, $start, $config['posts_per_page']);

if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$poster_id = $row['user_id'];
		$poster = (!$poster_id) ? $user->lang['GUEST'] : $row['username'];

		// Should we display this post? At present this is just karma but
		// it will also check the ignore list in future ... outputting the
		// appropriate message of course.
		if ($row['user_karma'] < $user->data['user_min_karma'] && (empty($_GET['view']) || $_GET['view'] != 'karma' || $post_id != $row['post_id']))
		{
			$template->assign_block_vars('postrow', array(
				'S_BELOW_MIN_KARMA' => true, 
				'S_ROW_COUNT' => $i++,

				'L_IGNORE_POST' => sprintf($user->lang['POST_BELOW_KARMA'], $poster, '<a href="viewtopic.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '&amp;view=karma#' . $row['post_id'] . '">', '</a>'))
			);

			continue;
		}

		// Display the post
		$poster_posts = ($row['user_id']) ? $user->lang['POSTS'] . ': ' . $row['user_posts'] : '';

		$poster_from = ($row['user_from'] && $row['user_id']) ? $user->lang['LOCATION'] . ': ' . $row['user_from'] : '';

		if (!isset($user_cache[$poster_id]['joined']))
		{
			$user_cache[$poster_id]['joined'] = ($row['user_id']) ? $user->lang['JOINED'] . ': ' . $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']) : '';
		}

		if (isset($user_cache[$poster_id]['avatar']))
		{
			if ($row['user_avatar_type'] && $poster_id && $row['user_allowavatar'])
			{
				switch ($row['user_avatar_type'])
				{
					case USER_AVATAR_UPLOAD:
						$user_cache[$poster_id]['avatar'] = ($config['allow_avatar_upload']) ? '<img src="' . $config['avatar_path'] . '/' . $row['user_avatar'] . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />' : '';
						break;
					case USER_AVATAR_REMOTE:
						$user_cache[$poster_id]['avatar'] = ($config['allow_avatar_remote']) ? '<img src="' . $row['user_avatar'] . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />' : '';
						break;
					case USER_AVATAR_GALLERY:
						$user_cache[$poster_id]['avatar'] = ($config['allow_avatar_local']) ? '<img src="' . $config['avatar_gallery_path'] . '/' . $row['user_avatar'] . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />' : '';
						break;
				}
			}
			else
			{
				$user_cache[$poster_id]['avatar'] = '';
			}
		}



		// Generate ranks, set them to empty string initially.
		if (!isset($user_cache[$poster_id]['rank_title']))
		{
			if ($row['user_rank'])
			{
				for($j = 0; $j < count($ranksrow); $j++)
				{
					if ($row['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'])
					{
						$user_cache[$poster_id]['rank_title'] = $ranksrow[$j]['rank_title'];
						$user_cache[$poster_id]['rank_image'] = ($ranksrow[$j]['rank_image']) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" border="0" alt="' . $poster_rank . '" title="' . $poster_rank . '" /><br />' : '';
					}
				}
			}
			else
			{
				for($j = 0; $j < count($ranksrow); $j++)
				{
					if ($row['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'])
					{
						$user_cache[$poster_id]['rank_title'] = $ranksrow[$j]['rank_title'];
						$user_cache[$poster_id]['rank_image'] = ($ranksrow[$j]['rank_image']) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" border="0" alt="' . $poster_rank . '" title="' . $poster_rank . '" /><br />' : '';
					}
				}
			}
		}



		// Handle anon users posting with usernames
		if (!$poster_id && $row['post_username'] != '')
		{
			$poster = $row['post_username'];
			$poster_rank = $user->lang['GUEST'];
		}



		if (!isset($user_cache[$poster_id]['profile']) && $poster_id)
		{
			$temp_url = "ucp.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id";
			$user_cache[$poster_id]['profile_img'] = '<a href="' . $temp_url . '">' . $user->img('icon_profile', $user->lang['READ_PROFILE']) . '</a>';
			$user_cache[$poster_id]['profile'] = '<a href="' . $temp_url . '">' . $user->lang['READ_PROFILE'] . '</a>';

			$temp_url = "privmsg.$phpEx$SID&amp;mode=post&amp;u=$poster_id";
			$user_cache[$poster_id]['pm_img'] = '<a href="' . $temp_url . '">' . $user->img('icon_pm', $user->lang['SEND_PRIVATE_MESSAGE']) . '</a>';
			$user_cache[$poster_id]['pm'] = '<a href="' . $temp_url . '">' . $user->lang['SEND_PRIVATE_MESSAGE'] . '</a>';

			if (!empty($row['user_viewemail']) || $auth->acl_gets('m_', 'a_', $forum_id))
			{
				$email_uri = ($config['board_email_form'] && $config['email_enable']) ? "ucp.$phpEx$SID&amp;mode=email&amp;u=" . $poster_id : 'mailto:' . $row['user_email'];

				$user_cache[$poster_id]['email_img'] = '<a href="' . $email_uri . '">' . $user->img('icon_email', $user->lang['SEND_EMAIL']) . '</a>';
				$user_cache[$poster_id]['email'] = '<a href="' . $email_uri . '">' . $user->lang['SEND_EMAIL'] . '</a>';
			}
			else
			{
				$user_cache[$poster_id]['email_img'] = '';
				$user_cache[$poster_id]['email'] = '';
			}

			$user_cache[$poster_id]['www_img'] = ($row['user_website']) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $user->img('icon_www', $user->lang['VISIT_WEBSITE']) . '</a>' : '';
			$user_cache[$poster_id]['www'] = ($row['user_website']) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $user->lang['VISIT_WEBSITE'] . '</a>' : '';

			if (!empty($row['user_icq']))
			{
				$user_cache[$poster_id]['icq_status_img'] = '<a href="http://wwp.icq.com/' . $row['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&amp;img=5" width="18" height="18" border="0" alt="" title="" /></a>';
				$user_cache[$poster_id]['icq_img'] = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . $user->img('icon_icq', $user->lang['ICQ']) . '</a>';
				$user_cache[$poster_id]['icq'] =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . $user->lang['ICQ'] . '</a>';
			}
			else
			{
				$user_cache[$poster_id]['icq_status_img'] = '';
				$user_cache[$poster_id]['icq_img'] = '';
				$user_cache[$poster_id]['icq'] = '';
			}

			$user_cache[$poster_id]['aim_img'] = ($row['user_aim']) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $user->img('icon_aim', $user->lang['AIM']) . '</a>' : '';
			$user_cache[$poster_id]['aim'] = ($row['user_aim']) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $user->lang['AIM'] . '</a>' : '';

			$temp_url = "ucp.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id";
			$user_cache[$poster_id]['msn_img'] = ($row['user_msnm']) ? '<a href="' . $temp_url . '">' . $user->img('icon_msnm', $user->lang['MSNM']) . '</a>' : '';
			$user_cache[$poster_id]['msn'] = ($row['user_msnm']) ? '<a href="' . $temp_url . '">' . $user->lang['MSNM'] . '</a>' : '';

			$user_cache[$poster_id]['yim_img'] = ($row['user_yim']) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . $user->img('icon_yim', $user->lang['YIM']) . '</a>' : '';
			$user_cache[$poster_id]['yim'] = ($row['user_yim']) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . $user->lang['YIM'] . '</a>' : '';

			if ($auth->acl_get('f_search', $forum_id))
			{
				$temp_url = 'search.' . $phpEx . $SID . '&amp;search_author=' . urlencode($row['username']) .'"&amp;showresults=posts';
				$search_img = '<a href="' . $temp_url . '">' . $user->img('icon_search', $user->lang['SEARCH_USER_POSTS']) . '</a>';
				$search ='<a href="' . $temp_url . '">' . $user->lang['SEARCH_USER_POSTS'] . '</a>';
			}
			else
			{
				$search_img = '';
				$search = '';
			}

		}
		else if (!$poster_id)
		{
			$user_cache[$poster_id]['profile_img'] = '';
			$user_cache[$poster_id]['profile'] = '';
			$user_cache[$poster_id]['pm_img'] = '';
			$user_cache[$poster_id]['pm'] = '';
			$user_cache[$poster_id]['email_img'] = '';
			$user_cache[$poster_id]['email'] = '';
			$user_cache[$poster_id]['www_img'] = '';
			$user_cache[$poster_id]['www'] = '';
			$user_cache[$poster_id]['icq_status_img'] = '';
			$user_cache[$poster_id]['icq_img'] = '';
			$user_cache[$poster_id]['icq'] = '';
			$user_cache[$poster_id]['aim_img'] = '';
			$user_cache[$poster_id]['aim'] = '';
			$user_cache[$poster_id]['msn_img'] = '';
			$user_cache[$poster_id]['msn'] = '';
			$user_cache[$poster_id]['search_img'] = '';
			$user_cache[$poster_id]['search'] = '';
		}



		// Non-user specific images/text
		$temp_url = 'posting.' . $phpEx . $SID . '&amp;mode=quote&amp;p=' . $row['post_id'];
		$quote_img = '<a href="' . $temp_url . '">' . $user->img('icon_quote', $user->lang['REPLY_WITH_QUOTE']) . '</a>';
		$quote = '<a href="' . $temp_url . '">' . $user->lang['REPLY_WITH_QUOTE'] . '</a>';

		if (($user->data['user_id'] == $poster_id && $auth->acl_get('f_edit', $forum_id)) || $auth->acl_gets('m_edit', 'a_', $forum_id))
		{
			$temp_url = "posting.$phpEx$SID&amp;mode=edit&amp;f=" . $row['forum_id'] . "&amp;p=" . $row['post_id'];
			$edit_img = '<a href="' . $temp_url . '">' . $user->img('icon_edit', $user->lang['EDIT_DELETE_POST']) . '</a>';
			$edit = '<a href="' . $temp_url . '">' . $user->lang['EDIT_DELETE_POST'] . '</a>';
		}
		else
		{
			$edit_img = '';
			$edit = '';
		}

		if ($auth->acl_gets('m_ip', 'a_', $forum_id))
		{
			$temp_url = "mcp.$phpEx?sid=" . $user->session_id . "&amp;mode=ip&amp;p=" . $row['post_id'] . "&amp;t=" . $topic_id;
			$ip_img = '<a href="' . $temp_url . '">' . $user->img('icon_ip', $user->lang['VIEW_IP']) . '</a>';
			$ip = '<a href="' . $temp_url . '">' . $user->lang['VIEW_IP'] . '</a>';
		}
		else
		{
			$ip_img = '';
			$ip = '';
		}

		if (($user->data['user_id'] == $poster_id && $auth->acl_get('f_delete', $forum_id) && $forum_topic_data['topic_last_post_id'] == $row['post_id']) || $auth->acl_gets('m_delete', 'a_', $forum_id))
		{
			$temp_url = "posting.$phpEx$SID&amp;mode=delete&amp;p=" . $row['post_id'];
			$delpost_img = '<a href="' . $temp_url . '">' . $user->img('icon_delete', $user->lang['DELETE_POST']) . '</a>';
			$delpost = '<a href="' . $temp_url . '">' . $user->lang['DELETE_POST'] . '</a>';
		}
		else
		{
			$delpost_img = '';
			$delpost = '';
		}



		// Does post have an attachment? If so, add it to the list
		if ($row['post_attach'])
		{
			$attach_list[] = $post_id;
		}



		// Parse the message and subject
		$post_subject = ($row['post_subject'] != '') ? $row['post_subject'] : '';
		$message = $row['post_text'];
		$bbcode_uid = $row['bbcode_uid'];



		// If the board has HTML off but the post has HTML
		// on then we process it, else leave it alone
		if (!$auth->acl_get('f_html', $forum_id))
		{
			if ($row['enable_html'] && $auth->acl_get('f_bbcode', $forum_id))
			{
				$message = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $message);
			}
		}


		// Second parse bbcode here



		// If we allow users to disable display of emoticons
		// we'll need an appropriate check and preg_replace here
		$message = (empty($row['enable_smilies']) || empty($config['allow_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $message) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);



		// Highlight active words (primarily for search)
		if ($highlight_match)
		{
			// This was shamelessly 'borrowed' from volker at multiartstudio dot de
			// via php.net's annotated manual
			$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace('#\b(" . $highlight_match . ")\b#i', '<span class=\"hilit\">\\\\1</span>', '\\0')", '>' . $message . '<'), 1, -1));
		}



		// Replace naughty words such as farty pants
		if (sizeof($censors))
		{
			$post_subject = preg_replace($censors['match'], $censors['replace'], $post_subject);
			$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $message . '<'), 1, -1));
		}


		$message = nl2br($message);

		
		// Editing information
		if (intval($row['post_edit_count']))
		{
			$l_edit_time_total = (intval($row['post_edit_count']) == 1) ? $user->lang['Edited_time_total'] : $user->lang['Edited_times_total'];

			$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, $poster, $user->format_date($row['post_edit_time']), $row['post_edit_count']);
		}
		else
		{
			$l_edited_by = '';
		}



		// Signature
		if (!isset($user_cache[$poster_id]['sig']))
		{
			$user_sig = ($row['enable_sig'] && $row['user_sig'] != '' && $config['allow_sig']) ? $row['user_sig'] : '';

			if ($user_sig != '' && $auth->acl_gets('f_sigs', 'm_', 'a_', $forum_id))
			{
				if (!$auth->acl_get('f_html', $forum_id) && $user->data['user_allowhtml'])
				{
					$user_sig = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig);
				}

				$user_cache[$poster_id]['sig'] = (empty($row['user_allowsmile']) || empty($config['enable_smilies'])) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $user_cache[$poster_id]['sig']) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $user_cache[$poster_id]['sig']);

				if (count($censors))
				{
					$user_sig = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace(\$censors['match'], \$censors['replace'], '\\0')", '>' . $user_sig . '<'), 1, -1));
				}

				$user_cache[$poster_id]['sig'] = '<br />_________________<br />' . nl2br($user_cache[$poster_id]['sig']);
			}
			else
			{
				$user_cache[$poster_id]['sig'] = '';
			}
		}



		// Define the little post icon
		$mini_post_img = ($row['post_time'] > $user->data['user_lastvisit'] && $row['post_time'] > $topic_last_read) ? $user->img('goto_post_new', $user->lang['New_post']) : $user->img('goto_post', $user->lang['Post']);

		// Little post link and anchor name
		$mini_post_url = 'viewtopic.' . $phpEx . $SID . '&amp;p=' . $row['post_id'] . '#' . $row['post_id'];
		$u_post_id = (!empty($newest_post_id) && $newest_post_id == $row['post_id']) ? 'newest' : $row['post_id'];


		// Dump vars into template
		$template->assign_block_vars('postrow', array(
			'POSTER_NAME' 	=> $poster,
			'POSTER_RANK' 	=> $user_cache[$poster_id]['rank_title'],
			'RANK_IMAGE' 	=> $user_cache[$poster_id]['rank_image'],
			'POSTER_JOINED' => $user_cache[$poster_id]['joined'],
			'POSTER_POSTS' 	=> $poster_posts,
			'POSTER_FROM' 	=> $poster_from,
			'POSTER_AVATAR' => $user_cache[$poster_id]['avatar'],
			'POST_DATE' 	=> $user->format_date($row['post_time']),

			'POST_SUBJECT' 	=> $post_subject,
			'MESSAGE' 		=> $message,
			'SIGNATURE' 	=> $user_cache[$poster_id]['sig'],
			'EDITED_MESSAGE'=> $l_edited_by,

			'RATING'		=> $rating, 

			'MINI_POST_IMG' => $mini_post_img,
			'EDIT_IMG' 		=> $edit_img,
			'EDIT' 			=> $edit,
			'QUOTE_IMG' 	=> $quote_img,
			'QUOTE' 		=> $quote,
			'IP_IMG' 		=> $ip_img,
			'IP' 			=> $ip,
			'DELETE_IMG' 	=> $delpost_img,
			'DELETE' 		=> $delpost,

			'PROFILE_IMG' 	=> $user_cache[$poster_id]['profile_img'],
			'PROFILE' 		=> $user_cache[$poster_id]['profile'],
			'SEARCH_IMG' 	=> $user_cache[$poster_id]['search_img'],
			'SEARCH' 		=> $user_cache[$poster_id]['search'],
			'PM_IMG' 		=> $user_cache[$poster_id]['pm_img'],
			'PM' 			=> $user_cache[$poster_id]['pm'],
			'EMAIL_IMG' 	=> $user_cache[$poster_id]['email_img'],
			'EMAIL' 		=> $user_cache[$poster_id]['email'],
			'WWW_IMG' 		=> $user_cache[$poster_id]['www_img'],
			'WWW' 			=> $user_cache[$poster_id]['www'],
			'ICQ_STATUS_IMG'=> $user_cache[$poster_id]['icq_status_img'],
			'ICQ_IMG' 		=> $user_cache[$poster_id]['icq_img'],
			'ICQ' 			=> $user_cache[$poster_id]['icq'],
			'AIM_IMG' 		=> $user_cache[$poster_id]['aim_img'],
			'AIM' 			=> $user_cache[$poster_id]['aim'],
			'MSN_IMG' 		=> $user_cache[$poster_id]['msn_img'],
			'MSN' 			=> $user_cache[$poster_id]['msn'],
			'YIM_IMG' 		=> $user_cache[$poster_id]['yim_img'],
			'YIM' 			=> $user_cache[$poster_id]['yim'],

			'POST_ICON' 	=> (!empty($row['icon_id']) ) ? '<img src="' . $config['icons_path'] . '/' . $icons[$row['icon_id']]['img'] . '" width="' . $icons[$row['icon_id']]['width'] . '" height="' . $icons[$row['icon_id']]['height'] . '" alt="" title="" />' : '',

			'L_MINI_POST_ALT'	=> $mini_post_alt,

			'S_ROW_COUNT'	=> $i++,

			'U_MINI_POST'	=> $mini_post_url,
			'U_POST_ID' 	=> $u_post_id
		));
	}
	while ($row = $db->sql_fetchrow($result));

	unset($user_cache);
}
else
{
	trigger_error($user->lang['No_posts_topic']);
}

$rating = '';
if ($user->data['user_id'] != ANONYMOUS)
{
	$rating_text = array(0 => $user->lang['SPAM'], 5 => $user->lang['EXCELLENT']);

	$sql = "SELECT rating
		FROM " . TOPICS_RATINGS_TABLE . "
		WHERE user_id = " . $user->data['user_id'] . " 
			AND post_id IN ($post_id_sql)";
//	$result = $db->sql_query($sql);
}

// If we have attachments, grab them ... based on Acyd Burns 2.0.x Mod
if (sizeof($attach_list))
{
	$sql = "SELECT a.post_id, d.*
		FROM " . ATTACHMENTS_TABLE . " a, " . ATTACHMENTS_DESC_TABLE . " d
		WHERE a.post_id IN (" . implode(', ', $attach_list) . ") 
			AND a.attach_id = d.attach_id
		ORDER BY d.filetime " . $display_order;
	$result = $db->sql_query($sql);

	if ($db->sql_fetchrow($result))
	{
		$template->assign_vars(array(
			'L_POSTED_ATTACHMENTS' => $lang['Posted_attachments'],
			'L_KILOBYTE' => $lang['KB'])
		);

		$i = 0;

		do
		{
		}
		while ($db->sql_fetchrow($result));
	}
	else
	{
		// No attachments exist, but post table thinks they do
		// so go ahead and reset post_attach flags
		$sql = "UPDATE " . POSTS_TABLE . " 
			SET post_attach = 0 
			WHERE post_id IN (" . implode(', ', $attach_list) . ")";
		$db->sql_query($sql);
	}
	$db->sql_freeresult($result);
}

// Mark topics read
markread('topic', $forum_id, $topic_id, $forum_topic_data['topic_last_post_id']);

// Update the topic view counter
$sql = "UPDATE " . TOPICS_TABLE . "
	SET topic_views = topic_views + 1
	WHERE topic_id = $topic_id";
$db->sql_query($sql);

// Output the page
$page_title = $user->lang['View_topic'] .' - ' . $topic_title;
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => (isset($_GET['view']) && $_GET['view'] == 'print') ? 'viewtopic_print.html' : 'viewtopic_body.html')
);
make_jumpbox('viewforum.'.$phpEx, $forum_id);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

//, 'header' => 'overall_header.tpl', 'footer' => 'overall_footer.tpl'

?>