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
	$topic_id = $HTTP_GET_VARS[POST_TOPIC_URL];
}
if(isset($HTTP_GET_VARS[POST_POST_URL]))
{
	$post_id = $HTTP_GET_VARS[POST_POST_URL];
}

$start = (isset($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;
//
// End initial var setup
//

if(!isset($topic_id) && !isset($post_id))
{
	message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist']);
}

//
// Find topic id if user requested a newer
// or older topic
//
if( isset($HTTP_GET_VARS["view"]) && empty($HTTP_GET_VARS[POST_POST_URL]) )
{
	if($HTTP_GET_VARS["view"] == "next")
	{
		$sql_condition = ">";
		$sql_ordering = "ASC";
	}
	else if($HTTP_GET_VARS["view"] == "previous")
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

	list($topic_id) = $db->sql_fetchrow($result);
	if( empty($topic_id) )
	{
		if($HTTP_GET_VARS["view"] == "next")
		{
			message_die(GENERAL_MESSAGE, $lang['No_newer_topics']);
		}
		else
		{
			message_die(GENERAL_MESSAGE, $lang['No_older_topics']);
		}
	}
}

//
// This rather complex gaggle of code handles querying for topics but
// also allows for direct linking to a post (and the calculation of which
// page the post is on and the correct display of viewtopic)
//
$join_sql_table = (!isset($post_id)) ? "" : "" . POSTS_TABLE . " p, " . POSTS_TABLE . " p2,";
$join_sql = (!isset($post_id)) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
$count_sql = (!isset($post_id)) ? "" : ", COUNT(p2.post_id) AS prev_posts";

$order_sql = (!isset($post_id)) ? "" : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_status, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_votecreate, f.auth_vote, f.auth_attachments ORDER BY p.post_id ASC";

$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_status, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_votecreate, f.auth_vote, f.auth_attachments" . $count_sql . "
	FROM $join_sql_table " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
	WHERE $join_sql
		AND f.forum_id = t.forum_id
		$order_sql";

if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain topic information", "", __LINE__, __FILE__, $sql);
}

if(!$total_rows = $db->sql_numrows($result))
{
	message_die(GENERAL_MESSAGE,  $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
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

$forum_name = stripslashes($forum_row['forum_name']);
$topic_title = stripslashes($forum_row['topic_title']);
$topic_id = $forum_row['topic_id'];
$topic_time = $forum_row['topic_time'];

if(!empty($post_id))
{
	$start = floor(($forum_row['prev_posts'] - 1) / $board_config['posts_per_page']) * $board_config['posts_per_page'];
}

//
// Start auth check
//
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_row);

if(!$is_auth['auth_view'] || !$is_auth['auth_read'])
{
	//
	// The user is not authed to read this forum ...
	//
	$msg = $lang['Sorry_auth'] . $is_auth['auth_read_type'] . $lang['can_read'] . $lang['this_forum'];

	message_die(GENERAL_MESSAGE, $msg);
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

if($userdata['user_id'] != ANONYMOUS)
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
		if( isset($HTTP_GET_VARS['watch']) )
		{
			if( !$HTTP_GET_VARS['watch'] )
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
			if( $HTTP_GET_VARS['watch'] )
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
		}
		else
		{
			$is_watching_topic = 0;
		}
	}
}
else
{
	$can_watch_topic = 0;
	$is_watching_topic = 0;
}

//
// Generate a 'Show posts in previous x days' select box. If the postdays var is POSTed
// then get it's value, find the number of topics with dates newer than it (to properly
// handle pagination) and alter the main query
//
$previous_days = array(0, 1, 7, 14, 30, 90, 180, 364);
$previous_days_text = array($lang['All_Posts'], "1 " . $lang['Day'], "7 " . $lang['Days'], "2 " . $lang['Weeks'], "1 " . $lang['Month'], "3 ". $lang['Months'], "6 " . $lang['Months'], "1 " . $lang['Year']);

if(!empty($HTTP_POST_VARS['postdays']) || !empty($HTTP_GET_VARS['postdays']))
{
	$post_days = (!empty($HTTP_POST_VARS['postdays'])) ? $HTTP_POST_VARS['postdays'] : $HTTP_GET_VARS['postdays'];
	$min_post_time = time() - ($post_days * 86400);

	$sql = "SELECT COUNT(post_id) AS num_posts
		FROM " . POSTS_TABLE . "
		WHERE topic_id = $topic_id
			AND post_time >= $min_post_time";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain limited topics count information", "", __LINE__, __FILE__, $sql);
	}
	list($total_replies) = $db->sql_fetchrow($result);

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
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_avatar, p.post_time, p.post_id, p.post_username, p.bbcode_uid, p.post_edit_time, p.post_edit_count, p.enable_bbcode, p.enable_html, p.enable_smilies, pt.post_text, pt.post_subject
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
	message_die(GENERAL_ERROR, "There don't appear to be any posts for this topic.", "", __LINE__, __FILE__, $sql);
}

