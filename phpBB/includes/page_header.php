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

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

define('HEADER_INC', TRUE);

// gzip_compression
if ($config['gzip_compress'])
{
	if (extension_loaded('zlib') && !headers_sent())
	{
		ob_start('ob_gzhandler');
	}
}

// Generate logged in/logged out status
if ($user->data['user_id'] != ANONYMOUS)
{
	$u_login_logout = 'login.'.$phpEx. $SID . '&amp;logout=true';
	$l_login_logout = $user->lang['LOGOUT'] . ' [ ' . $user->data['username'] . ' ]';
}
else
{
	$u_login_logout = 'login.'.$phpEx . $SID;
	$l_login_logout = $user->lang['LOGIN'];
}

// Last visit date/time
$s_last_visit = ($user->data['user_id']) ? $user->format_date($user->data['session_last_visit']) : '';

// Get users online list
$userlist_ary = array();
$userlist_visible = array();

$logged_visible_online = 0;
$logged_hidden_online = 0;
$guests_online = 0;
$online_userlist = '';

$prev_user_id = 0;
$prev_user_ip = '';

$reading_sql = '';
if (!empty($_REQUEST['f']))
{
	$reading_sql = 'AND s.session_page LIKE \'%f=' . intval($_REQUEST['f']) . '%\'';
}

$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, u.user_colour, s.session_ip
	FROM " . USERS_TABLE . " u, " . SESSIONS_TABLE ." s
	WHERE s.session_time >= ".(time() - 300) . "
		$reading_sql
		AND u.user_id = s.session_user_id
	ORDER BY u.username ASC, s.session_ip ASC";
$result = $db->sql_query($sql, false);

while ($row = $db->sql_fetchrow($result))
{
	// User is logged in and therefor not a guest
	if ($row['user_id'] != ANONYMOUS)
	{
		// Skip multiple sessions for one user
		if ($row['user_id'] != $prev_user_id)
		{
			if ($row['user_colour'])
			{
				$row['username'] = '<b style="color:#' . $row['user_colour'] . '">' . $row['username'] . '</b>';
			}

			if ($row['user_allow_viewonline'])
			{
				$user_online_link = '<a href="' . "ucp.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '">' . $row['username'] . '</a>';
				$logged_visible_online++;
			}
			else
			{
				$user_online_link = '<a href="' . "ucp.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '"><i>' . $row['username'] . '</i></a>';
				$logged_hidden_online++;
			}

			if ($row['user_allow_viewonline'] || $auth->acl_get('a_'))
			{
				$online_userlist .= ($online_userlist != '') ? ', ' . $user_online_link : $user_online_link;
			}
		}

		$prev_user_id = $row['user_id'];
	}
	else
	{
		// Skip multiple sessions for one user
		if ($row['session_ip'] != $prev_session_ip)
		{
			$guests_online++;
		}
	}

	$prev_session_ip = $row['session_ip'];
}

if (empty($online_userlist))
{
	$online_userlist = $user->lang['None'];
}

if (empty($_REQUEST['f']))
{
	$online_userlist = $user->lang['Registered_users'] . ' ' . $online_userlist;
}
else
{
	$l_online = ($guests_online == 1) ? $user->lang['Browsing_forum_guest'] : $user->lang['Browsing_forum_guests'];
	$online_userlist = sprintf($l_online, $online_userlist, $guests_online);
}

$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

if ($total_online_users > $config['record_online_users'])
{
	set_config('record_online_users', $total_online_users);
	set_config('record_online_date', time());
}

if ($total_online_users == 0)
{
	$l_t_user_s = $user->lang['Online_users_zero_total'];
}
else if ($total_online_users == 1)
{
	$l_t_user_s = $user->lang['Online_user_total'];
}
else
{
	$l_t_user_s = $user->lang['Online_users_total'];
}

