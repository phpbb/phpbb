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

//
// Start initial var setup
//
$topic_id = ( isset($HTTP_GET_VARS['t']) ) ? intval($HTTP_GET_VARS['t']) : 0;
$post_id = ( isset($HTTP_GET_VARS['p'])) ? intval($HTTP_GET_VARS['p']) : 0;
$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;

if ( empty($topic_id) && empty($post_id) )
{
	message_die(MESSAGE, 'Topic_post_not_exist');
}

//
// Find topic id if user requested a newer
// or older topic
//
if ( isset($HTTP_GET_VARS['view']) && empty($post_id) )
{
	if ( $HTTP_GET_VARS['view'] == 'newest' )
	{
		$header_location = ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) ) ? 'Refresh: 0; URL=' : 'Location: ';

		if ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid']) )
		{
			$session_id = $HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_sid'];

			if ( $session_id )
			{
				$sql = "SELECT p.post_id 
					FROM " . POSTS_TABLE . " p, " . SESSIONS_TABLE . " s,  " . USERS_TABLE . " u 
					WHERE s.session_id = '$session_id' 
						AND u.user_id = s.session_user_id 
						AND p.topic_id = $topic_id 
						AND p.post_approved = " . TRUE . " 
						AND p.post_time >= u.user_lastvisit 
					ORDER BY p.post_time ASC 
					LIMIT 1";
				$result = $db->sql_query($sql);

				if ( !($row = $db->sql_fetchrow($result)) )
				{
					message_die(MESSAGE, 'No_new_posts_last_visit');
				}

				$post_id = $row['post_id'];
				header($header_location . 'viewtopic.' . $phpEx . '?sid=' . $session_id . '&p=' . $post_id . '#' . $post_id);
				exit;
			}
		}

		header($header_location . 'viewtopic.' . $phpEx . $SID . '&t=' . $topic_id);
		exit;
	}
	else if ( $HTTP_GET_VARS['view'] == 'next' || $HTTP_GET_VARS['view'] == 'previous' )
	{
		$sql_condition = ( $HTTP_GET_VARS['view'] == 'next' ) ? '>' : '<';
		$sql_ordering = ( $HTTP_GET_VARS['view'] == 'next' ) ? 'ASC' : 'DESC';

		$sql = "SELECT t.topic_id
			FROM " . TOPICS_TABLE . " t, " . TOPICS_TABLE . " t2, " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2
			WHERE t2.topic_id = $topic_id
				AND p2.post_id = t2.topic_last_post_id
				AND t.forum_id = t2.forum_id
				AND p.post_id = t.topic_last_post_id
				AND p.post_approved = " . TRUE . " 
				AND p.post_time $sql_condition p2.post_time
				AND p.topic_id = t.topic_id
			ORDER BY p.post_time $sql_ordering
			LIMIT 1";
		$result = $db->sql_query($sql);

		if ( !($row = $db->sql_fetchrow($result)) )
		{
			$message = ( $HTTP_GET_VARS['view'] == 'next' ) ? 'No_newer_topics' : 'No_older_topics';
			message_die(MESSAGE, $message);
		}
		else
		{
			$topic_id = $row['topic_id'];
		}
	}
}

//
// Start session management
//
$userdata = $session->start();
//
// End session management
//

if ( $userdata['user_id'] != ANONYMOUS && isset($HTTP_POST_VARS['rating']) )
{
	$sql = "SELECT rating
		FROM " . TOPICS_RATINGS_TABLE . " 
		WHERE topic_id = $topic_id 
			AND user_id = " . $userdata['user_id'];
	$result = $db->sql_query($sql);

	$rating = ( $row = $db->sql_fetchrow($result) ) ? $row['rating'] : '';

	if ( empty($HTTP_POST_VARS['rating_value']) && $rating != '' )
	{
	}
	else
	{
		$new_rating = intval($HTTP_POST_VARS['rating']);

		$sql = ( $rating != '' ) ? "UPDATE " . TOPICS_RATING_TABLE . " SET rating = $new_rating WHERE user_id = " . $userdata['user_id'] . " AND topic_id = $topic_id" : "INSERT INTO " . TOPICS_RATING_TABLE . " (topic_id, user_id, rating) VALUES ($topic_id, " . $userdata['user_id'] . ", $new_rating)";
	}
}

//
// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
//
$join_sql_table = ( !$post_id ) ? '' : ', ' . POSTS_TABLE . ' p, ' . POSTS_TABLE . ' p2 ';
$join_sql = ( !$post_id ) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND p.post_approved = " . TRUE . " AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_approved = " . TRUE . " AND p2.post_id <= $post_id";
$count_sql = ( !$post_id ) ? '' : ", COUNT(p2.post_id) AS prev_posts";
$order_sql = ( !$post_id ) ? '' : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, f.forum_name, f.forum_status, f.forum_id ORDER BY p.post_id ASC";

$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, f.forum_name, f.forum_status, f.forum_id " . $count_sql . "
	FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $join_sql_table . " 
	WHERE $join_sql
		AND f.forum_id = t.forum_id
		$order_sql";
$result = $db->sql_query($sql);

if ( !($forum_data = $db->sql_fetchrow($result)) )
{
	message_die(MESSAGE, 'Topic_post_not_exist');
}

//
// Configure style, language, etc.
//
$userdata['user_style'] = ( $forum_data['forum_style'] ) ? $forum_data['user_style'] : $userdata['user_style'];
$session->configure($userdata);

$forum_id = $forum_data['forum_id'];

$acl = new auth('forum', $userdata, $forum_id);