$sql = "SELECT *
	FROM " . RANKS_TABLE . "
	ORDER BY rank_special, rank_min";
if(!$ranks_result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain ranks information.", "", __LINE__, __FILE__, $sql);
}
$postrow = $db->sql_fetchrowset($result);
$ranksrow = $db->sql_fetchrowset($ranksresult);

//
// Dump out the page header and load viewtopic body template
//
setcookie('phpbb2_' . $forum_id . '_' . $topic_id, time(), 0, $board_config['cookie_path'], $board_config['cookie_domain'], $board_config['cookie_secure']);
$page_title = $lang['View_topic'] ." - $topic_title";
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "viewtopic_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],
	"JUMPBOX_LIST" => $jumpbox,
    "SELECT_NAME" => POST_FORUM_URL)
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"FORUM_ID" => $forum_id,
    "FORUM_NAME" => $forum_name,
    "TOPIC_ID" => $topic_id,
    "TOPIC_TITLE" => $topic_title,

	"L_DISPLAY_POSTS" => $lang['Display_posts'],

	"S_SELECT_POST_DAYS" => $select_post_days,
	"S_SELECT_POST_ORDER" => $select_post_order,
	"S_POST_DAYS_ACTION" => append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_id . "&amp;start=$start"))

);
//
// End header
//

//
// Post, reply and other URL generation for
// templating vars
//
$new_topic_url = append_sid("posting.$phpEx?mode=newtopic&amp;" . POST_FORUM_URL . "=$forum_id");
$reply_topic_url = append_sid("posting.$phpEx?mode=reply&amp;" . POST_TOPIC_URL . "=$topic_id");

$view_forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id");

$view_prev_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=previous");
$view_next_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;view=next");

$reply_img = ($forum_row['forum_status'] == FORUM_LOCKED || $forum_row['topic_status'] == TOPIC_LOCKED) ? $images['reply_locked'] : $images['reply_new'];
$post_img = ($forum_row['forum_status'] == FORUM_LOCKED) ? $images['post_locked'] : $images['post_new'];

