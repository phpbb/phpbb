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
		case 'usercount':
			$sql = "SELECT COUNT(user_id) AS total
				FROM " . USERS_TABLE . "
				WHERE user_id <> " . ANONYMOUS;
			break;

		case 'newestuser':
			$sql = "SELECT user_id, username
				FROM " . USERS_TABLE . "
				WHERE user_id <> " . ANONYMOUS . "
				ORDER BY user_id DESC
				LIMIT 1";
			break;

		case 'postcount':
		case 'topiccount':
			$sql = "SELECT SUM(forum_topics) AS topic_total, SUM(forum_posts) AS post_total
				FROM " . FORUMS_TABLE;
			break;
	}

	if ( !($result = $db->sql_query($sql)) )
	{
		return 'ERROR';
	}

	$row = $db->sql_fetchrow($result);

	switch ( $mode )
	{
		case 'usercount':
			return $row['total'];
			break;
		case 'newestuser':
			return $row;
			break;
		case 'postcount':
			return $row['post_total'];
			break;
		case 'topiccount':
			return $row['topic_total'];
			break;
	}

	return 'ERROR';
}

function get_userdata($user)
{
	global $db;

	$sql = "SELECT *
		FROM " . USERS_TABLE . " 
		WHERE ";
	$sql .= ( ( is_integer($user) ) ? "user_id = $user" : "username = '" .  str_replace("\'", "''", $user) . "'" ) . " AND user_id <> " . ANONYMOUS;
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Tried obtaining data for a non-existent user", "", __LINE__, __FILE__, $sql);
	}

	return ( $row = $db->sql_fetchrow($result) ) ? $row : false;
}

function make_jumpbox($match_forum_id = 0)
{
	global $lang, $db, $SID, $nav_links, $phpEx;

	$sql = "SELECT c.cat_id, c.cat_title, c.cat_order
		FROM " . CATEGORIES_TABLE . " c, " . FORUMS_TABLE . " f
		WHERE f.cat_id = c.cat_id
		GROUP BY c.cat_id, c.cat_title, c.cat_order
		ORDER BY c.cat_order";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain category list.", "", __LINE__, __FILE__, $sql);
	}
	
	$category_rows = array();
	while ( $row = $db->sql_fetchrow($result) )
	{
		$category_rows[] = $row;
	}

	if ( $total_categories = count($category_rows) )
	{
		$sql = "SELECT *
			FROM " . FORUMS_TABLE . "
			ORDER BY cat_id, forum_order";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
		}

		$boxstring = '<select name="' . POST_FORUM_URL . '" onChange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"><option value="-1">' . $lang['Select_forum'] . '</option>';

		$forum_rows = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$forum_rows[] = $row;
		}

		if ( $total_forums = count($forum_rows) )
		{
			for($i = 0; $i < $total_categories; $i++)
			{
				$boxstring_forums = "";
				for($j = 0; $j < $total_forums; $j++)
				{
					if ( $forum_rows[$j]['cat_id'] == $category_rows[$i]['cat_id'] && $forum_rows[$j]['auth_view'] <= AUTH_REG )
					{
						$selected = ( $forum_rows[$j]['forum_id'] == $match_forum_id ) ? 'selected="selected"' : '';
						$boxstring_forums .=  '<option value="' . $forum_rows[$j]['forum_id'] . '"' . $selected . '>' . $forum_rows[$j]['forum_name'] . '</option>';

						//
						// Add an array to $nav_links for the Mozilla navigation bar.
						// 'chapter' and 'forum' can create multiple items, therefore we are using a nested array.
						//
						$nav_links['chapter forum'][$forum_rows[$j]['forum_id']] = array (
							'url' => append_sid("viewforum.$phpEx?" . POST_FORUM_URL . "=" . $forum_rows[$j]['forum_id']),
							'title' => $forum_rows[$j]['forum_name']
						);
								
					}
				}

				if ( $boxstring_forums != "" )
				{
					$boxstring .= '<option value="-1">&nbsp;</option>';
					$boxstring .= '<option value="-1">' . $category_rows[$i]['cat_title'] . '</option>';
					$boxstring .= '<option value="-1">----------------</option>';
					$boxstring .= $boxstring_forums;
				}
			}
		}

		$boxstring .= '</select>';
	}
	else
	{
		$boxstring .= '<select name="' . POST_FORUM_URL . '" onChange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"></select>';
	}

	if ( isset($SID) )
	{
		$boxstring .= '<input type="hidden" name="sid" value="' . $SID . '" />';
	}

	return $boxstring;
}

