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
	global $lang, $db;

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
			FROM " . FORUMS_TABLE . "
			ORDER BY cat_id, forum_order";
		if(!$q_forums = $db->sql_query($sql))
		{
			message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
		}
		$total_forums = $db->sql_numrows($q_forums);
		$forum_rows = $db->sql_fetchrowset($q_forums);

//		$is_auth_ary = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

		$boxstring = '<select name="' . POST_FORUM_URL . '"><option value="-1">' . $lang['Select_forum'] . '</option>';
		for($i = 0; $i < $total_categories; $i++)
		{
			$boxstring .= '<option value="-1">&nbsp;</option>';
			$boxstring .= '<option value="-1">' . $category_rows[$i]['cat_title'] . '</option>';
			$boxstring .= '<option value="-1">----------------</option>';

			if($total_forums)
			{
				for($y = 0; $y < $total_forums; $y++)
				{
					if(  $forum_rows[$y]['cat_id'] == $category_rows[$i]['cat_id'] )
					{
						$boxstring .=  '<option value="' . $forum_rows[$y]['forum_id'] . '">' . $forum_rows[$y]['forum_name'] . '</option>';
					}
				}
			}
			else
			{
				$boxstring .= '<option value="-1">-- ! No Forums ! --</option>';
			}
		}
		$boxstring .= '</select>';
	}
	else
	{
		$boxstring .= '<select><option value="-1">-- ! No Categories ! --</option></select>';
	}

	return($boxstring);
}

//
// Simple version of jumpbox, just lists authed forums
//
function make_forum_select($box_name)
{
	global $db, $userdata;

	$is_auth_ary = auth(AUTH_READ, AUTH_LIST_ALL, $userdata);

	$sql = "SELECT forum_id, forum_name
		FROM " . FORUMS_TABLE . " 
		ORDER BY cat_id, forum_order";
	if( !$q_forums = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
	}

	$forum_list = "";
	while( $row = $db->sql_fetchrow($q_forums) )
	{
		if( $is_auth_ary[$row['forum_id']]['auth_read'] )
		{
			$forum_list .= "<option value=\"" . $row['forum_id'] . "\">" . $row['forum_name'] . "</option>";
		}
	}

	if( $forum_list == "" )
	{
		$forum_list .= "<option value=\"-1\">-- ! No Forums ! --</option>\n";
	}
	else
	{
		$forum_list = '<select name="' . $box_name . '">' . $forum_list . '</select>';
	}

	return($forum_list);
}

