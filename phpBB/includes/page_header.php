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
	$logged_in_status = $lang['You_are_logged_in'] . " <b>".$userdata["username"]."</b>.";
	$logged_in_status .= " [<a href=\"".append_sid("login.$phpEx?submit=logout")."\">".$lang['Logout']."</a>]";

	$u_login_logout = "login.$phpEx?submit=logout";
	$l_login_logout = $lang['Logout']." : ".$userdata["username"]."";
}
else
{
	$logged_in_status = $lang['You_are_not_logged_in'];

	$u_login_logout = "login.$phpEx";
	$l_login_logout = $lang['Login'];
}

//
// Do timezone text output
//
if($board_config['default_timezone'] < 0)
{
	$s_timezone = $lang['All_times'] . " " .$lang['GMT'] ." - ".(-$board_config['default_timezone']) . " " . $lang['Hours'];
}
else if($board_config['default_timezone'] == 0)
{
	$s_timezone = $lang['All_times'] . " " .$lang['GMT'];
}
else
{
	$s_timezone = $lang['All_times'] . " " .$lang['GMT'] ." + ".$board_config['default_timezone'] . " " . $lang['Hours'];
}

//
// Get basic (usernames + totals) online
// situation
//
$sql = "SELECT u.username, u.user_id, s.session_logged_in
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s 
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".(time() - 300);
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
		$userlist_ary[] = "<a href=\"".append_sid("profile." . $phpEx . "?mode=viewprofile&" . POST_USERS_URL . "=" . $row['user_id']) . "\">" . $row['username'] . "</a>";
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

//
// The following assigns all _common_
// variables that may be used at any point
// in a template. Note that all URL's should
// be wrapped in append_sid, as should all
// S_x_ACTIONS for forms. 
// 
$template->assign_vars(array(
	"SITENAME" => $board_config['sitename'],
	"PAGE_TITLE" => $page_title,
	"LOGIN_STATUS" => $logged_in_status,
	"META_INFO" => $meta_tags,
	"TOTAL_USERS_ONLINE" => "$l_There $l_is_are $logged_online $l_Registered $l_r_user_s $l_and $guests_online $l_guest $l_g_user_s $l_online",
	"LOGGED_IN_USER_LIST" => $userlist,

	"L_USERNAME" => $l_username,
	"L_PASSWORD" => $l_password,
	"L_LOGIN" => $l_login,
	"L_LOG_ME_IN" => $l_log_me_in,
	"L_WELCOMETO" => $l_welcometo,
	"L_INDEX" => $l_indextitle,
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
	"L_POSTED" => $l_posted,
	"L_JOINED" => $l_joined,
	"L_AUTO_LOGIN" => $l_autologin,
	"L_AUTHOR" => $l_author,
	"L_MESSAGE" => $l_message,
	"L_BY" => $l_by,
	"L_LOGIN_LOGOUT" => $l_login_logout,

	"U_INDEX" => append_sid("index.".$phpEx),
	"U_REGISTER" => append_sid("profile.".$phpEx."?mode=register"),
	"U_PROFILE" => append_sid("profile.".$phpEx."?mode=editprofile"),
	"U_PRIVATEMSGS" => append_sid("priv_msgs.".$phpEx."?mode=read"),
	"U_SEARCH" => append_sid("search.".$phpEx),
	"U_MEMBERLIST" => append_sid("memberlist.".$phpEx),
	"U_FAQ" => append_sid("faq.".$phpEx),
	"U_VIEWONLINE" => append_sid("viewonline.$phpEx"),
	"U_LOGIN_LOGOUT" => append_sid($u_login_logout),
	"U_MEMBERSLIST" => append_sid("memberlist.".$phpEx),

	"S_TIMEZONE" => $s_timezone,
	"S_LOGIN_ACTION" => append_sid("login.$phpEx"),
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"),

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
	"T_IMG4" => $theme['img4']));


header ("Expires: " . gmdate("D, d M Y H:i:s", time()+10) . " GMT"); 
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

$template->pparse("overall_header");

?>