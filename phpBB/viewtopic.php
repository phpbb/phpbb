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

//
// End initial var setup
//

if(!isset($topic_id) && !isset($post_id))
{
	error_die(GENERAL_ERROR, "You have reached this page in error, please go back and try again");
}

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
	// This is perhaps a bodged(?) way
	// of allowing a direct link to a post
	// it also allows calculation of which
	// page the post should be on
	//
	$join_sql_table = (!isset($post_id)) ? "" : "".POSTS_TABLE." p, ".POSTS_TABLE." p2,";
	$join_sql = (!isset($post_id)) ? "t.topic_id = $topic_id" : "p.post_id = $post_id AND t.topic_id = p.topic_id AND p2.topic_id = p.topic_id AND p2.post_id <= $post_id";
	$count_sql = (!isset($post_id)) ? "" : ", COUNT(p2.post_id) AS prev_posts";
	$order_sql = (!isset($post_id)) ? "" : "GROUP BY fa.forum_id, fa.auth_view, fa.auth_post, fa.auth_reply, fa.auth_edit, fa.auth_delete, fa.auth_vote, fa.auth_votecreate, fm.user_id, p.post_id, t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_id, u.username, u.user_id, fa.auth_read ORDER BY p.post_id ASC";

	$sql = "SELECT t.topic_id, t.topic_title, t.topic_status, t.topic_replies, t.topic_time, f.forum_name, f.forum_id, u.username, u.user_id, fa.*".$count_sql." 
		FROM $join_sql_table ".TOPICS_TABLE." t, ".FORUMS_TABLE." f, ".FORUM_MODS_TABLE." fm, ".USERS_TABLE." u, ".AUTH_FORUMS_TABLE." fa  
		WHERE $join_sql 
			AND f.forum_id = t.forum_id 
			AND fa.forum_id = f.forum_id 
			AND fm.forum_id = t.forum_id
			AND u.user_id = fm.user_id 
			$order_sql";

// This closes out the opening braces above
// Needed for the view/next query
//}

if(!$result = $db->sql_query($sql))
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