//
// Initialise user settings on page load
function init_userprefs($userdata)
{
	global $board_config, $theme, $images, $template, $lang, $phpEx, $phpbb_root_path;

//	if( !defined("IN_ADMIN") )
//	{
		if( !$board_config['override_user_style'] )
		{
			if( $userdata['user_id'] != ANONYMOUS && isset($userdata['user_style']) )
			{
				$theme = setup_style($userdata['user_style']);
				if( !$theme )
				{
					$theme = setup_style($board_config['default_style']);
				}
			}
			else
			{
				$theme = setup_style($board_config['default_style']);
			}
		}
		else
		{
			$theme = setup_style($board_config['default_style']);
		}
//	}
//	else
//	{
//		$theme = setup_style($board_config['default_admin_style']);
//	}

	if( $userdata['user_id'] != ANONYMOUS )
	{
		if( !empty($userdata['user_lang']))
		{
			$board_config['default_lang'] = $userdata['user_lang'];
		}

		if(!empty($userdata['user_dateformat']))
		{
			$board_config['default_dateformat'] = $userdata['user_dateformat'];
		}

		if(isset($userdata['user_timezone']))
		{
			$board_config['board_timezone'] = $userdata['user_timezone'];
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

function setup_style($style)
{
	global $db, $board_config, $template, $images, $phpbb_root_path;

	$sql = "SELECT *
		FROM " . THEMES_TABLE . "
		WHERE themes_id = $style";
	if(!$result = $db->sql_query($sql))
	{
		message_die(CRITICAL_ERROR, "Couldn't query database for theme info.");
	}

	if( !$row = $db->sql_fetchrow($result) )
	{
		message_die(CRITICAL_ERROR, "Couldn't get theme data for themes_id=$style.");
	}

//	$template_path = ( defined('IN_ADMIN') ) ? 'admin/templates/' : 'templates/' ;
//	$template_name = ( defined('IN_ADMIN') ) ? $board_config['board_admin_template'] : $myrow['template_name'] ;
	$template_path = 'templates/' ;
	$template_name = $row['template_name'] ;

	$template = new Template($phpbb_root_path . $template_path . $template_name);

	if( $template )
	{
		@include($phpbb_root_path . $template_path . $template_name . '/' . $template_name . '.cfg');

		if( !defined("TEMPLATE_CONFIG") )
		{
			message_die(CRITICAL_ERROR, "Couldn't open $template_name template config file");
		}

	}

	return($row);
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
	return (@gmdate($format, $gmepoch + (3600 * $tz)));
}

//
// Create a GMT timestamp
//
function get_gmt_ts()
{
	$time = @time();
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
		$page_string .= ($i == $on_page) ? "<b>$i</b>" : "<a href=\"".append_sid($base_url . "&amp;start=" . (($i - 1) * $per_page)) . "\">$i</a>";
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
			$page_string_prepend .= "<a href=\"" . append_sid($base_url . "&amp;start=" . ($i * $per_page)) . "\">" . ( ($i == 0) ? ($i + 1) : $i) . " - " . ($i + 9) . "</a>, ";
		}

		$page_string = $page_string_prepend . $page_string;
	}

	if($this_block_end < $total_pages)
	{
		$page_string_append = ", ";

		if(!($total_pages%10))
		{
			$page_url = append_sid($base_url."&amp;start=".( ( ($this_block_end + 1) * $per_page ) - $per_page ) );
			$page_string_append .= "<a href=\"$page_url\">$total_pages</a>";
		}
		else
		{

			for($i = $this_block_end + 1; $i < $total_pages; $i += 10)
			{
				$page_string_append .= "<a href=\"" . append_sid($base_url . "&amp;start=" . (($i * $per_page) - $per_page)) . "\">" . ( ($i == 0) ? ($i + 1) : $i) . " - " . ((($i + 9) < $total_pages) ? ($i + 9) : $total_pages) . "</a>";
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
			$page_string = " <a href=\"" . append_sid($base_url . "&amp;start=" . (($on_page - 2) * $per_page)) . "\">" . $lang['Previous'] . "</a>&nbsp;&nbsp;" . $page_string;
		}
		if($on_page < $total_pages)
		{
			$page_string .= "&nbsp;&nbsp;<a href=\"" . append_sid($base_url . "&amp;start=" . ($on_page * $per_page)) . "\">" . $lang['Next'] . "</a>";
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

	$sql = "SELECT word 
		FROM  " . WORDS_TABLE;
	if( !$words_result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get censored words from database.", "", __LINE__, __FILE__, $sql);
	}
	else
	{
		$word_list = $db->sql_fetchrowset($words_result);

		for($i = 0; $i < count($word_list); $i++)
		{
			if( preg_match("/\b(" . str_replace("\*", "\w*?", preg_quote($word_list[$i]['word'])) . ")\b/i", $username) )
			{
				return(FALSE);
			}
		}
	}

	return(TRUE);
}


//
// Synchronise functions for forums/topics
//
function sync($type, $id)
{
	global $db;

	switch($type)
	{
		case 'all forums':
			$sql = "SELECT forum_id
				FROM " . FORUMS_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get forum IDs", "Error", __LINE__, __FILE__, $sql);
			}
			$rowset = $db->sql_fetchrowset($result);

			for($i = 0; $i < count($rowset); $i++)
			{
   				sync("forum", $row[$i]['forum_id']);
	   		}
		   	break;

		case 'all topics':
			$sql = "SELECT topic_id
				FROM " . TOPICS_TABLE;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get topic ID's", "Error", __LINE__, __FILE__, $sql);
			}
			$rowset = $db->sql_fetchrowset($result);

			for($i = 0; $i < count($rowset); $i++)
			{
				sync("topic", $row[$i]['topic_id']);
			}
			break;

	  	case 'forum':
			$sql = "SELECT MAX(p.post_id) AS last_post
				FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t
				WHERE p.forum_id = $id
					AND p.topic_id = t.topic_id
					AND t.topic_status <> " . TOPIC_MOVED;
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post ID", "Error", __LINE__, __FILE__, $sql);
			}

			if( $row = $db->sql_fetchrow($result) )
			{
				$last_post = ($row['last_post']) ? $row['last_post'] : 0;
			}
			else
			{
				$last_post = 0;
			}

			$sql = "SELECT COUNT(post_id) AS total
				FROM " . POSTS_TABLE . "
				WHERE forum_id = $id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post count", "Error", __LINE__, __FILE__, $sql);
			}

			if( $row = $db->sql_fetchrow($result) )
			{
				$total_posts = ($row['total']) ? $row['total'] : 0;
			}
			else
			{
				$total_posts = 0;
			}

			$sql = "SELECT COUNT(topic_id) AS total
				FROM " . TOPICS_TABLE . "
				WHERE forum_id = $id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get topic count", "Error", __LINE__, __FILE__, $sql);
			}

			if( $row = $db->sql_fetchrow($result) )
			{
				$total_topics = ($row['total']) ? $row['total'] : 0;
			}
			else
			{
				$total_topics = 0;
			}

			$sql = "UPDATE " . FORUMS_TABLE . "
				SET forum_last_post_id = $last_post, forum_posts = $total_posts, forum_topics = $total_topics
				WHERE forum_id = $id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not update forum $id", "Error", __LINE__, __FILE__, $sql);
			}
			break;

		case 'topic':
			$sql = "SELECT MAX(post_id) AS last_post
				FROM " . POSTS_TABLE . "
				WHERE topic_id = $id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post ID", "Error", __LINE__, __FILE__, $sql);
			}

			if( $row = $db->sql_fetchrow($result) )
			{
				$last_post = ($row['last_post']) ? $row['last_post'] : 0;
			}
			else
			{
				$last_post = 0;
			}

			$sql = "SELECT COUNT(post_id) AS total
				FROM " . POSTS_TABLE . "
				WHERE topic_id = $id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post count", "Error", __LINE__, __FILE__, $sql);
			}

			if( $row = $db->sql_fetchrow($result) )
			{
				$total_posts = ($row['total']) ? $row['total'] - 1 : 0;
			}
			else
			{
				$total_posts = 0;
			}

			$sql = "UPDATE " . TOPICS_TABLE . "
				SET topic_replies = $total_posts, topic_last_post_id = $last_post
				WHERE topic_id = $id";
			if( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not update topic $id", "Error", __LINE__, __FILE__, $sql);
			}
			break;
	}

   return(TRUE);

}


