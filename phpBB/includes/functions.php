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
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

function get_db_stat($mode)
{
	global $db;

	switch( $mode )
	{
		case 'usercount':
			$sql = "SELECT COUNT(user_id) - 1 AS total
				FROM " . USERS_TABLE;
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

	$result = $db->sql_query($sql);

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

function sql_quote($msg)
{
	return str_replace("'", "''", $msg);
}

function get_userdata($user)
{
	global $db;

	$sql = "SELECT *
		FROM " . USERS_TABLE . "
		WHERE ";
	$sql .= ( ( is_int($user) ) ? "user_id = $user" : "username = '" .  sql_quote($user) . "'" ) . " AND user_id <> " . ANONYMOUS;
	$result = $db->sql_query($sql);

	return ( $row = $db->sql_fetchrow($result) ) ? $row : false;
}

function get_forum_branch($forum_id, $type='all', $order='descending', $include_forum=TRUE)
{
	global $db;

	switch ($type)
	{
		case 'parents':
			$condition = 'f1.left_id BETWEEN f2.left_id AND f2.right_id';
		break;

		case 'children':
			$condition = 'f2.left_id BETWEEN f1.left_id AND f1.right_id';
		break;

		default:
			$condition = 'f2.left_id BETWEEN f1.left_id AND f1.right_id OR f1.left_id BETWEEN f2.left_id AND f2.right_id';
	}
	$sql = 'SELECT f2.*
			FROM ' . FORUMS_TABLE . ' f1
			LEFT JOIN ' . FORUMS_TABLE . " f2 ON $condition
			WHERE f1.forum_id = $forum_id
			ORDER BY f2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');

	$rows = array();
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		if (!$include_forum && $row['forum_id'] == $forum_id)
		{
			continue;
		}
		$rows[] = $row;
	}
	return $rows;
}

//
// Obtain list of moderators of each forum
// First users, then groups ... broken into two queries
//
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $SID, $db, $phpEx;

	$forum_sql = ( $forum_id ) ? 'AND au.forum_id = ' . $forum_id : '';

	$sql = "SELECT au.forum_id, u.user_id, u.username
		FROM " . ACL_USERS_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao, " . USERS_TABLE . " u
		WHERE ao.auth_value = 'm_global'
			$forum_sql
			AND au.auth_option_id = ao.auth_option_id
			AND u.user_id = au.user_id";
	$result = $db->sql_query($sql);

	while ( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
	}

	$sql = "SELECT au.forum_id, g.group_id, g.group_name
		FROM " . ACL_GROUPS_TABLE . " au, " . ACL_OPTIONS_TABLE . " ao, " . GROUPS_TABLE . " g
		WHERE ao.auth_value = 'm_global'
			$forum_sql
			AND au.auth_option_id = ao.auth_option_id
			AND g.group_id = au.group_id";
	$result = $db->sql_query($sql);

	while ( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="groupcp.' . $phpEx . $SID . '&amp;g=' . $row['group_id'] . '">' . $row['group_name'] . '</a>';
	}

	return;
}

//
// User authorisation levels output
//
function get_forum_rules($mode, &$rules, &$forum_id)
{
	global $SID, $auth, $lang, $phpEx;

	$rules .= ( ( $auth->acl_get('f_post', $forum_id) ) ? $lang['Rules_post_can'] : $lang['Rules_post_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_reply', $forum_id) ) ? $lang['Rules_reply_can'] : $lang['Rules_reply_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_edit', $forum_id) ) ? $lang['Rules_edit_can'] : $lang['Rules_edit_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_delete', $forum_id) || $auth->acl_get('m_delete', $forum_id) ) ? $lang['Rules_delete_can'] : $lang['Rules_delete_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_attach', $forum_id) ) ? $lang['Rules_attach_can'] : $lang['Rules_attach_cannot'] ) . '<br />';

	if ( $auth->acl_get('a_') || $auth->acl_get('m_', $forum_id) )
	{
		$rules .= sprintf($lang['Rules_moderate'], '<a href="modcp.' . $phpEx . $SID . '&amp;f=' . $forum_id . '">', '</a>');
	}

	return;
}

