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

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

//
// Start initial var setup
//
if(isset($HTTP_GET_VARS[POST_TOPIC_URL]))
{
	$topic_id = intval($HTTP_GET_VARS[POST_TOPIC_URL]);
}
if(isset($HTTP_GET_VARS[POST_POST_URL]))
{
	$post_id = intval($HTTP_GET_VARS[POST_POST_URL]);
}

$start = ( isset($HTTP_GET_VARS['start']) ) ? intval($HTTP_GET_VARS['start']) : 0;

//
// End initial var setup
//

if( !isset($topic_id) && !isset($post_id) )
{
	message_die(GENERAL_MESSAGE, 'Topic_post_not_exist');
}

//
// Find topic id if user requested a newer
// or older topic
//
if( isset($HTTP_GET_VARS["view"]) && empty($HTTP_GET_VARS[POST_POST_URL]) )
{
	if( $HTTP_GET_VARS["view"] == "newest" )
	{
		if( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_sid"]) )
		{
			$session_id = $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_sid"];

			if( $session_id )
			{
				$sql = "SELECT p.post_id 
					FROM " . POSTS_TABLE . " p, " . SESSIONS_TABLE . " s,  " . USERS_TABLE . " u 
					WHERE s.session_id = '$session_id' 
						AND u.user_id = s.session_user_id 
						AND p.topic_id = $topic_id 
						AND p.post_time >= u.user_lastvisit 
					ORDER BY p.post_time ASC 
					LIMIT 1";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't obtain newer/older topic information", "", __LINE__, __FILE__, $sql);
				}

				if( !($row = $db->sql_fetchrow($result)) )
				{
					message_die(GENERAL_MESSAGE, $lang['No_new_posts_last_visit']);
				}
				else
				{
					$post_id = $row['post_id'];
					header("Location: " . append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=$post_id#$post_id", true));
				}
			}
			else
			{
				header("Location: " . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id", true));
			}
		}
		else
		{
			header("Location: " . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id", true));
		}
	}
	else if( $HTTP_GET_VARS["view"] == "next" )
	{
		$sql_condition = ">";
		$sql_ordering = "ASC";
	}
	else if( $HTTP_GET_VARS["view"] == "previous" )
	{
		$sql_condition = "<";
		$sql_ordering = "DESC";
	}

	$sql = "SELECT t.topic_id
		FROM " . TOPICS_TABLE . " t, " . TOPICS_TABLE . " t2, " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2
		WHERE t2.topic_id = $topic_id
			AND p2.post_id = t2.topic_last_post_id
			AND t.forum_id = t2.forum_id
			AND p.post_id = t.topic_last_post_id
			AND p.post_time $sql_condition p2.post_time
			AND p.topic_id = t.topic_id
		ORDER BY p.post_time $sql_ordering
		LIMIT 1";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain newer/older topic information", "", __LINE__, __FILE__, $sql);
	}

	if( !$row = $db->sql_fetchrow($result) )
	{
		if( $HTTP_GET_VARS["view"] == "next" )
		{
			message_die(GENERAL_MESSAGE, 'No_newer_topics');
		}
		else
		{
			message_die(GENERAL_MESSAGE, 'No_older_topics');
		}
	}
	else
	{
		$topic_id = $row['topic_id'];
	}
}


//
// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
//
$join_sql_table = ( !isset($post_id) ) ? "" : ", " . POSTS_TABLE . " p, " . POSTS_TABLE . " p2 ";
$join_sql = ( !isset($post_id) ) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
$count_sql = ( !isset($post_id) ) ? "" : ", COUNT(p2.post_id) AS prev_posts";

$order_sql = ( !isset($post_id) ) ? "" : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, f.forum_name, f.forum_status, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_pollcreate, f.auth_vote, f.auth_attachments ORDER BY p.post_id ASC";

$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, t.topic_type, t.topic_vote, f.forum_name, f.forum_status, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_pollcreate, f.auth_vote, f.auth_attachments" . $count_sql . "
	FROM " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $join_sql_table . " 
	WHERE $join_sql
		AND f.forum_id = t.forum_id
		$order_sql";
if( !$result = $db->sql_query($sql) )
{
	message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
}

if( !$total_rows = $db->sql_numrows($result) )
{
	message_die(GENERAL_MESSAGE,  'Topic_post_not_exist', "", __LINE__, __FILE__, $sql);
}
$forum_row = $db->sql_fetchrow($result);

$forum_id = $forum_row['forum_id'];

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//

$forum_name = $forum_row['forum_name'];
$topic_title = $forum_row['topic_title'];
$topic_id = $forum_row['topic_id'];
$topic_time = $forum_row['topic_time'];

if(!empty($post_id))
{
	$start = floor(($forum_row['prev_posts'] - 1) / $board_config['posts_per_page']) * $board_config['posts_per_page'];
}

//
// Start auth check
//
$is_auth = array();
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_row);

if(!$is_auth['auth_view'] || !$is_auth['auth_read'])
{
	//
	// The user is not authed to read this forum ...
	//
	$message = sprintf($lang['Sorry_auth_read'], $is_auth['auth_read_type']);

	message_die(GENERAL_MESSAGE, $message);
}
//
// End auth check
//

