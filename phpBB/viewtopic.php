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

if(!isset($HTTP_GET_VARS['topic']))  // For backward compatibility
{
	$topic_id = $HTTP_GET_VARS[POST_TOPIC_URL];
}
else
{
	$topic_id = $HTTP_GET_VARS['topic'];
}

$is_moderator = 0;

if(!isset($topic_id))
{
   error_die(GENERAL_ERROR, "You have reached this page in error, please go back and try again");
}

$sql = "SELECT t.topic_title, t.topic_status, t.topic_replies,
		f.forum_type, f.forum_name, f.forum_id, u.username, u.user_id
	FROM ".TOPICS_TABLE." t, ".FORUMS_TABLE." f, ".FORUM_MODS_TABLE." fm, ".USERS_TABLE." u
	WHERE t.topic_id = '$topic_id'
		AND f.forum_id = t.forum_id
		AND fm.forum_id = t.forum_id
		AND u.user_id = fm.user_id";

if(!$result = $db->sql_query($sql))
{
   error_die(SQL_QUERY, "Couldn't obtain topic information.", __LINE__, __FILE__);
}
if(!$total_rows = $db->sql_numrows($result))
{
   error_die(GENERAL_ERROR, "The forum you selected does not exist. Please go back and try again.");
}
$forum_row = $db->sql_fetchrowset($result);
$topic_title = $forum_row[0]["topic_title"];
$forum_id = $forum_row[0]["forum_id"];
$forum_name = stripslashes($forum_row[0]["forum_name"]);

//
// Start session management
//
$userdata = session_pagestart($user_ip, $forum_id, $session_length);
init_userprefs($userdata);
//
// End session management
//

for($x = 0; $x < $total_rows; $x++)
{
	$moderators[] = array("user_id" => $forum_row[$x]["user_id"],
		"username" => $forum_row[$x]["username"]);
	if($userdata["user_id"] == $forum_row[$x]["user_id"])
	{
		$is_moderator = 1;
	}
}

//
// Add checking for private forums here
//



$total_replies = $forum_row[0]["topic_replies"] + 1;

if(!isset($start))
{
   $start = 0;
}

$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, r.rank_title, r.rank_image, p.post_time, p.post_id, p.bbcode_uid, pt.post_text
	FROM ".POSTS_TABLE." p
	LEFT JOIN ".USERS_TABLE." u ON p.poster_id = u.user_id
	LEFT JOIN ".POSTS_TEXT_TABLE." pt ON p.post_id = pt.post_id
	LEFT JOIN ".RANKS_TABLE." r ON ( u.user_rank = r.rank_id )
		AND (r.rank_special = 1)
	WHERE p.topic_id = '$topic_id'
	ORDER BY p.post_time ASC
	LIMIT $start, ".$board_config['posts_per_page'];