//
// Simple version of jumpbox, just lists authed forums
//
function make_forum_select($box_name, $ignore_forum = false)
{
	global $db, $userdata;

	$is_auth_ary = auth(AUTH_READ, AUTH_LIST_ALL, $userdata);

	$sql = "SELECT forum_id, forum_name
		FROM " . FORUMS_TABLE . " 
		ORDER BY cat_id, forum_order";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't obtain forums information.", "", __LINE__, __FILE__, $sql);
	}

	$forum_list = '';
	while( $row = $db->sql_fetchrow($result) )
	{
		if ( $is_auth_ary[$row['forum_id']]['auth_read'] && $ignore_forum != $row['forum_id'] )
		{
			$forum_list .= '<option value="' . $row['forum_id'] . '">' . $row['forum_name'] . '</option>';
		}
	}

	$forum_list = ( $forum_list == "" ) ? '<option value="-1">-- ! No Forums ! --</option>' : '<select name="' . $box_name . '">' . $forum_list . '</select>';

	return $forum_list;
}

//
// Initialise user settings on page load
function init_userprefs($userdata)
{
	global $board_config, $theme, $images;
	global $template, $lang, $phpEx, $phpbb_root_path;

	if ( $userdata['user_id'] != ANONYMOUS )
	{
		if ( !empty($userdata['user_lang']))
		{
			$board_config['default_lang'] = $userdata['user_lang'];
		}

		if ( !empty($userdata['user_dateformat']) )
		{
			$board_config['default_dateformat'] = $userdata['user_dateformat'];
		}

		if ( !empty($userdata['user_timezone']) )
		{
			$board_config['board_timezone'] = $userdata['user_timezone'];
		}
	}

	if ( !file_exists($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/lang_main.".$phpEx) )
	{
		$board_config['default_lang'] = "english";
	}

	include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);

	if ( defined("IN_ADMIN") )
	{
		if( !file_exists($phpbb_root_path . "language/lang_" . $board_config['default_lang'] . "/lang_admin.".$phpEx) )
		{
			$board_config['default_lang'] = "english";
		}

		include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.' . $phpEx);
	}

	//
	// Set up style
	//
	if ( !$board_config['override_user_style'] )
	{
		if ( $userdata['user_id'] != ANONYMOUS && isset($userdata['user_style']) )
		{
			if ( $theme = setup_style($userdata['user_style']) )
			{
				return;
			}
		}
	}

	$theme = setup_style($board_config['default_style']);

	return;
}

function setup_style($style)
{
	global $db, $board_config, $template, $images, $phpbb_root_path;

	$sql = "SELECT *
		FROM " . THEMES_TABLE . "
		WHERE themes_id = $style";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(CRITICAL_ERROR, "Couldn't query database for theme info.");
	}

	if ( !($row = $db->sql_fetchrow($result)) )
	{
		message_die(CRITICAL_ERROR, "Couldn't get theme data for themes_id=$style.");
	}

	$template_path = 'templates/' ;
	$template_name = $row['template_name'] ;

	$template = new Template($phpbb_root_path . $template_path . $template_name, $board_config, $db);

	if ( $template )
	{
		$current_template_path = $template_path . $template_name;
		@include($phpbb_root_path . $template_path . $template_name . '/' . $template_name . '.cfg');

		if ( !defined("TEMPLATE_CONFIG") )
		{
			message_die(CRITICAL_ERROR, "Couldn't open $template_name template config file");
		}

		if ( file_exists($current_template_path . '/images/lang_' . $board_config['default_lang']) )
		{
			while( list($key, $value) = @each($images) )
			{
				$images[$key] = str_replace("{LANG}", 'lang_' . $board_config['default_lang'], $value);
			}
		}
	}

	return $row;
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
		$act_key .= $chars[rand(0, $max_elements)];
	}
	$act_key_md = md5($act_key);

	return $act_key_md;
}

