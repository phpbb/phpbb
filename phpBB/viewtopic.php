<?php
/***************************************************************************
 *				 						   viewtopic.php
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
include('extension.inc');
include('common.'.$phpEx);
include('includes/bbcode.'.$phpEx);

$page_title = "View Topic - $topic_title";
$pagetype = "viewtopic";

//
// Start initial var setup
//

if(!isset($HTTP_GET_VARS['topic']))  // For backward compatibility
{
	$topic_id = $HTTP_GET_VARS[POST_TOPIC_URL];
}
else
{
	$topic_id = $HTTP_GET_VARS['topic'];
}
if(isset($HTTP_GET_VARS[POST_POST_URL]))
{
	$post_id = $HTTP_GET_VARS[POST_POST_URL];
}
$start = (isset($HTTP_GET_VARS['start'])) ? $HTTP_GET_VARS['start'] : 0;

$is_moderator = 0;

if(!isset($topic_id) && !isset($post_id))
{
	message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist']);
}

//
// End initial var setup
//

// This is the single/double 'integrated'
// query to obtain the next/previous
// topic from just the current topic_id
//
// We will make this word, if it's the last thing I
// do ... and it quite possibly will be!
/*
if(isset($HTTP_GET_VARS['view']))
{
	if($HTTP_GET_VARS['view'] == 'newer')
	{
		$operator = ">";
	}
	else if($HTTP_GET_VARS['view'] == 'older')
	{
		$operator = "<";
	}

	switch($dbms)
	{
		case 'mysql':
			// And now the stupid MySQL case...I wish they would get around to implementing subselectes...
			$sub_query = "SELECT topic_time
				FROM ".TOPICS_TABLE."
				WHERE topic_id = $topic_id";
			if($sub_result = $db->sql_query($sub_query))
			{
				$resultset = $db->sql_fetchrowset($sub_result);
				$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies,
							f.forum_type, f.forum_name, f.forum_id, u.username, u.user_id
							FROM ".TOPICS_TABLE." t, ".FORUMS_TABLE." f, ".FORUM_MODS_TABLE." fm, ".USERS_TABLE." u
							WHERE t.topic_time ".$operator." ".$resultset[0]['topic_time']."
							AND f.forum_id = ".$HTTP_GET_VARS[POST_FORUM_URL]."
							AND f.forum_id = t.forum_id
							AND fm.forum_id = t.forum_id
							AND u.user_id = fm.user_id";
				$db->sql_freeresult($sub_result);
			}
			else
			{
				if(DEBUG)
				{
					$dberror = $db->sql_error();
					error_die(SQL_QUERY, "Couldn't obtain topic information.<br>Reason: ".$dberror['message']."<br>Query: $sql", __LINE__, __FILE__);
				}
				else
				{
					error_die(SQL_QUERY, "Couldn't obtain topic information.", __LINE__, __FILE__);
				}
			}
			break;
		default:
			$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies,
						f.forum_type, f.forum_name, f.forum_id, u.username, u.user_id
						FROM ".TOPICS_TABLE." t, ".FORUMS_TABLE." f, ".FORUM_MODS_TABLE." fm, ".USERS_TABLE." u
						WHERE t.topic_id in
						(select max(topic_id) from ".TOPICS_TABLE." WHERE topic_time ".$operator." (select topic_time as t_time from ".TOPICS_TABLE." where topic_id = $topic_id))
							AND f.forum_id = ".$HTTP_GET_VARS[POST_FORUM_URL]."
							AND f.forum_id = t.forum_id
							AND fm.forum_id = t.forum_id
							AND u.user_id = fm.user_id";
		break;
	}
}
//
// End.
//
else
{
*/

	//
	// This is perhaps a bodged(?) way of allowing a direct link to a post
	// it also allows calculation of which page the post should be on. This
	// query no longer grabs moderator info for this forum ... right now 
	// that's fine, but if needed it can be easily replaced/added
	//
	$join_sql_table = (!isset($post_id)) ? "" : "" . POSTS_TABLE . " p, " . POSTS_TABLE . " p2,";
	$join_sql = (!isset($post_id)) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
	$count_sql = (!isset($post_id)) ? "" : ", COUNT(p2.post_id) AS prev_posts";

	$order_sql = (!isset($post_id)) ? "" : "GROUP BY p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_votecreate, f.auth_vote, f.auth_attachments ORDER BY p.post_id ASC";

	$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_id, f.auth_view, f.auth_read, f.auth_post, f.auth_reply, f.auth_edit, f.auth_delete, f.auth_votecreate, f.auth_vote, f.auth_attachments" . $count_sql . "
		FROM $join_sql_table " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f
		WHERE $join_sql
			AND f.forum_id = t.forum_id
			$order_sql";