$template->assign_vars(array(
	"FORUM_NAME" => $forum_name,
	"TOPIC_TITLE" => $topic_title,

	"L_POSTED" => $lang['Posted'],
	"L_POST_SUBJECT" => $lang['Post_subject'],
	"L_VIEW_NEXT_TOPIC" => $lang['View_next_topic'],
	"L_VIEW_PREVIOUS_TOPIC" => $lang['View_previous_topic'],

	"IMG_POST" => $post_img,
	"IMG_REPLY" => $reply_img,

	"U_VIEW_FORUM" => $view_forum_url,
	"U_VIEW_OLDER_TOPIC" => $view_prev_topic_url,
	"U_VIEW_NEWER_TOPIC" => $view_next_topic_url,
	"U_POST_NEW_TOPIC" => $new_topic_url,
	"U_POST_REPLY_TOPIC" => $reply_topic_url)
);

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
	$poster = stripslashes($postrow[$i]['username']);

	$post_date = create_date($board_config['default_dateformat'], $postrow[$i]['post_time'], $board_config['default_timezone']);

	$poster_posts = ($postrow[$i]['user_id'] != ANONYMOUS) ? $lang['Posts'] . ": " . $postrow[$i]['user_posts'] : "";

	$poster_from = ($postrow[$i]['user_from'] && $postrow[$i]['user_id'] != ANONYMOUS) ? $lang['From'] . ": " . stripslashes($postrow[$i]['user_from']) : "";

	$poster_joined = ($postrow[$i]['user_id'] != ANONYMOUS) ? $lang['Joined'] . ": " . create_date($board_config['default_dateformat'], $postrow[$i]['user_regdate'], $board_config['default_timezone']) : "";

	if($postrow[$i]['user_avatar'] != "" && $poster_id != ANONYMOUS)
	{
		$poster_avatar = (strstr("http", $postrow[$i]['user_avatar']) && $board_config['allow_avatar_remote']) ? "<br /><img src=\"" . stripslashes($postrow[$i]['user_avatar']) . "\"><br />" : "<br /><img src=\"" . $board_config['avatar_path'] . "/" . stripslashes($postrow[$i]['user_avatar']) . "\" alt=\"\" /><br />";
	}
	else
	{
		$poster_avatar = "";
	}

	//
	// Generate ranks
	//
	if( $postrow[$i]['user_id'] == ANONYMOUS )
	{
		$poster_rank = "";
		$rank_image = "";
	}
	else if( $postrow[$i]['user_rank'] )
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if($postrow[$i]['user_rank'] == $ranksrow[$j]['rank_id'] && $ranksrow[$j]['rank_special'])
			{
				$poster_rank = stripslashes($ranksrow[$j]['rank_title']);
				$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . stripslashes($ranksrow[$j]['rank_image']) . "\"><br />" : "";
			}
		}
	}
	else
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if($postrow[$i]['user_posts'] > $ranksrow[$j]['rank_min'] && $postrow[$i]['user_posts'] < $ranksrow[$j]['rank_max'] && !$ranksrow[$j]['rank_special'])
			{
				$poster_rank = stripslashes($ranksrow[$j]['rank_title']);
				$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . stripslashes($ranksrow[$j]['rank_image']) . "\"><br />" : "";
			}
		}
	}

	//
	// Handle anon users posting with usernames
	//
	if($poster_id == ANONYMOUS && $postrow[$i]['post_username'] != '')
	{
		$poster = stripslashes($postrow[$i]['post_username']);
		$poster_rank = $lang['Guest'];
	}

	if($poster_id != ANONYMOUS)
	{
		$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . " $poster\" border=\"0\" /></a>";

		$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&amp;" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\" /></a>";

		$email_img = ($postrow[$i]['user_viewemail'] == 1) ? "<a href=\"mailto:" . stripslashes($postrow[$i]['user_email']) . "\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . " $poster\" border=\"0\" /></a>" : "";

		$www_img = ($postrow[$i]['user_website']) ? "<a href=\"" . stripslashes($postrow[$i]['user_website']) . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\" /></a>" : "";

		if($postrow[$i]['user_icq'])
		{
			$icq_status_img = "<a href=\"http://wwp.icq.com/" . stripslashes($postrow[$i]['user_icq']) . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $postrow[$i]['user_icq'] . "&amp;img=5\" border=\"0\" /></a>";

			$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . stripslashes($postrow[$i]['user_icq']) . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\" /></a>";
		}
		else
		{
			$icq_status_img = "";
			$icq_add_img = "";
		}

		$aim_img = ($postrow[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . stripslashes($postrow[$i]['user_aim']) . "&amp;message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\" /></a>" : "";

		$msn_img = ($postrow[$i]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\" /></a>" : "";

		$yim_img = ($postrow[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . stripslashes($postrow[$i]['user_yim']) . "&amp;.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\" /></a>" : "";
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

	$search_img = "<a href=\"" . append_sid("search.$phpEx?a=" . urlencode($poster) . "&amp;f=all&amp;b=0&amp;d=DESC&amp;c=100&amp;dosearch=1") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\" /></a>";

	$edit_img = "<a href=\"" . append_sid("posting.$phpEx?mode=editpost&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id'] . "&amp;" . POST_TOPIC_URL . "=$topic_id") . "\"><img src=\"" . $images['icon_edit'] . "\" alt=\"" . $lang['Edit_delete_post'] . "\" border=\"0\" /></a>";

	$quote_img = "<a href=\"" . append_sid("posting.$phpEx?mode=quote&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_quote'] . "\" alt=\"" . $lang['Reply_with_quote'] ."\" border=\"0\" /></a>";

	if( $is_auth['auth_mod'] )
	{
		$ip_img = "<a href=\"" . append_sid("modcp.$phpEx?mode=viewip&amp;" . POST_POST_URL . "=" . $post_id) . "\"><img src=\"" . $images['icon_ip'] . "\" alt=\"" . $lang['View_IP'] . "\" border=\"0\" /></a>";

		$delpost_img = "<a href=\"" . append_sid("topicadmin.$phpEx?mode=delpost&amp;" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_delpost'] . "\" alt=\"" . $lang['Delete_post'] . "\" border=\"0\" /></a>";
	}

	$post_subject = ($postrow[$i]['post_subject'] != "") ? stripslashes($postrow[$i]['post_subject']) : $topic_title;

	$bbcode_uid = $postrow[$i]['bbcode_uid'];

	$user_sig = stripslashes($postrow[$i]['user_sig']);
	$message = stripslashes($postrow[$i]['post_text']);

	if(!$board_config['allow_html'] || !$postrow[$i]['enable_html'])
	{
		if($user_sig != "")
		{
			$user_sig = htmlspecialchars($user_sig);
		}
		$message = htmlspecialchars($message);
	}

	if($board_config['allow_bbcode'] && $bbcode_uid != "")
	{
		if($user_sig != "")
		{
			$sig_uid = make_bbcode_uid();
			$user_sig = bbencode_first_pass($user_sig, $sig_uid);
			$user_sig = bbencode_second_pass($user_sig, $sig_uid);
		}

		$message = bbencode_second_pass($message, $bbcode_uid);

		//
		// This compensates for bbcode's rather agressive (but I guess necessary) 
		// HTML handling
		//
		if(!$postrow[$i]['enable_html'] || ($postrow[$i]['enable_html'] && !$board_config['allow_html']) )
		{
			$message = preg_replace("'&amp;'", "&", $message);
		}
	}
	else
	{
		// Removes UID from BBCode entries
		$message = preg_replace("/\:[0-9a-z\:]+\]/si", "]", $message);
	}

	$message = make_clickable($message);

	$message = ($user_sig != "") ? ereg_replace("\[addsig]$", "<br /><br />_________________<br />" . $user_sig, $message) : ereg_replace("\[addsig]$", "", $message);
	
	$message = str_replace("\n", "<br />", $message);

	if($board_config['allow_smilies'] && $postrow[$i]['enable_smilies'])
	{
		$message = smilies_pass($message);
	}

	//
	// Editing information
	//
	if($postrow[$i]['post_edit_count'])
	{
		$l_edit_total = ($postrow[$i]['post_edit_count'] == 1) ? $lang['time_in_total'] : $lang['times_in_total'];

		$message = $message . "<br /><br /><font size=\"-2\">" . $lang['Edited_by'] . " " . $poster . " " . $lang['on'] . " " . create_date($board_config['default_dateformat'], $postrow[$i]['post_edit_time'], $board_config['default_timezone']) . ", " . $lang['edited'] . " " . $postrow[$i]['post_edit_count'] . " $l_edit_total</font>";
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
		"DELPOST_IMG" => $delpost_img,

		"U_POST_ID" => $postrow[$i]['post_id'])
	);
}

//
// User authorisation levels output
//
$s_auth_can = $lang['You'] . " " . ( ($is_auth['auth_read']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['read_posts'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_post']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['post_topics'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_reply']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['reply_posts'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_edit']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['edit_posts'] . "<br />";
$s_auth_can .= $lang['You'] . " " . ( ($is_auth['auth_delete']) ? $lang['can'] : $lang['cannot'] ) . " " . $lang['delete_posts'] . "<br />";
/*
$s_auth_read_img = "<img src=\"" . ( ($is_auth['auth_read']) ? $image['auth_can_read'] : $image['auth_cannot_read'] ) . "\" alt=\"" . $lang['You'] . " " . ( ($is_auth['auth_read']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['read_posts'] . "\" />";
$s_auth_post_img = "<img src=\"" . ( ($is_auth['auth_post']) ? $image['auth_can_post'] : $image['auth_cannot_post'] ) . "\" alt=\"" . $lang['You'] . " " . ( ($is_auth['auth_post']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['post_topics'] . "\" />";
$s_auth_reply_img = "<img src=\"" . ( ($is_auth['auth_reply']) ? $image['auth_can_reply'] : $image['auth_cannot_reply'] ) . "\" alt=\"" . $lang['You'] . " " . ( ($is_auth['auth_reply']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['reply_posts'] . "\" />";
$s_auth_edit_img = "<img src=\"" . ( ($is_auth['auth_edit']) ? $image['auth_can_edit'] : $image['auth_cannot_edit'] ) . "\" alt=\"" . $lang['You'] . " " . ( ($is_auth['auth_edit']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['edit_posts'] . "\" />";
$s_auth_delete_img = "<img src=\"" . ( ($is_auth['auth_delete']) ? $image['auth_can_delete'] : $image['auth_cannot_delete'] ) . "\" alt=\"" . $lang['You'] . " " . ( ($is_auth['auth_delete']) ? $lang['can']  : $lang['cannot'] ) . " " . $lang['delete_posts'] . "\" />";
*/

if( $is_auth['auth_mod'] )
{
	$s_auth_can .= $lang['You'] . " " . $lang['can'] . " <a href=\"" . append_sid("modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['moderate_forum'] . "</a><br />";

//	$s_auth_mod_img = "<a href=\"" . append_sid("modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\"><img src=\"" . $images['auth_mod'] . "\" alt=\"" . $lang['You'] . " " . $lang['can'] . " " . $lang['moderate_forum'] . "\" border=\"0\"/></a>";

	$topic_mod = "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=delete&amp;quick_op=1") . "\"><img src=\"" . $images['topic_mod_delete'] . "\" alt = \"" . $lang['Delete_topic'] . "\" border=\"0\" /></a>&nbsp;";

	$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=move&amp;quick_op=1"). "\"><img src=\"" . $images['topic_mod_move'] . "\" alt = \"" . $lang['Move_topic'] . "\" border=\"0\" /></a>&nbsp;";

	if($forum_row['topic_status'] == TOPIC_UNLOCKED)
	{
		$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=lock&amp;quick_op=1") . "\"><img src=\"" . $images['topic_mod_lock'] . "\" alt = \"" . $lang['Lock_topic'] . "\" border=\"0\" /></a>&nbsp;";
	}
	else
	{
		$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=unlock&amp;quick_op=1") . "\"><img src=\"" . $images['topic_mod_unlock'] . "\" alt = \"" . $lang['Unlock_topic'] . "\" border=\"0\" /></a>&nbsp;";
	}
	$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;mode=split") . "\"><img src=\"" . $images['topic_mod_split'] . "\" alt = \"" . $lang['Split_topic'] . "\" border=\"0\" /></a>&nbsp;";
}

//
// Topic watch information
//
if($can_watch_topic)
{
	if($is_watching_topic)
	{
		$s_watching_topic = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=0\">" . $lang['Stop_watching_topic'] . "</a>";
		$s_watching_topic_img = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=0\"><img src=\"" . $images['Topic_un_watch'] . "\" alt=\"" . $lang['Stop_watching_topic'] . "\" border=\"0\"></a>";
	}
	else
	{
		$s_watching_topic = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=1\">" . $lang['Start_watching_topic'] . "</a>";
		$s_watching_topic_img = "<a href=\"viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;watch=1\"><img src=\"" . $images['Topic_watch'] . "\" alt=\"" . $lang['Start_watching_topic'] . "\" border=\"0\"></a>";
	}
}
else
{
	$s_watching_topic = "";
}

$template->assign_vars(array(
	"PAGINATION" => generate_pagination("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&amp;postdays=$post_days&amp;postorder=$post_order", $total_replies, $board_config['posts_per_page'], $start),
	"ON_PAGE" => ( floor( $start / $board_config['posts_per_page'] ) + 1 ),
	"TOTAL_PAGES" => ceil( $total_replies / $board_config['posts_per_page'] ),

	"S_AUTH_LIST" => $s_auth_can, 
	"S_AUTH_READ_IMG" => $s_auth_read_img, 
	"S_AUTH_POST_IMG" => $s_auth_post_img, 
	"S_AUTH_REPLY_IMG" => $s_auth_reply_img, 
	"S_AUTH_EDIT_IMG" => $s_auth_edit_img, 
	"S_AUTH_MOD_IMG" => $s_auth_mod_img,
	"S_TOPIC_ADMIN" => $topic_mod, 
	"S_WATCH_TOPIC" => $s_watching_topic, 
	"S_WATCH_TOPIC_IMG" => $s_watching_topic_img, 

	"L_OF" => $lang['of'],
	"L_PAGE" => $lang['Page'],
	"L_GOTO_PAGE" => $lang['Goto_page'])
);

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>