//
// Start auth check
//
if ( !$acl->get_acl($forum_id, 'forum', 'read') )
{
	if ( $userdata['user_id'] != ANONYMOUS )
	{
		$redirect = ( isset($post_id) ) ? "p=$post_id" : "t=$topic_id"; 
		$redirect .= ( isset($start) ) ? "&start=$start" : '';
		$header_location = ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) ) ? 'Refresh: 0; URL=' : 'Location: ';
		header($header_location . 'login.' . $phpEx . $SID . '&redirect=viewtopic.' . $phpEx . '&' . $redirect);
		exit;
	}

	$message = sprintf($lang['Sorry_auth_read'], $is_auth['auth_read_type']);

	message_die(MESSAGE, $message);
}
//
// End auth check
//

$forum_name = $forum_data['forum_name'];
$topic_title = $forum_data['topic_title'];
$topic_id = $forum_data['topic_id'];
$topic_time = $forum_data['topic_time'];

if ( !empty($post_id) )
{
	$start = floor(($forum_data['prev_posts'] - 1) / $board_config['posts_per_page']) * $board_config['posts_per_page'];
}

$s_watching_topic = '';
$s_watching_topic_img = '';
watch_topic_forum('topic', $s_watching_topic, $s_watching_topic_img, $userdata['user_id'], $topic_id);

//
// Post ordering options
//
$previous_days = array(0 => $lang['All_Posts'], 1 => $lang['1_Day'], 7 => $lang['7_Days'], 14 => $lang['2_Weeks'], 30 => $lang['1_Month'], 90 => $lang['3_Months'], 180 => $lang['6_Months'], 364 => $lang['1_Year']);
$sort_by_text = array('a' => $lang['Author'], 't' => $lang['Post_time'], 's' => $lang['Subject']);
$sort_by = array('a' => 'u.username', 't' => 'p.post_id', 's' => 'pt.post_subject');

if ( isset($HTTP_POST_VARS['sort']) )
{
	if ( !empty($HTTP_POST_VARS['sort_days']) )
	{
		$sort_days = ( !empty($HTTP_POST_VARS['sort_days']) ) ? intval($HTTP_POST_VARS['sort_days']) : intval($HTTP_GET_VARS['sort_days']);
		$min_post_time = time() - ( $sort_days * 86400 );

		$sql = "SELECT COUNT(post_id) AS num_posts
			FROM " . POSTS_TABLE . "
			WHERE topic_id = $topic_id
				AND post_time >= $min_post_time 
				AND post_approved = " . TRUE;
		$result = $db->sql_query($sql);

		$start = 0;
		$total_replies = ( $row = $db->sql_fetchrow($result) ) ? $row['num_posts'] : 0;
		$limit_posts_time = "AND p.post_time >= $min_post_time ";
	}
	else
	{
		$total_replies = ( $forum_data['topic_replies'] ) ? $forum_data['topic_replies'] + 1 : 1;
	}

	$sort_key = ( isset($HTTP_POST_VARS['sort_key']) ) ? $HTTP_POST_VARS['sort_key'] : $HTTP_GET_VARS['sort_key'];
	$sort_dir = ( isset($HTTP_POST_VARS['sort_dir']) ) ? $HTTP_POST_VARS['sort_dir'] : $HTTP_GET_VARS['sort_dir'];
}
else
{
	$total_replies = $forum_data['topic_replies'] + 1;
	$limit_posts_time = '';

	$sort_days = 0;
	$sort_key = 't';
	$sort_dir = 'a';
}

$sort_order = $sort_by[$sort_key] . ' ' . ( ( $sort_dir == 'd' ) ? 'DESC' : 'ASC' );

$select_sort_days = '<select name="sort_days">';
foreach ( $previous_days as $day => $text )
{
	$selected = ( $sort_days == $day ) ? ' selected="selected"' : '';
	$select_sort_days .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
}
$select_sort_days .= '</select>';

$select_sort = '<select name="sort_key">';
foreach ( $sort_by_text as $key => $text )
{
	$selected = ( $sort_key == $key ) ? ' selected="selected"' : '';
	$select_sort .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
}
$select_sort .= '</select>';

$select_sort_dir = '<select name="sort_dir">';
$select_sort_dir .= ( $sort_dir == 'a' ) ? '<option value="a" selected="selected">' . $lang['Ascending'] . '</option><option value="d">' . $lang['Descending'] . '</option>' : '<option value="a">' . $lang['Ascending'] . '</option><option value="d" selected="selected">' . $lang['Descending'] . '</option>';
$select_sort_dir .= '</select>';

$select_post_days = '<select name="postdays">';
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($post_days == $previous_days[$i]) ? ' selected="selected"' : '';
	$select_post_days .= '<option value="' . $previous_days[$i] . '"' . $selected . '>' . $previous_days_text[$i] . '</option>';
}
$select_post_days .= '</select>';

//
// Decide how to order the post display
//
if ( !empty($HTTP_POST_VARS['postorder']) || !empty($HTTP_GET_VARS['postorder']) )
{
	$post_order = (!empty($HTTP_POST_VARS['postorder'])) ? $HTTP_POST_VARS['postorder'] : $HTTP_GET_VARS['postorder'];
	$post_time_order = ( $post_order == 'asc' ) ? 'ASC' : 'DESC';
}
else
{
	$post_order = 'asc';
	$post_time_order = 'ASC';
}

$select_post_order = '<select name="postorder">';
if ( $post_time_order == 'ASC' )
{
	$select_post_order .= '<option value="asc" selected="selected">' . $lang['Oldest_First'] . '</option><option value="desc">' . $lang['Newest_First'] . '</option>';
}
else
{
	$select_post_order .= '<option value="asc">' . $lang['Oldest_First'] . '</option><option value="desc" selected="selected">' . $lang['Newest_First'] . '</option>';
}
$select_post_order .= '</select>';