if(!$result = $db->sql_query($sql))
{
   error_die(SQL_QUERY, "Couldn't obtain post/user information.", __LINE__, __FILE__);
}
if(!$total_posts = $db->sql_numrows($result))
{
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
// Post, reply and other URL generation for 
// templating vars
//
$new_topic_url = append_sid("posting.".$phpEx."?mode=newtopic&".POST_FORUM_URL."=$forum_id");
$reply_topic_url = append_sid("posting.".$phpEx."?mode=reply&".POST_TOPIC_URL."=$topic_id");
$view_forum_url = append_sid("viewforum.".$phpEx."?".POST_FORUM_URL."=$forum_id");
$view_older_topic_url = append_sid("viewtopic.".$phpEx."?".POST_TOPIC_URL."=".$topic_id."&view=older");
$view_newer_topic_url = append_sid("viewtopic.".$phpEx."?".POST_TOPIC_URL."=".$topic_id."&view=newer");
$template->assign_vars(array(
	"U_POST_NEW_TOPIC" => $new_topic_url,
	"U_VIEW_FORUM" => $view_forum_url,
	"U_VIEW_OLDER_TOPIC" => $view_older_topic_url,
	"U_VIEW_NEWER_TOPIC" => $view_newer_topic_url,
	"U_POST_REPLY_TOPIC" => $reply_topic_url));

//
// Dump out the page header
//
include('includes/page_header.'.$phpEx);

//
// Okay, let's do the loop, yeah come on baby let's do the loop
// and it goes like this ...
//
for($x = 0; $x < $total_posts; $x++)
{
	$poster = stripslashes($postrow[$x]["username"]);
	$poster_id = $postrow[$x]["user_id"];
	$post_date = create_date($board_config['default_dateformat'], $postrow[$x]["post_time"], $board_config['default_timezone']);
	$poster_posts = $postrow[$x]["user_posts"];
	$poster_from = ($postrow[$x]["user_from"]) ? "$l_from: ".$postrow[$x]["user_from"] : "";
	$poster_joined = create_date($board_config['default_dateformat'], $postrow[$x]["user_regdate"], $board_config['default_timezone']);
	if($poster_id != ANONYMOUS && $poster_id != DELETED)
	{
		if(!$postrow[$x]["rank_title"])
		{
			for($i = 0; $i < count($ranksrow); $i++)
			{
				if($poster_posts > $ranksrow[$i]['rank_min'] && $poster_posts < $ranksrow[$i]['rank_max'])
				{
					$poster_rank = $ranksrow[$i]['rank_title'];
					$rank_image = ($ranksrow[$x]["rank_image"]) ? "<img src=\"".$ranksrow[$x]["rank_image"]."\">" : "";
				}
			}
		}
		else
		{
			$poster_rank = stripslashes($postrow[$x]["rank_title"]);
			$rank_image = ($postrow[$x]["rank_image"]) ? "<img src=\"".$postrow[$x]["rank_image"]."\">" : "";
		}
	}
	else
	{
		$poster_rank = "";
	}

	$profile_img = "<a href=\"".append_sid("profile.$phpEx?mode=viewprofile&".POST_USERS_URL."=$poster_id")."\"><img src=\"".$images['profile']."\" alt=\"$l_profileof $poster\" border=\"0\"></a>";
	$email_img = ($postrow[$x]["user_viewemail"] == 1) ? "<a href=\"mailto:".$postrow[$x]["user_email"]."\"><img src=\"".$images['email']."\" alt=\"$l_email $poster\" border=\"0\"></a>" : "";
	$www_img = ($postrow[$x]["user_website"]) ? "<a href=\"".$postrow[$x]["user_website"]."\"><img src=\"".$images['www']."\" alt=\"$l_viewsite\" border=\"0\"></a>" : "";

	if($postrow[$x]["user_icq"])
	{
		$icq_status_img = "<a href=\"http://wwp.icq.com/".$postrow[$x]["user_icq"]."#pager\"><img src=\"http://online.mirabilis.com/scripts/online.dll?icq=".$postrow[$x]["user_icq"]."&img=5\" alt=\"$l_icqstatus\" border=\"0\"></a>";
		$icq_add_img = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=".$postrow[$x]["user_icq"]."\"><img src=\"".$images['icq']."\" alt=\"$l_icq\" border=\"0\"></a>";
	}
	else
	{
		$icq_status_img = "";
		$icq_add_img = "";
	}
	
	$aim_img = ($postrow[$x]["user_aim"]) ? "<a href=\"aim:goim?screenname=".$postrow[$x]["user_aim"]."&message=Hello+Are+you+there?\"><img src=\"".$images['aim']."\" border=\"0\"></a>" : "";
	$msn_img = ($postrow[$x]["user_msnm"]) ? "<a href=\"profile.$phpEx?mode=viewprofile&user_id=$poster_id\"><img src=\"".$images['msn']."\" border=\"0\"></a>" : "";
	$yim_img = ($postrow[$x]["user_yim"]) ? "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target=".$postrow[$x]["user_yim"]."&.src=pg\"><img src=\"".$images['yim']."\" border=\"0\"></a>" : "";
	
	$edit_img = "<a href=\"".append_sid("posting.$phpEx?mode=editpost&post_id=".$postrow[$x]["post_id"]."&topic_id=$topic_id&forum_id=$forum_id")."\"><img src=\"".$images['edit']."\" alt=\"$l_editdelete\" border=\"0\"></a>";
	$quote_img = "<a href=\"".append_sid("posting.$phpEx?mode=reply&quote=true&post_id=".$postrow[$x]["post_id"]."&topic_id=$topic_id&forum_id=$forum_id")."\"><img src=\"".$images['quote']."\" alt=\"$l_replyquote\" border=\"0\"></a>";
	$pmsg_img = "<a href=\"".append_sid("priv_msgs.$phpEx?mode=send")."\"><img src=\"".$images['pmsg']."\" alt=\"$l_sendpmsg\" border=\"0\"></a>";
	
	if($is_moderator)
	{
		$ip_img = "<a href=\"".append_sid("topicadmin.$phpEx?mode=viewip&user_id=".$poster_id)."\"><img src=\"".$images['ip']."\" alt=\"$l_viewip\" border=\"0\"></a>";
		$delpost_img = "<a href=\"".append_sid("topicadmin.$phpEx?mode=delpost$post_id=".$postrow[$x]["post_id"])."\"><img src=\"".$images['delpost']."\" alt=\"$l_delete\" border=\"0\"></a>";
	}
	
	$message = stripslashes($postrow[$x]["post_text"]);
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
	
	if(!($x % 2))
	{
		if(isset($theme['td_color1']))
		{
			$color = "#".$theme['td_color1'];
		}
		else
		{
			$color = "#DDDDDD";
		}
	}
	else
	{
		if(isset($theme['td_color2']))
		{
			$color = "#".$theme['td_color2'];
		}
		else
		{
			$color = "#CCCCCC";
		}
	}
	
	$message = eregi_replace("\[addsig]$", "<br />_________________<br />" . nl2br($user_sig), $message);
	
	$template->assign_block_vars("postrow", array(
		"TOPIC_TITLE" => $topic_title,
		"POSTER_NAME" => $poster,
		"POSTER_RANK" => $poster_rank,
		"RANK_IMAGE" => $rank_image,
		"ROW_COLOR" => $color,
		"POSTER_JOINED" => $poster_joined,
		"POSTER_POSTS" => $poster_posts,
		"POSTER_FROM" => $poster_from,
		"POST_DATE" => $post_date,
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
		"DELPOST_IMG" => $delpost_img));
}

if($total_replies > $board_config['posts_per_page'])
{
	$times = 0;
	for($x = 0; $x < $total_replies; $x += $board_config['posts_per_page'])
	{
		$times++;
	}
	$pages = $times . " $l_pages";
	
	$times = 1;
	$pagination = "$l_gotopage (";
	
	$last_page = $start - $board_config['posts_per_page'];
	if($start > 0)
	{
		$pagination .= "<a href=\"".append_sid("$PHP_SELF?".POST_TOPIC_URL."=$topic_id&start=$last_page")."\">$l_prevpage</a> ";
	}
	
	for($x = 0; $x < $total_replies; $x += $board_config['posts_per_page'])
	{
		if($times != 1)
		{
			$pagination .=  " | ";
		}
		if($start && ($start == $x))
		{
			$pagination .=  $times;
		}
		else if($start == 0 && $x == 0)
		{
			$pagination .= "1";
		}
		else
		{
			$pagination .= "<a href=\"".append_sid("$PHP_SELF?".POST_TOPIC_URL."=$topic_id&start=$x")."\">$times</a>";
		}
		$times++;
	}
	
	if(($start + $board_config['posts_per_page']) < $total_replies)
	{
		$next_page = $start + $board_config['posts_per_page'];
		$pagination .=  " <a href=\"".append_sid("$PHP_SELF?".POST_TOPIC_URL."=$topic_id&start=$next_page")."\">$l_nextpage</a>";
	}
	$pagination .= " )";
}
else
{
	$pages = "1 $l_page";
}

$template->assign_vars(array(
	"PAGES" => $pages,
	"PAGINATION" => $pagination));

$template->pparse("body");

include('includes/page_tail.'.$phpEx);

?>
