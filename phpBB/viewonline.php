<?php
/***************************************************************************
 *                              viewonline.php
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

$phpbb_root_path = "./";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_VIEWONLINE, $session_length);
init_userprefs($userdata);
//
// End session management
//

// ---------------
// Begin functions
//
function inarray($needle, $haystack)
{ 
	for($i = 0; $i < sizeof($haystack); $i++ )
	{ 
		if( $haystack[$i] == $needle )
		{ 
			return true; 
		} 
	} 
	return false; 
}
//
// End functions
// -------------

//
// Output page header and load
// viewonline template
//
$page_title = $lang['Who_is_online'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "viewonline_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);

$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"L_GO" => $lang['Go'],
	"L_JUMP_TO" => $lang['Jump_to'],
	"L_SELECT_FORUM" => $lang['Select_forum'],
	
	"S_JUMPBOX_LIST" => $jumpbox,
	"S_JUMPBOX_ACTION" => append_sid("viewforum.$phpEx"))
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");
//
// End header
//

$sql = "SELECT u.user_id, u.username, u.user_allow_viewonline, s.session_page, s.session_logged_in, s.session_time
	FROM " . USERS_TABLE . " u, " . SESSIONS_TABLE . " s
	WHERE u.user_id <> " . ANONYMOUS . "
		AND u.user_id = s.session_user_id
		AND s.session_time >= " . ( time() - 300 ) . "
		AND s.session_logged_in = " . TRUE . "
	ORDER BY s.session_time DESC";
if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain regd user/online information.", "", __LINE__, __FILE__, $sql);
}
$onlinerow_reg = $db->sql_fetchrowset($result);

$sql = "SELECT session_page, session_logged_in, session_time, session_ip
	FROM " . SESSIONS_TABLE . "
	WHERE session_logged_in = 0
		AND session_time >= " . ( time() - 300 ) . "
	ORDER BY session_time DESC";
if(!$result = $db->sql_query($sql))
{
	message_die(GENERAL_ERROR, "Couldn't obtain guest user/online information.", "", __LINE__, __FILE__, $sql);
}
$onlinerow_guest = $db->sql_fetchrowset($result);

$sql = "SELECT forum_name, forum_id
	FROM " . FORUMS_TABLE;
if($forums_result = $db->sql_query($sql))
{
	while($forumsrow = $db->sql_fetchrow($forums_result))
	{
		$forum_data[$forumsrow['forum_id']] = $forumsrow['forum_name'];
	}
}
else
{
	message_die(GENERAL_ERROR, "Couldn't obtain user/online forums information.", "", __LINE__, __FILE__, $sql);
}

$template->assign_vars(array(
	"L_WHOSONLINE" => $lang['Who_is_online'],
	"L_ONLINE_EXPLAIN" => $lang['Online_explain'],
	"L_USERNAME" => $lang['Username'],
	"L_LOCATION" => $lang['Location'],
	"L_LAST_UPDATE" => $lang['Last_updated'])
);

$active_users = 0;
$guest_users = 0;

//
// Get auth data
//
$is_auth_ary = array();
$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

//
// Registered users ...
//
$reg_userid_ary = array();

if( count($onlinerow_reg) )
{
	$registered_users = 0;
	$hidden_users = 0;

	$displayed_userid_list = "";

	for($i = 0; $i < count($onlinerow_reg); $i++)
	{
		if( !inarray($onlinerow_reg[$i]['user_id'], $reg_userid_ary) )
		{
			if( $onlinerow_reg[$i]['user_allow_viewonline'] || $userdata['user_level'] == ADMIN )
			{
				$username = $onlinerow_reg[$i]['username'];
				$hidden = FALSE;
				$registered_users++;
			}
			else
			{
				$username = $onlinerow_reg[$i]['username'];
				$hidden = TRUE;
				$hidden_users++;
			}

			if( $onlinerow_reg[$i]['session_page'] < 1 || !$is_auth_ary[$onlinerow_reg[$i]['session_page']]['auth_view'] )
			{
				switch($onlinerow_reg[$i]['session_page'])
				{
					case PAGE_INDEX:
						$location = $lang['Forum_index'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_POSTING:
						$location = $lang['Posting_message'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_LOGIN:
						$location = $lang['Logging_on'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_SEARCH:
						$location = $lang['Searching_forums'];
						$location_url = "search.$phpEx";
						break;
					case PAGE_PROFILE:
						$location = $lang['Viewing_profile'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_VIEWONLINE:
						$location = $lang['Viewing_online'];
						$location_url = "viewonline.$phpEx";
						break;
					case PAGE_VIEWMEMBERS:
						$location = $lang['Viewing_member_list'];
						$location_url = "memberlist.$phpEx";
						break;
					case PAGE_PRIVMSGS:
						$location = $lang['Viewing_priv_msgs'];
						$location_url = "privmsg.$phpEx";
						break;
					case PAGE_FAQ:
						$location = $lang['Viewing_FAQ'];
						$location_url = "faq.$phpEx";
						break;
					default:
						$location = $lang['Forum_index'];
						$location_url = "index.$phpEx";
				}
			}
			else
			{
				$location_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $onlinerow_reg[$i]['session_page']);
				$location = $forum_data[$onlinerow_reg[$i]['session_page']];
			}

			if( !$hidden || $userdata['user_level'] == ADMIN )
			{
				$row_color = ( $registered_users % 2 ) ? $theme['td_color1'] : $theme['td_color2'];
				$row_class = ( $registered_users % 2 ) ? $theme['td_class1'] : $theme['td_class2'];

				$template->assign_block_vars("reg_user_row", array(
					"ROW_COLOR" => "#" . $row_color,
					"ROW_CLASS" => $row_class,
					"USERNAME" => $username,
					"LASTUPDATE" => create_date($board_config['default_dateformat'], $onlinerow_reg[$i]['session_time'], $board_config['board_timezone']),
					"LOCATION" => $location,

					"U_USER_PROFILE" => append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=" . $onlinerow_reg[$i]['user_id']),
					"U_FORUM_LOCATION" => append_sid($location_url))
				);
			}
		}
	}

	$template->assign_vars(array(
		"TOTAL_REGISTERED_USERS_ONLINE" => sprintf($lang['Reg_users_online'], $registered_users, $hidden_users))
	);

}
else
{
	$template->assign_vars(array(
		"TOTAL_REGISTERED_USERS_ONLINE" => sprintf($lang['Reg_users_online'], 0, 0),
		"L_NO_REGISTERED_USERS_BROWSING" => $lang['No_users_browsing'])
	);
}

//
// Guest users
//
$guest_userip_ary = array();

if( count($onlinerow_guest) )
{
	$guest_users = 0;

	for($i = 0; $i < count($onlinerow_guest); $i++)
	{
		if( !inarray($onlinerow_guest[$i]['session_ip'], $guest_userip_ary) )
		{
			$guest_users++;

			if($onlinerow_guest[$i]['session_page'] < 1 || !$is_auth_ary[$onlinerow_reg[$i]['session_page']]['auth_view'] )
			{
				switch($onlinerow_guest[$i]['session_page'])
				{
					case PAGE_INDEX:
						$location = $lang['Forum_index'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_POSTING:
						$location = $lang['Posting_message'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_LOGIN:
						$location = $lang['Logging_on'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_SEARCH:
						$location = $lang['Searching_forums'];
						$location_url = "search.$phpEx";
						break;
					case PAGE_PROFILE:
						$location = $lang['Viewing_profile'];
						$location_url = "index.$phpEx";
						break;
					case PAGE_VIEWONLINE:
						$location = $lang['Viewing_online'];
						$location_url = "viewonline.$phpEx";
						break;
					case PAGE_VIEWMEMBERS:
						$location = $lang['Viewing_member_list'];
						$location_url = "memberlist.$phpEx";
						break;
					case PAGE_PRIVMSGS:
						$location = $lang['Viewing_priv_msgs'];
						$location_url = "privmsg.$phpEx";
						break;
					case PAGE_FAQ:
						$location = $lang['Viewing_FAQ'];
						$location_url = "faq.$phpEx";
						break;
					default:
						$location = $lang['Forum_index'];
						$location_url = "index.$phpEx";
				}
			}
			else
			{
				$location_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $onlinerow_guest[$i]['session_page']);
				$location = $forum_data[$onlinerow_guest[$i]['session_page']];
			}

			$row_color = ( $guest_users % 2 ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( $guest_users % 2 ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("guest_user_row", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"USERNAME" => $lang['Guest'],
				"LASTUPDATE" => create_date($board_config['default_dateformat'], $onlinerow_guest[$i]['session_time'], $board_config['board_timezone']),
				"LOCATION" => $location,

				"U_FORUM_LOCATION" => append_sid($location_url))
			);
		}
	}

	$l_g_user_s = ( $guest_users == 1 ) ? $lang['Guest_user_online'] : $lang['Guest_users_online'];

	$template->assign_vars(array(
		"TOTAL_GUEST_USERS_ONLINE" => sprintf($l_g_user_s, $guest_users))
	);

}
else
{
	$template->assign_vars(array(
		"TOTAL_GUEST_USERS_ONLINE" => sprintf($lang['Guest_users_online'], 0, 0),
		"L_NO_GUESTS_BROWSING" => $lang['No_users_browsing'])
	);
}

$template->pparse("body");

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>
