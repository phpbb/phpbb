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
$topic_id = ( isset($_GET['t']) ) ? intval($_GET['t']) : 0;
$post_id = ( isset($_GET['p'])) ? intval($_GET['p']) : 0;
$start = ( isset($_GET['start']) ) ? intval($_GET['start']) : 0;

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
		if (!empty($_COOKIE[$config['cookie_name'] . '_sid']) || !empty($_GET['sid']))
		{
			$session_id = (!empty($_COOKIE[$config['cookie_name'] . '_sid'])) ? $_COOKIE[$config['cookie_name'] . '_sid'] : $_GET['sid'];

			$SID = '?sid=' . ((!empty($_GET['sid'])) ? $session_id : '');

			if ($session_id)
			{
				$sql = "SELECT p.post_id
					FROM " . POSTS_TABLE . " p, " . SESSIONS_TABLE . " s,  " . USERS_TABLE . " u
					WHERE s.session_id = '$session_id'
						AND u.user_id = s.session_user_id
						AND p.topic_id = $topic_id
						AND p.post_approved = 1
						AND p.post_time >= u.user_lastvisit
					ORDER BY p.post_time ASC
					LIMIT 1";
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					trigger_error('No_new_posts_last_visit');
				}

				$post_id = $row['post_id'];
				redirect("viewtopic.$phpEx$SID&p=$post_id#$post_id");
			}
		}

		redirect("index.$phpEx");
	}
	else if ($_GET['view'] == 'next' || $_GET['view'] == 'previous')
	{
		$sql_condition = ( $_GET['view'] == 'next' ) ? '>' : '<';
		$sql_ordering = ( $_GET['view'] == 'next' ) ? 'ASC' : 'DESC';

		$sql = "SELECT t.topic_id
			FROM " . TOPICS_TABLE . " t, " . TOPICS_TABLE . " t2
			WHERE t2.topic_id = $topic_id
				AND t.forum_id = t2.forum_id
				AND t.topic_last_post_time $sql_condition t2.topic_last_post_time
			ORDER BY t.topic_last_post_time $sql_ordering
			LIMIT 1";
		$result = $db->sql_query($sql);

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

if ($user->data['user_id'] != ANONYMOUS)
{
	if (isset($_POST['rating']))
	{
		$sql = "SELECT rating
			FROM " . TOPICS_RATINGS_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $user->data['user_id'];
		$result = $db->sql_query($sql);

		$rating = ($row = $db->sql_fetchrow($result)) ? $row['rating'] : '';

		if ( empty($_POST['rating_value']) && $rating != '')
		{
		}
		else
		{
			$new_rating = intval($_POST['rating']);

			$sql = ($rating != '') ? "UPDATE " . TOPICS_RATING_TABLE . " SET rating = $new_rating WHERE user_id = " . $user->data['user_id'] . " AND topic_id = $topic_id" : "INSERT INTO " . TOPICS_RATING_TABLE . " (topic_id, user_id, rating) VALUES ($topic_id, " . $user->data['user_id'] . ", $new_rating)";
		}
	}
	else if (isset($_POST['castvote']))
	{
		if (!isset($_POST['vote_id']))
		{
			trigger_error($user->lang['No_vote']);
		}
	}
}

// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
$join_sql_table = (!$post_id) ? '' : ', ' . POSTS_TABLE . ' p, ' . POSTS_TABLE . ' p2 ';
$join_sql = (!$post_id) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND p.post_approved = " . TRUE . " AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_approved = " . TRUE . " AND p2.post_id <= $post_id";
$extra_fields = (!$post_id)  ? '' : ", COUNT(p2.post_id) AS prev_posts";
$order_sql = (!$post_id) ? '' : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, f.forum_name, f.forum_status, f.forum_id, f.forum_style ORDER BY p.post_id ASC";

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

$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.poll_start, t.poll_length, t.poll_title, f.forum_name, f.forum_status, f.forum_id, f.forum_style" . $extra_fields . "
	FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $join_sql_table . "
	WHERE $join_sql
		AND f.forum_id = t.forum_id
		$order_sql";
$result = $db->sql_query($sql);

if (!extract($db->sql_fetchrow($result)))
{
	trigger_error('Topic_post_not_exist');
}

// Configure style, language, etc.
$user->setup(false, intval($forum_style));
$auth->acl($user->data, intval($forum_id));
// End configure

// Start auth check
if (!$auth->acl_gets('f_read', 'm_', 'a_', intval($forum_id)))
{
	if ($user->data['user_id'] == ANONYMOUS)
	{
		$redirect = ( isset($post_id) ) ? "p=$post_id" : "t=$topic_id";
		$redirect .= ( isset($start) ) ? "&start=$start" : '';
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
$previous_days = array(0 => $user->lang['All_Posts'], 1 => $user->lang['1_Day'], 7 => $user->lang['7_Days'], 14 => $user->lang['2_Weeks'], 30 => $user->lang['1_Month'], 90 => $user->lang['3_Months'], 180 => $user->lang['6_Months'], 364 => $user->lang['1_Year']);
$sort_by_text = array('a' => $user->lang['Author'], 't' => $user->lang['Post_time'], 's' => $user->lang['Subject']);
$sort_by = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'pt.post_subject');

if (isset($_POST['sort']))
{
	if (!empty($_POST['sort_days']))
	{
		$sort_days = (!empty($_POST['sort_days'])) ? intval($_POST['sort_days']) : intval($_GET['sort_days']);
		$min_post_time = time() - ( $sort_days * 86400 );

		$sql = "SELECT COUNT(post_id) AS num_posts
			FROM " . POSTS_TABLE . "
			WHERE topic_id = $topic_id
				AND post_time >= $min_post_time
				AND post_approved = " . TRUE;
		$result = $db->sql_query($sql);

		$start = 0;
		$topic_replies = ( $row = $db->sql_fetchrow($result) ) ? $row['num_posts'] : 0;
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
$select_sort_dir .= ($sort_dir == 'a') ? '<option value="a" selected="selected">' . $user->lang['Ascending'] . '</option><option value="d">' . $user->lang['Descending'] . '</option>' : '<option value="a">' . $user->lang['Ascending'] . '</option><option value="d" selected="selected">' . $user->lang['Descending'] . '</option>';
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

$rating = '';
if ($user->data['user_id'] != ANONYMOUS)
{
	$rating_text = array(-5 => $user->lang['Very_poor'], -2 => $user->lang['Quite_poor'], 0 => $user->lang['Unrated'], 2 => $user->lang['Quite_good'], 5 => $user->lang['Very_good']);

	$sql = "SELECT rating
		FROM " . TOPICS_RATINGS_TABLE . "
		WHERE topic_id = $topic_id
			AND user_id = " . $user->data['user_id'];
	$result = $db->sql_query($sql);

	$user_rating = ($row = $db->sql_fetchrow($result)) ? $row['rating'] : 0;

	for($i = -5; $i < 6; $i++)
	{
		$selected = ($user_rating == $i) ? ' selected="selected"' : '';
		$rating .= '<option value="' . $i . '"' . $selected . '>' . $i . ((!empty($rating_text[$i])) ? ' > ' . $rating_text[$i] : '') . '</option>';
	}

	$rating = '<select name="rating">' . $rating . '</select>';
}

// Was a highlight request part of the URI? Yes, this idea was
// taken from vB but we did already have a highlighter in place
// in search itself ... it's just been extended a bit!
$highlight_match = '';
if (isset($_GET['highlight']))
{
	// Split words and phrases
	$words = explode(' ', trim(urldecode($_GET['highlight'])));

	foreach ($words as $word)
	{
		if (trim($word) != '')
		{
			$highlight_match .= (($highlight_match != '') ? '|' : '') . str_replace('*', '\w*', preg_quote($word, '#'));
		}
	}
	unset($words);
}

// Quick mod tools
$s_forum_rules = '';
get_forum_rules('topic', $s_forum_rules, $forum_id);

$topic_mod = '';
$topic_mod .= ($auth->acl_gets('m_lock', 'a_', $forum_id)) ? ((intval($topic_status) == ITEM_UNLOCKED) ? '<option value="lock">' . $user->lang['Lock_topic'] . '</option>' : '<option value="unlock">' . $user->lang['Unlock_topic'] . '</option>') : '';
$topic_mod .= ($auth->acl_gets('m_delete', 'a_', $forum_id)) ? '<option value="delete">' . $user->lang['Delete_topic'] . '</option>' : '';
$topic_mod .= ($auth->acl_gets('m_move', 'a_', $forum_id)) ? '<option value="move">' . $user->lang['Move_topic'] . '</option>' : '';
$topic_mod .= ($auth->acl_gets('m_split', 'a_', $forum_id)) ? '<option value="split">' . $user->lang['Split_topic'] . '</option>' : '';
$topic_mod .= ($auth->acl_gets('m_merge', 'a_', $forum_id)) ? '<option value="merge">' . $user->lang['Merge_topic'] . '</option>' : '';

// If we've got a hightlight set pass it on to pagination.
$pagination = ($highlight_match) ? generate_pagination("viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=" . $_GET['highlight'], $topic_replies, $config['posts_per_page'], $start) : generate_pagination("viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $topic_replies, $config['posts_per_page'], $start);

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

// Define censored word matches
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

// Replace naughty words in title
if (count($orig_word))
{
	$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
}

// Send vars to template
$template->assign_vars(array(
	'FORUM_ID' 		=> $forum_id,
    'FORUM_NAME' 	=> $forum_name,
    'TOPIC_ID' 		=> $topic_id,
    'TOPIC_TITLE' 	=> $topic_title,
	'PAGINATION' 	=> $pagination,
	'PAGE_NUMBER' 	=> sprintf($user->lang['Page_of'], ( floor( $start / $config['posts_per_page'] ) + 1 ), ceil( $topic_replies / $config['posts_per_page'] )),
	'MOD_CP' 		=> ($auth->acl_gets('m_', 'a_', $forum_id)) ? sprintf($user->lang['MCP'], '<a href="modcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>') : '',

	'POST_IMG' 	=> $post_img,
	'REPLY_IMG' => $reply_img,

	'L_AUTHOR' 				=> $user->lang['Author'],
	'L_MESSAGE' 			=> $user->lang['Message'],
	'L_POSTED' 				=> $user->lang['Posted'],
	'L_POST_SUBJECT' 		=> $user->lang['Post_subject'],
	'L_VIEW_NEXT_TOPIC' 	=> $user->lang['View_next_topic'],
	'L_VIEW_PREVIOUS_TOPIC' => $user->lang['View_previous_topic'],
	'L_BACK_TO_TOP' 		=> $user->lang['Back_to_top'],
	'L_DISPLAY_POSTS' 		=> $user->lang['Display_posts'],
	'L_LOCK_TOPIC' 			=> $user->lang['Lock_topic'],
	'L_UNLOCK_TOPIC' 		=> $user->lang['Unlock_topic'],
	'L_MOVE_TOPIC' 			=> $user->lang['Move_topic'],
	'L_SPLIT_TOPIC' 		=> $user->lang['Split_topic'],
	'L_DELETE_TOPIC' 		=> $user->lang['Delete_topic'],
	'L_GOTO_PAGE' 			=> $user->lang['Goto_page'],
	'L_SORT_BY' 			=> $user->lang['Sort_by'],
	'L_RATE_TOPIC' 			=> $user->lang['Rate_topic'],
	'L_QUICK_MOD' 			=> $user->lang['Quick_mod'],

	'S_TOPIC_LINK' 			=> 't',
	'S_SELECT_SORT_DIR' 	=> $select_sort_dir,
	'S_SELECT_SORT_KEY' 	=> $select_sort,
	'S_SELECT_SORT_DAYS' 	=> $select_sort_days,
	'S_SELECT_RATING' 		=> $rating,
	'S_TOPIC_ACTION' 		=> "viewtopic.$phpEx$SID&amp;t=" . $topic_id . "&amp;start=$start",
	'S_AUTH_LIST' 			=> $s_forum_rules,
	'S_TOPIC_MOD' 			=> ( $topic_mod != '' ) ? '<select name="mode">' . $topic_mod . '</select>' : '',
	'S_MOD_ACTION' 			=> "modcp.$phpEx$SID&amp;t=$topic_id",
	'S_WATCH_TOPIC' 		=> $s_watching_topic,

	'U_VIEW_TOPIC' 			=> "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;start=$start&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=" . $_GET['highlight'],
	'U_VIEW_FORUM' 			=> $view_forum_url,
	'U_VIEW_OLDER_TOPIC'	=> $view_prev_topic_url,
	'U_VIEW_NEWER_TOPIC'	=> $view_next_topic_url,
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
		$poll_option['poll_option_text'] = (sizeof($orig_word)) ? preg_replace($orig_word, $replacement_word, $poll_option['poll_option_text']) : $poll_option['poll_option_text'];
		$option_pct = ( $poll_total > 0 ) ? $poll_option['poll_option_total'] / $poll_total : 0;
		$option_pct_txt = sprintf("%.1d%%", ($option_pct * 100));

		$template->assign_block_vars('poll_option', array(
			'POLL_OPTION_ID' 		=> $poll_option['poll_option_id'],
			'POLL_OPTION_CAPTION' 	=> $poll_option['poll_option_text'],
			'POLL_OPTION_RESULT' 	=> $poll_option['poll_option_total'],
			'POLL_OPTION_PERCENT' 	=> $vote_percent,
			'POLL_OPTION_IMG' 		=> $user->img('poll_center', $option_pct_txt, round($option_pct * $user->theme['poll_length']), true))
		);
	}

	$poll_title = (sizeof($orig_word)) ? preg_replace($orig_word, $replacement_word, $poll_title) : $poll_title;

	$template->assign_vars(array(
		'POLL_QUESTION'		=> $poll_title,
		'TOTAL_VOTES' 		=> $poll_total,
		'POLL_LEFT_CAP_IMG'	=> $user->img('poll_left'),
		'POLL_RIGHT_CAP_IMG'=> $user->img('poll_right'),

		'S_HAS_POLL_OPTIONS'=> !$display_results,
		'S_HAS_POLL_DISPLAY'=> $display_results,
		'S_POLL_ACTION'		=>	"viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_dats&amp;postorder=$poster_order",

		'L_SUBMIT_VOTE'	=> $user->lang['Submit_vote'],
		'L_VIEW_RESULTS'=> $user->lang['View_results'],
		'L_TOTAL_VOTES' => $user->lang['Total_votes'],

		'U_VIEW_RESULTS' => "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;vote=viewresult")
	);
}

// Container for user details, only process once
$poster_details = array();
$i = 0;

// Go ahead and pull all data for this topic
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*, pt.post_text, pt.post_subject, pt.bbcode_uid
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id
		AND p.post_approved = " . TRUE . "
		AND pt.post_id = p.post_id
		$limit_posts_time
		AND u.user_id = p.poster_id
	ORDER BY $sort_order
	LIMIT $start, " . $config['posts_per_page'];
$result = $db->sql_query($sql);

if ($row = $db->sql_fetchrow($result))
{
	do
	{
		$poster_id = $row['user_id'];
		$poster = (!$poster_id) ? $user->lang['Guest'] : $row['username'];

		$poster_posts = ($row['user_id']) ? $user->lang['Posts'] . ': ' . $row['user_posts'] : '';

		$poster_from = ($row['user_from'] && $row['user_id']) ? $user->lang['Location'] . ': ' . $row['user_from'] : '';

		if (!isset($poster_details[$poster_id]['joined']))
		{
			$poster_details[$poster_id]['joined'] = ($row['user_id']) ? $user->lang['Joined'] . ': ' . $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']) : '';
		}

		if (isset($poster_details[$poster_id]['avatar']))
		{
			if ($row['user_avatar_type'] && $poster_id && $row['user_allowavatar'])
			{
				switch ($row['user_avatar_type'])
				{
					case USER_AVATAR_UPLOAD:
						$poster_details[$poster_id]['avatar'] = ($config['allow_avatar_upload']) ? '<img src="' . $config['avatar_path'] . '/' . $row['user_avatar'] . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />' : '';
						break;
					case USER_AVATAR_REMOTE:
						$poster_details[$poster_id]['avatar'] = ($config['allow_avatar_remote']) ? '<img src="' . $row['user_avatar'] . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />' : '';
						break;
					case USER_AVATAR_GALLERY:
						$poster_details[$poster_id]['avatar'] = ($config['allow_avatar_local']) ? '<img src="' . $config['avatar_gallery_path'] . '/' . $row['user_avatar'] . '" width="' . $row['user_avatar_width'] . '" height="' . $row['user_avatar_height'] . '" border="0" alt="" />' : '';
						break;
				}
			}
			else
			{
				$poster_details[$poster_id]['avatar'] = '';
			}
		}

		// Generate ranks, set them to empty string initially.
		if (!isset($poster_details[$poster_id]['rank_title']) )
		{
			if ($row['user_rank'] )
			{
				for($j = 0; $j < count($ranksrow); $j++)
				{
					if ($row['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'])
					{
						$poster_details[$poster_id]['rank_title'] = $ranksrow[$j]['rank_title'];
						$poster_details[$poster_id]['rank_image'] = ($ranksrow[$j]['rank_image']) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" border="0" alt="' . $poster_rank . '" title="' . $poster_rank . '" /><br />' : '';
					}
				}
			}
			else
			{
				for($j = 0; $j < count($ranksrow); $j++)
				{
					if ($row['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'])
					{
						$poster_details[$poster_id]['rank_title'] = $ranksrow[$j]['rank_title'];
						$poster_details[$poster_id]['rank_image'] = ($ranksrow[$j]['rank_image']) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" border="0" alt="' . $poster_rank . '" title="' . $poster_rank . '" /><br />' : '';
					}
				}
			}
		}

		// Handle anon users posting with usernames
		if (!$poster_id && $row['post_username'] != '')
		{
			$poster = $row['post_username'];
			$poster_rank = $user->lang['Guest'];
		}

		if (!isset($poster_details[$poster_id]['profile']) && $poster_id)
		{
			$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id";
			$poster_details[$poster_id]['profile_img'] = '<a href="' . $temp_url . '">' . $user->img('icon_profile', $user->lang['Read_profile']) . '</a>';
			$poster_details[$poster_id]['profile'] = '<a href="' . $temp_url . '">' . $user->lang['Read_profile'] . '</a>';

			$temp_url = "privmsg.$phpEx$SID&amp;mode=post&amp;u=$poster_id";
			$poster_details[$poster_id]['pm_img'] = '<a href="' . $temp_url . '">' . $user->img('icon_pm', $user->lang['Send_private_message']) . '</a>';
			$poster_details[$poster_id]['pm'] = '<a href="' . $temp_url . '">' . $user->lang['Send_private_message'] . '</a>';

			if (!empty($row['user_viewemail']) || $auth->acl_get('m_', $forum_id))
			{
				$email_uri = ($config['board_email_form'] && $config['email_enable']) ? "profile.$phpEx$SID&amp;mode=email&amp;u=" . $poster_id : 'mailto:' . $row['user_email'];

				$poster_details[$poster_id]['email_img'] = '<a href="' . $email_uri . '">' . $user->img('icon_email', $user->lang['Send_email']) . '</a>';
				$poster_details[$poster_id]['email'] = '<a href="' . $email_uri . '">' . $user->lang['Send_email'] . '</a>';
			}
			else
			{
				$poster_details[$poster_id]['email_img'] = '';
				$poster_details[$poster_id]['email'] = '';
			}

			$poster_details[$poster_id]['www_img'] = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $user->img('icon_www', $user->lang['Visit_website']) . '</a>' : '';
			$poster_details[$poster_id]['www'] = ( $row['user_website'] ) ? '<a href="' . $row['user_website'] . '" target="_userwww">' . $user->lang['Visit_website'] . '</a>' : '';

			if (!empty($row['user_icq']))
			{
				$poster_details[$poster_id]['icq_status_img'] = '<a href="http://wwp.icq.com/' . $row['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $row['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
				$poster_details[$poster_id]['icq_img'] = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . $user->img('icon_icq', $user->lang['ICQ']) . '</a>';
				$poster_details[$poster_id]['icq'] =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $row['user_icq'] . '">' . $user->lang['ICQ'] . '</a>';
			}
			else
			{
				$poster_details[$poster_id]['icq_status_img'] = '';
				$poster_details[$poster_id]['icq_img'] = '';
				$poster_details[$poster_id]['icq'] = '';
			}

			$poster_details[$poster_id]['aim_img'] = ($row['user_aim']) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $user->img('icon_aim', $user->lang['AIM']) . '</a>' : '';
			$poster_details[$poster_id]['aim'] = ($row['user_aim']) ? '<a href="aim:goim?screenname=' . $row['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $user->lang['AIM'] . '</a>' : '';

			$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id";
			$poster_details[$poster_id]['msn_img'] = ($row['user_msnm']) ? '<a href="' . $temp_url . '">' . $user->img('icon_msnm', $user->lang['MSNM']) . '</a>' : '';
			$poster_details[$poster_id]['msn'] = ($row['user_msnm']) ? '<a href="' . $temp_url . '">' . $user->lang['MSNM'] . '</a>' : '';

			$poster_details[$poster_id]['yim_img'] = ($row['user_yim']) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . $user->img('icon_yim', $user->lang['YIM']) . '</a>' : '';
			$poster_details[$poster_id]['yim'] = ($row['user_yim']) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&amp;.src=pg">' . $user->lang['YIM'] . '</a>' : '';

			if ($auth->acl_get('f_search', $forum_id))
			{
				$temp_url = 'search.' . $phpEx . $SID . '&amp;search_author=' . urlencode($row['username']) .'"&amp;showresults=posts';
				$search_img = '<a href="' . $temp_url . '">' . $user->img('icon_search', $user->lang['Search_user_posts']) . '</a>';
				$search ='<a href="' . $temp_url . '">' . $user->lang['Search_user_posts'] . '</a>';
			}
			else
			{
				$search_img = '';
				$search = '';
			}

		}
		else if (!$poster_id)
		{
			$poster_details[$poster_id]['profile_img'] = '';
			$poster_details[$poster_id]['profile'] = '';
			$poster_details[$poster_id]['pm_img'] = '';
			$poster_details[$poster_id]['pm'] = '';
			$poster_details[$poster_id]['email_img'] = '';
			$poster_details[$poster_id]['email'] = '';
			$poster_details[$poster_id]['www_img'] = '';
			$poster_details[$poster_id]['www'] = '';
			$poster_details[$poster_id]['icq_status_img'] = '';
			$poster_details[$poster_id]['icq_img'] = '';
			$poster_details[$poster_id]['icq'] = '';
			$poster_details[$poster_id]['aim_img'] = '';
			$poster_details[$poster_id]['aim'] = '';
			$poster_details[$poster_id]['msn_img'] = '';
			$poster_details[$poster_id]['msn'] = '';
			$poster_details[$poster_id]['search_img'] = '';
			$poster_details[$poster_id]['search'] = '';
		}

		// Non-user specific images/text
		$temp_url = 'posting.' . $phpEx . $SID . '&amp;mode=quote&amp;p=' . $row['post_id'];
		$quote_img = '<a href="' . $temp_url . '">' . $user->img('icon_quote', $user->lang['Reply_with_quote']) . '</a>';
		$quote = '<a href="' . $temp_url . '">' . $user->lang['Reply_with_quote'] . '</a>';

		if (($user->data['user_id'] == $poster_id && $auth->acl_get('f_edit', $forum_id)) || $auth->acl_gets('m_edit', 'a_', $forum_id))
		{
			$temp_url = "posting.$phpEx$SID&amp;mode=edit&amp;f=" . $row['forum_id'] . "&amp;p=" . $row['post_id'];
			$edit_img = '<a href="' . $temp_url . '">' . $user->img('icon_edit', $user->lang['Edit_delete_post']) . '</a>';
			$edit = '<a href="' . $temp_url . '">' . $user->lang['Edit_delete_post'] . '</a>';
		}
		else
		{
			$edit_img = '';
			$edit = '';
		}

		if ($auth->acl_gets('m_ip', 'a_', $forum_id))
		{
			$temp_url = "modcp.$phpEx$SID&amp;mode=ip&amp;p=" . $row['post_id'] . "&amp;t=" . $topic_id;
			$ip_img = '<a href="' . $temp_url . '">' . $user->img('icon_ip', $user->lang['View_IP']) . '</a>';
			$ip = '<a href="' . $temp_url . '">' . $user->lang['View_IP'] . '</a>';
		}
		else
		{
			$ip_img = '';
			$ip = '';
		}

		if (($user->data['user_id'] == $poster_id && $auth->acl_get('f_delete', $forum_id) && $forum_topic_data['topic_last_post_id'] == $row['post_id']) || $auth->acl_gets('m_delete', 'a_', $forum_id))
		{
			$temp_url = "posting.$phpEx$SID&amp;mode=delete&amp;p=" . $row['post_id'];
			$delpost_img = '<a href="' . $temp_url . '">' . $user->img('icon_delete', $user->lang['Delete_post']) . '</a>';
			$delpost = '<a href="' . $temp_url . '">' . $user->lang['Delete_post'] . '</a>';
		}
		else
		{
			$delpost_img = '';
			$delpost = '';
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

		// Parse message for admin-defined/templated BBCode if reqd
		if ($bbcode_uid != '')
		{
//			$message = ( $auth->acl_get('f_bbcode', $forum_id) ) ? bbencode_second_pass($message, $bbcode_uid, $auth->acl_get('f_img', $forum_id)) : preg_replace('/\:[0-9a-z\:]+\]/si', ']', $message);
		}

		// If we allow users to disable display of emoticons
		// we'll need an appropriate check and preg_replace here
		if ($row['enable_smilies'])
		{
			$message = str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $message);
		}

		// Highlight active words (primarily for search)
		if ($highlight_match)
		{
			// This was shamelessly 'borrowed' from volker at multiartstudio dot de
			// via php.net's annotated manual
			$message = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace('#\b(" . $highlight_match . ")\b#i', '<span class=\"hilit\">\\\\1</span>', '\\0')", '>' . $message . '<'), 1, -1));
		}

		// Replace naughty words such as farty pants
		if (count($orig_word))
		{
			$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);
			$message = preg_replace($orig_word, $replacement_word, $message);
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
		if (!isset($poster_details[$poster_id]['sig']))
		{
			$user_sig = ($row['enable_sig'] && $row['user_sig'] != '' && $config['allow_sig']) ? $row['user_sig'] : '';
			$user_sig_bbcode_uid = $row['user_sig_bbcode_uid'];

			if ($user_sig != '' && $user_sig_bbcode_uid != '' && $auth->acl_get('f_sigs', $forum_id))
			{
				if (!$auth->acl_get('f_html', $forum_id) && $user->data['user_allowhtml'])
				{
					$user_sig = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig);
				}

//				$poster_details[$poster_id]['sig'] = bbencode_second_pass($user_sig, $user_sig_bbcode_uid, $auth->acl_get('f_img', $forum_id));

//				$poster_details[$poster_id]['sig'] = make_clickable($poster_details[$poster_id]['sig']);

				if ($row['user_allowsmile'])
				{
					$poster_details[$poster_id]['sig'] = str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $poster_details[$poster_id]['sig']);
				}

				if (count($orig_word))
				{
					$user_sig = preg_replace($orig_word, $replacement_word, $user_sig);
				}

				$poster_details[$poster_id]['sig'] = '<br />_________________<br />' . nl2br($poster_details[$poster_id]['sig']);
			}
			else
			{
				$poster_details[$poster_id]['sig'] = '';
			}
		}

		// Define the little post icon
		$mini_post_img = ($row['post_time'] > $user->data['user_lastvisit'] && $row['post_time'] > $topic_last_read) ? $user->img('goto_post_new', $user->lang['New_post']) : $user->img('goto_post', $user->lang['Post']);

		// Dump vars into template
		$template->assign_block_vars('postrow', array(
			'POSTER_NAME' 	=> $poster,
			'POSTER_RANK' 	=> $poster_details[$poster_id]['rank_title'],
			'RANK_IMAGE' 	=> $poster_details[$poster_id]['rank_image'],
			'POSTER_JOINED' => $poster_details[$poster_id]['joined'],
			'POSTER_POSTS' 	=> $poster_posts,
			'POSTER_FROM' 	=> $poster_from,
			'POSTER_AVATAR' => $poster_details[$poster_id]['avatar'],
			'POST_DATE' 	=> $user->format_date($row['post_time']),

			'POST_SUBJECT' 	=> $post_subject,
			'MESSAGE' 		=> $message,
			'SIGNATURE' 	=> $poster_details[$poster_id]['sig'],
			'EDITED_MESSAGE'=> $l_edited_by,

			'MINI_POST_IMG' => $mini_post_img,
			'EDIT_IMG' 		=> $edit_img,
			'EDIT' 			=> $edit,
			'QUOTE_IMG' 	=> $quote_img,
			'QUOTE' 		=> $quote,
			'IP_IMG' 		=> $ip_img,
			'IP' 			=> $ip,
			'DELETE_IMG' 	=> $delpost_img,
			'DELETE' 		=> $delpost,

			'PROFILE_IMG' 	=> $poster_details[$poster_id]['profile_img'],
			'PROFILE' 		=> $poster_details[$poster_id]['profile'],
			'SEARCH_IMG' 	=> $poster_details[$poster_id]['search_img'],
			'SEARCH' 		=> $poster_details[$poster_id]['search'],
			'PM_IMG' 		=> $poster_details[$poster_id]['pm_img'],
			'PM' 			=> $poster_details[$poster_id]['pm'],
			'EMAIL_IMG' 	=> $poster_details[$poster_id]['email_img'],
			'EMAIL' 		=> $poster_details[$poster_id]['email'],
			'WWW_IMG' 		=> $poster_details[$poster_id]['www_img'],
			'WWW' 			=> $poster_details[$poster_id]['www'],
			'ICQ_STATUS_IMG'=> $poster_details[$poster_id]['icq_status_img'],
			'ICQ_IMG' 		=> $poster_details[$poster_id]['icq_img'],
			'ICQ' 			=> $poster_details[$poster_id]['icq'],
			'AIM_IMG' 		=> $poster_details[$poster_id]['aim_img'],
			'AIM' 			=> $poster_details[$poster_id]['aim'],
			'MSN_IMG' 		=> $poster_details[$poster_id]['msn_img'],
			'MSN' 			=> $poster_details[$poster_id]['msn'],
			'YIM_IMG' 		=> $poster_details[$poster_id]['yim_img'],
			'YIM' 			=> $poster_details[$poster_id]['yim'],

			'L_MINI_POST_ALT' => $mini_post_alt,

			'S_ROW_COUNT' => $i++,

			'U_MINI_POST'	=> $mini_post_url,
			'U_POST_ID' 	=> $row['post_id'])
		);
	}
	while ($row = $db->sql_fetchrow($result));
}
else
{
	trigger_error($user->lang['No_posts_topic']);
}

// Output the page
$page_title = $user->lang['View_topic'] .' - ' . $topic_title;
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'viewtopic_body.html')
);
make_jumpbox('viewforum.'.$phpEx, $forum_id);

// Update the topic view counter
$sql = "UPDATE " . TOPICS_TABLE . "
	SET topic_views = topic_views + 1
	WHERE topic_id = $topic_id";
$db->sql_query($sql);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>