function make_jumpbox($action, $forum_id = false)
{
	global $auth, $template, $lang, $db, $nav_links, $phpEx;

	$boxstring = '<select name="f" onChange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"><option value="-1">' . $lang['Select_forum'] . '</option><option value="-1">&nbsp;</option>';

	$sql = 'SELECT forum_id, forum_name, forum_status, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	$right = 0;
	$cat_right = 0;
	$padding = '';
	$forum_list = '';
	while ( $row = $db->sql_fetchrow($result) )
	{
		if ( $row['left_id'] < $right  )
		{
			$padding .= '&nbsp; &nbsp;';
		}
		else if ( $row['left_id'] > $right + 1 )
		{
			$padding = substr($padding, 0, -13 * ( $row['left_id'] - $right + 1 ));
		}

		$right = $row['right_id'];

		$linefeed = FALSE;
		if ( ( $auth->acl_get('f_list', $forum_id) || $auth->acl_get('a_') ))
		{
			$selected = ( $row['forum_id'] == $forum_id ) ? ' selected="selected"' : '';

			if ($row['left_id'] > $cat_right)
			{
				$holding = '';
			}
			if ($row['parent_id'] == 0)
			{
				if ($row['forum_status'] == ITEM_CATEGORY)
				{
					$linefeed = TRUE;
					$holding = '<option value="-1">&nbsp;</option>';
				}
				elseif (!empty($linefeed))
				{
					$linefeed = FALSE;
					$boxstring .= '<option value="-1">&nbsp;</option>';
				}
			}
			else
			{
				$linefeed = TRUE;
			}

			if ($row['forum_status'] == ITEM_CATEGORY)
			{
				$cat_right = max($cat_right, $row['right_id']);

				$holding .= '<option value="c' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option><option value="-1">' . $padding . '----------------</option>';
			}
			else
			{
				$boxstring .= $holding . '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option>';
				$holding = '';
			}

			//
			// TODO: do not add empty categories to nav links
			//
			$nav_links['chapter forum'][$row['forum_id']] = array (
				'url' => ($row['forum_status'] == ITEM_CATEGORY) ? "index.$phpEx$SIDc=" : "viewforum.$phpEx$SID&f=" . $row['forum_id'],
				'title' => $row['forum_name']
			);
		}

	}
	$db->sql_freeresult($result);

	if (!$right)
	{
		$boxstring .= '<option value="-1">' . $lang['No_forums'] . '</option>';
	}
	$boxstring .= '</select>';

	$template->assign_vars(array(
		'L_GO' => $lang['Go'],
		'L_JUMP_TO' => $lang['Jump_to'],

		'S_JUMPBOX_SELECT' => $boxstring,
		'S_JUMPBOX_ACTION' => $action)
	);

	return;
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
		if ( preg_match('#^lang_#', $file) && !is_file($dirname . '/' . $file) && !is_link($dirname . '/' . $file) )
		{
			$filename = trim(str_replace('lang_', '', $file));
			$displayname = preg_replace('/^(.*?)_(.*)$/', '\\1 [ \\2 ]', $filename);
			$displayname = preg_replace('/\[(.*?)_(.*)\]/', '[ \\1 - \\2 ]', $displayname);
			$lang[$displayname] = $filename;
		}
	}

	closedir($dir);

	@asort($lang);
	@reset($lang);

	$lang_select = '<select name="' . $select_name . '">';
	foreach ( $lang as $displayname => $filename )
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

	$sql = "SELECT style_id, style_name
		FROM " . STYLES_TABLE . "
		ORDER BY style_name, style_id";
	$result = $db->sql_query($sql);

	$style_select = '<select name="' . $select_name . '">';
	while ( $row = $db->sql_fetchrow($result) )
	{
		$selected = ( $row['style_id'] == $default_style ) ? ' selected="selected"' : '';

		$style_select .= '<option value="' . $row['style_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
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
// Topic and forum watching common code
//
function watch_topic_forum($mode, &$s_watching, &$s_watching_img, $user_id, $match_id)
{
	global $template, $db, $lang, $phpEx, $SID, $start;

	$table_sql = ( $mode == 'forum' ) ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ( $mode == 'forum' ) ? 'forum_id' : 'topic_id';
	$u_url = ( $mode == 'forum' ) ? 'f' : 't';

	//
	// Is user watching this thread?
	//
	if ( $user_id )
	{
		$can_watch = TRUE;

		$sql = "SELECT notify_status
			FROM " . $table_sql . "
			WHERE $where_sql = $match_id
				AND user_id = $user_id";
		$result = $db->sql_query($sql);

		if ( $row = $db->sql_fetchrow($result) )
		{
			if ( isset($_GET['unwatch']) )
			{
				if ( $_GET['unwatch'] == $mode )
				{
					$is_watching = 0;

					$sql = "DELETE FROM " . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);
				}

				$template->assign_vars(array(
					'META' => '<meta http-equiv="refresh" content="3;url=' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">')
				);

				$message = $lang['No_longer_watching_' . $mode] . '<br /><br />' . sprintf($lang['Click_return_' . $mode], '<a href="' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">', '</a>');
				message_die(MESSAGE, $message);
			}
			else
			{
				$is_watching = TRUE;

				if ( $row['notify_status'] )
				{
					$sql = "UPDATE " . $table_sql . "
						SET notify_status = 0
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);
				}
			}
		}
		else
		{
			if ( isset($_GET['watch']) )
			{
				if ( $_GET['watch'] == $mode )
				{
					$is_watching = TRUE;

					$sql = "INSERT INTO " . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, 0)";
					$db->sql_query($sql);
				}

				$template->assign_vars(array(
					'META' => '<meta http-equiv="refresh" content="3;url=' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">')
				);

				$message = $lang['You_are_watching_' . $mode] . '<br /><br />' . sprintf($lang['Click_return_' . $mode], '<a href="' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">', '</a>');
				message_die(MESSAGE, $message);
			}
			else
			{
				$is_watching = 0;
			}
		}
	}
	else
	{
		if ( isset($_GET['unwatch']) )
		{
			if ( $_GET['unwatch'] == $mode )
			{
				redirect("login.$phpEx$SID&redirect=view$mode.$phpEx&" . $u_url . "=$match_id&unwatch=forum");
			}
		}
		else
		{
			$can_watch = 0;
			$is_watching = 0;
		}
	}

	if ( $can_watch )
	{
		if ( $is_watching )
		{
			$watch_url = "view$mode." . $phpEx . $SID . '&amp;' . $u_url . "=$match_id&amp;unwatch=$mode&amp;start=$start";
			$img = ( $mode == 'forum' ) ? $images['Forum_un_watch'] : $images['Topic_un_watch'];

			$s_watching = '<a href="' . $watch_url . '">' . $lang['Stop_watching_' . $mode] . '</a>';
			$s_watching_img = ( isset($img) ) ? '<a href="' . $watch_url . '"><img src="' . $img . '" alt="' . $lang['Stop_watching_' . $mode] . '" title="' . $lang['Stop_watching_' . $mode] . '" border="0"></a>' : '';
		}
		else
		{
			$watch_url = "view$mode." . $phpEx . $SID . '&amp;' . $u_url . "=$match_id&amp;watch=$mode&amp;start=$start";
			$img = ( $mode == 'forum' ) ? $images['Forum_watch'] : $images['Topic_watch'];

			$s_watching = '<a href="' . $watch_url . '">' . $lang['Start_watching_' . $mode] . '</a>';
			$s_watching_img = ( isset($img) ) ? '<a href="' . $watch_url . '"><img src="' . $img . '" alt="' . $lang['Stop_watching_' . $mode] . '" title="' . $lang['Start_watching_' . $mode] . '" border="0"></a>' : '';
		}
	}

	return;
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
		foreach ( $lang['datetime'] as $match => $replace )
		{
			$translate[$match] = $replace;
		}
	}

	return ( !empty($translate) ) ? strtr(@gmdate($format, $gmepoch + (3600 * $tz)), $translate) : @gmdate($format, $gmepoch + (3600 * $tz));
}

function create_img($img, $alt = '')
{
	return '<img src=' . $img . ' alt="' . $alt . '" title="' . $alt . '" />';
}

//
// Pagination routine, generates
// page number sequence
//
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{
	global $lang;

	$total_pages = ceil($num_items/$per_page);

	if ( $total_pages == 1 || !$num_items )
	{
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = ( $on_page == 1 ) ? '<b>1</b>' : '<a href="' . $base_url . "&amp;start=" . ( ( $on_page - 2 ) * $per_page ) . '">' . $lang['Previous'] . '</a>&nbsp;&nbsp;<a href="' . $base_url . '">1</a>';

	if ( $total_pages > 5 )
	{
		$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 4), 6);

		$page_string .= ( $start_cnt > 1 ) ? ' ... ' : ', ';

		for($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
			if ( $i < $end_cnt - 1 )
			{
				$page_string .= ', ';
			}
		}

		$page_string .= ( $end_cnt < $total_pages ) ? ' ... ' : ', ';
	}
	else
	{
		$page_string .= ', ';

		for($i = 2; $i < $total_pages; $i++)
		{
			$page_string .= ( $i == $on_page ) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . ( ( $i - 1 ) * $per_page ) . '">' . $i . '</a>';
			if ( $i < $total_pages )
			{
				$page_string .= ', ';
			}
		}
	}

	$page_string .= ( $on_page == $total_pages ) ? '<b>' . $total_pages . '</b>' : '<a href="' . $base_url . '&amp;start=' . ( ( $total_pages - 1 ) * $per_page ) . '">' . $total_pages . '</a>&nbsp;&nbsp;<a href="' . $base_url . "&amp;start=" . ( $on_page * $per_page ) . '">' . $lang['Next'] . '</a>';

	$page_string = $lang['Goto_page'] . ' ' . $page_string;

	return $page_string;
}

function on_page($num_items, $per_page, $start)
{
	global $lang;

	return sprintf($lang['Page_of'], floor( $start / $per_page ) + 1, max(ceil( $num_items / $per_page ), 1) );
}

// Obtain list of naughty words and build preg style replacement arrays for use by the
// calling script, note that the vars are passed as references this just makes it easier
// to return both sets of arrays
function obtain_word_list(&$orig_word, &$replacement_word)
{
	global $db;

	$sql = "SELECT word, replacement
		FROM  " . WORDS_TABLE;
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		do
		{
			$orig_word[] = '#\b(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')\b#i';
			$replacement_word[] = $row['replacement'];
		}
		while ( $row = $db->sql_fetchrow($result) );
	}

	return true;
}

