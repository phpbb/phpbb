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
			if(extension_loaded("zlib"))
			{
				$do_gzip_compress = TRUE;
				ob_start();
				ob_implicit_flush(0);

				header("Content-Encoding: gzip");
			}
		}
	}
}

//
// Parse and show the overall header.
//
if( empty($gen_simple_header) )
{
	$template->set_filenames(array(
		"overall_header" => "overall_header.tpl")
	);
}
else
{
	$template->set_filenames(array(
		"overall_header" => "simple_header.tpl")
	);
}

//
// Generate logged in/logged out status
//
if($userdata['session_logged_in'])
{
	$u_login_logout = "login.$phpEx?logout=true";
	$l_login_logout = $lang['Logout'] . " : " . $userdata["username"] . "";
}
else
{
	$u_login_logout = "login.$phpEx";
	$l_login_logout = $lang['Login'];
}

$s_last_visit = create_date($board_config['default_dateformat'], $userdata['session_last_visit'], $board_config['board_timezone']);

//
// Get basic (usernames + totals) online
// situation
//
$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, s.session_logged_in, s.session_ip
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".( time() - 300 );
$result = $db->sql_query($sql);
if(!$result)
{
	message_die(GENERAL_ERROR, "Couldn't obtain user/online information.", "", __LINE__, __FILE__, $sql);
}

$userlist_ary = array();
$userlist_visible = array();
$logged_visible_online = 0;
$logged_hidden_online = 0;
$guests_online = 0;

while( $row = $db->sql_fetchrow($result) )
{
	if( $row['session_logged_in'] )
	{
		if( $row['user_allow_viewonline'] )
		{
			$userlist_ary[] = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . "\">" . $row['username'] . "</a>";
			$userlist_visible[] = 1;
		}
		else
		{
			$userlist_ary[] = "<a href=\"" . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $row['user_id']) . "\">" . $row['username'] . "</a>";
			$userlist_visible[] = 0;
		}
	}
	else
	{
		$guests_online++;
	}
}

$online_userlist = "";
for($i = 0; $i < count($userlist_ary); $i++)
{
	if( !strstr($online_userlist, $userlist_ary[$i]) )
	{
		if( $userlist_visible[$i] || $userdata['user_level'] == ADMIN )
		{
			$online_userlist .= ($online_userlist != "") ? ", " . $userlist_ary[$i] : $userlist_ary[$i];
			$logged_visible_online++;
		}
		else
		{
			$logged_hidden_online++;
		}
	}
}
$online_userlist = $lang['Registered_users'] . " " . $online_userlist;

$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

$l_online_users = ( $total_online_users == 1 ) ? sprintf($lang['Online_user'], $total_online_users, $logged_visible_online, $logged_hidden_online, $guests_online) : sprintf($lang['Online_users'], $total_online_users, $logged_visible_online, $logged_hidden_online, $guests_online);