//
// Go ahead and pull all data for this topic
//
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id 
		AND p.post_approved = " . TRUE . " 
		$limit_posts_time
		AND pt.post_id = p.post_id
		AND u.user_id = p.poster_id
	ORDER BY $sort_order 
	LIMIT $start, " . $board_config['posts_per_page'];
$result = $db->sql_query($sql);

if ( $row = $db->sql_fetchrow($result) )
{
	$postrow = array();
	do
	{
		$postrow[] = $row;
	}
	while ( $row = $db->sql_fetchrow($result) );
	$db->sql_freeresult($result);

	$total_posts = count($postrow);
}
else
{
	message_die(MESSAGE, $lang['No_posts_topic']);
}

$sql = "SELECT *
	FROM " . RANKS_TABLE . "
	ORDER BY rank_special, rank_min";
$result = $db->sql_query($sql);

$ranksrow = array();
while ( $row = $db->sql_fetchrow($result) )
{
	$ranksrow[] = $row;
}
$db->sql_freeresult($result);

$rating = '';
if ( $userdata['user_id'] != ANONYMOUS )
{
	$rating_text = array(-5 => $lang['Very_poor'], -2 => $lang['Quite_poor'], 0 => $lang['Unrated'], 2 => $lang['Quite_good'], 5 => $lang['Very_good']);

	$sql = "SELECT rating 
		FROM " . TOPICS_RATINGS_TABLE . " 
		WHERE topic_id = $topic_id 
			AND user_id = " . $userdata['user_id'];
	$result = $db->sql_query($sql);

	$user_rating = ( $row = $db->sql_fetchrow($result) ) ? $row['rating'] : 0;

	for($i = -5; $i < 6; $i++)
	{
		$selected = ( $user_rating == $i ) ? ' selected="selected"' : '';
		$rating .= '<option value="' . $i . '"' . $selected . '>' . $i . ( ( !empty($rating_text[$i]) ) ? ' > ' . $rating_text[$i] : '' ) . '</option>';
	}

	$rating = '<select name="rating">' . $rating . '</select>';
}

//
// Was a highlight request part of the URI? Yes, this idea was
// taken from vB but we did already have a highlighter in place
// in search itself ... it's just been extended a bit!
//
if ( isset($HTTP_GET_VARS['highlight']) )
{
	$highlight_match = array();

	//
	// Split words and phrases
	//
	$words = explode(' ', trim(urldecode($HTTP_GET_VARS['highlight'])));

	for($i = 0; $i < count($words); $i++)
	{
		if ( trim($words[$i]) != '' )
		{
			$highlight_match[] = '#\b(' . str_replace('*', '([\w]+)?', $words[$i]) . ')\b#is';
		}
	}

	$highlight_active = ( count($highlight_match) ) ? true : false;
}
else
{
	$highlight_active = false;
}

//
// Define censored word matches
//
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

//
// User authorisation levels output
//
$s_forum_rules = '';
get_forum_rules('topic', $s_forum_rules, $forum_id);

$topic_mod .= ( $acl->get_acl($forum_id, 'mod', 'lock') ) ? ( ( $forum_data['topic_status'] == TOPIC_UNLOCKED ) ? '<a href="' . "modcp.$phpEx?t=$topic_id&amp;mode=lock" . '"><img src="' . $theme['topic_mod_lock'] . '" alt="' . $lang['Lock_topic'] . '" title="' . $lang['Lock_topic'] . '" border="0" /></a>&nbsp;' : '<a href="' . "modcp.$phpEx$SID&amp;t=$topic_id&amp;mode=unlock" . '"><img src="' . $theme['topic_mod_unlock'] . '" alt="' . $lang['Unlock_topic'] . '" title="' . $lang['Unlock_topic'] . '" border="0" /></a>&nbsp;' ) : '';

$topic_mod = ( $acl->get_acl($forum_id, 'mod', 'delete') ) ? '<a href="' . "modcp.$phpEx$SID&amp;t=$topic_id&amp;mode=delete" . '"><img src="' . $theme['topic_mod_delete'] . '" alt="' . $lang['Delete_topic'] . '" title="' . $lang['Delete_topic'] . '" border="0" /></a>&nbsp;' : '';

$topic_mod .= ( $acl->get_acl($forum_id, 'mod', 'move') ) ? '<a href="' . "modcp.$phpEx$SID&amp;t=$topic_id&amp;mode=move". '"><img src="' . $theme['topic_mod_move'] . '" alt="' . $lang['Move_topic'] . '" title="' . $lang['Move_topic'] . '" border="0" /></a>&nbsp;' : '';

$topic_mod .= ( $acl->get_acl($forum_id, 'mod', 'split') ) ? '<a href="' . "modcp.$phpEx$SID&amp;t=$topic_id&amp;mode=split" . '"><img src="' . $theme['topic_mod_split'] . '" alt="' . $lang['Split_topic'] . '" title="' . $lang['Split_topic'] . '" border="0" /></a>&nbsp;' : '';

$topic_mod .= ( $acl->get_acl($forum_id, 'mod', 'merge') ) ? '<a href="' . "modcp.$phpEx$SID&amp;t=$topic_id&amp;mode=merge" . '"><img src="' . $theme['topic_mod_merge'] . '" alt="' . $lang['Merge_topic'] . '" title="' . $lang['Merge_topic'] . '" border="0" /></a>&nbsp;' : '';