//
// Pick a language, any language ...
//
function language_select($default, $select_name = "language", $dirname="language/")
{
	global $phpEx;

	$dir = opendir($dirname);

	$lang_select = "<select name=\"$select_name\">";
	while ($file = readdir($dir))
	{
		if (ereg("^lang_", $file))
		{
			$filename = str_replace("lang_", "", $file);
			$filename = str_replace(".$phpEx", "", $filename);
			$displayname = preg_replace("/(.*)_(.*)/", "\\1 [ \\2 ]", $filename);
			$selected = (strtolower($default) == strtolower($filename)) ? " selected=\"selected\"" : "";
			$lang_select .= "<option value=\"$filename\"$selected>".ucwords($displayname)."</option>";
		}
	}
	$lang_select .= "</select>";

	closedir($dir);

	return $lang_select;
}


//
// Pick a template/theme combo, personally recommend
// PSO - Blue but then I would ...
//
function style_select($default_style, $select_name = "style", $dirname = "templates")
{
	global $db;

	$sql = "SELECT themes_id, style_name
		FROM " . THEMES_TABLE . "
		ORDER BY template_name, themes_id";
	if( !$result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't query themes table", "", __LINE__, __FILE__, $sql);
	}

	$template_style = $db->sql_fetchrowset($result);
	
	$style_select = "<select name=\"$select_name\">";
	for($i = 0; $i < count($template_style); $i++)
	{
		$selected = ( $template_style[$i]['themes_id'] == $default_style ) ? " selected=\"selected\"" : "";

		$style_select .= "<option value=\"" . $template_style[$i]['themes_id'] . "\"$selected>" . $template_style[$i]['style_name'] . "</option>";
	}
	$style_select .= "</select>";

	return($style_select);
}


