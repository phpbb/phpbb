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

$total_posts = get_db_stat($db, 'postcount');
$total_users = get_db_stat($db, 'usercount');
$newest_userdata = get_db_stat($db, 'newestuser');
$newest_user = $newest_userdata["username"];
$newest_uid = $newest_userdata["user_id"];

include('includes/page_header.'.$phpEx);

$sql = "SELECT u.username, u.user_id, f.forum_name, f.forum_id, s.session_page, s.session_logged_in
	FROM ".USERS_TABLE." u, ".SESSIONS_TABLE." s 
	LEFT JOIN ".FORUMS_TABLE." f ON f.forum_id = s.session_page
	WHERE u.user_id = s.session_user_id";
$result = $db->sql_query($sql);
if(!$result)
{
	error_die(SQL_QUERY, "Couldn't obtain user/online information.", __LINE__, __FILE__);
}
$onlinerow = $db->sql_fetchrowset($result);
if(!$onlinerow)
{
	error_die(SQL_QUERY, "Couldn't fetchrow", __LINE__, __FILE__);
}

$template->assign_vars(array(
	"PHP_SELF" => $PHP_SELF,
	"POST_FORUM_URL" => POST_FORUM_URL,
	"POST_USER_URL" => POST_USERS_URL,
	"L_WHOSONLINE" => $l_whosonline,
	"L_USERNAME" => $l_username,
	"L_LOCATION" => $l_location
	)
);

$online_count = $db->sql_numrows($result);
if($online_count)
{
	for($i = 0; $i < $db->sql_numrows($result); $i++)
	{

		if($row_color == "#DDDDDD")
		{
			$row_color = "#CCCCCC";
		}
		else
		{
			$row_color = "#DDDDDD";
		}

		if(!stristr($onlinerow[$i]['username'], "Anonymous"))
		{
			$username = $onlinerow[$i]['username'];
			if($onlinerow[$i]['session_logged_in'])
			{
				$username .= "&nbsp;&nbsp;[ Logged In ]";
			}
			else
			{
				$username .= "&nbsp;&nbsp;[ Logged Out ]";
			}
		}
		else
		{
			$username .= "$l_anonymous";
		}

		if($onlinerow[$i]['forum_name'] == "")
		{
			switch($onlinerow[$i]['session_page'])
			{
				case PAGE_INDEX:
					$location = "Forum Index";
					break;
				case PAGE_LOGIN:
					$location = "Logging On";
					break;
				case PAGE_SEARCH:
					$location = "Topic Search";
					break;
				case PAGE_REGISTER:
					$location = "Registering";
					break;
				case PAGE_VIEWPROFILE:
					$location = "Viewing Profiles";
					break;
				case PAGE_ALTERPROFILE:
					$location = "Altering Profile";
					break;
				case PAGE_VIEWONLINE:
					$location = "Viewing Who's Online";
					break;
				case PAGE_VIEWMEMBERS:
					$location = "Viewing Memberlist";
					break;
				case PAGE_FAQ:
					$location = "Viewing FAQ";
					break;
				default:
					$location = "Forum Index";
			}
			$location_url = "index.".$phpEx;
		}
		else
		{
			$location_url = "viewforum.".$phpEx."?".POST_FORUM_URL."=".$onlinerow[$i]['forum_id'];
			$location = $onlinerow[$i]['forum_name'];
		}

		$template->assign_block_vars("userrow", 
			array("ROW_COLOR" => $row_color,
				"USER_ID" => $onlinerow[$i]['user_id'],
				"USERNAME" => $username,
				"LOCATION" => $location,
				"LOCATION_URL" => $location_url
			)
		);

	}

	$template->pparse("body");
}
else
{
	error_die(GENERAL_ERROR, "There are no users currently browsing this forum");
}

include('includes/page_tail.'.$phpEx);

?>