// This closes out the opening braces above
// Needed for the view/next query
//}

if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_MESSAGE, $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
}

if(!$total_rows = $db->sql_numrows($result))
{
	message_die(GENERAL_MESSAGE,  $lang['Topic_post_not_exist'], "", __LINE__, __FILE__, $sql);
}
$forum_row = $db->sql_fetchrowset($result);
$forum_name = stripslashes($forum_row[0]['forum_name']);
$forum_id = $forum_row[0]['forum_id'];
$topic_id = $forum_row[0]['topic_id'];
$total_replies = $forum_row[0]['topic_replies'] + 1;
$topic_title = $forum_row[0]['topic_title'];
$topic_time = $forum_row[0]['topic_time'];

if(!empty($post_id))
{
	$start = floor(($forum_row[0]['prev_posts'] - 1) / $board_config['posts_per_page']) * $board_config['posts_per_page'];
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
$is_auth = auth(AUTH_ALL, $forum_id, $userdata, $forum_row[0]);

if(!$is_auth['auth_view'] || !$is_auth['auth_view'])
{
	//
	// Ooopss, user is not authed
	// to read this forum ...
	//
	include('includes/page_header.'.$phpEx);

	$msg = "I am sorry but only " . $is_auth['auth_read_type'] . " can read this topic.";

	message_die(GENERAL_MESSAGE, $msg);
}
//
// End auth check
//

/*
//
// This code allows for individual topic
// read tracking, on small, low volume sites
// it'll probably work very well. However, for
// busy sites the use of a text field in the DB
// combined with the additional UPDATE's required
// in viewtopic may be unacceptable. So, by default
// this code is off, however you may want to play
// ...
//
// psoTFX
//
if($userdata['user_id'] != ANONYMOUS)
{
	$unread_topic_list = unserialize($userdata['user_topics_unvisited']);

	if(isset($unread_topic_list[$forum_id][$topic_id]))
	{
		unset($unread_topic_list[$forum_id][$topic_id]);

		$sql = "UPDATE " . USERS_TABLE . "
			SET user_topics_unvisited = '" . serialize($unread_topic_list) . "'
			WHERE user_id = " . $userdata['user_id'];
		if(!$s_topic_times = $db->sql_query($sql))
		{
			error_die(SQL_QUERY, "Could not update user topics list.", __LINE__, __FILE__);
		}
	}
}
*/

for($x = 0; $x < $total_rows; $x++)
{
	$moderators[] = array(
		"user_id" => $forum_row[$x]['user_id'],
		"username" => $forum_row[$x]['username']);

	if($userdata['user_id'] == $forum_row[$x]['user_id'])
	{
		$is_moderator = 1;
	}
}

//
// Get next and previous topic_id's
//
$sql_next_id = "SELECT topic_id
	FROM ".TOPICS_TABLE."
	WHERE topic_time > $topic_time
		AND forum_id = $forum_id
	ORDER BY topic_time ASC
	LIMIT 1";

$sql_prev_id = "SELECT topic_id
	FROM ".TOPICS_TABLE."
	WHERE topic_time < $topic_time
		AND forum_id = $forum_id
	ORDER BY topic_time DESC
	LIMIT 1";

$result_next = $db->sql_query($sql_next_id);
$result_prev = $db->sql_query($sql_prev_id);
$topic_next_row = $db->sql_fetchrow($result_next);
$topic_prev_row = $db->sql_fetchrow($result_prev);

//
// Go ahead and pull all data for this topic
//
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_avatar, p.post_time, p.post_id, p.bbcode_uid, pt.post_text, pt.post_subject, p.post_username
	FROM ".POSTS_TABLE." p, ".USERS_TABLE." u, ".POSTS_TEXT_TABLE." pt
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
	//
	// Again this should be considered temporary and
	// will appear in the templates file at some
	// point
	//
	message_die(GENERAL_ERROR, "There don't appear to be any posts for this topic.", "", __LINE__, __FILE__, $sql);
}
$sql = "SELECT *
	FROM ".RANKS_TABLE."
	ORDER BY rank_min";