//
// Pick a timezone
//
function tz_select($default, $select_name = 'timezone')
{
	global $sys_timezone;

	if(!isset($default))
	{
		$default == $sys_timezone;
	}
	$tz_select = "<select name=\"$select_name\">";
	$tz_array = array(
			"-12"		=> "(GMT -12:00 hours) Eniwetok, Kwajalein",
			"-11"		=> "(GMT -11:00 hours) Midway Island, Samoa",
			"-10"		=> "(GMT -10:00 hours) Hawaii",
			"-9"		=> "(GMT -9:00 hours) Alaska",
			"-8"		=> "(GMT -8:00 hours) Pacific Time (US &amp; Canada)",
			"-7"		=> "(GMT -7:00 hours) Mountain Time (US &amp; Canada)",
			"-6"		=> "(GMT -6:00 hours) Central Time (US &amp; Canada), Mexico City",
			"-5"		=> "(GMT -5:00 hours) Eastern Time (US &amp; Canada), Bogota, Lima, Quito",
			"-4"		=> "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz",
			"-3.5"		=> "(GMT -3:30 hours) Newfoundland",
			"-3"		=> "(GMT -3:00 hours) Brazil, Buenos Aires, Georgetown",
			"-2"		=> "(GMT -2:00 hours) Mid-Atlantic, Ascension Is., St. Helena, ",
			"-1"		=> "(GMT -1:00 hours) Azores, Cape Verde Islands",
			"0"			=> "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia",
			"+1"		=> "(GMT +1:00 hours) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome",
			"+2"		=> "(GMT +2:00 hours) Kaliningrad, South Africa, Warsaw",
			"+3"		=> "(GMT +3:00 hours) Baghdad, Riyadh, Moscow, Nairobi",
			"+3.5"		=> "(GMT +3:30 hours) Tehran",
			"+4"		=> "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi",
			"+4.5"		=> "(GMT +4:30 hours) Kabul",
			"+5"		=> "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karachi, Tashkent",
			"+5.5"		=> "(GMT +5:30 hours) Bombay, Calcutta, Madras, New Delhi",
			"+6"		=> "(GMT +6:00 hours) Almaty, Colombo, Dhaka",
			"+7"		=> "(GMT +7:00 hours) Bangkok, Hanoi, Jakarta",
			"+8"		=> "(GMT +8:00 hours) Beijing, Hong Kong, Perth, Singapore, Taipei",
			"+9"		=> "(GMT +9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Yakutsk",
			"+9.5"		=> "(GMT +9:30 hours) Adelaide, Darwin",
			"+10"		=> "(GMT +10:00 hours) Melbourne, Papua New Guinea, Sydney, Vladivostok",
			"+11"		=> "(GMT +11:00 hours) Magadan, New Caledonia, Solomon Islands",
			"+12"		=> "(GMT +12:00 hours) Auckland, Wellington, Fiji, Marshall Island");

	while( list($offset, $zone) = each($tz_array) )
	{
		$selected = ($offset == $default) ? " selected=\"selected\"" : "";
		$tz_select .= "\t<option value=\"$offset\"$selected>$zone</option>";
	}
	$tz_select .= "</select>";

	return($tz_select);
}