//
// Is user watching this thread? This could potentially
// be combined into the above query but the LEFT JOIN causes
// a number of problems which will probably end up in this
// solution being practically as fast and certainly simpler!
//
if( $userdata['user_id'] != ANONYMOUS )
{
	$can_watch_topic = TRUE;

	$sql = "SELECT notify_status
		FROM " . TOPICS_WATCH_TABLE . "
		WHERE topic_id = $topic_id
			AND user_id = " . $userdata['user_id'];
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain topic watch information", "", __LINE__, __FILE__, $sql);
	}
	else if( $db->sql_numrows($result) )
	{
		if( isset($HTTP_GET_VARS['unwatch']) )
		{
			if( $HTTP_GET_VARS['unwatch'] == "topic" )
			{
				$is_watching_topic = 0;

				$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : "";
				$sql = "DELETE $sql_priority FROM " . TOPICS_WATCH_TABLE . "
					WHERE topic_id = $topic_id
						AND user_id = " . $userdata['user_id'];
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't delete topic watch information", "", __LINE__, __FILE__, $sql);
				}
			}
			
			$template->assign_vars(array(
				"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . '">')
			);

			$message = $lang['No_longer_watching']. "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			$is_watching_topic = TRUE;

			$watch_data = $db->sql_fetchrow($result);

			if( $watch_data['notify_status'] )
			{
				$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : "";
				$sql = "UPDATE $sql_priority " . TOPICS_WATCH_TABLE . "
					SET notify_status = 0
					WHERE topic_id = $topic_id
						AND user_id = " . $userdata['user_id'];
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't update topic watch information", "", __LINE__, __FILE__, $sql);
				}
			}
		}
	}
	else
	{
		if( isset($HTTP_GET_VARS['watch']) )
		{
			if( $HTTP_GET_VARS['watch'] == "topic" )
			{
				$is_watching_topic = TRUE;

				$sql_priority = (SQL_LAYER == "mysql") ? "LOW_PRIORITY" : "";
				$sql = "INSERT $sql_priority INTO " . TOPICS_WATCH_TABLE . " (user_id, topic_id, notify_status)
					VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
				if( !$result = $db->sql_query($sql) )
				{
					message_die(GENERAL_ERROR, "Couldn't insert topic watch information", "", __LINE__, __FILE__, $sql);
				}
			}

			$template->assign_vars(array(
				"META" => '<meta http-equiv="refresh" content="3;url=' . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . '">')
			);

			$message = $lang['You_are_watching']. "<br /><br />" . sprintf($lang['Click_return_topic'], "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;start=$start") . "\">", "</a>");
			message_die(GENERAL_MESSAGE, $message);
		}
		else
		{
			$is_watching_topic = 0;
		}
	}
}
else
{
	if( isset($HTTP_GET_VARS['unwatch']) )
	{
		if( $HTTP_GET_VARS['unwatch'] == "topic" )
		{
			header("Location: " . append_sid("login.$phpEx?redirect=viewtopic.$phpEx&" . POST_TOPIC_URL . "=$topic_id&unwatch=topic", true));
		}
	}
	else
	{
		$can_watch_topic = 0;
		$is_watching_topic = 0;
	}
}

//
// Generate a 'Show posts in previous x days' select box. If the postdays var is POSTed
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
$previous_days_text = array($lang['All_Posts'], $lang['1_Day'], $lang['7_Days'], $lang['2_Weeks'], $lang['1_Month'], $lang['3_Months'], $lang['6_Months'], $lang['1_Year']);

if( !empty($HTTP_POST_VARS['postdays']) || !empty($HTTP_GET_VARS['postdays']) )
{
	$post_days = ( !empty($HTTP_POST_VARS['postdays']) ) ? $HTTP_POST_VARS['postdays'] : $HTTP_GET_VARS['postdays'];
	$min_post_time = time() - ($post_days * 86400);

	$sql = "SELECT COUNT(post_id) AS num_posts
		FROM " . POSTS_TABLE . "
		WHERE topic_id = $topic_id
			AND post_time >= $min_post_time";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain limited topics count information", "", __LINE__, __FILE__, $sql);
	}

	$total_replies = ( $row = $db->sql_fetchrow($result) ) ? $row['num_posts'] : 0;

	$limit_posts_time = "AND p.post_time >= $min_post_time ";

	if(!empty($HTTP_POST_VARS['postdays']))
	{
		$start = 0;
	}
}
else
{
	$total_replies = $forum_row['topic_replies'] + 1;

	$limit_posts_time = "";
	$post_days = 0;
}

$select_post_days = "<select name=\"postdays\">";
for($i = 0; $i < count($previous_days); $i++)
{
	$selected = ($post_days == $previous_days[$i]) ? " selected=\"selected\"" : "";
	$select_post_days .= "<option value=\"" . $previous_days[$i] . "\"$selected>" . $previous_days_text[$i] . "</option>";
}
$select_post_days .= "</select>";