//
// Redirects the user to another page then exits the script nicely
//
function redirect($location)
{
	global $db;
	if (isset($db))
	{
		$db->sql_close();
	}

	$header_location = (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE'))) ? 'Refresh: 0; URL=' : 'Location: ';
	header($header_location . $location);
	exit;
}

//
// This is general replacement for die(), allows templated output in users (or default)
// language, etc. $msg_code can be one of these constants:
//
// -> MESSAGE : Use for any simple text message, eg. results of an operation, authorisation
//    failures, etc.
// -> ERROR : Use for any error, a simple page will be output
//
// $errno, $errstr, $errfile, $errline
function message_die($msg_code, $msg_text = '', $msg_title = '')
{
	global $db, $session, $auth, $template, $board_config, $theme, $lang, $user;
	global $userdata, $user_ip, $phpEx, $phpbb_root_path, $nav_links, $starttime;

	switch ( $msg_code )
	{
		case MESSAGE:
			if ( empty($lang) && !empty($board_config['default_lang']) )
			{
				if ( !file_exists($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx) )
				{
					$board_config['default_lang'] = 'english';
				}

				include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);
			}

			$msg_title = ( $msg_title == '' ) ? $lang['Information'] : $msg_title;
			$msg_text = ( !empty($lang[$msg_text]) ) ? $lang[$msg_text] : $msg_text;

			if ( !defined('HEADER_INC') )
			{
				if ( empty($userdata) )
				{
					echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="admin/subSilver.css" type="text/css"><style type="text/css">th { background-image: url(\'admin/images/cellpic3.gif\') } td.cat	{ background-image: url(\'admin/images/cellpic1.gif\') }</style><title>' . $msg_title . '</title></html>' . "\n";
					echo '<body><table width="100%" height="100%" border="0"><tr><td align="center" valign="middle"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0"><tr><th>' . $msg_title . '</th></tr><tr><td class="row1" align="center">' . $msg_text . '</td></tr></table></td></tr></table></body></html>';
					$db->sql_close();
					exit;
				}
				else if ( defined('IN_ADMIN') )
				{
					page_header('', '', false);
				}
				else
				{
					include($phpbb_root_path . 'includes/page_header.' . $phpEx);
				}
			}

			if ( defined('IN_ADMIN') )
			{
				page_message($msg_title, $msg_text, $display_header);
				page_footer();
			}
			else
			{
				$template->set_filenames(array(
					'body' => 'message_body.html')
				);

				$template->assign_vars(array(
					'MESSAGE_TITLE' => $msg_title,
					'MESSAGE_TEXT' => $msg_text)
				);

				include($phpbb_root_path . 'includes/page_tail.' . $phpEx);
			}

			break;

		case ERROR:
			$db->sql_close();

			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><title>phpBB 2 :: General Error</title></html>' . "\n";
			echo '<body><h1 style="font-family:Verdana,serif;font-size:18pt;font-weight:bold">phpBB2 :: General Error</h1><hr style="height:2px;border-style:dashed;color:black" /><p style="font-family:Verdana,serif;font-size:10pt">' . $msg_text . '</p><hr style="height:2px;border-style:dashed;color:black" /><p style="font-family:Verdana,serif;font-size:10pt">Contact the site administrator to report this failure</p></body></html>';
			break;
	}

	exit;
}

// Error and message handler, call with trigger_error if reqd
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $db, $session, $auth, $template, $board_config, $theme, $lang, $userdata, $user_ip;
	global $phpEx, $phpbb_root_path, $nav_links, $starttime;

	switch ( $errno )
	{
		case E_WARNING:
			break;

		case E_NOTICE:
			break;

		case E_ERROR:
		case E_USER_ERROR:
			$db->sql_close();

			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><title>phpBB 2 :: General Error</title></html>' . "\n";
			echo '<body><h1 style="font-family:Verdana,serif;font-size:18pt;font-weight:bold">phpBB2 :: General Error</h1><hr style="height:2px;border-style:dashed;color:black" /><p style="font-family:Verdana,serif;font-size:10pt">' . $msg_text . '</p><hr style="height:2px;border-style:dashed;color:black" /><p style="font-family:Verdana,serif;font-size:10pt">Contact the site administrator to report this failure</p></body></html>';
			break;

		case E_USER_NOTICE:
			if ( empty($lang) && !empty($board_config['default_lang']) )
			{
				if ( !file_exists($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx) )
				{
					$board_config['default_lang'] = 'english';
				}

				include($phpbb_root_path . 'language/lang_' . $board_config['default_lang'] . '/lang_main.' . $phpEx);
			}

			$msg_text = ( !empty($lang[$msg_text]) ) ? $lang[$msg_text] : $msg_text;

			if ( !defined('HEADER_INC') )
			{
				if ( empty($userdata) )
				{
					echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="admin/subSilver.css" type="text/css"><style type="text/css">th { background-image: url(\'admin/images/cellpic3.gif\') } td.cat	{ background-image: url(\'admin/images/cellpic1.gif\') }</style><title>' . $lang['Information'] . '</title></html>' . "\n";
					echo '<body><table width="100%" height="100%" border="0"><tr><td align="center" valign="middle"><table class="bg" width="80%" cellspacing="1" cellpadding="4" border="0"><tr><th>' . $lang['Information'] . '</th></tr><tr><td class="row1" align="center">' . $msg_text . '</td></tr></table></td></tr></table></body></html>';
					$db->sql_close();
					exit;
				}
				else if ( defined('IN_ADMIN') )
				{
					page_header('', '', false);
				}
				else
				{
					include($phpbb_root_path . 'includes/page_header.' . $phpEx);
				}
			}

			if ( defined('IN_ADMIN') )
			{
				page_message($msg_title, $msg_text, $display_header);
				page_footer();
			}
			else
			{
				$template->set_filenames(array(
					'body' => 'message_body.html')
				);

				$template->assign_vars(array(
					'MESSAGE_TITLE' => $msg_title,
					'MESSAGE_TEXT' => $msg_text)
				);

				include($phpbb_root_path . 'includes/page_tail.' . $phpEx);
			}
			break;
	}
}

?>