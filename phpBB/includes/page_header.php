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
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

define('HEADER_INC', TRUE);

// gzip_compression
if ( $board_config['gzip_compress'] )
{
	if ( extension_loaded('zlib') && strstr($HTTP_USER_AGENT,'compatible') && !headers_sent() )
	{
		ob_start('ob_gzhandler');
	}
}

// Generate logged in/logged out status
if ( $userdata['user_id'] )
{
	$u_login_logout = 'login.'.$phpEx. $SID . '&amp;logout=true';
	$l_login_logout = $lang['Logout'] . ' [ ' . $userdata['username'] . ' ]';
}
else
{
	$u_login_logout = 'login.'.$phpEx . $SID;
	$l_login_logout = $lang['Login'];
}

// Last visit date/time
$s_last_visit = ( $userdata['user_id'] ) ? $user->format_date($userdata['session_last_visit']) : '';

// Timezone : $user->dst
$s_timezone = ( $userdata['user_dst'] ) ? sprintf($lang['All_times'], $lang[floatval($board_config['board_timezone'])], $lang['tz']['dst']) : sprintf($lang['All_times'], $lang[floatval($board_config['board_timezone'])], '');

// Get users online list
$userlist_ary = array();
$userlist_visible = array();

$logged_visible_online = 0;
$logged_hidden_online = 0;
$guests_online = 0;
$online_userlist = '';

$prev_user_id = 0;
$prev_user_ip = '';
//  && $auth->acl_get('forum', 'read', $_GET['f'])
$user_forum_sql = ( empty($_GET['f'])) ? '' : "AND s.session_page LIKE '%f=" . intval($_GET['f']) . "%'";
$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, u.user_colour, s.session_ip
	FROM " . USERS_TABLE . " u, " . SESSIONS_TABLE ." s
	WHERE s.session_time >= ".( time() - 300 ) . "
		$user_forum_sql
		AND u.user_id = s.session_user_id
	ORDER BY u.username ASC, s.session_ip ASC";
$result = $db->sql_query($sql, false);

while( $row = $db->sql_fetchrow($result) )
{
	// User is logged in and therefor not a guest
	if ( $row['user_id'] )
	{
		// Skip multiple sessions for one user
		if ( $row['user_id'] != $prev_user_id )
		{
			if ( $row['user_colour'] )
			{
				$row['username'] = '<b style="color:#' . $row['user_colour'] . '">' . $row['username'] . '</b>';
			}

			if ( $row['user_allow_viewonline'] )
			{
				$user_online_link = '<a href="' . "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '">' . $row['username'] . '</a>';
				$logged_visible_online++;
			}
			else
			{
				$user_online_link = '<a href="' . "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '"><i>' . $row['username'] . '</i></a>';
				$logged_hidden_online++;
			}

			if ( $row['user_allow_viewonline'] || $auth->acl_get('a_') )
			{
				$online_userlist .= ( $online_userlist != '' ) ? ', ' . $user_online_link : $user_online_link;
			}
		}

		$prev_user_id = $row['user_id'];
	}
	else
	{
		// Skip multiple sessions for one user
		if ( $row['session_ip'] != $prev_session_ip )
		{
			$guests_online++;
		}
	}

	$prev_session_ip = $row['session_ip'];
}

if ( empty($online_userlist) )
{
	$online_userlist = $lang['None'];
}

if ( empty($_GET['f']) )
{
	$online_userlist = $lang['Registered_users'] . ' ' . $online_userlist;
}
else
{
	$l_online = ( $guests_online == 1 ) ? $lang['Browsing_forum_guest'] : $lang['Browsing_forum_guests'];
	$online_userlist = sprintf($l_online, $online_userlist, $guests_online);
}

$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

if ( $total_online_users > $board_config['record_online_users'])
{
	$board_config['record_online_users'] = $total_online_users;
	$board_config['record_online_date'] = time();

	$sql = "UPDATE " . CONFIG_TABLE . "
		SET config_value = '$total_online_users'
		WHERE config_name = 'record_online_users'";
	$db->sql_query($sql);

	$sql = "UPDATE " . CONFIG_TABLE . "
		SET config_value = '" . $board_config['record_online_date'] . "'
		WHERE config_name = 'record_online_date'";
	$db->sql_query($sql);
}