//
// If we've got a hightlight set pass it on to pagination.
//
$pagination = ( $highlight_active ) ? generate_pagination("viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=" . $HTTP_GET_VARS['highlight'], $total_replies, $board_config['posts_per_page'], $start) : generate_pagination("viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $total_replies, $board_config['posts_per_page'], $start);

//
// Post, reply and other URL generation for
// templating vars
//
$new_topic_url = 'posting.' . $phpEx . $SID . '&amp;mode=newtopic&amp;f=' . $forum_id;
$reply_topic_url = 'posting.' . $phpEx . $SID . '&amp;mode=reply&amp;f=' . $forum_id . '&amp;t=' . $topic_id;
$view_forum_url = 'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_id;
$view_prev_topic_url = 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '&amp;view=previous';
$view_next_topic_url = 'viewtopic.' . $phpEx . $SID . '&amp;f=' . $forum_id . '&amp;t=' . $topic_id . '&amp;view=next';

$reply_img = ( $forum_data['forum_status'] == FORUM_LOCKED || $forum_data['topic_status'] == TOPIC_LOCKED ) ? create_img($theme['reply_locked'], $lang['Topic_locked']) : create_img($theme['reply_new'], $lang['Reply_to_topic']);
$post_img = ( $forum_data['forum_status'] == FORUM_LOCKED ) ? create_img($theme['post_locked'], $lang['Forum_locked']) : create_img($theme['post_new'], $lang['Post_new_topic']);

//
// Set a cookie for this topic
//
if ( $userdata['user_id'] != ANONYMOUS )
{
	$tracking_topics = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_t']) : array();
	$tracking_forums = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) ) ? unserialize($HTTP_COOKIE_VARS[$board_config['cookie_name'] . '_f']) : array();

	if ( !empty($tracking_topics[$topic_id]) && !empty($tracking_forums[$forum_id]) )
	{
		$topic_last_read = ( $tracking_topics[$topic_id] > $tracking_forums[$forum_id] ) ? $tracking_topics[$topic_id] : $tracking_forums[$forum_id];
	}
	else if ( !empty($tracking_topics[$topic_id]) || !empty($tracking_forums[$forum_id]) )
	{
		$topic_last_read = ( !empty($tracking_topics[$topic_id]) ) ? $tracking_topics[$topic_id] : $tracking_forums[$forum_id];
	}
	else
	{
		$topic_last_read = $userdata['user_lastvisit'];
	}

	if ( count($tracking_topics) >= 150 && empty($tracking_topics[$topic_id]) )
	{
		asort($tracking_topics);
		unset($tracking_topics[key($tracking_topics)]);
	}

	$tracking_topics[$topic_id] = time();

	setcookie($board_config['cookie_name'] . '_t', serialize($tracking_topics), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
}

//
// Load templates
//
$template->set_filenames(array(
	'body' => 'viewtopic_body.html')
);
make_jumpbox('viewforum.'.$phpEx, $forum_id);

//
// Output page header
// 
$page_title = $lang['View_topic'] .' - ' . $topic_title;
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

if ( count($orig_word) )
{
	$topic_title = preg_replace($orig_word, $replacement_word, $topic_title); // Censor topic title
}

//
// Send vars to template
//
$template->assign_vars(array(
	'FORUM_ID' => $forum_id,
    'FORUM_NAME' => $forum_name,
    'TOPIC_ID' => $topic_id,
    'TOPIC_TITLE' => $topic_title,
	'PAGINATION' => $pagination,
	'PAGE_NUMBER' => sprintf($lang['Page_of'], ( floor( $start / $board_config['posts_per_page'] ) + 1 ), ceil( $total_replies / $board_config['posts_per_page'] )), 

	'POST_IMG' => $post_img,
	'REPLY_IMG' => $reply_img,

	'L_AUTHOR' => $lang['Author'],
	'L_MESSAGE' => $lang['Message'],
	'L_POSTED' => $lang['Posted'], 
	'L_POST_SUBJECT' => $lang['Post_subject'],
	'L_VIEW_NEXT_TOPIC' => $lang['View_next_topic'],
	'L_VIEW_PREVIOUS_TOPIC' => $lang['View_previous_topic'],
	'L_BACK_TO_TOP' => $lang['Back_to_top'],
	'L_DISPLAY_POSTS' => $lang['Display_posts'],
	'L_LOCK_TOPIC' => $lang['Lock_topic'], 
	'L_UNLOCK_TOPIC' => $lang['Unlock_topic'], 
	'L_MOVE_TOPIC' => $lang['Move_topic'], 
	'L_SPLIT_TOPIC' => $lang['Split_topic'], 
	'L_DELETE_TOPIC' => $lang['Delete_topic'], 
	'L_GOTO_PAGE' => $lang['Goto_page'], 
	'L_SORT_BY' => $lang['Sort_by'], 
	'L_RATE_TOPIC' => $lang['Rate_topic'], 

	'S_TOPIC_LINK' => 't', 
	'S_SELECT_SORT_DIR' => $select_sort_dir, 
	'S_SELECT_SORT_KEY' => $select_sort, 
	'S_SELECT_SORT_DAYS' => $select_sort_days,
	'S_SELECT_RATING' => $rating, 
	'S_TOPIC_ACTION' => "viewtopic.$phpEx$SID&amp;t=" . $topic_id . "&amp;start=$start", 
	'S_AUTH_LIST' => $s_forum_rules,
	'S_TOPIC_ADMIN' => $topic_mod,
	'S_WATCH_TOPIC' => $s_watching_topic,

	'U_VIEW_TOPIC' => "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;start=$start&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=" . $HTTP_GET_VARS['highlight'], 
	'U_VIEW_FORUM' => $view_forum_url,
	'U_VIEW_OLDER_TOPIC' => $view_prev_topic_url,
	'U_VIEW_NEWER_TOPIC' => $view_next_topic_url,
	'U_POST_NEW_TOPIC' => $new_topic_url,
	'U_POST_REPLY_TOPIC' => $reply_topic_url)
);

//
// Mozilla navigation bar
//
$nav_links['prev'] = array(
	'url' => $view_prev_topic_url,
	'title' => $lang['View_previous_topic']
);
$nav_links['next'] = array(
	'url' => $view_next_topic_url,
	'title' => $lang['View_next_topic']
);
$nav_links['up'] = array(
	'url' => $view_forum_url,
	'title' => $forum_name
);

