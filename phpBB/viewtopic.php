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
//
//
function smilies_pass($message)
{
	global $db, $smilies_url;
	static $smilies;

	if(empty($smilies))
	{
		$sql = "SELECT code, smile_url 
			FROM " . SMILIES_TABLE;
		if($result = $db->sql_query($sql))
		{
			$smilies = $db->sql_fetchrowset($result);
		}
	}

	for($i = 0; $i < count($smilies); $i++)
	{
		$message = preg_replace("'([\n\\ \\.])" . preg_quote($smilies[$i]['code']) . "'s", '\1<img src="' . $smilies_url . '/' . $smilies[$i]['smile_url'] . '" alt="' . $smilies[$i]['smile_url'] . '">', ' ' . $message);
	}
	return($message);
}
//
//
//



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

if(!isset($topic_id) && !isset($post_id))
{
	message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist']);
}

$start = (isset($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;
//
// End initial var setup
//

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
	if(empty($topic_id))
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

$order_sql = (!isset($post_id)) ? "" : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_sticky, f.auth_announce, f.auth_votecreate, f.auth_vote, f.auth_attachments ORDER BY p.post_id ASC";

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

$forum_name = stripslashes($forum_row['forum_name']);
$forum_id = $forum_row['forum_id'];

$topic_title = stripslashes($forum_row['topic_title']);
$topic_id = $forum_row['topic_id'];
$topic_time = $forum_row['topic_time'];
$total_replies = $forum_row['topic_replies'] + 1;

if(!empty($post_id))
{
	$start = floor(($forum_row['prev_posts'] - 1) / $board_config['posts_per_page']) * $board_config['posts_per_page'];
}

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//

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
// Go ahead and pull all data for this topic
//
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_avatar, p.post_time, p.post_id, p.post_username, p.bbcode_uid, p.post_edit_time, p.post_edit_count, pt.post_text, pt.post_subject
	FROM " . POSTS_TABLE . " p, " . USERS_TABLE . " u, " . POSTS_TEXT_TABLE . " pt
	WHERE p.topic_id = $topic_id
		AND p.poster_id = u.user_id
		AND p.post_id = pt.post_id
	ORDER BY p.post_time ASC
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
setcookie('phpbb2_' . $forum_id . '_' . $topic_id, time(), time()+6000, $cookiepath, $cookiedomain, $cookiesecure); 
$page_title = $lang['View_topic'] ." - $topic_title";
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "viewtopic_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"JUMPBOX_LIST" => $jumpbox,
    "SELECT_NAME" => POST_FORUM_URL)
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");

$template->assign_vars(array(
	"FORUM_ID" => $forum_id,
    "FORUM_NAME" => $forum_name,
    "TOPIC_ID" => $topic_id,
    "TOPIC_TITLE" => $topic_title,
	"POST_FORUM_URL" => POST_FORUM_URL,
	"USERS_BROWSING" => $users_browsing)
);
//
// End header
//

//
// Post, reply and other URL generation for
// templating vars
//
$new_topic_url = append_sid("posting.$phpEx?mode=newtopic&" . POST_FORUM_URL . "=$forum_id");
$reply_topic_url = append_sid("posting.$phpEx?mode=reply&" . POST_TOPIC_URL . "=$topic_id");

$view_forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id");

$view_prev_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&view=previous");
$view_next_topic_url = append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=$topic_id&view=next");

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

	$poster_from = ($postrow[$i]['user_from'] && $postrow[$i]['user_id'] != ANONYMOUS) ? $lang['From'] . ": " .$postrow[$i]['user_from'] : "";

	$poster_joined = ($postrow[$i]['user_id'] != ANONYMOUS) ? $lang['Joined'] . ": " . create_date($board_config['default_dateformat'], $postrow[$i]['user_regdate'], $board_config['default_timezone']) : "";

	if($postrow[$i]['user_avatar'] != "" && $poster_id != ANONYMOUS)
	{
		$poster_avatar = (strstr("http", $postrow[$i]['user_avatar']) && $board_config['allow_avatar_remote']) ? "<br /><img src=\"" . $postrow[$i]['user_avatar'] . "\"><br />" : "<br /><img src=\"" . $board_config['avatar_path'] . "/" . $postrow[$i]['user_avatar'] . "\"><br />";
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
				$poster_rank = $ranksrow[$j]['rank_title'];
				$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . $ranksrow[$j]['rank_image'] . "\"><br />" : "";
			}
		}
	}
	else 
	{
		for($j = 0; $j < count($ranksrow); $j++)
		{
			if($postrow[$i]['user_posts'] > $ranksrow[$j]['rank_min'] && $postrow[$i]['user_posts'] < $ranksrow[$j]['rank_max'] && !$ranksrow[$j]['rank_special'])
			{
				$poster_rank = $ranksrow[$j]['rank_title'];
				$rank_image = ($ranksrow[$j]['rank_image']) ? "<img src=\"" . $ranksrow[$j]['rank_image'] . "\"><br />" : "";
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
		$profile_img = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"" . $images['icon_profile'] . "\" alt=\"" . $lang['Read_profile'] . " $poster\" border=\"0\"></a>";

		$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&" . POST_USERS_URL . "=$poster_id") . "\"><img src=\"". $images['icon_pm'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\"></a>";

		$email_img = ($postrow[$i]['user_viewemail'] == 1) ? "<a href=\"mailto:" . $postrow[$i]['user_email'] . "\"><img src=\"" . $images['icon_email'] . "\" alt=\"" . $lang['Send_email'] . " $poster\" border=\"0\"></a>" : "";

		$www_img = ($postrow[$i]['user_website']) ? "<a href=\"" . $postrow[$i]['user_website'] . "\" target=\"_userwww\"><img src=\"" . $images['icon_www'] . "\" alt=\"" . $lang['Visit_website'] . "\" border=\"0\"></a>" : "";

		if($postrow[$i]['user_icq'])
		{
			$icq_status_img = "<a href=\"http://wwp.icq.com/" . $postrow[$i]['user_icq'] . "#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=" . $postrow[$i]['user_icq'] . "&img=5\" border=\"0\"></a>";

			$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=" . $postrow[$i]['user_icq'] . "\"><img src=\"" . $images['icon_icq'] . "\" alt=\"" . $lang['ICQ'] . "\" border=\"0\"></a>";
		}
		else
		{
			$icq_status_img = "";
			$icq_add_img = "";
		}

		$aim_img = ($postrow[$i]['user_aim']) ? "<a href=\"aim:goim?screenname=" . $postrow[$i]['user_aim'] . "&message=Hello+Are+you+there?\"><img src=\"" . $images['icon_aim'] . "\" border=\"0\" alt=\"" . $lang['AIM'] . "\"></a>" : "";

		$msn_img = ($postrow[$i]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=$poster_id\"><img src=\"" . $images['icon_msnm'] . "\" border=\"0\" alt=\"" . $lang['MSNM'] . "\"></a>" : "";

		$yim_img = ($postrow[$i]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=" . $postrow[$i]['user_yim'] . "&.src=pg\"><img src=\"" . $images['icon_yim'] . "\" border=\"0\" alt=\"" . $lang['YIM'] . "\"></a>" : "";
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

	$search_img = "<a href=\"" . append_sid("search.$phpEx?a=" . urlencode($poster) . "&f=all&b=0&d=DESC&c=100&dosearch=1") . "\"><img src=\"" . $images['icon_search'] . "\" border=\"0\"></a>";

	$edit_img = "<a href=\"" . append_sid("posting.$phpEx?mode=editpost&" . POST_POST_URL . "=" . $postrow[$i]['post_id'] . "&" . POST_TOPIC_URL . "=$topic_id") . "\"><img src=\"" . $images['icon_edit'] . "\" alt=\"" . $lang['Edit_delete_post'] . "\" border=\"0\"></a>";

	$quote_img = "<a href=\"" . append_sid("posting.$phpEx?mode=quote&" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_quote'] . "\" alt=\"" . $lang['Reply_with_quote'] ."\" border=\"0\"></a>";

	if( $is_auth['auth_mod'] )
	{
		$ip_img = "<a href=\"" . append_sid("modcp.$phpEx?mode=viewip&" . POST_POST_URL . "=" . $post_id) . "\"><img src=\"" . $images['icon_ip'] . "\" alt=\"" . $lang['View_IP'] . "\" border=\"0\"></a>";

		$delpost_img = "<a href=\"" . append_sid("topicadmin.$phpEx?mode=delpost&" . POST_POST_URL . "=" . $postrow[$i]['post_id']) . "\"><img src=\"" . $images['icon_delpost'] . "\" alt=\"" . $lang['Delete_post'] . "\" border=\"0\"></a>";
	}

	$post_subject = ($postrow[$i]['post_subject'] != "") ? stripslashes($postrow[$i]['post_subject']) : $topic_title;

	$bbcode_uid = $postrow[$i]['bbcode_uid'];

	$user_sig = stripslashes($postrow[$i]['user_sig']);
	$message = stripslashes($postrow[$i]['post_text']);

	if(!$board_config['allow_html'])
	{
		if($user_sig != "")
		{
			$user_sig = strip_tags($user_sig);
		}
		$message = strip_tags($message);
	}

	if($board_config['allow_bbcode'])
	{
		if($user_sig != "")
		{
			$sig_uid = make_bbcode_uid();
			$user_sig = bbencode_first_pass($user_sig, $sig_uid);
			$user_sig = bbencode_second_pass($user_sig, $sig_uid);
		}

		$message = bbencode_second_pass($message, $bbcode_uid);
	}

	$message = make_clickable($message);
	$message = str_replace("\n", "<br />", $message);

	if($user_sig != "")
	{
		$message = eregi_replace("\[addsig]$", "<br /><br />_________________<br />" . nl2br($user_sig), $message);
	}

	if($board_config['allow_smilies'])
	{
//		$message = smilies_pass($message);
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
	$color = (!($i % 2)) ? "#" . $theme['td_color1'] : "#" . $theme['td_color2'];

	$template->assign_block_vars("postrow", array(
		"POSTER_NAME" => $poster,
		"POSTER_RANK" => $poster_rank,
		"RANK_IMAGE" => $rank_image,
		"ROW_COLOR" => $color,
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

if( $is_auth['auth_mod'] )
{
	$s_auth_can .= $lang['You'] . " " . $lang['can'] . " <a href=\"" . append_sid("modcp.$phpEx?" . POST_FORUM_URL . "=$forum_id") . "\">" . $lang['moderate_forum'] . "</a><br />";

	$topic_mod = "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=delete&quick_op=1") . "\"><img src=\"" . $images['topic_mod_delete'] . "\" alt = \"" . $lang['Delete_topic'] . "\" border=\"0\"></a>&nbsp;";

	$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=move&quick_op=1"). "\"><img src=\"" . $images['topic_mod_move'] . "\" alt = \"" . $lang['Move_topic'] . "\" border=\"0\"></a>&nbsp;";

	if($forum_row['topic_status'] == TOPIC_UNLOCKED)
	{
		$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=lock&quick_op=1") . "\"><img src=\"" . $images['topic_mod_lock'] . "\" alt = \"" . $lang['Lock_topic'] . "\" border=\"0\"></a>&nbsp;";
	}
	else
	{
		$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=unlock&quick_op=1") . "\"><img src=\"" . $images['topic_mod_unlock'] . "\" alt = \"" . $lang['Unlock_topic'] . "\" border=\"0\"></a>&nbsp;";
	}
	$topic_mod .= "<a href=\"" . append_sid("modcp.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=split") . "\"><img src=\"" . $images['topic_mod_split'] . "\" alt = \"" . $lang['Split_topic'] . "\" border=\"0\"></a>&nbsp;";
}

$template->assign_vars(array(
	"PAGINATION" => generate_pagination("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id", $total_replies, $board_config['posts_per_page'], $start), 
	"ON_PAGE" => ( floor( $start / $board_config['posts_per_page'] ) + 1 ),
	"TOTAL_PAGES" => ceil( $total_replies / $board_config['posts_per_page'] ),

	"S_AUTH_LIST" => $s_auth_can,
	"S_TOPIC_ADMIN" => $topic_mod,

	"L_OF" => $lang['of'],
	"L_PAGE" => $lang['Page'],
	"L_GOTO_PAGE" => $lang['Goto_page'])
);

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>