if(!$ranks_result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain ranks information.", "", __LINE__, __FILE__, $sql);
}
$postrow = $db->sql_fetchrowset($result);
$ranksrow = $db->sql_fetchrowset($ranksresult);

//
// Dump out the page header and
// load viewtopic body template
//
include('includes/page_header.'.$phpEx);

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
$reply_topic_url = append_sid("posting.$phpEx?mode=reply&" . POST_TOPIC_URL . "=$topic_id&" . POST_FORUM_URL . "=$forum_id");
$view_forum_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=$forum_id");
$view_prev_topic_url = (!empty($topic_prev_row['topic_id'])) ? append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_prev_row['topic_id']) : "";
$view_next_topic_url = (!empty($topic_next_row['topic_id'])) ? append_sid("viewtopic.$phpEx?" . POST_TOPIC_URL . "=" . $topic_next_row['topic_id']) : "";

$template->assign_vars(array(
	"L_POSTED" => $l_posted,
	"U_POST_NEW_TOPIC" => $new_topic_url,
	"FORUM_NAME" => $forum_name,
	"TOPIC_TITLE" => $topic_title,

	"U_VIEW_FORUM" => $view_forum_url,
	"U_VIEW_OLDER_TOPIC" => $view_prev_topic_url,
	"U_VIEW_NEWER_TOPIC" => $view_next_topic_url,
	"U_POST_REPLY_TOPIC" => $reply_topic_url));