//
// Decide how to order the post display
//
if(!empty($HTTP_POST_VARS['postorder']) || !empty($HTTP_GET_VARS['postorder']))
{
	$post_order = (!empty($HTTP_POST_VARS['postorder'])) ? $HTTP_POST_VARS['postorder'] : $HTTP_GET_VARS['postorder'];
	$post_time_order = ($post_order == "asc") ? "ASC" : "DESC";
}
else
{
	$post_time_order = "ASC";
}

$select_post_order = "<select name=\"postorder\">";
if($post_time_order == "ASC")
{
	$select_post_order .= "<option value=\"asc\" selected=\"selected\">" . $lang['Oldest_First'] . "</option><option value=\"desc\">" . $lang['Newest_First'] . "</option>";
}
else
{
	$select_post_order .= "<option value=\"asc\">" . $lang['Oldest_First'] . "</option><option value=\"desc\" selected=\"selected\">" . $lang['Newest_First'] . "</option>";
}
$select_post_order .= "</select>";

//
// Go ahead and pull all data for this topic
//
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id
		AND p.poster_id = u.user_id
		AND p.post_id = pt.post_id
		$limit_posts_time
	ORDER BY p.post_time $post_time_order
	LIMIT $start, ".$board_config['posts_per_page'];
if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain post/user information.", "", __LINE__, __FILE__, $sql);
}

if(!$total_posts = $db->sql_numrows($result))
{
	message_die(GENERAL_MESSAGE, $lang['No_posts_topic']);
}
$postrow = $db->sql_fetchrowset($result);

$sql = "SELECT *
	FROM " . RANKS_TABLE . "
	ORDER BY rank_special, rank_min";
if(!$ranks_result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain ranks information.", "", __LINE__, __FILE__, $sql);
}
$ranksrow = $db->sql_fetchrowset($ranksresult);

//
// Define censored word matches
//
$orig_word = array();
$replacement_word = array();
obtain_word_list($orig_word, $replacement_word);

//
// Post, reply and other URL generation for
// templating vars
//
$new_topic_url = append_sid("posting.$phpEx?mode=newtopic&amp;" . POST_FORUM_URL . "=$forum_id");
$reply_topic_url = append_sid("posting.$phpEx?mode=reply&amp;" . POST_TOPIC_URL . "=$topic_id");

$view_forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id");

$view_prev_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=previous");
$view_next_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=next");

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

$reply_img = ( $forum_row['forum_status'] == FORUM_LOCKED || $forum_row['topic_status'] == TOPIC_LOCKED ) ? $images['reply_locked'] : $images['reply_new'];
$reply_alt = ( $forum_row['forum_status'] == FORUM_LOCKED || $forum_row['topic_status'] == TOPIC_LOCKED ) ? $lang['Topic_locked'] : $lang['Reply_to_topic'];
$post_img = ( $forum_row['forum_status'] == FORUM_LOCKED ) ? $images['post_locked'] : $images['post_new'];
$post_alt = ( $forum_row['forum_status'] == FORUM_LOCKED ) ? $lang['Forum_locked'] : $lang['Post_new_topic'];

