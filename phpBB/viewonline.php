<?php
/***************************************************************************  
 *                               viewonline.php
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

$pagetype = "viewonline";
$page_title = "Who's Online";

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_VIEWONLINE, $session_length);
init_userprefs($userdata);
//
// End session management
//

//
// Output page header and load
// viewonline template
//
include('includes/page_header.'.$phpEx);

$template->set_filenames(array(
	"body" => "viewonline_body.tpl",
	"jumpbox" => "jumpbox.tpl")
);
$jumpbox = make_jumpbox();
$template->assign_vars(array(
	"JUMPBOX_LIST" => $jumpbox,
    "SELECT_NAME" => POST_FORUM_URL)
);
$template->assign_var_from_handle("JUMPBOX", "jumpbox");
//
// End header
//

$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, s.session_page, s.session_logged_in, s.session_time
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s 
	WHERE u.user_id = s.session_user_id
		AND s.session_time >= ".(time()-300)."
	ORDER BY s.session_time DESC";
$result = $db->sql_query($sql);
if(!$result)
{
	error_die(SQL_QUERY, "Couldn't obtain user/online information.", __LINE__, __FILE__);
}
$onlinerow = $db->sql_fetchrowset($result);
$sql = "SELECT forum_name, forum_id
	FROM ".FORUMS_TABLE;
$forums_result = $db->sql_query($sql);
if(!$forums_result)
{
	error_die(SQL_QUERY, "Couldn't obtain user/online forums information.", __LINE__, __FILE__);
}
else
{
	while($forumsrow = $db->sql_fetchrow($forums_result))
	{
		$forum_data[$forumsrow['forum_id']] = $forumsrow['forum_name'];
	}
}

if(!$onlinerow || !$forum_data)
{
	error_die(SQL_QUERY, "Couldn't fetchrow.", __LINE__, __FILE__);
}

$template->assign_vars(array(
	"L_WHOSONLINE" => $lang['Who_is_online'],
	"L_USERNAME" => $lang['Username'],
	"L_LOCATION" => $lang['Location'],
	"L_LAST_UPDATE" => $lang['Last_updated'])
);

$active_users = 0;
$guest_users = 0;

$online_count = $db->sql_numrows($result);
if($online_count)
{
	for($i = 0; $i < $online_count; $i++)
	{

		if(!($i % 2))
		{
			$row_color = "#" . $theme['td_color1'];
		}
		else
		{
			$row_color = "#" . $theme['td_color2'];
		}

		if($onlinerow[$i]['user_id'] != ANONYMOUS)
		{
			if($onlinerow[$i]['session_logged_in'])
			{
				if($onlinerow[$i]['user_allow_viewonline'])
				{
					$username = $onlinerow[$i]['username'];
					$hidden = FALSE;
					$logged_on = TRUE;
					$active_users++;
				}
				else
				{
					$username = $onlinerow[$i]['username'];
					$hidden = TRUE;
					$logged_on = TRUE;
					$hidden_users++;
				}
			}
			else
			{
				if($onlinerow[$i]['user_allow_viewonline'])
				{
					$username = $onlinerow[$i]['username'];
					$hidden = FALSE;
					$logged_on = FALSE;
					$guest_users++;
				}
				else
				{
					$username = $onlinerow[$i]['username'];
					$hidden = TRUE;
					$logged_on = FALSE;
					$guest_users++;
				}
			}
		}
		else
		{
			$username = $lang['Anonymous'];
			$hidden = FALSE;
			$logged_on = FALSE;
			$guest_users++;
		}

		if($onlinerow[$i]['session_page'] < 1)
		{
			switch($onlinerow[$i]['session_page'])
			{
				case PAGE_INDEX:
					$location = $lang['Forum_index'];
					$location_url = "index.$phpEx";
					break;
				case PAGE_LOGIN:
					$location = $lang['Loggin_on'];
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
			$location_url = append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $onlinerow[$i]['session_page']);
			$location = $forum_data[$onlinerow[$i]['session_page']];
		}

		//
		// What would be nice here is to let
		// the template designer decide whether
		// to display all users, registered users
		// or just logged in users ... but we need
		// if... constructs in the templating system
		// for that ...
		//
		if( $logged_on && ( !$hidden || $userdata['user_level'] == ADMIN ) )
		{
			$template->assign_block_vars("userrow", 
				array(
					"ROW_COLOR" => $row_color,
					"USERNAME" => $username,
					"LOGGED_ON" => $logged_on,
					"LASTUPDATE" => create_date($board_config['default_dateformat'], $onlinerow[$i]['session_time'], $board_config['default__timezone']),
					"LOCATION" => $location,
					"U_USER_PROFILE" => append_sid("profile.$phpEx?mode=viewprofile&" . POST_USERS_URL . "=" . $onlinerow[$i]['user_id']),
					"U_FORUM_LOCATION" => append_sid($location_url)
				)
			);
		}

	}

	$template->assign_vars(array(
		"ACTIVE_USERS" => $active_users, 
		"HIDDEN_USERS" => $hidden_users, 
		"GUEST_USERS" => $guest_users
		)
	);

	$template->pparse("body");
}
else
{
	error_die(GENERAL_ERROR, "There are no users currently browsing this forum");
}

include('includes/page_tail.'.$phpEx);

?>