if ($logged_visible_online == 0)
{
	$l_r_user_s = $user->lang['Reg_users_zero_total'];
}
else if ($logged_visible_online == 1)
{
	$l_r_user_s = $user->lang['Reg_user_total'];
}
else
{
	$l_r_user_s = $user->lang['Reg_users_total'];
}

if ($logged_hidden_online == 0)
{
	$l_h_user_s = $user->lang['Hidden_users_zero_total'];
}
else if ($logged_hidden_online == 1)
{
	$l_h_user_s = $user->lang['Hidden_user_total'];
}
else
{
	$l_h_user_s = $user->lang['Hidden_users_total'];
}

if ($guests_online == 0)
{
	$l_g_user_s = $user->lang['Guest_users_zero_total'];
}
else if ($guests_online == 1)
{
	$l_g_user_s = $user->lang['Guest_user_total'];
}
else
{
	$l_g_user_s = $user->lang['Guest_users_total'];
}

$l_online_users = sprintf($l_t_user_s, $total_online_users);
$l_online_users .= sprintf($l_r_user_s, $logged_visible_online);
$l_online_users .= sprintf($l_h_user_s, $logged_hidden_online);
$l_online_users .= sprintf($l_g_user_s, $guests_online);

// Obtain number of new private messages if user is logged in
if ($user->data['user_id'] != ANONYMOUS)
{
	if ($user->data['user_new_privmsg'])
	{
		$l_message_new = ($user->data['user_new_privmsg'] == 1) ? $user->lang['New_pm'] : $user->lang['New_pms'];
		$l_privmsgs_text = sprintf($l_message_new, $user->data['user_new_privmsg']);

		if ($user->data['user_last_privmsg'] > $user->data['session_last_visit'])
		{
			$sql = "UPDATE " . USERS_TABLE . "
				SET user_last_privmsg = " . $user->data['session_last_visit'] . "
				WHERE user_id = " . $user->data['user_id'];
			$db->sql_query($sql);

			$s_privmsg_new = 1;
		}
		else
		{
			$s_privmsg_new = 0;
		}
	}
	else
	{
		$l_privmsgs_text = $user->lang['No_new_pm'];
		$s_privmsg_new = 0;
	}

	if ($user->data['user_unread_privmsg'])
	{
		$l_message_unread = ($user->data['user_unread_privmsg'] == 1) ? $user->lang['Unread_pm'] : $user->lang['Unread_pms'];
		$l_privmsgs_text_unread = sprintf($l_message_unread, $user->data['user_unread_privmsg']);
	}
	else
	{
		$l_privmsgs_text_unread = $user->lang['No_unread_pm'];
	}
}

// Generate HTML required for Mozilla Navigation bar
$nav_links_html = '';
/*
$nav_link_proto = '<link rel="%s" href="%s" title="%s" />' . "\n";
foreach ($nav_links as $nav_item => $nav_array)
{
	if (!empty($nav_array['url']))
	{
		$nav_links_html .= sprintf($nav_link_proto, $nav_item, $nav_array['url'], $nav_array['title']);
	}
	else
	{
		// We have a nested array, used for items like <link rel='chapter'> that can occur more than once.
		foreach ($nav_array as $key => $nested_array)
		{
			$nav_links_html .= sprintf($nav_link_proto, $nav_item, $nested_array['url'], $nested_array['title']);
		}
	}
}
*/

// Which timezone?
$tz = ($user->data['user_id'] != ANONYMOUS) ? strval(doubleval($user->data['user_timezone'])) : strval(doubleval($config['board_timezone']));