//
// Smilies code ... would this be better tagged on to the end of bbcode.php?
// Probably so and I'll move it before B2
//
function smilies_pass($message)
{
	global $db, $board_config;
	static $smilies;

	if(empty($smilies))
	{
		$sql = "SELECT code, smile_url
			FROM " . SMILIES_TABLE;
		if($result = $db->sql_query($sql))
		{
			$smilies = $db->sql_fetchrowset($result);
		}
	}
	usort($smilies, 'smiley_sort');
	for($i = 0; $i < count($smilies); $i++)
	{
		$orig[] = "'(?<=.\\W|\\W.|^\\W)" . preg_quote($smilies[$i]['code']) . "(?=.\\W|\\W.|\\W$)'i";
		$repl[] = '<img src="'. $board_config['smilies_path'] . '/' . $smilies[$i]['smile_url'] . '" alt="' . $smilies[$i]['smile_url'] . '" border="0">';
	}

	if($i > 0)
	{
		$message = preg_replace($orig, $repl, ' ' . $message . ' ');
		$message = substr($message, 1, -1);
	}
	return($message);
}
function smiley_sort($a, $b)
{
	if (strlen($a['code']) == strlen($b['code']))
	{
		return 0;
	}
	return (strlen($a['code']) > strlen($b['code'])) ? -1 : 1;
}


//
// Obtain list of naughty words and build preg style replacement arrays for use by the
// calling script, note that the vars are passed as references this just makes it easier
// to return both sets of arrays
//
function obtain_word_list(&$orig_word, &$replacement_word)
{
	global $db;

	//
	// Define censored word matches
	//
	$sql = "SELECT word, replacement
		FROM  " . WORDS_TABLE;
	if( !$words_result = $db->sql_query($sql) )
	{
		message_die(GENERAL_ERROR, "Couldn't get censored words from database.", "", __LINE__, __FILE__, $sql);
	}
	else
	{
		$word_list = $db->sql_fetchrowset($words_result);

		$orig_word = array();
		$replacement_word = array();

		for($i = 0; $i < count($word_list); $i++)
		{
			$word = str_replace("\*", "\w*?", preg_quote($word_list[$i]['word']));

			$orig_word[] = "/\b(" . $word . ")\b/i";
			$replacement_word[] = $word_list[$i]['replacement'];
		}
	}

	return(TRUE);
}

