<?php
/***************************************************************************
 *                              page_header.php
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

define(HEADER_INC, TRUE);

//
// Parse and show the overall header.
//
$template->set_filenames(array(
	"overall_header" => "overall_header.tpl",
	"overall_footer" => "overall_footer.tpl"));

//
// Generate logged in/logged out status
//
if($userdata['session_logged_in'])
{
	$logged_in_status = "You are logged in as <b>".$userdata["username"]."</b>.";
	$logged_in_status .= " [<A HREF=\"login.$phpEx?submit=logout\">Logout</A>]";
}
else
{
	$logged_in_status = "You are not logged in.";
}

//
// Do timezone text output
//
if($sys_timezone < 0)
{
	$s_timezone = "$l_all_times GMT $sys_timezone $l_hours";
}
else if($sys_timezone == 0)
{
	$s_timezone = "$l_all_times GMT";
}
else
{
	$s_timezone = "$l_all_times GMT + $sys_timezone $l_hours";
}

//
// Get basic (usernames + totals) online
// situation
//
$sql = "SELECT u.username, u.user_id, s.session_logged_in
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s 
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= '".(time()-300)."'
	";
$result = $db->sql_query($sql);
if(!$result)
{
	error_die(SQL_QUERY, "Couldn't obtain user/online information.", __LINE__, __FILE__);
}

$logged_online = 0;
$guests_online = 0;
while($row = $db->sql_fetchrow($result))
{
	if($row['session_logged_in'])
	{
		$userlist_ary[] = "<a href=\"profile." . $phpEx . "?mode=viewprofile&" . POST_USERS_URL . "=" . $row['user_id'] . "\">" . $row['username'] . "</a>";
		$logged_online++;
	}
	else
	{
		$guests_online++;
	}
}
$userlist = "";
for($i = 0; $i < $logged_online; $i++)
{
	$userlist .= ($i ==  $logged_online - 1 && $logged_online > 1) ? " and " : "";
	$userlist .= $userlist_ary[$i];
	$userlist .= ($i < $logged_online - 2) ? ", " : "";
}

$l_r_user_s = ($logged_online == 1) ? $l_user : $l_users;
$l_g_user_s = ($guests_online == 1) ? $l_user : $l_users;
$l_is_are = ($logged_online == 1) ? $l_is : $l_are;
$userlist = ($logged_online > 0) ? "$l_Registered $l_r_user_s: " . $userlist : "$l_Registered $l_r_user_s: $l_None";

$template->assign_vars(array(
	"SITENAME" => $sitename,
	"PHPEX" => $phpEx,
	"PHPSELF" => $PHP_SELF,

	"L_USERNAME" => $l_username,
	"L_PASSWORD" => $l_password,
	"L_LOG_ME_IN" => $l_log_me_in,
	"L_WELCOMETO" => $l_welcometo,
	"L_REGISTER" => $l_register,
	"L_PROFILE" => $l_profile,
	"L_SEARCH" => $l_search,
	"L_PRIVATEMSGS" => $l_privmsgs,
	"L_MEMBERLIST" => $l_memberslist,
	"L_FAQ" => $l_faq,
	"L_FORUM" => $l_forum,
	"L_TOPICS" => $l_topics,
	"L_REPLIES" => $l_replies,
	"L_VIEWS" => $l_views,
	"L_POSTS" => $l_posts,
	"L_LASTPOST" => $l_lastpost,
	"L_MODERATOR" => $l_moderator,
	"L_MESSAGES" => $l_messages,
	"L_POSTEDTOTAL" => $l_postedtotal,
	"L_WEHAVE" => $l_wehave,
	"L_REGUSERS" => $l_regedusers,
	"L_NEWESTUSER" => $l_newestuser,
	"L_BROWSING" => $l_browsing,
	"L_ARECURRENTLY" => $l_arecurrently,
	"L_THEFORUMS" => $l_theforums,
	"L_NONEWPOSTS" => $l_nonewposts,
	"L_NEWPOSTS" => $l_newposts,

	"S_TIMEZONE" => $s_timezone,
	"S_FORUMS_URL" => POST_FORUM_URL,
	"S_TOPICS_URL" => POST_TOPIC_URL,
	"S_USERS_URL" => POST_USERS_URL,

	"T_HEAD_STYLESHEET" => $theme['head_stylesheet'],
	"T_BODY_BACKGROUND" => $theme['body_background'],
	"T_BODY_BGCOLOR" => "#".$theme['body_bgcolor'],
	"T_BODY_TEXT" => "#".$theme['body_text'],
	"T_BODY_LINK" => "#".$theme['body_link'],
	"T_BODY_VLINK" => "#".$theme['body_vlink'],
	"T_BODY_ALINK" => "#".$theme['body_alink'],
	"T_BODY_HLINK" => "#".$theme['body_hlink'],
	"T_TR_COLOR1" => "#".$theme['tr_color1'],
	"T_TR_COLOR2" => "#".$theme['tr_color2'],
	"T_TR_COLOR3" => "#".$theme['tr_color3'],
	"T_TH_COLOR1" => "#".$theme['th_color1'],
	"T_TH_COLOR2" => "#".$theme['th_color2'],
	"T_TH_COLOR3" => "#".$theme['th_color3'],
	"T_TD_COLOR1" => "#".$theme['td_color1'],
	"T_TD_COLOR2" => "#".$theme['td_color2'],
	"T_TD_COLOR3" => "#".$theme['td_color3'],
	"T_FONTFACE1" => $theme['fontface1'],
	"T_FONTFACE2" => $theme['fontface2'],
	"T_FONTFACE3" => $theme['fontface3'],
	"T_FONTSIZE1" => $theme['fontsize1'],
	"T_FONTSIZE2" => $theme['fontsize2'],
	"T_FONTSIZE3" => $theme['fontsize3'],
	"T_FONTCOLOR1" => "#".$theme['fontcolor1'],
	"T_FONTCOLOR2" => "#".$theme['fontcolor2'],
	"T_FONTCOLOR3" => "#".$theme['fontcolor3'],
	"T_IMG1" => $theme['img1'],
	"T_IMG2" => $theme['img2'],
	"T_IMG3" => $theme['img3'],
	"T_IMG4" => $theme['img4'],

	"PAGE_TITLE" => $page_title,
	"LOGIN_STATUS" => $logged_in_status,
	"META_INFO" => $meta_tags,
	
	"TOTAL_USERS_ONLINE" => "$l_There $l_is_are $logged_online $l_Registered $l_r_user_s $l_and $guests_online $l_guest $l_g_user_s $l_online",
	"LOGGED_IN_USER_LIST" => $userlist
	));

$template->pparse("overall_header");

//
// Do a switch on page type, this way we only load
// the templates that we need at the time
//
switch($pagetype)
{

	case 'index':
		$template->set_filenames(array(
			"header" => "index_header.tpl",
			"body" => "index_body.tpl",
			"footer" => "index_footer.tpl"));
		$template->assign_vars(array(
			"TOTAL_POSTS" => $total_posts,
			"TOTAL_USERS" => $total_users,
			"NEWEST_USER" => $newest_user,
			"NEWEST_UID" => $newest_uid,
			"USERS_BROWSING" => $users_browsing));
		$template->pparse("header");
		break;

	case 'viewforum':
		$template->set_filenames(array(
			"header" => "viewforum_header.tpl",
			"body" => "viewforum_body.tpl",
			"jumpbox" => "jumpbox.tpl",
			"footer" => "viewforum_footer.tpl"));
		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"JUMPBOX_LIST" => $jumpbox,
		    "JUMPBOX_ACTION" => "viewforum.".$phpEx,
		    "SELECT_NAME" => POST_FORUM_URL));
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		$template->assign_vars(array(
			"FORUM_ID" => $forum_id,
			"FORUM_NAME" => $forum_name,
			"MODERATORS" => $forum_moderators));
		$template->pparse("header");
		break;
		
	case 'viewtopic':
		$template->set_filenames(array(
			"header" => "viewtopic_header.tpl",
			"body" => "viewtopic_body.tpl",
			"jumpbox" => "jumpbox.tpl",
			"footer" => "viewtopic_footer.tpl"));
		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"JUMPBOX_LIST" => $jumpbox,
		    "JUMPBOX_ACTION" => "viewforum.".$phpEx,
		    "SELECT_NAME" => POST_FORUM_URL));
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		$template->assign_vars(array(
			"FORUM_ID" => $forum_id,
		    "FORUM_NAME" => $forum_name,
		    "TOPIC_ID" => $topic_id,
		    "TOPIC_TITLE" => $topic_title,
			"POST_FORUM_URL" => POST_FORUM_URL));
		$template->pparse("header");
		break;

	case 'viewonline':
		$template->set_filenames(array(
			"header" => "viewonline_header.tpl",
			"body" => "viewonline_body.tpl",
			"jumpbox" => "jumpbox.tpl",
			"footer" => "viewonline_footer.tpl"));
		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"JUMPBOX_LIST" => $jumpbox,
		    "JUMPBOX_ACTION" => "viewforum.".$phpEx,
		    "SELECT_NAME" => POST_FORUM_URL));
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		$template->assign_vars(array(
			"TOTAL_POSTS" => $total_posts,
			"TOTAL_USERS" => $total_users,
			"POST_USER_URL" => POST_USERS_URL,
			"NEWEST_USER" => $newest_user,
			"NEWEST_UID" => $newest_uid));
		$template->pparse("header");
		break;

	case 'newtopic':
		$template->set_filenames(array(
			"header" => "newtopic_header.tpl",
			"jumpbox" => "jumpbox.tpl",
			"body" => "posting_body.tpl"));
		$jumpbox = make_jumpbox();
		$template->assign_vars(array(
			"JUMPBOX_LIST" => $jumpbox,
		    "JUMPBOX_ACTION" => "viewforum.".$phpEx,
		    "SELECT_NAME" => POST_FORUM_URL));
		$template->assign_var_from_handle("JUMPBOX", "jumpbox");
		$template->assign_vars(array(
			"L_POSTNEWIN" => $l_postnewin,
			"FORUM_ID" => $forum_id,
			"FORUM_NAME" => $forum_name));
		$template->pparse("header");
		break;

	case 'register':
		if(!isset($agreed))
		{
			if(!isset($coppa))
			{
				$coppa = FALSE;
			}
			$template->set_filenames(array(
				"body" => "agreement.tpl"));
			$template->assign_vars(array(
				"COPPA" => $coppa));
		}
		else
		{
			$template->set_filenames(array(
				"body" => "profile_add_body.tpl"));
		}
		break;
		
	case 'profile':
		$template->set_filenames(array(
			"body" => "profile_view_body.tpl"));
   break;
}

?>