// The following assigns all _common_ variables that may be used at any point
// in a template.
$template->assign_vars(array(
	'SITENAME' 						=> $config['sitename'],
	'SITE_DESCRIPTION' 				=> $config['site_desc'],
	'PAGE_TITLE' 					=> $page_title,
	'LAST_VISIT_DATE' 				=> sprintf($user->lang['You_last_visit'], $s_last_visit),
	'CURRENT_TIME' 					=> sprintf($user->lang['Current_time'], $user->format_date(time())),
	'TOTAL_USERS_ONLINE' 			=> $l_online_users,
	'LOGGED_IN_USER_LIST' 			=> $online_userlist,
	'RECORD_USERS' 					=> sprintf($user->lang['Record_online_users'], $config['record_online_users'], $user->format_date($config['record_online_date'])),
	'PRIVATE_MESSAGE_INFO' 			=> $l_privmsgs_text,
	'PRIVATE_MESSAGE_NEW_FLAG'		=> $s_privmsg_new,
	'PRIVATE_MESSAGE_INFO_UNREAD' 	=> $l_privmsgs_text_unread,

	'L_LOGIN_LOGOUT' 	=> $l_login_logout,
	'L_INDEX' 			=> $user->lang['Forum_Index'],

	'U_PRIVATEMSGS'	=> 'privmsg.'.$phpEx.$SID.'&amp;folder=inbox',
	'U_MEMBERLIST' 	=> 'memberlist.'.$phpEx.$SID,
	'U_VIEWONLINE' 	=> 'viewonline.'.$phpEx.$SID,
	'U_MEMBERSLIST' => 'memberlist.'.$phpEx.$SID,
	'U_GROUP_CP' 	=> 'groupcp.'.$phpEx.$SID,
	'U_LOGIN_LOGOUT'=> $u_login_logout,
	'U_INDEX' 		=> 'index.'.$phpEx.$SID,
	'U_SEARCH' 		=> 'search.'.$phpEx.$SID,
	'U_REGISTER' 	=> 'ucp.'.$phpEx.$SID.'&amp;mode=register',
	'U_PROFILE' 	=> 'ucp.'.$phpEx.$SID.'&amp;mode=editprofile',
	'U_MODCP' 		=> 'mcp.'.$phpEx.$SID,
	'U_FAQ' 		=> 'faq.'.$phpEx.$SID,
	'U_SEARCH_SELF'	=> 'search.'.$phpEx.$SID.'&amp;search_id=egosearch',
	'U_SEARCH_NEW' 	=> 'search.'.$phpEx.$SID.'&amp;search_id=newposts',
	'U_PRIVATEMSGS_POPUP'	=> 'privmsg.'.$phpEx.$SID.'&amp;mode=newpm',
	'U_SEARCH_UNANSWERED'	=> 'search.'.$phpEx.$SID.'&amp;search_id=unanswered',

	'S_USER_LOGGED_IN' 		=> ($user->data['user_id']) ? true : false,
	'S_USER_PM_POPUP' 		=> (!empty($user->data['user_popup_pm'])) ? true : false,
	'S_USER_BROWSER' 		=> $user->data['session_browser'],
	'S_CONTENT_DIRECTION' 	=> $user->lang['DIRECTION'],
	'S_CONTENT_ENCODING' 	=> $user->lang['ENCODING'],
	'S_CONTENT_DIR_LEFT' 	=> $user->lang['LEFT'],
	'S_CONTENT_DIR_RIGHT' 	=> $user->lang['RIGHT'],
	'S_LOGIN_ACTION' 		=> 'login.'.$phpEx.$SID,
	'S_TIMEZONE' 			=> ($user->data['user_dst']) ? sprintf($user->lang['All_times'], $user->lang[$tz], $user->lang['tz']['dst']) : sprintf($user->lang['All_times'], $user->lang[$tz], ''),

	'T_STYLESHEET_DATA'	=> $user->theme['css_data'],
	'T_STYLESHEET_LINK' => 'templates/' . $user->theme['css_external'],

	'NAV_LINKS' => $nav_links_html)
);

/*if ($config['send_encoding'])
{
	header ('Content-type: text/html; charset: ' . $user->lang['ENCODING']);
}*/
header ('Cache-Control: private, pre-check=0, post-check=0, max-age=0');
header ('Expires: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header ('Pragma: private');

?>