//
// Update the topic view counter
//
$sql = "UPDATE ".TOPICS_TABLE."
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
for($x = 0; $x < $total_posts; $x++)
{
	$poster = stripslashes($postrow[$x]['username']);
	$poster_id = $postrow[$x]['user_id'];

	$post_date = create_date($board_config['default_dateformat'], $postrow[$x]['post_time'], $board_config['default_timezone']);

	$poster_posts = ($postrow[$x]['user_id'] != ANONYMOUS) ? $lang['Posts'] . ": " . $postrow[$x]['user_posts'] : "";

	$poster_from = ($postrow[$x]['user_from'] && $postrow[$x]['user_id'] != ANONYMOUS) ? $lang['From'] . ": " .$postrow[$x]['user_from'] : "";

	$poster_joined = ($postrow[$x]['user_id'] != ANONYMOUS) ? $lang['Joined'] . ": " . create_date($board_config['default_dateformat'], $postrow[$x]['user_regdate'], $board_config['default_timezone']) : "";

	$poster_avatar = ($postrow[$x]['user_avatar'] != "" && $userdata['user_id'] != ANONYMOUS) ? "<img src=\"".$board_config['avatar_path']."/".$postrow[$x]['user_avatar']."\">" : "";

	if(empty($postrow[$x]['user_rank']) && $postrow[$x]['user_id'] != ANONYMOUS)
	{
		for($i = 0; $i < count($ranksrow); $i++)
		{
			if($poster_posts > $ranksrow[$i]['rank_min'] && $poster_posts < $ranksrow[$i]['rank_max'])
			{
				$poster_rank = $ranksrow[$i]['rank_title'];
				$rank_image = ($ranksrow[$i]['rank_image']) ? "<img src=\"".$ranksrow[$i]['rank_image']."\">" : "";
			}
		}
	}
	else 
	{
		if(!empty($postrow[$x]['user_rank']))
		{
			for($i = 0; $i < count($ranksrow); $i++)
			{
				if($postrow[$x]['user_rank'] == $ranksrow[$i]['rank_special'])
				{
					$poster_rank = $ranksrow[$i]['rank_title'];
					$rank_image = ($ranksrow[$i]['rank_image']) ? "<img src=\"".$ranksrow[$i]['rank_image']."\">" : "";
				}
			}
		}
	}

	// Handle anon users posting with usernames
	if($poster_id == ANONYMOUS && $postrow[$x]['post_username'] != '')
	{
		$poster = stripslashes($postrow[$x]['post_username']);
		$poster_rank = $lang['Guest'];
	}

	$profile_img = "<a href=\"".append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=$poster_id")."\"><img src=\"".$images['profile']."\" alt=\"$l_profileof $poster\" border=\"0\"></a>";

	$pm_img = "<a href=\"" . append_sid("privmsg.$phpEx?mode=post&" . POST_USERS_URL. "=$poster_id") . "\"><img src=\"". $images['privmsg'] . "\" alt=\"" . $lang['Private_messaging'] . "\" border=\"0\"></a>";

	$email_img = ($postrow[$x]['user_viewemail'] == 1) ? "<a href=\"mailto:".$postrow[$x]['user_email']."\"><img src=\"".$images['email']."\" alt=\"$l_email $poster\" border=\"0\"></a>" : "";

	$www_img = ($postrow[$x]['user_website']) ? "<a href=\"".$postrow[$x]['user_website']."\"><img src=\"".$images['www']."\" alt=\"$l_viewsite\" border=\"0\"></a>" : "";

	if($postrow[$x]['user_icq'])
	{
		$icq_status_img = "<a href=\"http://wwp.icq.com/".$postrow[$x]['user_icq']."#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=".$postrow[$x]['user_icq']."&img=5\" alt=\"$l_icqstatus\" border=\"0\"></a>";

		$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=".$postrow[$x]['user_icq']."\"><img src=\"".$images['icq']."\" alt=\"$l_icq\" border=\"0\"></a>";
	}
	else
	{
		$icq_status_img = "";

		$icq_add_img = "";
	}

	$aim_img = ($postrow[$x]['user_aim']) ? "<a href=\"aim:goim?screenname=".$postrow[$x]['user_aim']."&message=Hello+Are+you+there?\"><img src=\"".$images['aim']."\" border=\"0\"></a>" : "";

	$msn_img = ($postrow[$x]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=$poster_id\"><img src=\"".$images['msn']."\" border=\"0\"></a>" : "";

	$yim_img = ($postrow[$x]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=".$postrow[$x]['user_yim']."&.src=pg\"><img src=\"".$images['yim']."\" border=\"0\"></a>" : "";
	
	if($x == 0)
	{
		$edit_post_url = append_sid("posting.$phpEx?mode=editpost&".POST_POST_URL."=".$postrow[$x]['post_id']."&".POST_TOPIC_URL."=$topic_id&".POST_FORUM_URL."=$forum_id&is_first_post=1");
	}
	else
	{ 
		$edit_post_url = append_sid("posting.$phpEx?mode=editpost&".POST_POST_URL."=".$postrow[$x]['post_id']."&".POST_TOPIC_URL."=$topic_id&".POST_FORUM_URL."=$forum_id");
	}
	$edit_img = "<a href=\"".$edit_post_url."\"><img src=\"".$images['edit']."\" alt=\"$l_editdelete\" border=\"0\"></a>";

	$quote_img = "<a href=\"".append_sid("posting.$phpEx?mode=reply&quote=true&".POST_POST_URL."=".$postrow[$x]['post_id']."&".POST_TOPIC_URL."=$topic_id&".POST_FORUM_URL."=$forum_id")."\"><img src=\"".$images['quote']."\" alt=\"$l_replyquote\" border=\"0\"></a>";

	$pmsg_img = "<a href=\"".append_sid("privmsg.$phpEx?mode=send&" . POST_USERS_URL . "=" .$poster_id) . "\"><img src=\"".$images['pmsg']."\" alt=\"$l_sendpmsg\" border=\"0\"></a>";

	if($is_auth['auth_mod'])
	{
		$ip_img = "<a href=\"".append_sid("topicadmin.$phpEx?mode=viewip&".POST_USERS_URL."=".$poster_id)."\"><img src=\"".$images['ip']."\" alt=\"$l_viewip\" border=\"0\"></a>";

		$delpost_img = "<a href=\"".append_sid("topicadmin.$phpEx?mode=delpost&".POST_POST_URL."=".$postrow[$x]['post_id'])."\"><img src=\"".$images['delpost']."\" alt=\"$l_delete\" border=\"0\"></a>";
	}


	$post_subject = ($postrow[$x]['post_subject'] != "") ? stripslashes($postrow[$x]['post_subject']) : "Re: ".$topic_title;

	$bbcode_uid = $postrow[$x]['bbcode_uid'];

	$user_sig = stripslashes($postrow[$x]['user_sig']);
	$message = stripslashes($postrow[$x]['post_text']);

	if(!$board_config['allow_html'])
	{
		$user_sig = strip_tags($user_sig);
		$message = strip_tags($message);
	}
	if($board_config['allow_bbcode'])
	{
		// do bbcode stuff here
		$sig_uid = make_bbcode_uid();
		$user_sig = bbencode_first_pass($user_sig, $sig_uid);
		$user_sig = bbencode_second_pass($user_sig, $sig_uid);

		$message = bbencode_second_pass($message, $bbcode_uid);
	}

	$message = make_clickable($message);
	$message = str_replace("\n", "<br />", $message);

	//
	// Again this will be handled by the templating
	// code at some point
	//
	if(!($x % 2))
	{
		$color = "#".$theme['td_color1'];
	}
	else
	{
		$color = "#".$theme['td_color2'];
	}

	$message = eregi_replace("\[addsig]$", "<br /><br />_________________<br />" . nl2br($user_sig), $message);

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

		"U_POST_ID" => $postrow[$x]['post_id']));
}