function encode_ip($dotquad_ip)
{
	$ip_sep = explode(".", $dotquad_ip);
	return sprintf("%02x%02x%02x%02x", $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}

function decode_ip($int_ip)
{
	$hexipbang = explode(".",chunk_split($int_ip, 2, "."));
	return hexdec($hexipbang[0]).".".hexdec($hexipbang[1]).".".hexdec($hexipbang[2]).".".hexdec($hexipbang[3]);
}

//
// Create date/time from format and timezone
//
function create_date($format, $gmepoch, $tz)
{
	return gmdate($format, $gmepoch + (3600 * $tz));
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{
	global $lang;

	$total_pages = ceil($num_items/$per_page);

	if ( $total_pages == 1 )
	{
		return "";
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = "";
	if ( $total_pages > 10 )
	{
		$init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

		for($i = 1; $i < $init_page_max + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			if ( $i <  $init_page_max )
			{
				$page_string .= ", ";
			}
		}

		if ( $total_pages > 3 )
		{
			if ( $on_page > 1  && $on_page < $total_pages )
			{
				$page_string .= ( $on_page > 5 ) ? ' ... ' : ', ';

				$init_page_min = ( $on_page > 4 ) ? $on_page : 5;
				$init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;

				for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
				{
					$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
					if ( $i <  $init_page_max + 1 )
					{
						$page_string .= ', ';
					}
				}

				$page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : ', ';
			}
			else
			{
				$page_string .= ' ... ';
			}

			for($i = $total_pages - 2; $i < $total_pages + 1; $i++)
			{
				$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>'  : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
				if( $i <  $total_pages )
				{
					$page_string .= ", ";
				}
			}
		}
	}
	else
	{
		for($i = 1; $i < $total_pages + 1; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . append_sid($base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) ) . '">' . $i . '</a>';
			if ( $i <  $total_pages )
			{
				$page_string .= ', ';
			}
		}
	}

	if ( $add_prevnext_text )
	{
		if ( $on_page > 1 )
		{
			$page_string = ' <a href="' . append_sid($base_url . "&amp;start=" . ( ( $on_page - 2 ) * $per_page ) ) . '">' . $lang['Previous'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ( $on_page < $total_pages )
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . append_sid($base_url . "&amp;start=" . ( $on_page * $per_page ) ) . '">' . $lang['Next'] . '</a>';
		}

	}

	$page_string = $lang['Goto_page'] . ' ' . $page_string;

	return $page_string;
}


//
// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
//
function validate_username($username)
{
	global $db, $lang, $userdata;

	$username = str_replace("\'", "''", $username);

	$sql = "SELECT username 
		FROM " . USERS_TABLE . " 
		WHERE LOWER(username) = '" . strtolower($username) . "'";
	if ( $result = $db->sql_query($sql) )
	{
		if ( $row = $db->sql_fetchrow($result) )
		{
			return ( $userdata['session_logged_in'] ) ? ( ( $row['username'] != $userdata['username'] ) ? array('error' => true, 'error_msg' => $lang['Username_taken']) : array('error' => false, 'error_msg' => '') ) : array('error' => true, 'error_msg' => $lang['Username_taken']);
		}
	}

	$sql = "SELECT group_name
		FROM " . GROUPS_TABLE . " 
		WHERE LOWER(group_name) = '" . strtolower($username) . "'";
	if ( $result = $db->sql_query($sql) )
	{
		if ( $row = $db->sql_fetchrow($result) )
		{
			return array('error' => true, 'error_msg' => $lang['Username_taken']);
		}
	}

	$sql = "SELECT disallow_username
		FROM " . DISALLOW_TABLE . "
		WHERE disallow_username LIKE '$username'";
	if ( $result = $db->sql_query($sql) )
	{
		if ( $db->sql_fetchrow($result) )
		{
			return array('error' => true, 'error_msg' => $lang['Username_disallowed']);
		}
	}

	$sql = "SELECT word 
		FROM  " . WORDS_TABLE;
	if ( $result = $db->sql_query($sql) )
	{
		while( $row = $db->sql_fetchrow($result) )
		{
			if ( preg_match("/\b(" . str_replace("\*", "\w*?", preg_quote($row['word'])) . ")\b/i", $username) )
			{
				return array('error' => true, 'error_msg' => $lang['Username_disallowed']);
			}
		}
	}

	// Don't allow " in username.
	if ( strstr($username, '"') )
	{
		return array('error' => true, 'error_msg' => $lang['Username_invalid']);
	}

	return array('error' => false, 'error_msg' => '');
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
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get forum IDs", "Error", __LINE__, __FILE__, $sql);
			}

			while( $row = $db->sql_fetchrow($result) )
			{
				sync("forum", $row['forum_id']);
			}
		   	break;

		case 'all topics':
			$sql = "SELECT topic_id
				FROM " . TOPICS_TABLE;
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get topic ID's", "Error", __LINE__, __FILE__, $sql);
			}

			while( $row = $db->sql_fetchrow($result) )
			{
				sync("topic", $row['topic_id']);
			}
			break;

	  	case 'forum':
			$sql = "SELECT MAX(post_id) AS last_post, COUNT(post_id) AS total 
				FROM " . POSTS_TABLE . " 
				WHERE forum_id = $id";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post ID", "Error", __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				$last_post = ($row['last_post']) ? $row['last_post'] : 0;
				$total_posts = ($row['total']) ? $row['total'] : 0;
			}
			else
			{
				$last_post = 0;
				$total_posts = 0;
			}

			$sql = "SELECT COUNT(topic_id) AS total
				FROM " . TOPICS_TABLE . "
				WHERE forum_id = $id 
					AND topic_status <> " . TOPIC_MOVED;
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get topic count", "Error", __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
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
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not update forum $id", "Error", __LINE__, __FILE__, $sql);
			}
			break;

		case 'topic':
			$sql = "SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM " . POSTS_TABLE . "
				WHERE topic_id = $id";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, "Could not get post ID", "Error", __LINE__, __FILE__, $sql);
			}

			if ( $row = $db->sql_fetchrow($result) )
			{
				$sql = "UPDATE " . TOPICS_TABLE . "
					SET topic_replies = " . ( $row['total_posts'] - 1 ) . ", topic_first_post_id = " . $row['first_post'] . ", topic_last_post_id = " . $row['last_post'] . " 
					WHERE topic_id = $id";
				if ( !($result = $db->sql_query($sql)) )
				{
					message_die(GENERAL_ERROR, "Could not update topic $id", "Error", __LINE__, __FILE__, $sql);
				}
			}

			break;
	}
	
	return true;
}

