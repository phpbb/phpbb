<?php
/***************************************************************************
 *                             (admin) index.php
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

//
// Load default header
//
$phpbb_root_dir = "./../";
$no_page_header = TRUE;
require('pagestart.inc');

//
// Generate relevant output
//
if( $HTTP_GET_VARS['pane'] == 'left' )
{
	$dir = @opendir(".");

	$setmodules = 1;
	while( $file = @readdir($dir) )
	{
		if( preg_match("/^admin_.*?\.php$/", $file) )
		{
			include($file);
		}
	}

	@closedir($dir);

	unset($setmodules);

	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/index_navigate.tpl")
	);

	$template->assign_vars(array(
		"U_FORUM_INDEX" => append_sid("../index.$phpEx"),
		"U_ADMIN_INDEX" => append_sid("index.$phpEx?pane=right"),

		"L_FORUM_INDEX" => $lang['Forum_Index'],
		"L_ADMIN_INDEX" => $lang['Admin_Index'], 
		"L_PREVIEW_FORUM" => $lang['Preview_forum'])
	);

	while( list($cat, $action_array) = each($module) )
	{
		$template->assign_block_vars("catrow", array(
			"ADMIN_CATEGORY" => $cat)
		);

		$row_count = 0;
		while( list($action, $file)	= each($action_array) )
		{
			$row_color = ( !($row_count%2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($row_count%2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$action = preg_replace("'_'", " ", $action);

			$template->assign_block_vars("catrow.modulerow", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class, 

				"ADMIN_MODULE" => $action,
				"U_ADMIN_MODULE" => append_sid($file))
			);
			$row_count++;
		}
	}

	$template->pparse("body");

	include('page_footer_admin.'.$phpEx);
}
elseif( $HTTP_GET_VARS['pane'] == 'right' )
{

	include('page_header_admin.'.$phpEx);

	$template->set_filenames(array(
		"body" => "admin/index_body.tpl")
	);

	$template->assign_vars(array(
		"L_WELCOME" => $lang['Welcome_phpBB'],
		"L_ADMIN_INTRO" => $lang['Admin_intro'],
		"L_FORUM_STATS" => $lang['Forum_stats'],
		"L_WHO_IS_ONLINE" => $lang['Who_is_Online'],
		"L_LOCATION" => $lang['Location'],
		"L_LAST_UPDATE" => $lang['Last_updated'],
		"L_IP_ADDRESS" => $lang['IP_Address'],
		"L_STATISTIC" => $lang['Statistic'],
		"L_VALUE" => $lang['Value'],
		"L_NUMBER_POSTS" => $lang['Number_posts'],
		"L_POSTS_PER_DAY" => $lang['Posts_per_day'],
		"L_NUMBER_TOPICS" => $lang['Number_topics'],
		"L_TOPICS_PER_DAY" => $lang['Topics_per_day'],
		"L_NUMBER_USERS" => $lang['Number_users'],
		"L_USERS_PER_DAY" => $lang['Users_per_day'],
		"L_BOARD_STARTED" => $lang['Board_started'],
		"L_AVATAR_DIR_SIZE" => $lang['Avatar_dir_size'],
		"L_DB_SIZE" => $lang['Database_size'], 
		"L_GZIP_COMPRESSION" => $lang['Gzip_compression'])
	);

	//
	// Get forum statistics
	//
	$total_posts = get_db_stat('postcount');
	$total_users = get_db_stat('usercount');
	$total_topics = get_db_stat('topiccount');

	$start_date = create_date($board_config['default_dateformat'], $board_config['board_startdate'], $board_config['board_timezone']);

	$boarddays = ( time() - $board_config['board_startdate'] ) / 86400;

	$posts_per_day = sprintf("%.2f", $total_posts / $boarddays);
	$topics_per_day = sprintf("%.2f", $total_topics / $boarddays);
	$users_per_day = sprintf("%.2f", $total_users / $boarddays);

	$avatar_dir_size = 0;

	if ($avatar_dir = @opendir($phpbb_root_path . $board_config['avatar_path']))
	{
		while( $file = @readdir($avatar_dir) )
		{
			if( $file != "." && $file != ".." )
			{
				$avatar_dir_size += @filesize($phpbb_root_path . $board_config['avatar_path'] . "/" . $file);
			}
		}
		@closedir($avatar_dir);
	}
	else
	{
		// Couldn't open Avatar dir.
		$avatar_dir_size = $lang['Not_available'];
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
	if( preg_match("/^mysql/", SQL_LAYER) )
	{
		$sql = "SELECT VERSION() AS mysql_version";
		if($result = $db->sql_query($sql))
		{
			$row = $db->sql_fetchrow($result);
			$version = $row['mysql_version'];

			if( preg_match("/^(3\.23|4\.)/", $version) )
			{
				$sql = "SHOW TABLE STATUS 
					FROM " . $dbname;
				if(!$result = $db->sql_query($sql))
				{
					message_die(GENERAL_ERROR, "Couldn't obtain table information.", "", __LINE__, __FILE__, $sql);
				}
				$tabledata_ary = $db->sql_fetchrowset($result);

				$dbsize = 0;
				for($i = 0; $i < count($tabledata_ary); $i++)
				{
					if( $tabledata_ary[$i]['Type'] != "MRG_MyISAM" )
					{
						if( $table_prefix != "" )
						{
							if( strstr($tabledata_ary[$i]['Name'], $table_prefix) )
							{
								$dbsize += $tabledata_ary[$i]['Data_length'] + $tabledata_ary[$i]['Index_length'];
							}
						}
						else
						{
							$dbsize += $tabledata_ary[$i]['Data_length'] + $tabledata_ary[$i]['Index_length'];
						}
					}
				}

				if( $dbsize >= 1048576 )
				{
					$dbsize = sprintf("%.2f MB", ( $dbsize / 1048576 ));
				}
				else if( $dbsize >= 1024 )
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
				$dbsize = $lang['Not_available'];
			}
		}
		else
		{
			$dbsize = $lang['Not_available'];
		}
	}
	else
	{
		$dbsize = $lang['Not_available'];
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
		"DB_SIZE" => $dbsize, 
		"GZIP_COMPRESSION" => ( $board_config['gzip_compress'] ) ? $lang['ON'] : $lang['OFF'])
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

			$row_color = ( !($count % 2) ) ? $theme['td_color1'] : $theme['td_color2'];
			$row_class = ( !($count % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

			$count++;

			$ip_address = decode_ip($onlinerow[$i]['session_ip']);

			if( empty($username) )
			{
				$username = $lang['Guest'];
			}

			$template->assign_block_vars("userrow", array(
				"ROW_COLOR" => "#" . $row_color,
				"ROW_CLASS" => $row_class,
				"USERNAME" => $username,
				"LOGGED_ON" => $logged_on,
				"LASTUPDATE" => create_date($board_config['default_dateformat'], $onlinerow[$i]['session_time'], $board_config['board_timezone']),
				"LOCATION" => $location,
				"IP_ADDRESS" => $ip_address,
				"U_USER_PROFILE" => append_sid("admin_users.$phpEx?" . POST_USERS_URL . "=" . $onlinerow[$i]['user_id']))
			);
		}
	}

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
		"S_FRAME_NAV" => append_sid("index.$phpEx?pane=left"),
		"S_FRAME_MAIN" => append_sid("index.$phpEx?pane=right"))
	);

	header ("Expires: " . gmdate("D, d M Y H:i:s", time()) . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	$template->pparse("body");

	exit;

}

?>