//
// Does this topic contain a poll? 
//
if ( !empty($forum_data['topic_vote']) )
{
	$sql = "SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
		FROM " . VOTE_DESC_TABLE . " vd, " . VOTE_RESULTS_TABLE . " vr
		WHERE vd.topic_id = $topic_id
			AND vr.vote_id = vd.vote_id
		ORDER BY vr.vote_option_id ASC";
	$result = $db->sql_query($sql);

	if ( $vote_info = $db->sql_fetchrowset($result) )
	{
		$db->sql_freeresult($result);
		$vote_options = count($vote_info);

		$vote_id = $vote_info[0]['vote_id'];
		$vote_title = $vote_info[0]['vote_text'];

		$sql = "SELECT vote_id
			FROM " . VOTE_USERS_TABLE . "
			WHERE vote_id = $vote_id
				AND vote_user_id = " . $userdata['user_id'];
		$result = $db->sql_query($sql);

		$user_voted = ( $row = $db->sql_fetchrow($result) ) ? TRUE : 0;
		$db->sql_freeresult($result);

		if ( isset($HTTP_GET_VARS['vote']) || isset($HTTP_POST_VARS['vote']) )
		{
			$view_result = ( ( ( isset($HTTP_GET_VARS['vote']) ) ? $HTTP_GET_VARS['vote'] : $HTTP_POST_VARS['vote'] ) == 'viewresult' ) ? TRUE : 0;
		}
		else
		{
			$view_result = 0;
		}

		$poll_expired = ( $vote_info[0]['vote_length'] ) ? ( ( $vote_info[0]['vote_start'] + $vote_info[0]['vote_length'] < time() ) ? TRUE : 0 ) : 0;

		if ( $user_voted || $view_result || $poll_expired || !$acl->get_acl($forum_id, 'forum', 'vote') || $forum_data['topic_status'] == TOPIC_LOCKED )
		{
			$vote_results_sum = 0;
			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_results_sum += $vote_info[$i]['vote_result'];
			}

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_percent = ( $vote_results_sum > 0 ) ? $vote_info[$i]['vote_result'] / $vote_results_sum : 0;
				$poll_length = round($vote_percent * $board_config['vote_graphic_length']);
				$vote_percent = sprintf("%.1d%%", ($vote_percent * 100));
				$vote_graphic_img = create_img($theme['voting_graphic'] . ' width="' . $poll_length . '"', $vote_percent);

				if ( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars('poll_option', array(
					'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'],
					'POLL_OPTION_RESULT' => $vote_info[$i]['vote_result'],
					'POLL_OPTION_PERCENT' => $vote_percent,

					'POLL_OPTION_IMG' => $vote_graphic_img)
				);
			}

			$template->assign_vars(array(
				'S_HAS_POLL_DISPLAY' => true, 

				'L_TOTAL_VOTES' => $lang['Total_votes'],
				'TOTAL_VOTES' => $vote_results_sum)
			);

		}
		else
		{
			for($i = 0; $i < $vote_options; $i++)
			{
				if ( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars('poll_option', array(
					'POLL_OPTION_ID' => $vote_info[$i]['vote_option_id'],
					'POLL_OPTION_CAPTION' => $vote_info[$i]['vote_option_text'])
				);
			}

			$template->assign_vars(array(
				'S_HAS_POLL_OPTIONS' => true, 

				'L_SUBMIT_VOTE' => $lang['Submit_vote'],
				'L_VIEW_RESULTS' => $lang['View_results'],

				'U_VIEW_RESULTS' => "viewtopic.$phpEx$SID&amp;t=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;vote=viewresult")
			);

			$s_hidden_fields = '<input type="hidden" name="topic_id" value="' . $topic_id . '"><input type="hidden" name="mode" value="vote">';
		}

		if ( count($orig_word) )
		{
			$vote_title = preg_replace($orig_word, $replacement_word, $vote_title);
		}

		$template->assign_vars(array(
			'POLL_QUESTION' => $vote_title,

			'S_HIDDEN_FIELDS' => ( !empty($s_hidden_fields) ) ? $s_hidden_fields : '',
			'S_POLL_ACTION' => "posting.$phpEx$SID&amp;t=$topic_id")
		);
	}
}

//
// Update the topic view counter
//
$sql = "UPDATE " . TOPICS_TABLE . "
	SET topic_views = topic_views + 1
	WHERE topic_id = $topic_id";
$db->sql_query($sql);

//
// Container for user details, only process once
//
$poster_details = array();