if ( $total_online_users == 0 )
{
	$l_t_user_s = $lang['Online_users_zero_total'];
}
else if ( $total_online_users == 1 )
{
	$l_t_user_s = $lang['Online_user_total'];
}
else
{
	$l_t_user_s = $lang['Online_users_total'];
}

if ( $logged_visible_online == 0 )
{
	$l_r_user_s = $lang['Reg_users_zero_total'];
}
else if ( $logged_visible_online == 1 )
{
	$l_r_user_s = $lang['Reg_user_total'];
}
else
{
	$l_r_user_s = $lang['Reg_users_total'];
}

if ( $logged_hidden_online == 0 )
{
	$l_h_user_s = $lang['Hidden_users_zero_total'];
}
else if ( $logged_hidden_online == 1 )
{
	$l_h_user_s = $lang['Hidden_user_total'];
}
else
{
	$l_h_user_s = $lang['Hidden_users_total'];
}

if ( $guests_online == 0 )
{
	$l_g_user_s = $lang['Guest_users_zero_total'];
}
else if ( $guests_online == 1 )
{
	$l_g_user_s = $lang['Guest_user_total'];
}
else
{
	$l_g_user_s = $lang['Guest_users_total'];
}

$l_online_users = sprintf($l_t_user_s, $total_online_users);
$l_online_users .= sprintf($l_r_user_s, $logged_visible_online);
$l_online_users .= sprintf($l_h_user_s, $logged_hidden_online);
$l_online_users .= sprintf($l_g_user_s, $guests_online);

//
// Obtain number of new private messages
// if user is logged in
//
if ( $userdata['user_id'] )
{
	if ( $userdata['user_new_privmsg'] )
	{
		$l_message_new = ( $userdata['user_new_privmsg'] == 1 ) ? $lang['New_pm'] : $lang['New_pms'];
		$l_privmsgs_text = sprintf($l_message_new, $userdata['user_new_privmsg']);

		if ( $userdata['user_last_privmsg'] > $userdata['session_last_visit'] )
		{
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_last_privmsg = " . $userdata['session_last_visit'] . "
				WHERE user_id = " . $userdata['user_id'];
			$db->sql_query($sql);

			$s_privmsg_new = 1;
			$icon_pm = $images['pm_new_msg'];
		}
		else
		{
			$s_privmsg_new = 0;
			$icon_pm = $images['pm_no_new_msg'];
		}
	}
	else
	{
		$l_privmsgs_text = $lang['No_new_pm'];

		$s_privmsg_new = 0;
		$icon_pm = $images['pm_no_new_msg'];
	}

	if ( $userdata['user_unread_privmsg'] )
	{
		$l_message_unread = ( $userdata['user_unread_privmsg'] == 1 ) ? $lang['Unread_pm'] : $lang['Unread_pms'];
		$l_privmsgs_text_unread = sprintf($l_message_unread, $userdata['user_unread_privmsg']);
	}
	else
	{
		$l_privmsgs_text_unread = $lang['No_unread_pm'];
	}
}
else
{
	$icon_pm = $images['pm_no_new_msg'];
	$l_privmsgs_text = $lang['Login_check_pm'];
	$l_privmsgs_text_unread = '';
	$s_privmsg_new = 0;
}

