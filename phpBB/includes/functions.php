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

	switch( $mode )
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
		return false;
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

	return false;
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
		message_die(GENERAL_ERROR, 'Tried obtaining data for a non-existent user', '', __LINE__, __FILE__, $sql);
	}

	return ( $row = $db->sql_fetchrow($result) ) ? $row : false;
}

function make_jumpbox($action, $match_forum_id = 0)
{
	global $template, $userdata, $lang, $db, $nav_links, $phpEx;

//	$is_auth = auth(AUTH_VIEW, AUTH_LIST_ALL, $userdata);

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
			message_die(GENERAL_ERROR, 'Could not obtain forums information', '', __LINE__, __FILE__, $sql);
		}

		$boxstring = '<select name="' . POST_FORUM_URL . '" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"><option value="-1">' . $lang['Select_forum'] . '</option>';

		$forum_rows = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$forum_rows[] = $row;
		}

		if ( $total_forums = count($forum_rows) )
		{
			for($i = 0; $i < $total_categories; $i++)
			{
				$boxstring_forums = '';
				for($j = 0; $j < $total_forums; $j++)
				{
					if ( $forum_rows[$j]['cat_id'] == $category_rows[$i]['cat_id'] && $forum_rows[$j]['auth_view'] <= AUTH_REG )
					{

//					if ( $forum_rows[$j]['cat_id'] == $category_rows[$i]['cat_id'] && $is_auth[$forum_rows[$j]['forum_id']]['auth_view'] )
//					{
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

				if ( $boxstring_forums != '' )
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
		$boxstring .= '<select name="' . POST_FORUM_URL . '" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"></select>';
	}

	if ( !empty($SID) )
	{
		$boxstring .= '<input type="hidden" name="sid" value="' . $userdata['session_id'] . '" />';
	}

	$template->set_filenames(array(
		'jumpbox' => 'jumpbox.tpl')
	);
	$template->assign_vars(array(
		'L_GO' => $lang['Go'],
		'L_JUMP_TO' => $lang['Jump_to'],
		'L_SELECT_FORUM' => $lang['Select_forum'],

		'S_JUMPBOX_SELECT' => $boxstring,
		'S_JUMPBOX_ACTION' => append_sid($action))
	);
	$template->assign_var_from_handle('JUMPBOX', 'jumpbox');

	return;
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

		if ( isset($userdata['user_timezone']) )
		{
			$board_config['board_timezone'] = $userdata['user_timezone'];
		}
	}

	if ( !file_exists(@realpath($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.'.$phpEx)) )
	{
		$board_config['default_lang'] = 'english';
	}

	include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);

	if ( defined('IN_ADMIN') )
	{
		if( !file_exists(@realpath($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.'.$phpEx)) )
		{
			$board_config['default_lang'] = 'english';
		}

		include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_admin.' . $phpEx);
	}

	//
	// Set up style
	//
	if ( !$board_config['override_user_style'] )
	{
		if ( $userdata['user_id'] != ANONYMOUS && $userdata['user_style'] > 0 )
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
		message_die(CRITICAL_ERROR, 'Could not query database for theme info');
	}

	if ( !($row = $db->sql_fetchrow($result)) )
	{
		message_die(CRITICAL_ERROR, "Could not get theme data for themes_id [$style]");
	}

	$template_path = 'templates/' ;
	$template_name = $row['template_name'] ;

	$template = new Template($phpbb_root_path . $template_path . $template_name, $board_config, $db);

	if ( $template )
	{
		$current_template_path = $template_path . $template_name;
		@include($phpbb_root_path . $template_path . $template_name . '/' . $template_name . '.cfg');

		if ( !defined('TEMPLATE_CONFIG') )
		{
			message_die(CRITICAL_ERROR, "Could not open $template_name template config file", '', __LINE__, __FILE__);
		}

		$img_lang = ( file_exists(@realpath($phpbb_root_path . $current_template_path . '/images/lang_' . $board_config['default_lang'])) ) ? $board_config['default_lang'] : 'english';

		while( list($key, $value) = @each($images) )
		{
			if ( !is_array($value) )
			{
				$images[$key] = str_replace('{LANG}', 'lang_' . $img_lang, $value);
			}
		}
	}

	return $row;
}

function encode_ip($dotquad_ip)
{
	$ip_sep = explode('.', $dotquad_ip);
	return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}

function decode_ip($int_ip)
{
	$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
	return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
}

//
// Create date/time from format and timezone
//
function create_date($format, $gmepoch, $tz)
{
	global $board_config, $lang;
	static $translate;

	if ( empty($translate) && $board_config['default_lang'] != 'english' )
	{
		@reset($lang['datetime']);
		while ( list($match, $replace) = @each($lang['datetime']) )
		{
			$translate[$match] = $replace;
		}
	}

	return ( !empty($translate) ) ? strtr(@gmdate($format, $gmepoch + (3600 * $tz)), $translate) : @gmdate($format, $gmepoch + (3600 * $tz));
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
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = '';
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
// This does exactly what preg_quote() does in PHP 4-ish
// If you just need the 1-parameter preg_quote call, then don't bother using this.
//
function phpbb_preg_quote($str, $delimiter)
{
	$text = preg_quote($str);
	$text = str_replace($delimiter, '\\' . $delimiter, $text);
	
	return $text;
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
		message_die(GENERAL_ERROR, 'Could not get censored words from database', '', __LINE__, __FILE__, $sql);
	}

	if ( $row = $db->sql_fetchrow($result) )
	{
		do 
		{
			$orig_word[] = '#\b(' . str_replace('\*', '\w*?', phpbb_preg_quote($row['word'], '#')) . ')\b#i';
			$replacement_word[] = $row['replacement'];
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	return true;
}

//
// This is general replacement for die(), allows templated
// output in users (or default) language, etc.
//
// $msg_code can be one of these constants:
//
// GENERAL_MESSAGE : Use for any simple text message, eg. results 
// of an operation, authorisation failures, etc.
//
// GENERAL ERROR : Use for any error which occurs _AFTER_ the 
// common.php include and session code, ie. most errors in 
// pages/functions
//
// CRITICAL_MESSAGE : Used when basic config data is available but 
// a session may not exist, eg. banned users
//
// CRITICAL_ERROR : Used when config data cannot be obtained, eg
// no database connection. Should _not_ be used in 99.5% of cases
//
function message_die($msg_code, $msg_text = '', $msg_title = '', $err_line = '', $err_file = '', $sql = '')
{
	global $db, $template, $board_config, $theme, $lang, $phpEx, $phpbb_root_path, $nav_links, $gen_simple_header, $images;
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

		$debug_text = '';

		if ( $sql_error['message'] != '' )
		{
			$debug_text .= '<br /><br />SQL Error : ' . $sql_error['code'] . ' ' . $sql_error['message'];
		}

		if ( $sql_store != '' )
		{
			$debug_text .= "<br /><br />$sql_store";
		}

		if ( $err_line != '' && $err_file != '' )
		{
			$debug_text .= '</br /><br />Line : ' . $err_line . '<br />File : ' . $err_file;
		}
	}

	if( empty($userdata) && ( $msg_code == GENERAL_MESSAGE || $msg_code == GENERAL_ERROR ) )
	{
		$userdata = session_pagestart($user_ip, PAGE_INDEX);
		init_userprefs($userdata);
	}

	//
	// If the header hasn't been output then do it
	//
	if ( !defined('HEADER_INC') && $msg_code != CRITICAL_ERROR )
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
			$template = new Template($phpbb_root_path . 'templates/' . $board_config['board_template']);
		}
		if ( empty($theme) )
		{
			$theme = setup_style($board_config['default_style']);
		}

		//
		// Load the Page Header
		//
		if ( !defined('IN_ADMIN') )
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
			if ( $msg_title == '' )
			{
				$msg_title = $lang['Information'];
			}
			break;

		case CRITICAL_MESSAGE:
			if ( $msg_title == '' )
			{
				$msg_title = $lang['Critical_Information'];
			}
			break;

		case GENERAL_ERROR:
			if ( $msg_text == '' )
			{
				$msg_text = $lang['An_error_occured'];
			}

			if ( $msg_title == '' )
			{
				$msg_title = $lang['General_Error'];
			}
			break;

		case CRITICAL_ERROR:
			//
			// Critical errors mean we cannot rely on _ANY_ DB information being
			// available so we're going to dump out a simple echo'd statement
			//
			include($phpbb_root_path . 'language/lang_english/lang_main.'.$phpEx);

			if ( $msg_text == '' )
			{
				$msg_text = $lang['A_critical_error'];
			}

			if ( $msg_title == '' )
			{
				$msg_title = 'phpBB : <b>' . $lang['Critical_Error'] . '</b>';
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
		if ( $debug_text != '' )
		{
			$msg_text = $msg_text . '<br /><br /><b><u>DEBUG MODE</u></b>' . $debug_text;
		}
	}

	if ( $msg_code != CRITICAL_ERROR )
	{
		if ( !empty($lang[$msg_text]) )
		{
			$msg_text = $lang[$msg_text];
		}

		if ( !defined('IN_ADMIN') )
		{
			$template->set_filenames(array(
				'message_body' => 'message_body.tpl')
			);
		}
		else
		{
			$template->set_filenames(array(
				'message_body' => 'admin/admin_message_body.tpl')
			);
		}

		$template->assign_vars(array(
			'MESSAGE_TITLE' => $msg_title,
			'MESSAGE_TEXT' => $msg_text)
		);
		$template->pparse('message_body');

		if ( !defined('IN_ADMIN') )
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
// This function is for compatibility with PHP 4.x's realpath()
// function.  In later versions of PHP, it needs to be called
// to do checks with some functions.  Older versions of PHP don't
// seem to need this, so we'll just return the original value.
//
// dougk_ff7 <October 5, 2002>
if ( !function_exists(realpath) )
{
	function realpath($path)
	{
		return $path;
	}
}

function redirect($url)
{
	global $board_config;

	$server_protocol = ($board_config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($board_config['server_name']));
	$script_name = preg_replace('/^\/?(.*?)\/?$/', '/\1', trim($board_config['script_path']));
	$server_port = ($board_config['server_port'] <> 80) ? ':' . trim($board_config['server_port']) . '/' : '/';
	$url = preg_replace('/^\/?(.*?\/)?$/', '\1', trim($url));

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $server_protocol . $server_name . $script_name . $server_port . $url);
		echo '<html><meta http-equiv="refresh" content="0; url=' . $server_protocol . $server_name . $script_name . $server_port . $url . '"></html>';
		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $server_protocol . $server_name . $script_name . $server_port . $url);
	exit;
}

?>