if(!$total_rows = $db->sql_numrows($result))
{
	//
	// This should be considered temporary since
	// it should be moved to the templating file
	// when if...else constructs become available
	//
/*	if(isset($HTTP_GET_VARS['view']))
	{
		error_die(GENERAL_ERROR, $l_nomoretopics);
	}
	else
	{ */
		if(DEBUG)
		{
			$error = $db->sql_error();
			error_die(GENERAL_ERROR, "The forum/topic you selected does not exist.<br>Reason: ".$error['message']."<br>Query: $sql", __LINE__, __FILE__);
		}
		else
		{
   			error_die(GENERAL_ERROR, "The forum you selected does not exist. Please go back and try again.");
		}
//	}
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
	$start = floor($forum_row[0]['prev_posts'] / $board_config['posts_per_page']) * $board_config['posts_per_page'];
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
$is_auth = auth(ALL,  $forum_id, $userdata, $forum_row[0]);

if(!$is_auth)
{
	//
	// Ooopss, user is not authed
	// to read this forum ...
	//
	include('includes/page_header.'.$phpEx);
	
	$msg = "I am sorry but you are not currently authorised to read this forum. You could try logging on and trying again. If you are logged on then this is a private forum for which you have not been granted access.";

	$template->set_filenames(array(
		"reg_header" => "error_body.tpl"
	));
	$template->assign_vars(array(
		"ERROR_MESSAGE" => $msg
	));
	$template->pparse("reg_header");

	include('includes/page_tail.'.$phpEx);
}
//
// End auth check
// 


for($x = 0; $x < $total_rows; $x++)
{
	$moderators[] = array("user_id" => $forum_row[$x]['user_id'],
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
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_avatar, p.post_time, p.post_id, p.bbcode_uid, pt.post_text, pt.post_subject
	FROM ".POSTS_TABLE." p, ".USERS_TABLE." u, ".POSTS_TEXT_TABLE." pt
	WHERE p.topic_id = $topic_id
		AND p.poster_id = u.user_id 
		AND p.post_id = pt.post_id
	ORDER BY p.post_time ASC
	LIMIT $start, ".$board_config['posts_per_page'];
if(!$result = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Couldn't obtain post/user information.", __LINE__, __FILE__);
}
if(!$total_posts = $db->sql_numrows($result))
{
	//
	// Again this should be considered temporary and
	// will appear in the templates file at some
	// point
	//
	error_die(GENERAL_ERROR, "There don't appear to be any posts for this topic.", __LINE__, __FILE__);
}
$sql = "SELECT *
	FROM ".RANKS_TABLE."
	ORDER BY rank_min";
if(!$ranks_result = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Couldn't obtain ranks information.", __LINE__, __FILE__);
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
$new_topic_url = append_sid("posting.".$phpEx."?mode=newtopic&".POST_FORUM_URL."=$forum_id");
$reply_topic_url = append_sid("posting.".$phpEx."?mode=reply&".POST_TOPIC_URL."=$topic_id&".POST_FORUM_URL."=$forum_id");
$view_forum_url = append_sid("viewforum.".$phpEx."?".POST_FORUM_URL."=$forum_id");
$view_prev_topic_url = (!empty($topic_prev_row['topic_id'])) ? append_sid("viewtopic.".$phpEx."?".POST_TOPIC_URL."=".$topic_prev_row['topic_id']) : "";
$view_next_topic_url = (!empty($topic_next_row['topic_id'])) ? append_sid("viewtopic.".$phpEx."?".POST_TOPIC_URL."=".$topic_next_row['topic_id']) : "";
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
// If we get here then the page is unlikely
// to fail generating ...
//
$sql = "UPDATE ".TOPICS_TABLE." 
	SET topic_views = topic_views + 1 
	WHERE topic_id = $topic_id";
if(!$update_result = $db->sql_query($sql))
{
	error_die(SQL_QUERY, "Couldn't update topic views.", __LINE__, __FILE__);
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
	$poster_posts = $postrow[$x]['user_posts'];
	$poster_from = ($postrow[$x]['user_from']) ? "$l_from: ".$postrow[$x]['user_from'] : "";
	$poster_joined = create_date($board_config['default_dateformat'], $postrow[$x]['user_regdate'], $board_config['default_timezone']);
	$poster_avatar = ($postrow[$x]['user_avatar'] != "") ? "<img src=\"".$board_config['avatar_path']."/".$postrow[$x]['user_avatar']."\">" : "";
	if($poster_id != ANONYMOUS && $poster_id != DELETED)
	{
		if(!$postrow[$x]['user_rank'])
		{
			for($i = 0; $i < count($ranksrow); $i++)
			{
				if($poster_posts > $ranksrow[$i]['rank_min'] && $poster_posts < $ranksrow[$i]['rank_max'])
				{
					$poster_rank = $ranksrow[$i]['rank_title'];
					$rank_image = ($ranksrow[$x]['rank_image']) ? "<img src=\"".$ranksrow[$x]['rank_image']."\">" : "";
				}
			}
		}
		else
		{
			for($i = 0; $i < count($ranksrow); $i++)
			{
				if($postrow[$x]['user_rank'] == $ranksrow[$i]['rank_special'])
				{
					$poster_rank = $ranksrow[$i]['rank_title'];
					$rank_image = ($ranksrow[$x]['rank_image']) ? "<img src=\"".$ranksrow[$x]['rank_image']."\">" : "";
				}
			}
		}
	}
	else
	{
		$poster_rank = "";
	}

	$profile_img = "<a href=\"".append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=$poster_id")."\"><img src=\"".$images['profile']."\" alt=\"$l_profileof $poster\" border=\"0\"></a>";
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
	$msn_img = ($postrow[$x]['user_msnm']) ? "<a href=\"profile.$phpEx?mode=viewprofile&user_id=$poster_id\"><img src=\"".$images['msn']."\" border=\"0\"></a>" : "";
	$yim_img = ($postrow[$x]['user_yim']) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=".$postrow[$x]['user_yim']."&.src=pg\"><img src=\"".$images['yim']."\" border=\"0\"></a>" : "";

	$edit_img = "<a href=\"".append_sid("posting.$phpEx?mode=editpost&".POST_POST_URL."=".$postrow[$x]['post_id']."&".POST_TOPIC_URL."=$topic_id&".POST_FORUM_URL."=$forum_id")."\"><img src=\"".$images['edit']."\" alt=\"$l_editdelete\" border=\"0\"></a>";
	$quote_img = "<a href=\"".append_sid("posting.$phpEx?mode=reply&quote=true&post_id=".$postrow[$x]['post_id']."&topic_id=$topic_id&forum_id=$forum_id")."\"><img src=\"".$images['quote']."\" alt=\"$l_replyquote\" border=\"0\"></a>";
	$pmsg_img = "<a href=\"".append_sid("priv_msgs.$phpEx?mode=send")."\"><img src=\"".$images['pmsg']."\" alt=\"$l_sendpmsg\" border=\"0\"></a>";

	if($is_moderator)
	{
		$ip_img = "<a href=\"".append_sid("topicadmin.$phpEx?mode=viewip&user_id=".$poster_id)."\"><img src=\"".$images['ip']."\" alt=\"$l_viewip\" border=\"0\"></a>";
		$delpost_img = "<a href=\"".append_sid("topicadmin.$phpEx?mode=delpost&".POST_POST_URL."=".$postrow[$x]['post_id'])."\"><img src=\"".$images['delpost']."\" alt=\"$l_delete\" border=\"0\"></a>";
	}

	$post_subject = ($postrow[$x]['post_subject'] != "") ? stripslashes($postrow[$x]['post_subject']) : "Re: ".$topic_title;
	$message = stripslashes($postrow[$x]['post_text']);
	$bbcode_uid = $postrow[$x]['bbcode_uid'];
	$user_sig = stripslashes($postrow[$x]['user_sig']);

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

if($total_replies > $board_config['posts_per_page'])
{
	$times = 0;
	for($x = 0; $x < $total_replies; $x += $board_config['posts_per_page'])
	{
		$times++;
	}
	$pages = $times . " $l_pages";
}
else
{
	$pages = "1 $l_page";
}

$s_auth_can = "";
$s_auth_can .= "You " . (($is_auth['auth_read']) ? "<b>can</b>" : "<b>cannot</b>" ) . " read posts in this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_post']) ? "<b>can</b>" : "<b>cannot</b>") . " add new topics to this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_reply']) ? "<b>can</b>" : "<b>cannot</b>") . " reply to posts in this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_edit']) ? "<b>can</b>" : "<b>cannot</b>") . " edit your posts in this forum<br>";
$s_auth_can .= "You " . (($is_auth['auth_delete']) ? "<b>can</b>" : "<b>cannot</b>") . " delete your posts in this forum<br>";
$s_auth_can .= ($is_auth['auth_mod']) ? "You are a moderator of this forum<br>" : "";
$s_auth_can .= ($is_auth['auth_admin']) ? "You are a board admin<br>" : "";

$template->assign_vars(array(
	"PAGINATION" => generate_pagination("viewtopic.$phpEx?".POST_TOPIC_URL."=$topic_id", $total_replies, $board_config['posts_per_page'], $start),
	"ON_PAGE" => (floor($start/$board_config['posts_per_page'])+1),
	"TOTAL_PAGES" => ceil(($total_replies)/$board_config['posts_per_page']),
		
	"S_AUTH_LIST" => $s_auth_can,

	"L_OF" => $lang['of'],
	"L_PAGE" => $lang['Page'],
	"L_GOTO_PAGE" => $lang['Goto_page'])
);

$template->pparse("body");

include('includes/page_tail.'.$phpEx);

?>