$s_auth_can = "You " . (($is_auth['auth_read']) ? "<b>can</b>" : "<b>cannot</b>" ) . " read posts in this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_post']) ? "<b>can</b>" : "<b>cannot</b>") . " add new topics to this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_reply']) ? "<b>can</b>" : "<b>cannot</b>") . " reply to posts in this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_edit']) ? "<b>can</b>" : "<b>cannot</b>") . " edit your posts in this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_delete']) ? "<b>can</b>" : "<b>cannot</b>") . " delete your posts in this forum<br>";

if($is_auth['auth_mod'])
{
	$topic_mod = "<a href=\"topicadmin.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=delete\"><img src=\"images/topic_delete.gif\" border=\"0\"></a>&nbsp;&nbsp;";

	$topic_mod .= "<a href=\"topicadmin.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=move\"><img src=\"images/topic_move.gif\" border=\"0\"></a>&nbsp;&nbsp;";

	if($forum_row[0]['topic_status'] == UNLOCKED)
	{
		$topic_mod .= "<a href=\"topicadmin.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=lock\"><img src=\"images/topic_lock.gif\" border=\"0\"></a>&nbsp;&nbsp;";
	}
	else
	{
		$topic_mod .= "<a href=\"topicadmin.$phpEx?" . POST_TOPIC_URL . "=$topic_id&mode=unlock\"><img src=\"images/topic_unlock.gif\" border=\"0\"></a>&nbsp;&nbsp;";
	}
}

$pagination = generate_pagination("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id", $total_replies, $board_config['posts_per_page'], $start);

$template->assign_vars(array(
	"PAGINATION" => $pagination, 
	"ON_PAGE" => ( floor( $start / $board_config['posts_per_page'] ) + 1 ),
	"TOTAL_PAGES" => ceil( $total_replies / $board_config['posts_per_page'] ),

	"S_AUTH_LIST" => $s_auth_can,
	"S_TOPIC_ADMIN" => $topic_mod,

	"L_OF" => $lang['of'],
	"L_PAGE" => $lang['Page'],
	"L_GOTO_PAGE" => $lang['Goto_page'])
);

$template->pparse("body");

include('includes/page_tail.'.$phpEx);

?>