//
// Okay, let's do the loop, yeah come on baby let's do the loop
// and it goes like this ...
//
for($i = 0; $i < $total_posts; $i++)
{
	$poster_id = $postrow[$i]['user_id'];
	$poster = ( $poster_id == ANONYMOUS ) ? $lang['Guest'] : $postrow[$i]['username'];

	$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['board_timezone']);

	$poster_posts = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Posts'] . ': ' . $postrow[$i]['user_posts'] : '';

	$poster_from = ( $postrow[$i]['user_from'] && $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Location'] . ': ' . $postrow[$i]['user_from'] : '';

	$poster_joined = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Joined'] . ': ' . create_date($lang['DATE_FORMAT'], $postrow[$i]['user_regdate'], $board_config['board_timezone']) : '';

	if ( $postrow[$i]['user_avatar_type'] && $poster_id != ANONYMOUS && $postrow[$i]['user_allowavatar'] && !isset($poster_details[$poster_id]) )
	{
		switch( $postrow[$i]['user_avatar_type'] )
		{
			case USER_AVATAR_UPLOAD:
				$poster_details[$poster_id]['avatar'] = ( $board_config['allow_avatar_upload'] ) ? '<img src="' . $board_config['avatar_path'] . '/' . $postrow[$i]['user_avatar'] . '" width="' . $postrow[$i]['user_avatar_width'] . '" height="' . $postrow[$i]['user_avatar_height'] . '" border="0" alt="" />' : '';
				break;
			case USER_AVATAR_REMOTE:
				$poster_details[$poster_id]['avatar'] = ( $board_config['allow_avatar_remote'] ) ? '<img src="' . $postrow[$i]['user_avatar'] . '" width="' . $postrow[$i]['user_avatar_width'] . '" height="' . $postrow[$i]['user_avatar_height'] . '" border="0" alt="" />' : '';
				break;
			case USER_AVATAR_GALLERY:
				$poster_details[$poster_id]['avatar'] = ( $board_config['allow_avatar_local'] ) ? '<img src="' . $board_config['avatar_gallery_path'] . '/' . $postrow[$i]['user_avatar'] . '" width="' . $postrow[$i]['user_avatar_width'] . '" height="' . $postrow[$i]['user_avatar_height'] . '" border="0" alt="" />' : '';
				break;
		}
	}

	//
	// Define the little post icon
	//
	$mini_post_img = ( $postrow[$i]['post_time'] > $userdata['user_lastvisit'] && $postrow[$i]['post_time'] > $topic_last_read ) ? create_img($theme['goto_post_new'], $lang['New_post']) : create_img($theme['goto_post'], $lang['Post']);
	
	//
	// Generate ranks, set them to empty string initially.
	//
	if ( !isset($poster_details[$poster_id]['rank_title']) )
	{
		if ( $postrow[$i]['user_rank'] )
		{
			for($j = 0; $j < count($ranksrow); $j++)
			{
				if ( $postrow[$i]['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'] )
				{
					$poster_details[$poster_id]['rank_title'] = $ranksrow[$j]['rank_title'];
					$poster_details[$poster_id]['rank_image'] = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" border="0" alt="' . $poster_rank . '" title="' . $poster_rank . '" /><br />' : '';
				}
			}
		}
		else
		{
			for($j = 0; $j < count($ranksrow); $j++)
			{
				if ( $postrow[$i]['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'] )
				{
					$poster_details[$poster_id]['rank_title'] = $ranksrow[$j]['rank_title'];
					$poster_details[$poster_id]['rank_image'] = ( $ranksrow[$j]['rank_image'] ) ? '<img src="' . $ranksrow[$j]['rank_image'] . '" border="0" alt="' . $poster_rank . '" title="' . $poster_rank . '" /><br />' : '';
				}
			}
		}
	}

	//
	// Handle anon users posting with usernames
	//
	if ( $poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '' )
	{
		$poster = $postrow[$i]['post_username'];
		$poster_rank = $lang['Guest'];
	}

	$temp_url = '';

	if ( $poster_id != ANONYMOUS )
	{
		$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id";
		$profile_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_profile'], $lang['Read_profile']) . '</a>';
		$profile = '<a href="' . $temp_url . '">' . $lang['Read_profile'] . '</a>';

		$temp_url = "privmsg.$phpEx$SID&amp;mode=post&amp;u=$poster_id";
		$pm_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_pm'], $lang['Send_private_message']) . '</a>';
		$pm = '<a href="' . $temp_url . '">' . $lang['Send_private_message'] . '</a>';

		if ( !empty($postrow[$i]['user_viewemail']) || $acl->get_acl($forum_id, 'mod') )
		{
			$email_uri = ( $board_config['board_email_form'] ) ? "profile.$phpEx$SID&amp;mode=email&amp;u=" . $poster_id : 'mailto:' . $postrow[$i]['user_email'];

			$email_img = '<a href="' . $email_uri . '">' . create_img($theme['icon_email'], $lang['Send_email']) . '</a>';
			$email = '<a href="' . $email_uri . '">' . $lang['Send_email'] . '</a>';
		}
		else
		{
			$email_img = '';
			$email = '';
		}

		$www_img = ( $postrow[$i]['user_website'] ) ? '<a href="' . $postrow[$i]['user_website'] . '" target="_userwww">' . create_img($theme['icon_www'], $lang['Visit_website']) . '</a>' : '';
		$www = ( $postrow[$i]['user_website'] ) ? '<a href="' . $postrow[$i]['user_website'] . '" target="_userwww">' . $lang['Visit_website'] . '</a>' : '';

		if ( !empty($postrow[$i]['user_icq']) )
		{
			$icq_status_img = '<a href="http://wwp.icq.com/' . $postrow[$i]['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $postrow[$i]['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
			$icq_img = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $postrow[$i]['user_icq'] . '">' . create_img($theme['icon_icq'], $lang['ICQ']) . '</a>';
			$icq =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $postrow[$i]['user_icq'] . '">' . $lang['ICQ'] . '</a>';
		}
		else
		{
			$icq_status_img = '';
			$icq_img = '';
			$icq = '';
		}

		$aim_img = ( $postrow[$i]['user_aim'] ) ? '<a href="aim:goim?screenname=' . $postrow[$i]['user_aim'] . '&amp;message=Hello+Are+you+there?">' . create_img($theme['icon_aim'], $lang['AIM']) . '</a>' : '';
		$aim = ( $postrow[$i]['user_aim'] ) ? '<a href="aim:goim?screenname=' . $postrow[$i]['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $lang['AIM'] . '</a>' : '';

		$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$poster_id";
		$msn_img = ( $postrow[$i]['user_msnm'] ) ? '<a href="' . $temp_url . '">' . create_img($theme['icon_msnm'], $lang['MSNM']) . '</a>' : '';
		$msn = ( $postrow[$i]['user_msnm'] ) ? '<a href="' . $temp_url . '">' . $lang['MSNM'] . '</a>' : '';

		$yim_img = ( $postrow[$i]['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $postrow[$i]['user_yim'] . '&amp;.src=pg">' . create_img($theme['icon_yim'], $lang['YIM']) . '</a>' : '';
		$yim = ( $postrow[$i]['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $postrow[$i]['user_yim'] . '&amp;.src=pg">' . $lang['YIM'] . '</a>' : '';
	}
	else
	{
		$profile_img = '';
		$profile = '';
		$pm_img = '';
		$pm = '';
		$email_img = '';
		$email = '';
		$www_img = '';
		$www = '';
		$icq_status_img = '';
		$icq_img = '';
		$icq = '';
		$aim_img = '';
		$aim = '';
		$msn_img = '';
		$msn = '';
		$yim_img = '';
		$yim = '';
	}

	$temp_url = 'posting.' . $phpEx . $SID . '&amp;mode=quote&amp;p=' . $postrow[$i]['post_id'];
	$quote_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_quote'], $lang['Reply_with_quote']) . '</a>';
	$quote = '<a href="' . $temp_url . '">' . $lang['Reply_with_quote'] . '</a>';

	if ( $acl->get_acl($forum_id, 'forum', 'search') )
	{
		$temp_url = 'search.' . $phpEx . $SID . '&amp;search_author=' . urlencode($postrow[$i]['username']) .'"&amp;showresults=posts';
		$search_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_search'], $lang['Search_user_posts']) . '</a>';
		$search ='<a href="' . $temp_url . '">' . $lang['Search_user_posts'] . '</a>';
	}
	else
	{
		$search_img = '';
		$search = '';
	}

	if ( ( $userdata['user_id'] == $poster_id && $acl->get_acl($forum_id, 'forum', 'edit') ) || $acl->get_acl($forum_id, 'mod', 'edit') )
	{
		$temp_url = "posting.$phpEx$SID&amp;mode=editpost&amp;p=" . $postrow[$i]['post_id'];
		$edit_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_edit'], $lang['Edit_delete_post']) . '</a>';
		$edit = '<a href="' . $temp_url . '">' . $lang['Edit_delete_post'] . '</a>';
	}
	else
	{
		$edit_img = '';
		$edit = '';
	}

	if ( $acl->get_acl($forum_id, 'mod', 'ip') )
	{
		$temp_url = "modcp.$phpEx$SID&amp;mode=ip&amp;p=" . $postrow[$i]['post_id'] . "&amp;t=" . $topic_id;
		$ip_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_ip'], $lang['View_IP']) . '</a>';
		$ip = '<a href="' . $temp_url . '">' . $lang['View_IP'] . '</a>';
	}
	else
	{
		$ip_img = '';
		$ip = '';
	}

	if ( ( $userdata['user_id'] == $poster_id && $acl->get_acl($forum_id, 'forum', 'delete') && $forum_topic_data['topic_last_post_id'] == $postrow[$i]['post_id'] ) || $acl->get_acl($forum_id, 'mod', 'delete') )
	{
		$temp_url = "posting.$phpEx$SID&amp;mode=delete&amp;p=" . $postrow[$i]['post_id'];
		$delpost_img = '<a href="' . $temp_url . '">' . create_img($theme['icon_delete'], $lang['Delete_post']) . '</a>';
		$delpost = '<a href="' . $temp_url . '">' . $lang['Delete_post'] . '</a>';
	}
	else
	{
		$delpost_img = '';
		$delpost = '';
	}

	$post_subject = ( $postrow[$i]['post_subject'] != '' ) ? $postrow[$i]['post_subject'] : '';

	$message = $postrow[$i]['post_text'];
	$bbcode_uid = $postrow[$i]['bbcode_uid'];

	$user_sig = ( $postrow[$i]['enable_sig'] && $postrow[$i]['user_sig'] != '' && $board_config['allow_sig'] ) ? $postrow[$i]['user_sig'] : '';
	$user_sig_bbcode_uid = $postrow[$i]['user_sig_bbcode_uid'];

	//
	// Note! The order used for parsing the message _is_ important, moving things around could break
	// output
	//

	//
	// If the board has HTML off but the post has HTML
	// on then we process it, else leave it alone
	//
	if ( !$acl->get_acl($forum_id, 'forum', 'html') )
	{
		if ( $user_sig != '' && $userdata['user_allowhtml'] )
		{
			$user_sig = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $user_sig);
		}

		if ( $postrow[$i]['enable_html'] && $acl->get_acl($forum_id, 'forum', 'bbcode') )
		{
			$message = preg_replace('#(<)([\/]?.*?)(>)#is', "&lt;\\2&gt;", $message);
		}
	}

	//
	// Parse message and/or sig for BBCode if reqd
	//
	if ( $user_sig != '' && $user_sig_bbcode_uid != '' && !isset($poster_details[$poster_id]['sig']) && $acl->get_acl($forum_id, 'forum', 'sigs') )
	{
		$poster_details[$poster_id]['sig'] = bbencode_second_pass($user_sig, $user_sig_bbcode_uid, $acl->get_acl($forum_id, 'forum', 'img'));
		$poster_details[$poster_id]['sig'] = make_clickable($poster_details[$poster_id]['sig']);

		if ( $postrow[$i]['user_allowsmile'] )
		{
			$poster_details[$poster_id]['sig'] =  smilies_pass($poster_details[$poster_id]['sig']);
		}

		$poster_details[$poster_id]['sig'] = '<br />_________________<br />' . str_replace("\n", "\n<br />\n", $poster_details[$poster_id]['sig']);
	}

	if ( $bbcode_uid != '' )
	{
		$message = ( $acl->get_acl($forum_id, 'forum', 'bbcode') ) ? bbencode_second_pass($message, $bbcode_uid, $acl->get_acl($forum_id, 'forum', 'img')) : preg_replace('/\:[0-9a-z\:]+\]/si', ']', $message);
	}

	if ( $postrow[$i]['enable_magic_url'] ) 
	{
		$message = make_clickable($message);
	}

	//
	// Highlight active words (primarily for search)
	//
	if ( $highlight_active )
	{
		if ( preg_match('/<.*>/', $message) )
		{
			$message = preg_replace($highlight_match, '<!-- #sh -->\1<!-- #eh -->', $message);

			$end_html = 0;
			$start_html = 1;
			$temp_message = '';
			$message = ' ' . $message . ' ';

			while( $start_html = strpos($message, '<', $start_html) )
			{
				$grab_length = $start_html - $end_html - 1;
				$temp_message .= substr($message, $end_html + 1, $grab_length);

				if ( $end_html = strpos($message, '>', $start_html) )
				{
					$length = $end_html - $start_html + 1;
					$hold_string = substr($message, $start_html, $length);

					if ( strrpos(' ' . $hold_string, '<') != 1 )
					{
						$end_html = $start_html + 1;
						$end_counter = 1;

						while ( $end_counter && $end_html < strlen($message) )
						{
							if ( substr($message, $end_html, 1) == '>' )
							{
								$end_counter--;
							}
							else if ( substr($message, $end_html, 1) == '<' )
							{
								$end_counter++;
							}

							$end_html++;
						}

						$length = $end_html - $start_html + 1;
						$hold_string = substr($message, $start_html, $length);
						$hold_string = str_replace('<!-- #sh -->', '', $hold_string);
						$hold_string = str_replace('<!-- #eh -->', '', $hold_string);
					}
					else if ( $hold_string == '<!-- #sh -->' )
					{
						$hold_string = str_replace('<!-- #sh -->', '<span style="color:#' . $theme['fontcolor3'] . '"><b>', $hold_string);
					}
					else if ( $hold_string == '<!-- #eh -->' )
					{
						$hold_string = str_replace('<!-- #eh -->', '</b></span>', $hold_string);
					}

					$temp_message .= $hold_string;

					$start_html += $length;
				}
				else
				{
					$start_html = strlen($message);
				}
			}

			$grab_length = strlen($message) - $end_html - 1;
			$temp_message .= substr($message, $end_html + 1, $grab_length);

			$message = trim($temp_message);
		}
		else
		{
			$message = preg_replace($highlight_match, '<span style="color:#' . $theme['fontcolor3'] . '"><b>\1</b></span>', $message);
		}
	}

	//
	// Replace naughty words
	//
	if ( count($orig_word) )
	{
		if ( $user_sig != '' )
		{
			$user_sig = preg_replace($orig_word, $replacement_word, $user_sig);
		}

		$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);
		$message = preg_replace($orig_word, $replacement_word, $message);
	}

	if ( $postrow[$i]['enable_smilies'] && $acl->get_acl($forum_id, 'forum', 'smilies') )
	{
		$message = smilies_pass($message);
	}

	$message = str_replace("\n", "\n<br />\n", $message);

	//
	// Editing information
	//
	if ( $postrow[$i]['post_edit_count'] )
	{
		$l_edit_time_total = ( $postrow[$i]['post_edit_count'] == 1 ) ? $lang['Edited_time_total'] : $lang['Edited_times_total'];
		
		$l_edited_by = '<br /><br />' . sprintf($l_edit_time_total, $poster, create_date($board_config['default_dateformat'], $postrow[$i]['post_edit_time'], $board_config['board_timezone']), $postrow[$i]['post_edit_count']);
	}
	else
	{
		$l_edited_by = '';
	}

	//
	// Again this will be handled by the templating
	// code at some point
	//
	$template->assign_block_vars('postrow', array(
		'POSTER_NAME' => $poster,
		'POSTER_RANK' => $poster_details[$poster_id]['rank_title'],
		'RANK_IMAGE' => $poster_details[$poster_id]['rank_image'],
		'POSTER_JOINED' => $poster_joined,
		'POSTER_POSTS' => $poster_posts,
		'POSTER_FROM' => $poster_from,
		'POSTER_AVATAR' => $poster_details[$poster_id]['avatar'],
		'POST_DATE' => $post_date,
		'POST_SUBJECT' => $post_subject,
		'MESSAGE' => $message, 
		'SIGNATURE' => $poster_details[$poster_id]['sig'], 
		'EDITED_MESSAGE' => $l_edited_by, 

		'MINI_POST_IMG' => $mini_post_img, 
		'PROFILE_IMG' => $profile_img, 
		'PROFILE' => $profile, 
		'SEARCH_IMG' => $search_img,
		'SEARCH' => $search,
		'PM_IMG' => $pm_img,
		'PM' => $pm,
		'EMAIL_IMG' => $email_img,
		'EMAIL' => $email,
		'WWW_IMG' => $www_img,
		'WWW' => $www,
		'ICQ_STATUS_IMG' => $icq_status_img,
		'ICQ_IMG' => $icq_img, 
		'ICQ' => $icq, 
		'AIM_IMG' => $aim_img,
		'AIM' => $aim,
		'MSN_IMG' => $msn_img,
		'MSN' => $msn,
		'YIM_IMG' => $yim_img,
		'YIM' => $yim,
		'EDIT_IMG' => $edit_img,
		'EDIT' => $edit,
		'QUOTE_IMG' => $quote_img,
		'QUOTE' => $quote,
		'IP_IMG' => $ip_img, 
		'IP' => $ip, 
		'DELETE_IMG' => $delpost_img, 
		'DELETE' => $delpost, 

		'L_MINI_POST_ALT' => $mini_post_alt, 

		'S_ROW_COUNT' => $i, 

		'U_MINI_POST' => $mini_post_url,
		'U_POST_ID' => $postrow[$i]['post_id'])
	);
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>