//
// Pick a language, any language ...
//
function language_select($default, $select_name = "language", $dirname="language")
{
	global $phpEx;

	$dir = opendir($dirname);

	$lang = array();
	while ( $file = readdir($dir) )
	{
		if ( ereg("^lang_", $file) && !is_file($dirname . "/" . $file) && !is_link($dirname . "/" . $file) )
		{
			$filename = str_replace("lang_", "", $file);
			$displayname = preg_replace("/(.*)_(.*)/", "\\1 [ \\2 ]", $filename);
			$lang[$displayname] = $filename;
		}
	}

	closedir($dir);

	@asort($lang);
	@reset($lang);

	$lang_select = '<select name="' . $select_name . '">';
	while ( list($displayname, $filename) = @each($lang) )
	{
		$selected = ( strtolower($default) == strtolower($filename) ) ? ' selected="selected"' : '';
		$lang_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
	}
	$lang_select .= '</select>';

	return $lang_select;
}

//
// Pick a template/theme combo, 
//
function style_select($default_style, $select_name = "style", $dirname = "templates")
{
	global $db;

	$sql = "SELECT themes_id, style_name
		FROM " . THEMES_TABLE . "
		ORDER BY template_name, themes_id";
	if ( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't query themes table", "", __LINE__, __FILE__, $sql);
	}

	$style_select = '<select name="' . $select_name . '">';
	while ( $row = $db->sql_fetchrow($result) )
	{
		$selected = ( $row['themes_id'] == $default_style ) ? ' selected="selected"' : '';

		$style_select .= '<option value="' . $row['themes_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}
	$style_select .= "</select>";

	return $style_select;
}

//
// Pick a timezone
//
function tz_select($default, $select_name = 'timezone')
{
	global $sys_timezone, $lang;

	if ( !isset($default) )
	{
		$default == $sys_timezone;
	}
	$tz_select = '<select name="' . $select_name . '">';

	while( list($offset, $zone) = @each($lang['tz']) )
	{
		$selected = ( $offset == $default ) ? ' selected="selected"' : '';
		$tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
	}
	$tz_select .= '</select>';

	return $tz_select;
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
	if( !($result = $db->sql_query($sql)) )
	{
		message_die(GENERAL_ERROR, "Couldn't get censored words from database.", "", __LINE__, __FILE__, $sql);
	}

	if ( $row = $db->sql_fetchrow($result) )
	{
		do 
		{
			$orig_word[] = "#\b(" . str_replace("*", "\w*?", $row['word']) . ")\b#is";
			$replacement_word[] = $row['replacement'];
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	return true;
}

//
// Username search
//
function username_search($search_match, $is_inline_review = 0, $default_list = "")
{
	global $db, $board_config, $template, $lang, $images, $theme, $phpEx, $phpbb_root_path;
	global $starttime;

	$author_list = '';
	if ( !empty($search_match) )
	{
		$username_search = preg_replace("/\*/", "%", trim(strip_tags($search_match)));

		$sql = "SELECT username 
			FROM " . USERS_TABLE . " 
			WHERE username LIKE '" . str_replace("\'", "''", $username_search) . "' 
			ORDER BY username";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, "Couldn't obtain search results", "", __LINE__, __FILE__, $sql);
		}

		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$author_list .= '<option value="' . $row['username'] . '">' .$row['username'] . '</option>';
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		else
		{
			$author_list = '<option>' . $lang['No_match']. '</option>';
		}

	}

	if ( !$is_inline_review )
	{
		$gen_simple_header = TRUE;
		$page_title = $lang['Search'];
		include($phpbb_root_path . 'includes/page_header.'.$phpEx);

		$template->set_filenames(array(
			"search_user_body" => "search_username.tpl")
		);

		$template->assign_vars(array(
			"L_CLOSE_WINDOW" => $lang['Close_window'], 
			"L_SEARCH_USERNAME" => $lang['Find_username'], 
			"L_UPDATE_USERNAME" => $lang['Select_username'], 
			"L_SELECT" => $lang['Select'], 
			"L_SEARCH" => $lang['Search'], 
			"L_SEARCH_EXPLAIN" => $lang['Search_author_explain'], 
			"L_CLOSE_WINDOW" => $lang['Close_window'], 

			"S_AUTHOR_OPTIONS" => $author_list, 
			"S_SEARCH_ACTION" => append_sid("search.$phpEx?mode=searchuser"))
		);

		//
		// If we have results then dump them out and enable
		// the appropriate switch block
		//
		if ( !empty($author_list) )
		{
			$template->assign_block_vars("switch_select_name", array());
		}

		$template->pparse("search_user_body");

		include($phpbb_root_path . 'includes/page_tail.'.$phpEx);
	}

	return($author_list);
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
	global $db, $template, $board_config, $theme, $lang, $phpEx, $phpbb_root_path, $nav_links;
	global $userdata, $user_ip, $session_length;
	global $starttime;

	$sql_store = $sql;
	
	//
	// Get SQL error if we are debugging. Do this as soon as possible to prevent 
	// subsequent queries from overwriting the status of sql_error()
	//
	if ( DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
	{
		$sql_error = $db->sql_error();

		$debug_text = "";

		if ( $sql_error['message'] != "" )
		{
			$debug_text .= "<br /><br />SQL Error : " . $sql_error['code'] . " " . $sql_error['message'];
		}

		if ( $sql_store != "" )
		{
			$debug_text .= "<br /><br />$sql_store";
		}

		if ( $err_line != "" && $err_file != "" )
		{
			$debug_text .= "</br /><br />Line : " . $err_line . "<br />File : " . $err_file;
		}
	}

	if( empty($userdata) && ( $msg_code == GENERAL_MESSAGE || $msg_code == GENERAL_ERROR ) )
	{
		$userdata = session_pagestart($user_ip, PAGE_INDEX, $session_length);
		init_userprefs($userdata);
	}

	//
	// If the header hasn't been output then do it
	//
	if ( !defined("HEADER_INC") && $msg_code != CRITICAL_ERROR )
	{
		if ( empty($lang) )
		{
			if ( !empty($board_config['default_lang']) )
			{
				include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.'.$phpEx);
			}
			else
			{
				include($phpbb_root_path . 'language/lang_english/lang_main.'.$phpEx);
			}
		}

		if ( empty($template) )
		{
			$template = new Template($phpbb_root_path . "templates/" . $board_config['board_template']);
		}
		if ( empty($theme) )
		{
			$theme = setup_style($board_config['default_style']);
		}

		//
		// Load the Page Header
		//
		if ( !defined("IN_ADMIN") )
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
			if ( $msg_title == "" )
			{
				$msg_title = $lang['Information'];
			}
			break;

		case CRITICAL_MESSAGE:
			if ( $msg_title == "" )
			{
				$msg_title = $lang['Critical_Information'];
			}
			break;

		case GENERAL_ERROR:
			if ( $msg_text == "" )
			{
				$msg_text = $lang['An_error_occured'];
			}

			if ( $msg_title == "" )
			{
				$msg_title = $lang['General_Error'];
			}

		case CRITICAL_ERROR:
			//
			// Critical errors mean we cannot rely on _ANY_ DB information being
			// available so we're going to dump out a simple echo'd statement
			//
			include($phpbb_root_path . 'language/lang_english/lang_main.'.$phpEx);

			if ( $msg_text == "" )
			{
				$msg_text = $lang['A_critical_error'];
			}

			if ( $msg_title == "" )
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
	if ( DEBUG && ( $msg_code == GENERAL_ERROR || $msg_code == CRITICAL_ERROR ) )
	{
		if ( $debug_text != "" )
		{
			$msg_text = $msg_text . "<br /><br /><b><u>DEBUG MODE</u></b>" . $debug_text;
		}
	}

	if ( $msg_code != CRITICAL_ERROR )
	{
		if ( !empty($lang[$msg_text]) )
		{
			$msg_text = $lang[$msg_text];
		}

		if ( !defined("IN_ADMIN") )
		{
			$template->set_filenames(array(
				"message_body" => "message_body.tpl")
			);
		}
		else
		{
			$template->set_filenames(array(
				"message_body" => "admin/admin_message_body.tpl")
			);
		}

		$template->assign_vars(array(
			"MESSAGE_TITLE" => $msg_title,
			"MESSAGE_TEXT" => $msg_text)
		);
		$template->pparse("message_body");

		if ( !defined("IN_ADMIN") )
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

//
// this does exactly what preg_quote() does in PHP 4-ish: 
// http://www.php.net/manual/en/function.preg-quote.php
//
// This function is here because the 2nd paramter to preg_quote was added in some
// version of php 4.0.x.. So we use this in order to maintain compatibility with
// earlier versions of PHP.
// 
// If you just need the 1-parameter preg_quote call, then don't bother using this.
//
function phpbb_preg_quote($str, $delimiter)
{
	$text = preg_quote($str);
	$text = str_replace($delimiter, "\\" . $delimiter, $text);
	
	return $text;
}

?>