<?php
/***************************************************************************
 *                               functions.php
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

function get_db_stat($mode)
{
	global $db;

	switch($mode)
	{
		case 'postcount':
			$sql = "SELECT COUNT(post_id) AS total
				FROM ".POSTS_TABLE;
			break;

		case 'usercount':
			$sql = "SELECT COUNT(user_id) AS total
				FROM ". USERS_TABLE ."
				WHERE user_id <> " . ANONYMOUS;
			break;

		case 'newestuser':
			$sql = "SELECT user_id, username
				FROM ".USERS_TABLE."
				WHERE user_id <> " . ANONYMOUS . "
				ORDER BY user_id DESC
				LIMIT 1";
			break;

		case 'topiccount':
			$sql = "SELECT SUM(forum_topics) AS total
				FROM ".FORUMS_TABLE;
			break;
	}

	if(!$result = $db->sql_query($sql))
	{
		return 'ERROR';
	}
	else
	{
		$row = $db->sql_fetchrow($result);
		if($mode == 'newestuser')
		{
			return($row);
		}
		else
		{
			return($row['total']);
		}
	}
}

function get_userdata_from_id($userid)
{
	global $db;

	$sql = "SELECT *
		FROM " . USERS_TABLE . "
		WHERE user_id = $userid";
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain userdata for id", "", __LINE__, __FILE__, $sql);
	}

	if($db->sql_numrows($result))
	{
		$myrow = $db->sql_fetchrowset($result);
		return($myrow[0]);
	}
	else
	{
		message_die(GENERAL_ERROR, "No userdata for this user_id", "", __LINE__, __FILE__, $sql);
	}
}

function get_userdata($username) {

	global $db;

	$sql = "SELECT *
		FROM " . USERS_TABLE . "
		WHERE username = '$username'
			AND user_id <> " . ANONYMOUS;
	if(!$result = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Tried obtaining data for a non-existent user", "", __LINE__, __FILE__, $sql);
	}

	if($db->sql_numrows($result))
	{
		$myrow = $db->sql_fetchrowset($result);
		return($myrow[0]);
	}
	else
	{
		message_die(GENERAL_ERROR, "Tried obtaining data for a non-existent user", "", __LINE__, __FILE__, $sql);
	}
}

function make_jumpbox()
{
	global $db;
	global $l_jumpto, $l_noforums, $l_nocategories;

	$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
		FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
		WHERE f.cat_id = c.cat_id
		GROUP BY c.cat_id, c.cat_title, c.cat_order
		ORDER BY c.cat_order";
	if(!$q_categories = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain category list.", "", __LINE__, __FILE__, $sql);
	}

	$total_categories = $db->sql_numrows();
	if($total_categories)
	{
		$category_rows = $db->sql_fetchrowset($q_categories);

		$limit_forums = "";

		$sql = "SELECT *
			FROM ".FORUMS_TABLE."
			ORDER BY cat_id, forum_order";
		if(!$q_forums = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
		}
		$total_forums = $db->sql_numrows($q_forums);
		$forum_rows = $db->sql_fetchrowset($q_forums);

//		$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

		$boxstring = '';
		for($i = 0; $i < $total_categories; $i++)
		{
			$boxstring .= "<option value=\"-1\">&nbsp;</option>\n";
			$boxstring .= "<option value=\"-1\">".stripslashes($category_rows[$i]['cat_title'])."</option>\n";
			$boxstring .= "<option value=\"-1\">----------------</option>\n";

			if($total_forums)
			{
				for($y = 0; $y < $total_forums; $y++)
				{
					if(  $forum_rows[$y]['cat_id'] == $category_rows[$i]['cat_id'] )
					{
						$name = stripslashes($forum_rows[$y]['forum_name']);
						$boxstring .=  "<option value=\"".$forum_rows[$y]['forum_id']."\">$name</option>\n";
					}
				}
			}
			else
			{
				$boxstring .= "<option value=\"-1\">-- ! No Forums ! --</option>\n";
			}
		}
	}
	else
	{
		$boxstring .= "<option value=\"-1\">-- ! No Categories ! --</option>\n";
	}

	return($boxstring);
}

function make_forum_box($box_name, $default_forum = -1)
{
	global $db; 
	
	$limit_forums = "";

	$sql = "SELECT forum_id, forum_name
		FROM ".FORUMS_TABLE."
		ORDER BY cat_id, forum_order";
	if(!$q_forums = $db->sql_query($sql))
	{
		message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
	}
	$total_forums = $db->sql_numrows($q_forums);
	$forum_rows = $db->sql_fetchrowset($q_forums);

	$boxstring = '<select name="'.$box_name.'">';
	if($total_forums)
	{
		for($y = 0; $y < $total_forums; $y++)
		{
			$name = stripslashes($forum_rows[$y]['forum_name']);
			$boxstring .=  "<option value=\"".$forum_rows[$y]['forum_id']."\"";
			if($forum_rows[$y]['forum_id'] == $default_forum)
			{
				$boxstring .= " SELECTED";
			}
			$boxstring .= ">$name</option>\n";
		}
	}
	else
	{
		$boxstring .= "<option value=\"-1\">-- ! No Forums ! --</option>\n";
	}

	return($boxstring);
}	
//
// Initialise user settings on page load
function init_userprefs($userdata)
{
	global $board_config, $theme, $images, $template, $lang, $phpEx, $phpbb_root_path;

	if(!$board_config['override_user_themes'])
	{
		if( $userdata['user_id'] != ANONYMOUS && isset($userdata['user_theme']) )
		{
			$theme = setuptheme($userdata['user_theme']);
			if($theme == FALSE)
			{
				$theme = setuptheme($board_config['default_theme']);
			}
		}
		else
		{
			$theme = setuptheme($board_config['default_theme']);
		}
	}
	else
	{
		$theme = setuptheme($board_config['override_user_themes']);
	}

	if( $userdata['user_id'] != ANONYMOUS )
	{
		if(!empty($userdata['user_lang']))
		{
			$board_config['default_lang'] = $userdata['user_lang'];
		}

		if(!empty($userdata['user_dateformat']))
		{
			$board_config['default_dateformat'] = $userdata['user_dateformat'];
		}

		if(isset($userdata['user_timezone']))
		{
			$board_config['default_timezone'] = $userdata['user_timezone'];
		}

		if(!empty($userdata['user_template']))
		{
			$board_config['default_template'] = $userdata['user_template'];
		}
	}

	$template = new Template($phpbb_root_path . "templates/" . $board_config['default_template']);

	if($template)
	{
		@include($phpbb_root_path . "templates/" . $board_config['default_template'] . "/" . $board_config['default_template'] . ".cfg");

		if( !defined("TEMPLATE_CONFIG") )
		{
			message_die(CRITICAL_MESSAGE, "Couldn't open " . $board_config['default_template'] . " template config file");
		}

	}

	if(file_exists("language/lang_".$board_config['default_lang'].".".$phpEx) )
	{
		include($phpbb_root_path . 'language/lang_'.$board_config['default_lang'].'.'.$phpEx);
	}
	else
	{
		include($phpbb_root_path . 'language/lang_english.'.$phpEx);
	}

	return;
}

function setuptheme($theme)
{
	global $db;

	$sql = "SELECT *
		FROM " . THEMES_TABLE . "
		WHERE themes_id = $theme";
	if(!$result = $db->sql_query($sql))
	{
		return(FALSE);
	}
	if(!$myrow = $db->sql_fetchrow($result))
	{
		return(FALSE);
	}
	return($myrow);
}

function generate_activation_key()
{
	$chars = array(
		"a","A","b","B","c","C","d","D","e","E","f","F","g","G","h","H","i","I","j","J",
		"k","K","l","L","m","M","n","N","o","O","p","P","q","Q","r","R","s","S","t","T",
		"u","U","v","V","w","W","x","X","y","Y","z","Z","1","2","3","4","5","6","7","8",
		"9","0");

	$max_elements = count($chars) - 1;

	srand((double)microtime()*1000000);

	$act_key = '';
	for($i = 0; $i < 8; $i++)
	{
		$act_key .= $chars[rand(0,$max_elements)];
	}
	$act_key_md = md5($act_key);

	return($act_key_md);
}

function encode_ip($dotquad_ip)
{
	$ip_sep = explode(".", $dotquad_ip);
	return (sprintf("%02x%02x%02x%02x", $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]));

//	return (( $ip_sep[0] * 0xFFFFFF + $ip_sep[0] ) + ( $ip_sep[1] *   0xFFFF + $ip_sep[1] ) + ( $ip_sep[2] *     0xFF + $ip_sep[2] ) + ( $ip_sep[3] ) );
}

function decode_ip($int_ip)
{
	$hexipbang = explode(".",chunk_split($int_ip, 2, "."));
	return hexdec($hexipbang[0]).".".hexdec($hexipbang[1]).".".hexdec($hexipbang[2]).".".hexdec($hexipbang[3]);

//	return sprintf( "%d.%d.%d.%d", ( ( $int_ip >> 24 ) & 0xFF ), ( ( $int_ip >> 16 ) & 0xFF ), ( ( $int_ip >>  8 ) & 0xFF ), ( ( $int_ip       ) & 0xFF ) );
}

//
// Create date/time from format and timezone
//
function create_date($format, $gmepoch, $tz)
{
	return (gmdate($format, $gmepoch + (3600 * $tz)));
}

//
// Create a GMT timestamp
//
function get_gmt_ts()
{
	$time = time();
	return($time);
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{
	global $lang;

	$total_pages = ceil($num_items/$per_page);
	if($total_pages == 1)
	{
		return "";
	}

	$on_page = floor($start_item/$per_page) + 1;

	$page_string = "";

	$this_block_start = ($on_page < 10) ? 1 : floor($on_page/10) * 10;
	$this_block_end = ($on_page < 10) ? 9 : $this_block_start + 9;
	if($this_block_end > $total_pages)
	{
		$this_block_end = $total_pages;
	}

	for($i = $this_block_start; $i <= $this_block_end; $i++)
	{
		$page_string .= ($i == $on_page) ? "<b>$i</b>" : "<a href=\"".append_sid($base_url . "&start=" . (($i - 1) * $per_page)) . "\">$i</a>";
		if($i <  $this_block_end)
		{
			$page_string .= ", ";
		}
	}

	if($this_block_start > 1)
	{
		$page_string_prepend = "";
		for($i = 0; $i < $this_block_start; $i += 10)
		{
			$page_string_prepend .= "<a href=\"" . append_sid($base_url . "&start=" . ($i * $per_page)) . "\">" . ( ($i == 0) ? ($i + 1) : $i) . " - " . ($i + 9) . "</a>, ";
		}

		$page_string = $page_string_prepend . $page_string;
	}

	if($this_block_end < $total_pages)
	{
		$page_string_append = ", ";

		if(!($total_pages%10))
		{
			$page_url = append_sid($base_url."&start=".( ( ($this_block_end + 1) * $per_page ) - $per_page ) );
			$page_string_append .= "<a href=\"$page_url\">$total_pages</a>";
		}
		else
		{

			for($i = $this_block_end + 1; $i < $total_pages; $i += 10)
			{
				$page_string_append .= "<a href=\"" . append_sid($base_url . "&start=" . (($i * $per_page) - $per_page)) . "\">" . ( ($i == 0) ? ($i + 1) : $i) . " - " . ((($i + 9) < $total_pages) ? ($i + 9) : $total_pages) . "</a>";
				if($i < $total_pages - 10)
				{
					$page_string_append .= ", ";
				}
			}
		}
		$page_string .= $page_string_append;
	}

	if($add_prevnext_text)
	{
		if($on_page > 1)
		{
			$page_string = " <a href=\"" . append_sid($base_url . "&start=" . (($on_page - 2) * $per_page)) . "\">" . $lang['Previous'] . "</a>&nbsp;&nbsp;" . $page_string;
		}
		if($on_page < $total_pages)
		{
			$page_string .= "&nbsp;&nbsp;<a href=\"" . append_sid($base_url . "&start=" . ($on_page * $per_page)) . "\">" . $lang['Next'] . "</a>";
		}

		$page_string = $lang['Goto_page'] . ": " . $page_string;

	}

	return $page_string;

}

//
// Check to see if the username has been taken, or if it is disallowed.
// Used for registering, changing names, and posting anonymously with a username
//
function validate_username($username)
{

	global $db;

	switch(SQL_LAYER)
	{
		// Along with subqueries MySQL also lacks
		// a UNION clause which would be very nice here :(
		// So we have to use two queries
		case 'mysql':
			$sql_users = "SELECT u.username, g.group_name
				FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
				WHERE ug.user_id = u.user_id 
					AND g.group_id = ug.group_id 
					AND	( LOWER(u.username) = '" . strtolower($username) . "' 
						OR LOWER(g.group_name) = '" . strtolower($username) . "' )";
			$sql_disallow = "SELECT disallow_username
				FROM " . DISALLOW_TABLE . "
				WHERE disallow_username = '$username'";
			if($result = $db->sql_query($sql_users))
			{
				if($db->sql_numrows($result) > 0)
				{
					return(FALSE);
				}
			}
			if($result = $db->sql_query($sql_disallow))
			{
				if($db->sql_numrows($result) > 0)
				{
					return(FALSE);
				}
			}
			break;

		default:
			$sql = "SELECT u.username, g.group_name
				FROM " . USERS_TABLE . " u, " . GROUPS_TABLE . " g, " . USER_GROUP_TABLE . " ug 
				WHERE ug.user_id = u.user_id 
					AND g.group_id = ug.group_id 
					AND	( LOWER(u.username) = '" . strtolower($username) . "' 
						OR LOWER(g.group_name) = '" . strtolower($username) . "' )
				UNION 
				SELECT disallow_username, NULL
					FROM " . DISALLOW_TABLE . "
					WHERE disallow_username = '$username'";
			if($result = $db->sql_query($sql))
			{
				if($db->sql_numrows($result) > 0)
				{
					return(FALSE);
				}
			}
			break;
	}

	return(TRUE);
}


function sync($type, $id) 
{
	global $db;
	
   switch($type) 
   {
   	case 'forum':
   		$sql = "SELECT max(post_id) AS last_post FROM ".POSTS_TABLE." WHERE forum_id = $id";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not get post ID", "Error", __LINE__, __FILE__, $sql);
   		}
   		if($rowset = $db->sql_fetchrowset($result))
   		{
   			$last_post = $rowset[0]['last_post'];
   		}
   		
   		$sql = "SELECT count(post_id) AS total FROM ".POSTS_TABLE." WHERE forum_id = $id";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not get post count", "Error", __LINE__, __FILE__, $sql);
   		}
   		if($rowset = $db->sql_fetchrowset($result))
   		{
   			$total_posts = $rowset[0]['total'];
   		}
   		
   		$sql = "SELECT count(topic_id) AS total FROM ".TOPICS_TABLE." WHERE forum_id = $id";
   		if(!$result = $db->sql_query($sql, $db))
   		{
   			message_die(GENERAL_ERROR, "Could not get topic count", "Error", __LINE__, __FILE__, $sql);
   		}
   		if($rowset = $db->sql_fetchrowset($result))
   		{
   			$total_topics = $rowset[0]['total'];
   		}
   		
   		$sql = "UPDATE ".FORUMS_TABLE." SET forum_last_post_id = '$last_post', forum_posts = $total_posts, forum_topics = $total_topics WHERE forum_id = $id";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not update forum $id", "Error", __LINE__, __FILE__, $sql);
   		}
   	break;

   	case 'topic':
   		$sql = "SELECT max(post_id) AS last_post FROM ".POSTS_TABLE." WHERE topic_id = $id";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not get post ID", "Error", __LINE__, __FILE__, $sql);
   		}
   		if($row = $db->sql_fetchrowset($result))
   		{
   			$last_post = $row[0]["last_post"];
   		}
   		
   		$sql = "SELECT count(post_id) AS total FROM ".POSTS_TABLE." WHERE topic_id = $id";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not get post count", "Error", __LINE__, __FILE__, $sql);
   		}
   		if($row = $db->sql_fetchrowset($result))
   		{
   			$total_posts = $row[0]["total"];
   		}
   		$total_posts -= 1;
   		$sql = "UPDATE ".TOPICS_TABLE." SET topic_replies = $total_posts, topic_last_post_id = $last_post WHERE topic_id = $id";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not update topic $id", "Error", __LINE__, __FILE__, $sql);
   		}
   	break;

   	case 'all forums':
   		$sql = "SELECT forum_id FROM ".FORUMS_TABLE;
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not get forum IDs", "Error", __LINE__, __FILE__, $sql);
   		}
     		$rowset = $db->sql_fetchrowset($result);
     		$count = $db->sql_numrows($result);
   		for($i = 0; $i < $count; $i++)
   		{
   			$id = $row[$i]['forum_id'];
   			sync($db, $id, "forum");
   		}
   	break;
   	case 'all topics':
   		$sql = "SELECT topic_id FROM topics";
   		if(!$result = $db->sql_query($sql))
   		{
   			message_die(GENERAL_ERROR, "Could not get topic ID's", "Error", __LINE__, __FILE__, $sql);
     		}
     		$rowset = $db->sql_fetchrowset($result);
     		$count = $db->sql_numrows($result);
   		for($i = 0; $i < $count; $i++)
   		{
   			$id = $row[$i]['topic_id'];
   			sync($db, $id, "topic");
   		}
   	break;
   }
   return(TRUE);
}


?>