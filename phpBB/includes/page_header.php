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
// gzip_compression
//
$do_gzip_compress = FALSE;
if($board_config['gzip_compress'])
{
	$phpver = phpversion();

	if($phpver >= "4.0.4pl1")
	{
		if(extension_loaded("zlib"))
		{
			ob_start("ob_gzhandler");
		}
	}
	else if($phpver > "4.0")
	{
		if(strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip'))
		{ 
			$do_gzip_compress = TRUE;
			ob_start();
			ob_implicit_flush(0); 

			header("Content-Encoding: gzip"); 
		}
	}
}

//
// Parse and show the overall header.
//
$template->set_filenames(array(
	"overall_header" => "overall_header.tpl")
);

//
// Generate logged in/logged out status
//
if($userdata['session_logged_in'])
{
	$u_login_logout = "login.$phpEx?submit=logout";
	$l_login_logout = $lang['Logout'] . " : " . $userdata["username"] . "";
}
else
{
	$u_login_logout = "login.$phpEx";
	$l_login_logout = $lang['Login'];
}


$l_last_visit = $lang['You_last_visit'];
$s_last_visit = create_date($board_config['default_dateformat'], $userdata['session_last_visit'], $board_config['default_timezone']);

//
// Get basic (usernames + totals) online
// situation
//
$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, s.session_logged_in
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".(time() - 300);
$result = $db->sql_query($sql);
if(!$result)
{
	message_die(GENERAL_ERROR, "Couldn't obtain user/online information.", "", __LINE__, __FILE__, $sql);
}

$logged_visible_online = 0;
$logged_hidden_online = 0;
$guests_online = 0;
while($row = $db->sql_fetchrow($result))
{
	if($row['session_logged_in'])
	{
		if($row['user_allow_viewonline'] || $userdata['user_level'] == ADMIN)
		{
			$userlist_ary[] = "<a href=\"" . append_sid("profile." . $phpEx . "?mode=viewprofile&" . POST_USERS_URL . "=" . $row['user_id']) . "\">" . $row['username'] . "</a>";
			$logged_visible_online++;
		}
		else
		{
			$logged_hidden_online++;
		}
	}
	else
	{
		$guests_online++;
	}
}
$userlist = "";
for($i = 0; $i < $logged_visible_online; $i++)
{
	$userlist .= ($i ==  $logged_visible_online - 1 && $logged_visible_online > 1) ? " " . $lang['and'] . " " : "";
	$userlist .= $userlist_ary[$i];
	$userlist .= ($i < $logged_visible_online - 2) ? ", " : "";
}

$l_g_user_s = ($guests_online == 1) ? $lang['User'] : $lang['Users'];
$l_h_user_s = ($logged_hidden_online == 1) ? $lang['User'] : $lang['Users'];
$l_r_user_s = ($logged_visible_online == 1) ? $lang['User'] : $lang['Users'];
$l_is_are = ($logged_visible_online == 1) ? $lang['is'] : $lang['are'];
$userlist = ($logged_visible_online > 0) ? $lang['Registered'] ." $l_r_user_s: " . $userlist : $lang['Registered'] . " $l_r_user_s: ".$lang['None'];

//
// Obtain number of new private messages
// if user is logged in
//
if($userdata['session_logged_in'])
{
	$sql = "SELECT COUNT(privmsgs_type) AS new_messages 
		FROM " . PRIVMSGS_TABLE . " 
		WHERE privmsgs_type = " . PRIVMSGS_NEW_MAIL . "  
			AND privmsgs_to_userid = " . $userdata['user_id'];
	$result_pm = $db->sql_query($sql);
	if(!$result_pm)
	{
		message_die(GENERAL_MESSAGE, "Couldn't obtain user/online information.", "", __LINE__, __FILE__, $sql);
	}
	if($pm_result = $db->sql_fetchrow($result_pm))
	{
		$new_messages = $pm_result['new_messages'];

		$l_message_new = ($new_messages == 1) ? $lang['message'] : $lang['messages'];
		$l_privmsgs_text = $lang['You_have'] . " $new_messages " . $lang['new'] . " $l_message_new";
	}
	else
	{
		$l_privmsgs_text = $lang['No_new_pm'];
	}
}
else
{
	$l_privmsgs_text = $lang['Login_check_pm'];
}

//
// The following assigns all _common_ variables that may be used at any point
// in a template. Note that all URL's should be wrapped in append_sid, as
// should all S_x_ACTIONS for forms.
//
$template->assign_vars(array(
	"SITENAME" => $board_config['sitename'],
	"PAGE_TITLE" => $page_title,
	"META_INFO" => $meta_tags,
	"TOTAL_USERS_ONLINE" => $lang['There'] . " $l_is_are $logged_visible_online " . $lang['Registered'] . " $l_r_user_s, $logged_hidden_online " . $lang['Hidden'] . " $l_h_user_s ". $lang['and'] . " $guests_online " . $lang['Guest'] . " $l_g_user_s " . $lang['online'],
	"LOGGED_IN_USER_LIST" => $userlist,

	"L_USERNAME" => $lang['Username'],
	"L_PASSWORD" => $lang['Password'],
	"L_LOGIN" => $lang['Login'],
	"L_LOG_ME_IN" => $lang['Log_me_in'],
	"L_WELCOMETO" => $lang['Welcome_to'],
	"L_INDEX" => $lang['Forum_Index'],
	"L_REGISTER" => $lang['Register'],
	"L_PROFILE" => $lang['Profile'],
	"L_SEARCH" => $lang['Search'],
	"L_PRIVATEMSGS" => $lang['Private_msgs'],
	"L_MEMBERLIST" => $lang['Memberlist'],
	"L_FAQ" => $lang['FAQ'],
	"L_USERGROUPS" => $lang['Usergroups'],
	"L_FORUM" => $lang['Forum'],
	"L_TOPICS" => $lang['Topics'],
	"L_REPLIES" => $lang['Replies'],
	"L_VIEWS" => $lang['Views'],
	"L_POSTS" => $lang['Posts'],
	"L_LASTPOST" => $lang['Last_Post'],
	"L_MODERATOR" => $lang['Moderator'],
	"L_NONEWPOSTS" => $lang['No_new_posts'],
	"L_NEWPOSTS" => $lang['New_posts'],
	"L_TOPIC_IS_LOCKED" => $lang['Topic_is_locked'], 
	"L_POSTED" => $lang['Posted'],
	"L_JOINED" => $lang['Joined'],
	"L_AUTO_LOGIN" => $lang['Log_me_in'],
	"L_AUTHOR" => $lang['Author'],
	"L_MESSAGE" => $lang['Message'],
	"L_BY" => $lang['by'],
	"L_LOGIN_LOGOUT" => $l_login_logout,
	"L_PRIVATE_MESSAGE_INFO" => $l_privmsgs_text,
	"L_LAST_VISIT" => $l_last_visit,

	"U_INDEX" => append_sid("index.".$phpEx),
	"U_REGISTER" => append_sid("profile.".$phpEx."?mode=register"),
	"U_PROFILE" => append_sid("profile.".$phpEx."?mode=editprofile"),
	"U_PRIVATEMSGS" => append_sid("privmsg.".$phpEx."?folder=inbox"),
	"U_SEARCH" => append_sid("search.".$phpEx),
	"U_MEMBERLIST" => append_sid("memberlist.".$phpEx),
	"U_FAQ" => append_sid("faq.".$phpEx),
	"U_VIEWONLINE" => append_sid("viewonline.$phpEx"),
	"U_LOGIN_LOGOUT" => append_sid($u_login_logout),
	"U_MEMBERSLIST" => append_sid("memberlist.".$phpEx),
	"U_GROUP_CP" => append_sid("groupcp.".$phpEx),

	"S_TIMEZONE" => $lang['All_times'] . " " . $lang[$board_config['default_timezone']],
	"S_LOGIN_ACTION" => append_sid("login.$phpEx"),
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"),
	"S_LAST_VISIT_DATE" => $s_last_visit,
	"S_CURRENT_TIME" => create_date($board_config['default_dateformat'], time(), $board_config['default_timezone']),

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
	"T_IMG4" => $theme['img4'])
);

//
// Login box?
//
if(!$userdata['session_logged_in'])
{
	$template->assign_block_vars("loginbox", array());
}

header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

$template->pparse("overall_header");

?>