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

$total_posts = get_db_stat('postcount');
$total_users = get_db_stat('usercount');
$newest_userdata = get_db_stat('newestuser');
$newest_user = $newest_userdata["username"];
$newest_uid = $newest_userdata["user_id"];

include('includes/page_header.'.$phpEx);

$sql = "SELECT u.username, u.user_id, s.session_page, s.session_logged_in, s.session_time
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
$forumsrow = $db->sql_fetchrowset($forums_result);

if(!$onlinerow || !$forumsrow)
{
	error_die(SQL_QUERY, "Couldn't fetchrow", __LINE__, __FILE__);
}

$template->assign_vars(array(
	"L_WHOSONLINE" => $l_whosonline,
	"L_USERNAME" => $l_username,
	"L_LOCATION" => $l_forum_location,
	"L_LAST_UPDATE" => $l_last_updated
	)
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
			$row_color = "#".$theme['td_color1'];
		}
		else
		{
			$row_color = "#".$theme['td_color2'];
		}

		if($onlinerow[$i]['user_id'] != ANONYMOUS && $onlinerow[$i]['user_id'] != DELETED)
		{
			if($onlinerow[$i]['session_logged_in'])
			{
				$username = $onlinerow[$i]['username'];
				$logged_on = TRUE;
				$active_users++;
			}
			else
			{
				$username = $onlinerow[$i]['username'];
				$logged_on = FALSE;
				$guest_users++;
			}
		}
		else
		{
			$username = $l_anonymous;
			$logged_on = FALSE;
			$guest_users++;
		}

		if($onlinerow[$i]['session_page'] < 0)
		{
			switch($onlinerow[$i]['session_page'])
			{
				case PAGE_INDEX:
					$location = $l_forum_index;
					$location_url = "index.".$phpEx;
					break;
				case PAGE_LOGIN:
					$location = $l_logging_on;
					$location_url = "index.".$phpEx;
					break;
				case PAGE_SEARCH:
					$location = $l_searching;
					$location_url = "search.".$phpEx;
					break;
				case PAGE_REGISTER:
					$location = $l_registering;
					$location_url = "index.".$phpEx;
					break;
				case PAGE_VIEWPROFILE:
					$location = $l_viewing_profiles;
					$location_url = "index.".$phpEx;
					break;
				case PAGE_ALTERPROFILE:
					$location = $l_altering_profile;
					$location_url = "index.".$phpEx;
					break;
				case PAGE_VIEWONLINE:
					$location = $l_viewing_online;
					$location_url = "viewonline.".$phpEx;
					break;
				case PAGE_VIEWMEMBERS:
					$location = $l_viewing_members;
					$location_url = "memberlist.".$phpEx;
					break;
				case PAGE_FAQ:
					$location = $l_viewing_faq;
					$location_url = "faq.".$phpEx;
					break;
				default:
					$location = $l_forum_index;
					$location_url = "index.".$phpEx;
			}
		}
		else
		{
			for($j = 0; $j < count($forumsrow); $j++)
			{
				if($onlinerow[$i]['session_page'] == $forumsrow[$j]['forum_id'])
				{
					$location_url = append_sid("viewforum.".$phpEx."?".POST_FORUM_URL."=".$forumsrow[$j]['forum_id']);
					$location = $forumsrow[$j]['forum_name'];
					break;
				}
			}
		}

		//
		// What would be nice here is to let
		// the template designer decide whether
		// to display all users, registered users
		// or just logged in users ... but we need
		// if... constructs in the templating system
		// for that ...
		//
		if($logged_on)
		{
			$template->assign_block_vars("userrow", 
				array(
					"ROW_COLOR" => $row_color,
					"USERNAME" => $username,
					"LOGGED_ON" => $logged_on,
					"LASTUPDATE" => create_date($board_config['default_dateformat'], $onlinerow[$i]['session_time'], $board_config['default__timezone']),
					"LOCATION" => $location,
					"U_USER_PROFILE" => append_sid("profile.".$phpEx."?mode=viewprofile&".POST_USERS_URL."=".$onlinerow[$i]['user_id']),
					"U_FORUM_LOCATION" => append_sid($location_url)
				)
			);
		}

	}

	$template->assign_vars(array(
		"ACTIVE_USERS" => $active_users,
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