//
// This function gets called to output any message or error
// that doesn't require additional output from the calling
// page.
//
// $msg_code takes one of four constant values:
//
// GENERAL_MESSAGE -> Use for any simple text message, eg.
// results of an operation, authorisation failures, etc.
//
// GENERAL ERROR -> Use for any error which occurs _AFTER_
// the common.php include and session code, ie. most errors
// in pages/functions
//
// CRITICAL_MESSAGE -> Only currently used to announce a user
// has been banned, can be used where session results cannot
// be relied upon to exist but we can and do assume that basic
// board configuration data is available
//
// CRITICAL_ERROR -> Used whenever a DB connection cannot be
// guaranteed and/or we've been unable to obtain basic board
// configuration data. Shouldn't be used in general
// pages/functions (it results in a simple echo'd statement,
// no templates are used)
//
function message_die($msg_code, $msg_text = "", $msg_title = "", $err_line = "", $err_file = "", $sql = "")
{
	global $db, $template, $board_config, $theme, $lang, $phpEx, $phpbb_root_path;
	global $userdata, $user_ip, $session_length;
	global $starttime;

	$sql_store = $sql;

	if( empty($userdata) && ( $msg_code == GENERAL_MESSAGE || $msg_code == GENERAL_ERROR ) )
	{
		$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
		init_userprefs($userdata);
	}

	//
	// If the header hasn't been output then do it
	//
	if( !defined("HEADER_INC") && $msg_code != CRITICAL_ERROR )
	{
		if( empty($lang) )
		{
			if( !empty($board_config['default_lang']) )
			{
				include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '.'.$phpEx);
			}
			else
			{
				include($phpbb_root_path . 'language/lang_english.'.$phpEx);
			}
		}

		if( empty($template) )
		{
			$template = new Template($phpbb_root_path . "templates/" . $board_config['board_template']);
		}

		if( empty($theme) )
		{
			$theme = setuptheme($board_config['default_theme']);
		}

		//
		// Load the Page Header
		//
		if( !defined("IN_ADMIN") )
		{
			include($phpbb_root_path . 'includes/page_header.'.$phpEx);
		}
		else
		{
			include($phpbb_root_path . 'admin/page_header_admin.'.$phpEx);
		}
	}

	switch($msg_code)
	{
		case GENERAL_MESSAGE:
			if($msg_title == "")
			{
				$msg_title = $lang['Information'];
			}
			break;

		case CRITICAL_MESSAGE:
			if($msg_title == "")
			{
				$msg_title = $lang['Critical_Information'];
			}
			break;

		case GENERAL_ERROR:
			if($msg_text == "")
			{
				$msg_text = $lang['An_error_occured'];
			}

			if($msg_title == "")
			{
				$msg_title = $lang['General_Error'];
			}

		case CRITICAL_ERROR:
			//
			// Critical errors mean we cannot rely on _ANY_ DB information being
			// available so we're going to dump out a simple echo'd statement
			//
			include($phpbb_root_path . 'language/lang_english.'.$phpEx);

			if($msg_text == "")
			{
				$msg_text = $lang['A_critical_error'];
			}

			if($msg_title == "")
			{
				$msg_title = "phpBB : <b>" . $lang['Critical_Error'] . "</b>";
			}
			break;
	}

	//
	// Add on DEBUG info if we've enabled debug mode and this is an error. This
	// prevents debug info being output for general messages should DEBUG be
	// set TRUE by accident (preventing confusion for the end user!)
	//
	if(DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
	{
		$sql_error = $db->sql_error();

		$debug_text = "";

		if($sql_error['message'] != "")
		{
			$debug_text .= "<br /><br />SQL Error : " . $sql_error['code'] . " " . $sql_error['message'];
		}

		if($sql_store != "")
		{
			$debug_text .= "<br /><br />$sql_store";
		}

		if($err_line != "" && $err_file != "")
		{
			$debug_text .= "</br /><br />Line : " . $err_line . "<br />File : " . $err_file;
		}

		if($debug_text != "")
		{
			$msg_text = $msg_text . "<br /><br /><b><u>DEBUG MODE</u></b>" . $debug_text;
		}
	}

	if( $msg_code != CRITICAL_ERROR )
	{
		if( !empty($lang[$msg_text]) )
		{
			$msg_text = $lang[$msg_text];
		}

		$template->set_filenames(array(
			"message_body" => "message_body.tpl")
		);
		$template->assign_vars(array(
			"MESSAGE_TITLE" => $msg_title,
			"MESSAGE_TEXT" => $msg_text)
		);
		$template->pparse("message_body");

		if( !defined("IN_ADMIN") )
		{
			include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
		}
		else
		{
			include($phpbb_root_path . 'admin/page_footer_admin.'.$phpEx);
		}
	}
	else
	{
		echo "<html>\n<body>\n" . $msg_title . "\n<br /><br />\n" . $msg_text . "</body>\n</html>";
	}

	exit;

}

?>