//
// Generate HTML required for Mozilla Navigation bar
//
/*
$nav_links_html = '';
$nav_link_proto = '<link rel="%s" href="%s" title="%s" />' . "\n";
foreach ( $nav_links as $nav_item => $nav_array )
{
	if ( !empty($nav_array['url']) )
	{
		$nav_links_html .= sprintf($nav_link_proto, $nav_item, $nav_array['url'], $nav_array['title']);
	}
	else
	{
		// We have a nested array, used for items like <link rel='chapter'> that can occur more than once.
		foreach ( $nav_array as $key => $nested_array )
		{
			$nav_links_html .= sprintf($nav_link_proto, $nav_item, $nested_array['url'], $nested_array['title']);
		}
	}
}
*/
//
// The following assigns all _common_ variables that may be used at any point
// in a template.
//
$template->assign_vars(array(
	'SITENAME' => $board_config['sitename'],
	'SITE_DESCRIPTION' => $board_config['site_desc'],
	'PAGE_TITLE' => $page_title,
	'LAST_VISIT_DATE' => sprintf($lang['You_last_visit'], $s_last_visit),
	'CURRENT_TIME' => sprintf($lang['Current_time'], $user->format_date(time())),
	'TOTAL_USERS_ONLINE' => $l_online_users,
	'LOGGED_IN_USER_LIST' => $online_userlist,
	'RECORD_USERS' => sprintf($lang['Record_online_users'], $board_config['record_online_users'], $user->format_date($$board_config['record_online_date'])),
	'PRIVATE_MESSAGE_INFO' => $l_privmsgs_text,
	'PRIVATE_MESSAGE_INFO_UNREAD' => $l_privmsgs_text_unread,
	'PRIVATE_MESSAGE_NEW_FLAG' => $s_privmsg_new,

	'PRIVMSG_IMG' => $icon_pm,

	'L_USERNAME' => $lang['Username'],
	'L_PASSWORD' => $lang['Password'],
	'L_LOGIN_LOGOUT' => $l_login_logout,
	'L_LOGIN' => $lang['Login'],
	'L_LOG_ME_IN' => $lang['Log_me_in'],
	'L_AUTO_LOGIN' => $lang['Log_me_in'],
	'L_INDEX' => sprintf($lang['Forum_Index'], $board_config['sitename']),
	'L_REGISTER' => $lang['Register'],
	'L_PROFILE' => $lang['Profile'],
	'L_SEARCH' => $lang['Search'],
	'L_PRIVATEMSGS' => $lang['Private_Messages'],
	'L_WHO_IS_ONLINE' => $lang['Who_is_Online'],
	'L_MEMBERLIST' => $lang['Memberlist'],
	'L_FAQ' => $lang['FAQ'],
	'L_USERGROUPS' => $lang['Usergroups'],
	'L_SEARCH_NEW' => $lang['Search_new'],
	'L_SEARCH_UNANSWERED' => $lang['Search_unanswered'],
	'L_SEARCH_SELF' => $lang['Search_your_posts'],

	'U_SEARCH_UNANSWERED' => 'search.'.$phpEx.$SID.'&amp;search_id=unanswered',
	'U_SEARCH_SELF' => 'search.'.$phpEx.$SID.'&amp;search_id=egosearch',
	'U_SEARCH_NEW' => 'search.'.$phpEx.$SID.'&amp;search_id=newposts',
	'U_INDEX' => 'index.'.$phpEx.$SID,
	'U_REGISTER' => 'profile.'.$phpEx.$SID.'&amp;mode=register',
	'U_PROFILE' => 'profile.'.$phpEx.$SID.'&amp;mode=editprofile',
	'U_PRIVATEMSGS' => 'privmsg.'.$phpEx.$SID.'&amp;folder=inbox',
	'U_PRIVATEMSGS_POPUP' => 'privmsg.'.$phpEx.$SID.'&amp;mode=newpm',
	'U_SEARCH' => 'search.'.$phpEx.$SID,
	'U_MEMBERLIST' => 'memberlist.'.$phpEx.$SID,
	'U_MODCP' => 'modcp.'.$phpEx.$SID,
	'U_FAQ' => 'faq.'.$phpEx.$SID,
	'U_VIEWONLINE' => 'viewonline.'.$phpEx.$SID,
	'U_LOGIN_LOGOUT' => $u_login_logout,
	'U_MEMBERSLIST' => 'memberlist.'.$phpEx.$SID,
	'U_GROUP_CP' => 'groupcp.'.$phpEx.$SID,

	'S_USER_LOGGED_IN' => ( $userdata['user_id'] ) ? true : false,
	'S_USER_PM_POPUP' => ( !empty($userdata['user_popup_pm']) ) ? true : false,
	'S_USER_BROWSER' => $userdata['session_browser'],
	'S_CONTENT_DIRECTION' => $lang['DIRECTION'],
	'S_CONTENT_ENCODING' => $lang['ENCODING'],
	'S_CONTENT_DIR_LEFT' => $lang['LEFT'],
	'S_CONTENT_DIR_RIGHT' => $lang['RIGHT'],
	'S_TIMEZONE' => $s_timezone,
	'S_LOGIN_ACTION' => 'login.'.$phpEx.$SID,

	'T_STYLESHEET_DATA' => $theme['css_data'],
	'T_STYLESHEET_LINK' => 'templates/' . $theme['css_external'],

	'NAV_LINKS' => $nav_links_html)
);

header ('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
header ('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header ('Pragma: private');

?>