//
// Obtain number of new private messages
// if user is logged in
//
if( $userdata['session_logged_in'] )
{
	$sql = "SELECT COUNT(privmsgs_type) AS new_messages
		FROM " . PRIVMSGS_TABLE . "
		WHERE privmsgs_type = " . PRIVMSGS_NEW_MAIL . " 
			AND privmsgs_to_userid = " . $userdata['user_id'];
	if( !$result_pm = $db->sql_query($sql) )
	{
		message_die(GENERAL_MESSAGE, "Couldn't obtain user/online information.", "", __LINE__, __FILE__, $sql);
	}

	if( $pm_result = $db->sql_fetchrow($result_pm) )
	{
		$new_pm_messages = $pm_result['new_messages'];

		$l_message_new = ( $new_pm_messages == 1 ) ? $lang['New_pm'] : $lang['New_pms'];
		$l_privmsgs_text = sprintf($l_message_new, $new_pm_messages);
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
	"SITE_DESCRIPTION" => $board_config['site_desc'], 
	"PAGE_TITLE" => $page_title,
	"TOTAL_USERS_ONLINE" => $l_online_users,
	"LOGGED_IN_USER_LIST" => $online_userlist,
	"PRIVATE_MESSAGE_INFO" => $l_privmsgs_text,
	"PRIVATE_MESSAGE_COUNT" => $new_pm_messages_session, 
	"LAST_VISIT_DATE" => $s_last_visit, 

	"L_USERNAME" => $lang['Username'],
	"L_PASSWORD" => $lang['Password'],
	"L_LOGIN" => $lang['Login'],
	"L_LOG_ME_IN" => $lang['Log_me_in'],
	"L_INDEX" => $lang['Forum_Index'],
	"L_REGISTER" => $lang['Register'],
	"L_PROFILE" => $lang['Profile'],
	"L_SEARCH" => $lang['Search'],
	"L_PRIVATEMSGS" => $lang['Private_msgs'],
	"L_WHO_IS_ONLINE" => $lang['Who_is_Online'],
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
	"L_NO_NEW_POSTS" => $lang['No_new_posts'],
	"L_NEW_POSTS" => $lang['New_posts'],
	"L_NO_NEW_POSTS_HOT" => $lang['No_new_posts_hot'],
	"L_NEW_POSTS_HOT" => $lang['New_posts_hot'],
	"L_TOPIC_IS_LOCKED" => $lang['Topic_is_locked'], 
	"L_ANNOUNCEMENT" => $lang['Post_Announcement'], 
	"L_STICKY" => $lang['Post_Sticky'], 
	"L_POSTED" => $lang['Posted'],
	"L_JOINED" => $lang['Joined'],
	"L_AUTO_LOGIN" => $lang['Log_me_in'],
	"L_AUTHOR" => $lang['Author'],
	"L_MESSAGE" => $lang['Message'],
	"L_BY" => $lang['by'],
	"L_LOGIN_LOGOUT" => $l_login_logout,
	"L_LAST_VISIT" => $lang['You_last_visit'],

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

	"S_CONTENT_DIRECTION" => $lang['DIRECTION'], 
	"S_CONTENT_ENCODING" => $lang['ENCODING'], 
	"S_TIMEZONE" => $lang['All_times'] . " " . $lang[$board_config['board_timezone']],
	"S_LOGIN_ACTION" => append_sid("login.$phpEx"),
	"S_CURRENT_TIME" => create_date($board_config['default_dateformat'], time(), $board_config['board_timezone']),

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
	"T_TR_CLASS1" => $theme['tr_class1'],
	"T_TR_CLASS2" => $theme['tr_class2'],
	"T_TR_CLASS3" => $theme['tr_class3'],
	"T_TH_COLOR1" => "#".$theme['th_color1'],
	"T_TH_COLOR2" => "#".$theme['th_color2'],
	"T_TH_COLOR3" => "#".$theme['th_color3'],
	"T_TH_CLASS1" => $theme['th_class1'],
	"T_TH_CLASS2" => $theme['th_class2'],
	"T_TH_CLASS3" => $theme['th_class3'],
	"T_TD_COLOR1" => "#".$theme['td_color1'],
	"T_TD_COLOR2" => "#".$theme['td_color2'],
	"T_TD_COLOR3" => "#".$theme['td_color3'],
	"T_TD_CLASS1" => $theme['td_class1'],
	"T_TD_CLASS2" => $theme['td_class2'],
	"T_TD_CLASS3" => $theme['td_class3'],
	"T_FONTFACE1" => $theme['fontface1'],
	"T_FONTFACE2" => $theme['fontface2'],
	"T_FONTFACE3" => $theme['fontface3'],
	"T_FONTSIZE1" => $theme['fontsize1'],
	"T_FONTSIZE2" => $theme['fontsize2'],
	"T_FONTSIZE3" => $theme['fontsize3'],
	"T_FONTCOLOR1" => "#".$theme['fontcolor1'],
	"T_FONTCOLOR2" => "#".$theme['fontcolor2'],
	"T_FONTCOLOR3" => "#".$theme['fontcolor3'],
	"T_SPAN_CLASS1" => $theme['span_class1'],
	"T_SPAN_CLASS2" => $theme['span_class2'],
	"T_SPAN_CLASS3" => $theme['span_class3'])
);

//
// Login box?
//
if( !$userdata['session_logged_in'] )
{
	$template->assign_block_vars("switch_user_logged_out", array());
}
else
{
	$template->assign_block_vars("switch_user_logged_in", array());
}

header ("Cache-Control: no-store, no-cache, must-revalidate");
header ("Cache-Control: pre-check=0, post-check=0, max-age=0");
header ("Pragma: no-cache");
header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

$template->pparse("overall_header");

?>