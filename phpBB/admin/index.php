<?php
/***************************************************************************  
 *                                 
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

$phpbb_root_path = "./../";
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
init_userprefs($userdata);
// 
// End session management
//

//
// Is user logged in? If yes are they an admin?
//
if( !$userdata['session_logged_in'] )
{
	header("Location: ../login.$phpEx?forward_page=/admin/");
}
else if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, "You are not authorised to administer this board");
}

//
// Generate relevant output
//
if( $HTTP_GET_VARS['pane'] == 'left' )
{
	$dir = opendir(".");

	$setmodules = 1;
	while($file = readdir($dir))
	{
		if(preg_match("/^admin_.*/", $file))
		{
			include($file);
		}
	}

	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/navigate.tpl")
	);
	
	$template->assign_vars(array(
		"U_BOARD_INDEX" => append_sid("../index.$phpEx"),
		"U_ADMIN_INDEX" => append_sid("index.$phpEx?pane=right"),

		"L_BOARD_INDEX" => "Board Index", 
		"L_ADMIN_INDEX" => "Admin Index")
	);

	while( list($cat, $action_array) = each($module) )
	{
		$template->assign_block_vars("catrow", array(
			"CATNAME" => $cat)
		);

		$row_count = 0;
		while( list($action, $file)	= each($action_array) )
		{
			$row_color = "#" . ( ( !($row_count%2) ) ? $theme['td_color1'] : $theme['td_color2']);
			$row_class = ( !($row_count%2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars("catrow.actionrow", array(
				"ROW_COLOR" => $row_color, 
				"ROW_CLASS" => $row_class, 
				"ACTIONNAME" => $action,
				"FILE" => $file)
			);
			$row_count++;
		}
	}

	$template->pparse("body");

	unset($setmodules);
}
elseif( $HTTP_GET_VARS['pane'] == 'right' )
{

	include('page_header_admin.'.$phpEx);
	
	$template->set_filenames(array(
		"body" => "admin/admin_index_body.tpl")
	);

	//
	// Get forum statistics
	//
	$total_posts = get_db_stat('postcount');
	$total_users = get_db_stat('usercount');
	$total_topics = get_db_stat('topiccount');
	$start_date = create_date($board_config['default_dateformat'], $board_config['board_startdate'], $board_config['default_timezone']);

	$boarddays = (time() - $board_config['board_startdate']) / (24*60*60);
	$posts_per_day = sprintf("%.2f", $total_posts / $boarddays);
	$topics_per_day = sprintf("%.2f", $total_topics / $boarddays);
	$users_per_day = sprintf("%.2f", $total_users / $boarddays);

	$avatar_dir_size = 0;

	if ($avatar_dir = opendir($phpbb_root_path . $board_config['avatar_path']))
	{	
		while($file = readdir($avatar_dir))
		{
			if($file != "." && $file != "..")
			{
				$avatar_dir_size += filesize($phpbb_root_path . $board_config['avatar_path'] . "/" . $file);
			}
		}
		closedir($avatar_dir);
	}

	//
	// This bit of code translates the avatar directory size into human readable format
	// Borrowed the code from the PHP.net annoted manual, origanally written by:
	// Jesse (jesse@jess.on.ca)
	//
	if($avatar_dir_size >= 1048576) 
	{ 
		$avatar_dir_size = round($avatar_dir_size / 1048576 * 100) / 100 . " MB"; 
	} 
	else if($avatar_dir_size >= 1024) 
	{ 
		$avatar_dir_size = round($avatar_dir_size / 1024 * 100) / 100 . " KB"; 
	} 
	else 
	{ 
		$avatar_dir_size = $avatar_dir_size . " Bytes"; 
	} 

	if($posts_per_day > $total_posts)
	{
		$posts_per_day = $total_posts;
	}

	if($topics_per_day > $total_topics)
	{
		$topics_per_day = $total_topics;
	}

	if($users_per_day > $total_users)
	{
		$users_per_day = $total_users;
	}

	//
	// DB size ... MySQL only
	//
	// This code is heavily influenced by a similar routine
	// in phpMyAdmin 2.2.0
	//
	if(SQL_LAYER == 'mysql')
	{
		$sql = "SHOW TABLE STATUS FROM " . $dbname;
		if(!$result = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain table information.", "", __LINE__, __FILE__, $sql);
		}
		$tabledata_ary = $db->sql_fetchrowset($result);

		$dbsize = 0;
		for($i = 0; $i < count($tabledata_ary); $i++)
		{
			if($tabledata_ary[$i]['Type'] != "MRG_MyISAM")
			{
				$dbsize += $tabledata_ary[$i]['Data_length'] + $tabledata_ary[$i]['Index_length'];
			}
		}

		if($dbsize >= 1048576)
		{
			$dbsize = sprintf("%.2f MB", ( $dbsize / 1048576 ));
		}
		else if($dbsize >= 1024)
		{
			$dbsize = sprintf("%.2f KB", ( $dbsize / 1024 ));
		}
		else
		{
			$dbsize = sprintf("%.2f Bytes", $dbsize);
		}
	}
	else
	{
		$dbsize = "N/A";
	}

	$template->assign_vars(array(
		"NUMBER_OF_POSTS" => $total_posts,
		"NUMBER_OF_TOPICS" => $total_topics,
		"NUMBER_OF_USERS" => $total_users,
		"START_DATE" => $start_date,
		"POSTS_PER_DAY" => $posts_per_day,
		"TOPICS_PER_DAY" => $topics_per_day,
		"USERS_PER_DAY" => $users_per_day,
		"AVATAR_DIR_SIZE" => $avatar_dir_size, 
		"DB_SIZE" => $dbsize)
	);
	//
	// End forum statistics
	//

	//
	// Get users online information.
	//
	$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, s.session_page, s.session_logged_in, s.session_time, s.session_ip
		FROM " . USERS_TABLE . " u, " . SESSIONS_TABLE . " s 
		WHERE u.user_id = s.session_user_id
			AND s.session_time >= " . (time()-300) . "
		ORDER BY s.session_time DESC";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain user/online information.", "", __LINE__, __FILE__, $sql);
	}
	$onlinerow = $db->sql_fetchrowset($result);

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

	$online_count = $db->sql_numrows($result);
	if($online_count)
	{
		$count = 0;

		for($i = 0; $i < $online_count; $i++)
		{
			if($onlinerow[$i]['user_id'] != ANONYMOUS)
			{
				if($onlinerow[$i]['session_logged_in'])
				{
					$username = $onlinerow[$i]['username'];
				}
				else
				{
					$username = $onlinerow[$i]['username'];
				}
			}
			else
			{
				$username = $lang['Anonymous'];
			}

			if($onlinerow[$i]['session_page'] < 1)
			{
				switch($onlinerow[$i]['session_page'])
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
				$location_url = append_sid("admin_forum.$phpEx?" . POST_FORUM_URL . "=" . $onlinerow[$i]['session_page']);
				$location = $forum_data[$onlinerow[$i]['session_page']];
			}

			$row_color = "#" . ( ( !($count % 2) ) ? $theme['td_color1'] : $theme['td_color2']);
			$row_class = ( !($count % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
			$count++;
			
			$ip_address = decode_ip($onlinerow[$i]['session_ip']);
			// 
			// 	This resolves the users IP to a host name, but it REALLY slows the page down
			//
			//$host_name = gethostbyaddr($ip_address);
			//$ip_address = $ip_address . " ($host_name)";
			
			if(empty($username))
			{
				$username = $lang['Guest'];
			}

			$template->assign_block_vars("userrow", array(
				"ROW_COLOR" => $row_color,
				"ROW_CLASS" => $row_class,
				"USERNAME" => $username,
				"LOGGED_ON" => $logged_on,
				"LASTUPDATE" => create_date($board_config['default_dateformat'], $onlinerow[$i]['session_time'], $board_config['default_timezone']),
				"LOCATION" => $location,
				"IPADDRESS" => $ip_address,
				"U_USER_PROFILE" => append_sid("admin_user.$phpEx?" . POST_USERS_URL . "=" . $onlinerow[$i]['user_id']),
				"U_FORUM_LOCATION" => append_sid($location_url))
			);
		}
	}
	$template->assign_vars(array("L_USERNAME" => $lang['Username'],
		"L_LOCATION" => $lang['Location'],
		"L_LAST_UPDATE" => $lang['Last_updated'],
		"L_IPADDRESS" => $lang['IP_Address'])
	);

	$template->pparse("body");

	include('page_footer_admin.'.$phpEx);

}
else
{ 
	//
	// Generate frameset
	//
	$template->set_filenames(array(
		"body" => "admin/index_frameset.tpl")
	);

	$template->assign_vars(array(
		"S_FRAME_NAV" => "index.$phpEx?pane=left",
		"S_FRAME_MAIN" => "index.$phpEx?pane=right")
	);

	$template->pparse("body");
	
	exit;

}

?>