//
// Dump out the page header and load viewtopic body template
//
if( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t_$topic_id"]) && isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_f_$forum_id"]) )
{
	$topic_last_read = ( $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t_$topic_id"] > $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_f_$forum_id"] ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t_$topic_id"] : $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_f_$forum_id"];
}
else if( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t_$topic_id"]) || isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_f_$forum_id"]) )
{
	$topic_last_read = ( isset($HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t_$topic_id"]) ) ? $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_t_$topic_id"] : $HTTP_COOKIE_VARS[$board_config['cookie_name'] . "_f_$forum_id"];
}
else
{
	$topic_last_read = $userdata['session_last_visit'];
}

//
// Set a cookie for this topic
//
setcookie($board_config['cookie_name'] . "_t_$topic_id", time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
//session_send_cookie("_t_$topic_id", time(), 0);

//
// Load templates
//
$template->set_filenames(array(
	"body" => "viewtopic_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox($forum_id);
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],

	"S_JUMPBOX_LIST" => $jumpbox,
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"FORUM_ID" => $forum_id,
    "FORUM_NAME" => $forum_name,
    "TOPIC_ID" => $topic_id,
    "TOPIC_TITLE" => $topic_title,

	"L_DISPLAY_POSTS" => $lang['Display_posts'],
	"L_RETURN_TO_TOP" => $lang['Return_to_top'],

	"S_TOPIC_LINK" => POST_TOPIC_URL, 
	"S_SELECT_POST_DAYS" => $select_post_days,
	"S_SELECT_POST_ORDER" => $select_post_order,
	"S_POST_DAYS_ACTION" => append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&amp;start=$start"))

);

$page_title = $lang['View_topic'] ." - $topic_title";
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

//
// End header
//

//
// Censor topic title
//
if( count($orig_word) )
{
	$topic_title = preg_replace($orig_word, $replacement_word, $topic_title);
}

//
// Was a highlight request part of the URI? Yes, this idea was
// taken from vB but we did already have a highlighter in place
// in search itself ... it's just been extended a bit!
//

if( isset($HTTP_GET_VARS['highlight']) )
{
	$highlight_match = array();
	$highlight_replace = array();

	//
	// Split words and phrases
	//
	$words = explode(" ", trim(urldecode($HTTP_GET_VARS['highlight'])));

	for($i = 0; $i < count($words); $i++)
	{
		if( trim($words[$i]) != "" )
		{
			$highlight_match[] = "#\b(" . str_replace("\*", ".*?", phpbb_preg_quote($words[$i], "#")) . ")(?!.*?<\/a>)(?!.*?\[/url\])\b#i";
			$highlight_replace[] = "<font color=\"#" . $theme['fontcolor3'] . "\"><b>\\1</b></font>";
		}
	}

	$highlight_active = ( count($highlight_match) ) ? true : false;

}
else
{
	$highlight_active = false;
}

$template->assign_vars(array(
	"FORUM_NAME" => $forum_name,
	"TOPIC_TITLE" => $topic_title,

	"L_POSTED" => $lang['Posted'],
	"L_POST_SUBJECT" => $lang['Post_subject'],
	"L_VIEW_NEXT_TOPIC" => $lang['View_next_topic'],
	"L_VIEW_PREVIOUS_TOPIC" => $lang['View_previous_topic'],

	"L_POST_NEW_TOPIC" => $post_alt, 
	"L_POST_REPLY_TOPIC" => $reply_alt, 

	"L_BACK_TO_TOP" => $lang['Back_to_top'],

	"IMG_POST" => $post_img,
	"IMG_REPLY" => $reply_img,

	"U_VIEW_FORUM" => $view_forum_url,
	"U_VIEW_OLDER_TOPIC" => $view_prev_topic_url,
	"U_VIEW_NEWER_TOPIC" => $view_next_topic_url,
	"U_POST_NEW_TOPIC" => $new_topic_url,
	"U_POST_REPLY_TOPIC" => $reply_topic_url)
);

//
// Does this topic contain a voting element?
//
if( !empty($forum_row['topic_vote']) )
{
	$sql = "SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
		FROM " . VOTE_DESC_TABLE . " vd, " . VOTE_RESULTS_TABLE . " vr
		WHERE vd.topic_id = $topic_id
			AND vr.vote_id = vd.vote_id
		ORDER BY vr.vote_option_id ASC";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain vote data for this topic", "", __LINE__, __FILE__, $sql);
	}

	if( $vote_options = $db->sql_numrows($result) )
	{
		$vote_info = $db->sql_fetchrowset($result);

		$vote_id = $vote_info[0]['vote_id'];
		$vote_title = $vote_info[0]['vote_text'];

		$sql = "SELECT vote_id
			FROM " . VOTE_USERS_TABLE . "
			WHERE vote_id = $vote_id
				AND vote_user_id = " . $userdata['user_id'];
		if( !$result = $db->sql_query($sql) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain user vote data for this topic", "", __LINE__, __FILE__, $sql);
		}

		$user_voted = ( $db->sql_numrows($result) ) ? TRUE : 0;

		if( isset($HTTP_GET_VARS['vote']) || isset($HTTP_POST_VARS['vote']) )
		{
			$view_result = ( ( ( isset($HTTP_GET_VARS['vote']) ) ? $HTTP_GET_VARS['vote'] : $HTTP_POST_VARS['vote'] ) == "viewresult" ) ? TRUE : 0;
		}
		else
		{
			$view_result = 0;
		}

		$poll_expired = ( $vote_info[0]['vote_length'] ) ? ( ( $vote_info[0]['vote_start'] + $vote_info[0]['vote_length'] < time() ) ? TRUE : 0 ) : 0;

		if( $user_voted || $view_result || $poll_expired || !$is_auth['auth_vote'] || $forum_row['topic_status'] == TOPIC_LOCKED )
		{

			$template->set_filenames(array(
				"pollbox" => "viewtopic_poll_result.tpl")
			);

			$vote_results_sum = 0;

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_results_sum += $vote_info[$i]['vote_result'];
			}

			$vote_graphic = 0;
			$vote_graphic_max = count($images['voting_graphic']);

			for($i = 0; $i < $vote_options; $i++)
			{
				$vote_percent = ( $vote_results_sum > 0 ) ? $vote_info[$i]['vote_result'] / $vote_results_sum : 0;
				$vote_graphic_length = round($vote_percent * $board_config['vote_graphic_length']);

				$vote_graphic_img = $images['voting_graphic'][$vote_graphic];
				$vote_graphic = ($vote_graphic < $vote_graphic_max - 1) ? $vote_graphic + 1 : 0;

				if( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars("poll_option", array(
					"POLL_OPTION_CAPTION" => $vote_info[$i]['vote_option_text'],
					"POLL_OPTION_RESULT" => $vote_info[$i]['vote_result'],
					"POLL_OPTION_PERCENT" => sprintf("%.1d%%", ($vote_percent * 100)),

					"POLL_OPTION_IMG" => $vote_graphic_img,
					"POLL_OPTION_IMG_WIDTH" => $vote_graphic_length)
				);
			}

			$template->assign_vars(array(
				"TOTAL_VOTES" => $vote_results_sum)
			);

		}
		else
		{
			$template->set_filenames(array(
				"pollbox" => "viewtopic_poll_ballot.tpl")
			);

			for($i = 0; $i < $vote_options; $i++)
			{
				if( count($orig_word) )
				{
					$vote_info[$i]['vote_option_text'] = preg_replace($orig_word, $replacement_word, $vote_info[$i]['vote_option_text']);
				}

				$template->assign_block_vars("poll_option", array(
					"POLL_OPTION_ID" => $vote_info[$i]['vote_option_id'],
					"POLL_OPTION_CAPTION" => $vote_info[$i]['vote_option_text'])
				);
			}

			$template->assign_vars(array(
				"L_SUBMIT_VOTE" => $lang['Submit_vote'],
				"L_VIEW_RESULTS" => $lang['View_results'],

				"U_VIEW_RESULTS" => append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;vote=viewresult"))
			);

			$s_hidden_fields = '<input type="hidden" name="topic_id" value="' . $topic_id . '"><input type="hidden" name="mode" value="vote">';
		}

		if( count($orig_word) )
		{
			$vote_title = preg_replace($orig_word, $replacement_word, $vote_title);
		}

		$template->assign_vars(array(
			"POLL_QUESTION" => $vote_title,

			"S_HIDDEN_FIELDS" => $s_hidden_fields,
			"S_VOTE_ACTION" => append_sid("posting.$phpEx?" . POST_TOPIC_URL . "=$topic_id"))
		);

		$template->assign_var_from_handle("POLL_DISPLAY", "pollbox");

	}
}

//
// Update the topic view counter
//
$sql = "UPDATE " . TOPICS_TABLE . "
	SET topic_views = topic_views + 1
	WHERE topic_id = $topic_id";
if(!$update_result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't update topic views.", "", __LINE__, __FILE__, $sql);
}

//
// Okay, let's do the loop, yeah come on baby let's do the loop
// and it goes like this ...
//
for($i = 0; $i < $total_posts; $i++)
{
	$poster_id = $postrow[$i]['user_id'];
	$poster = $postrow[$i]['username'];

	$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['board_timezone']);

	$poster_posts = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Posts'] . ": " . $postrow[$i]['user_posts'] : "";

	$poster_from = ( $postrow[$i]['user_from'] && $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Location'] . ": " . $postrow[$i]['user_from'] : "";

	$poster_joined = ( $postrow[$i]['user_id'] != ANONYMOUS ) ? $lang['Joined'] . ": " . create_date($lang['DATE_FORMAT'], $postrow[$i]['user_regdate'], $board_config['board_timezone']) : "";

	if( $postrow[$i]['user_avatar_type'] && $poster_id != ANONYMOUS && $postrow[$i]['user_allowavatar'] )
	{
		switch( $postrow[$i]['user_avatar_type'] )
		{
			case USER_AVATAR_UPLOAD:
				$poster_avatar = ( $board_config['avatar_upload_db'] ) ? "<img src=\"avatar.$phpEx?p=" . $postrow[$i]['post_id'] . "\" alt=\"\" />" : "<img src=\"" . $board_config['avatar_path'] . "/" . $postrow[$i]['user_avatar'] . "\" alt=\"\" border=\"0\" />";
				break;
			case USER_AVATAR_REMOTE:
				$poster_avatar = "<img src=\"" . $postrow[$i]['user_avatar'] . "\" alt=\"\" border=\"0\" />";
				break;
			case USER_AVATAR_GALLERY:
				$poster_avatar = "<img src=\"" . $board_config['avatar_gallery_path'] . "/" . $postrow[$i]['user_avatar'] . "\" alt=\"\" border=\"0\" />";
				break;
		}
	}
	else
	{
		$poster_avatar = "";
	}

	//
	// Define the little post icon
	//
	if( $postrow[$i]['post_time'] > $userdata['session_last_visit'] && $postrow[$i]['post_time'] > $topic_last_read )
	{
		$mini_post_img = '<img src="' . $images['icon_minipost_new'] . '" alt="' . $lang['New_post'] . '" title="' . $lang['New_post'] . '" border="0" />';
	}
	else
	{
		$mini_post_img = '<img src="' . $images['icon_minipost'] . '" alt="' . $lang['Post'] . '" title="' . $lang['Post'] . '" border="0" />';
	}
	
	$mini_post_url = append_sid("viewtopic.$phpEx?" . POST_POST_URL . "=" . $postrow[$i]['post_id']) ."#".$postrow[$i]['post_id'];

	//
	// Generate ranks
	//
	
	//
	// Set them to empty string initially, in case we don't find a rank for this dude.
	//
	$poster_rank = "";
	$rank_image = "";
	
	if( $postrow[$i]['user_id'] == ANONYMOUS )
	{
		//
		// This is redundant, but some day we might wanna stick in a rank for anon. posts.
		//
		$poster_rank = "";
		$rank_image = "";
	}
	else if( $postrow[$i]['user_rank'] )
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if( $postrow[$i]['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'] )
			{
				$poster_rank = $ranksrow[$j]['rank_title'];
				$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . $ranksrow[$j]['rank_image'] . "\" alt=\"\" border=\"0\" /><br />" : "";
			}
		}
	}
	else
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if( $postrow[$i]['user_posts'] >= $ranksrow[$j]['rank_min'] && !$ranksrow[$j]['rank_special'] )
			{
				$poster_rank = $ranksrow[$j]['rank_title'];
				$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . $ranksrow[$j]['rank_image'] . "\" alt=\"\" border=\"0\" /><br />" : "";
			}
		}
	}

	//
	// Handle anon users posting with usernames
	//
	if($poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '')
	{
		$poster = $postrow[$i]['post_username'];
		$poster_rank = $lang['Guest'];
	}

	if($poster_id != ANONYMOUS)
	{
		$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . "\" title=\"" . $lang['Read_profile'] . "\" border=\"0\" /></a>";

		$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Send_private_message'] . "\" title=\"" . $lang['Send_private_message'] . "\" border=\"0\" /></a>";

		if( !empty($postrow[$i]['user_viewemail']) )
		{
			$email_uri = ( $board_config['board_email_form'] ) ? append_sid("profile.$phpEx?mode=email&amp;" . POST_USERS_URL ."=" . $poster_id) : "mailto:" . $postrow[$i]['user_email'];

			$email_img = "<a href=\"$email_uri\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . "\" title=\"" . $lang['Send_email'] . "\" border=\"0\" /></a>";
		}
		else
		{
			$email_img = "";
		}

		$www_img = ($postrow[$i]['user_website']) ? "<a href=\"" . $postrow[$i]['user_website'] . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" title=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "";

		if( !empty($postrow[$i]['user_icq']) )
		{
			$icq_status_img = "<a href=\"http://wwp.icq.com/" . $postrow[$i]['user_icq'] . "#pager\"><img src=\"http://web.icq.com/whitepages/online?icq=" . $postrow[$i]['user_icq'] . "&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" /></a>";

			//
			// This cannot stay like this, it needs a 'proper' solution, eg a separate
			// template for overlaying the ICQ icon, or we just do away with the icq status 
			// display (which is after all somewhat a pain in the rear :D 
			//
			if( $theme['template_name'] == "subSilver" )
			{
				$icq_add_img = '<table width="59" border="0" cellspacing="0" cellpadding="0"><tr><td nowrap="nowrap" class="icqback"><img src="images/spacer.gif" width="3" height="18" alt = "" />' . $icq_status_img . '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $postrow[$i]['user_icq'] . '"><img src="images/spacer.gif" width="35" height="18" border="0" alt="' . $lang['ICQ'] . '" title="' . $lang['ICQ'] . '" /></a></td></tr></table>'; 
				$icq_status_img = "";
			}
			else
			{
				$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $postrow[$i]['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" title=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
			}
		}
		else
		{
			$icq_status_img = "";
			$icq_add_img = "";
		}

		$aim_img = ($postrow[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . $postrow[$i]['user_aim'] . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" alt=\"" . $lang['AIM'] . "\" title=\"" . $lang['AIM'] . "\" border=\"0\" /></a>" : "";

		$msn_img = ($postrow[$i]['user_msnm']) ? "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"" . $images['icon_msnm'] . "\" alt=\"" . $lang['MSNM'] . "\" title=\"" . $lang['MSNM'] . "\" border=\"0\" /></a>" : "";

		$yim_img = ($postrow[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $postrow[$i]['user_yim'] . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" alt=\"" . $lang['YIM'] . "\" title=\"" . $lang['YIM'] . "\" border=\"0\" /></a>" : "";
	}
	else
	{
		$profile_img = "";
		$pm_img = "";
		$email_img = "";
		$www_img = "";
		$icq_status_img = "";
		$icq_add_img = "";
		$aim_img = "";
		$msn_img = "";
		$yim_img = "";
	}

	$quote_img = "<a href=\"" . append_sid("posting.$phpEx?mode=quote&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_quote'] . "\" alt=\"" . $lang['Reply_with_quote'] ."\" title=\"" . $lang['Reply_with_quote'] ."\" border=\"0\" /></a>";

	$search_img = "<a href=\"" . append_sid("search.$phpEx?search_author=" . urlencode($poster)) . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" /></a>";

	if( ( $userdata['user_id'] == $poster_id && $is_auth['auth_edit'] ) || $is_auth['auth_mod'] )
	{
		$edit_img = "<a href=\"" . append_sid("posting.$phpEx?mode=editpost&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_edit'] . "\" alt=\"" . $lang['Edit_delete_post'] . "\" title=\"" . $lang['Edit_delete_post'] . "\" border=\"0\" /></a>";
	}
	else
	{
		$edit_img = "";
	}

	if( $is_auth['auth_mod'] )
	{
		$ip_img = "<a href=\"" . append_sid("modcp.$phpEx?mode=ip&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id'] . "&amp;" . POST_TOPIC_URL . "=" . $topic_id) . "\"><img src=\"" . $images['icon_ip'] . "\" alt=\"" . $lang['View_IP'] . "\" title=\"" . $lang['View_IP'] . "\" border=\"0\" /></a>";

		$delpost_img = "<a href=\"" . append_sid("posting.$phpEx?mode=delete&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_delpost'] . "\" alt=\"" . $lang['Delete_post'] . "\" title=\"" . $lang['Delete_post'] . "\" border=\"0\" /></a>";
	}
	else
	{
		$ip_img = "";

		if( $userdata['user_id'] == $poster_id && $is_auth['auth_delete'] && $i == $total_replies - 1 )
		{
			$delpost_img = "<a href=\"" . append_sid("posting.$phpEx?mode=delete&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_delpost'] . "\" alt=\"" . $lang['Delete_post'] . "\" title=\"" . $lang['Delete_post'] . "\" border=\"0\" /></a>";
		}
		else
		{
			$delpost_img = "";
		}
	}

	$post_subject = ( $postrow[$i]['post_subject'] != "" ) ? $postrow[$i]['post_subject'] : "";

	$message = $postrow[$i]['post_text'];
	$bbcode_uid = $postrow[$i]['bbcode_uid'];

	$user_sig = $postrow[$i]['user_sig'];
	$user_sig_bbcode_uid = $postrow[$i]['user_sig_bbcode_uid'];

	//
	// Note! The order used for parsing the message _is_
	// important, moving things around could break any 
	// output
	//

	//
	// If the board has HTML off but the post has HTML
	// on then we process it, else leave it alone
	//
	if( !$board_config['allow_html'] )
	{
		if( $user_sig != "" && $postrow[$i]['enable_sig'] && $userdata['user_allowhtml'] )
		{
			$user_sig = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $user_sig);
		}

		if( $postrow[$i]['enable_html'] )
		{
			$message = preg_replace("#(<)([\/]?.*?)(>)#is", "&lt;\\2&gt;", $message);
		}
	}

	//
	// Parse signature for BBCode if reqd.
	//
	if( $user_sig != "" && $postrow[$i]['enable_sig'] && $user_sig_bbcode_uid != "" )
	{
		$user_sig = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($user_sig, $user_sig_bbcode_uid) : preg_replace("/\:[0-9a-z\:]+\]/si", "]", $user_sig);
	}

	//
	// Parse message for BBCode if reqd
	//
	if( $bbcode_uid != "" )
	{
		$message = ( $board_config['allow_bbcode'] ) ? bbencode_second_pass($message, $bbcode_uid) : preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);
	}

	//
	// Append signature
	//
	if( $postrow[$i]['enable_sig'] && $user_sig != "" )
	{
		$message .= "<br /><br />_________________<br />" . $user_sig;
	}

	$message = make_clickable($message);

	//
	// Highlight active words (primarily for search)
	//
	if( $highlight_active )
	{
		$message = preg_replace($highlight_match, $highlight_replace, $message);
	}

	//
	// Replace naughty words
	//
	if( count($orig_word) )
	{
		$post_subject = preg_replace($orig_word, $replacement_word, $post_subject);
		$message = preg_replace($orig_word, $replacement_word, $message);
	}

	//
	// Parse smilies
	//
	if( $board_config['allow_smilies'] && $postrow[$i]['enable_smilies'] )
	{
		$message = smilies_pass($message);
	}

	//
	// Replace newlines (we use this rather than nl2br because
	// till recently it wasn't XHTML compliant)
	//
	$message = str_replace("\n", "\n<br />", $message);

	//
	// Editing information
	//
	if( $postrow[$i]['post_edit_count'] )
	{
		$l_edit_time_total = ( $postrow[$i]['post_edit_count'] == 1 ) ? $lang['Edited_time_total'] : $lang['Edited_times_total'];
		
		$l_edited_by = "<br /><br />" . sprintf($l_edit_time_total, $poster, create_date($board_config['default_dateformat'], $postrow[$i]['post_edit_time'], $board_config['board_timezone']), $postrow[$i]['post_edit_count']);
	}
	else
	{
		$l_edited_by = "";
	}

	//
	// Again this will be handled by the templating
	// code at some point
	//
	$row_color = ( !($i % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
	$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

	$template->assign_block_vars("postrow", array(
		"ROW_COLOR" => "#" . $row_color,
		"ROW_CLASS" => $row_class,
		"MINI_POST_IMG" => $mini_post_img, 

		"POSTER_NAME" => $poster,
		"POSTER_RANK" => $poster_rank,
		"RANK_IMAGE" => $rank_image,
		"POSTER_JOINED" => $poster_joined,
		"POSTER_POSTS" => $poster_posts,
		"POSTER_FROM" => $poster_from,
		"POSTER_AVATAR" => $poster_avatar,

		"POST_DATE" => $post_date,
		"POST_SUBJECT" => $post_subject,
		"MESSAGE" => $message, 
		"EDITED_MESSAGE" => $l_edited_by, 

		"PROFILE_IMG" => $profile_img,
		"SEARCH_IMG" => $search_img,
		"PM_IMG" => $pm_img,
		"EMAIL_IMG" => $email_img,
		"WWW_IMG" => $www_img,
		"ICQ_STATUS_IMG" => $icq_status_img,
		"ICQ_ADD_IMG" => $icq_add_img, 
		"AIM_IMG" => $aim_img,
		"MSN_IMG" => $msn_img,
		"YIM_IMG" => $yim_img,

		"EDIT_IMG" => $edit_img,
		"QUOTE_IMG" => $quote_img,
		"PMSG_IMG" => $pmsg_img,
		"IP_IMG" => $ip_img, 
		"DELETE_IMG" => $delpost_img, 

		"U_MINI_POST" => $mini_post_url,

		"U_POST_ID" => $postrow[$i]['post_id'])
	);
}

//
// User authorisation levels output
//
$s_auth_can = ( ( $is_auth['auth_post'] ) ? $lang['Rules_post_can'] : $lang['Rules_post_cannot'] ) . "<br />";
$s_auth_can .= ( ( $is_auth['auth_reply'] ) ? $lang['Rules_reply_can'] : $lang['Rules_reply_cannot'] ) . "<br />";
$s_auth_can .= ( ( $is_auth['auth_edit'] ) ? $lang['Rules_edit_can'] : $lang['Rules_edit_cannot'] ) . "<br />";
$s_auth_can .= ( ( $is_auth['auth_delete'] ) ? $lang['Rules_delete_can'] : $lang['Rules_delete_cannot'] ) . "<br />";
$s_auth_can .= ( ( $is_auth['auth_vote'] ) ? $lang['Rules_vote_can'] : $lang['Rules_vote_cannot'] ) . "<br />";

if( $is_auth['auth_mod'] )
{
	$s_auth_can .= sprintf($lang['Rules_moderate'], "<a href=\"" . append_sid("modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">", "</a>");

	$topic_mod = "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=delete") . "\"><img src=\"" . $images['topic_mod_delete'] . "\" alt=\"" . $lang['Delete_topic'] . "\" title=\"" . $lang['Delete_topic'] . "\" border=\"0\" /></a>&nbsp;";

	$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move"). "\"><img src=\"" . $images['topic_mod_move'] . "\" alt=\"" . $lang['Move_topic'] . "\" title=\"" . $lang['Move_topic'] . "\" border=\"0\" /></a>&nbsp;";

	if($forum_row['topic_status'] == TOPIC_UNLOCKED)
	{
		$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=lock") . "\"><img src=\"" . $images['topic_mod_lock'] . "\" alt=\"" . $lang['Lock_topic'] . "\" title=\"" . $lang['Lock_topic'] . "\" border=\"0\" /></a>&nbsp;";
	}
	else
	{
		$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unlock") . "\"><img src=\"" . $images['topic_mod_unlock'] . "\" alt=\"" . $lang['Unlock_topic'] . "\" title=\"" . $lang['Unlock_topic'] . "\" border=\"0\" /></a>&nbsp;";
	}
	$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=split") . "\"><img src=\"" . $images['topic_mod_split'] . "\" alt=\"" . $lang['Split_topic'] . "\" title=\"" . $lang['Split_topic'] . "\" border=\"0\" /></a>&nbsp;";
}

//
// Topic watch information
//
if( $can_watch_topic )
{
	if( $is_watching_topic )
	{
		$s_watching_topic = "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;unwatch=topic&amp;start=$start") . "\">" . $lang['Stop_watching_topic'] . "</a>";
		$s_watching_topic_img = "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;unwatch=topic&amp;start=$start") . "\"><img src=\"" . $images['Topic_un_watch'] . "\" alt=\"" . $lang['Stop_watching_topic'] . "\" title=\"" . $lang['Stop_watching_topic'] . "\" border=\"0\"></a>";
	}
	else
	{
		$s_watching_topic = "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=topic&amp;start=$start") . "\">" . $lang['Start_watching_topic'] . "</a>";
		$s_watching_topic_img = "<a href=\"" . append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=topic&amp;start=$start") . "\"><img src=\"" . $images['Topic_watch'] . "\" alt=\"" . $lang['Start_watching_topic'] . "\" title=\"" . $lang['Start_watching_topic'] . "\" border=\"0\"></a>";
	}
}
else
{
	$s_watching_topic = "";
}

//
// If we've got a hightlight set pass it on to pagination, I get annoyed when I lose my highlight after the first page.
//
if(isset($HTTP_GET_VARS['highlight']))
{
	$pagination = generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order&amp;highlight=" . $HTTP_GET_VARS['highlight'], $total_replies, $board_config['posts_per_page'], $start);
}
else 
{
	$pagination = generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $total_replies, $board_config['posts_per_page'], $start);
}

$template->assign_vars(array(
	"PAGINATION" => $pagination,
	"PAGE_NUMBER" => sprintf($lang['Page_of'], ( floor( $start / $board_config['posts_per_page'] ) + 1 ), ceil( $total_replies / $board_config['posts_per_page'] )), 

	"L_LOCK_TOPIC" => $lang['Lock_topic'], 
	"L_UNLOCK_TOPIC" => $lang['Unlock_topic'], 
	"L_MOVE_TOPIC" => $lang['Move_topic'], 
	"L_SPLIT_TOPIC" => $lang['Split_topic'], 
	"L_DELETE_TOPIC" => $lang['Delete_topic'], 

	"S_AUTH_LIST" => $s_auth_can,
	"S_TOPIC_ADMIN" => $topic_mod,
	"S_WATCH_TOPIC" => $s_watching_topic,

	"L_GOTO_PAGE" => $lang['Goto_page'])
);

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>
