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

function sql_quote($msg)
{
	return str_replace("\'", "''", $msg);
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

function get_forum_branch($forum_id, $type = 'all', $order = 'descending', $include_forum = TRUE)
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

	$rows = array();

	$sql = 'SELECT f2.*
		FROM ( ' . FORUMS_TABLE . ' f1
		LEFT JOIN ' . FORUMS_TABLE . " f2 ON $condition )
		WHERE f1.forum_id = $forum_id
		ORDER BY f2.left_id " . ( ($order == 'descending') ? 'ASC' : 'DESC' );
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

function forum_nav_links(&$forum_id, &$forum_data)
{
	global $SID, $template, $phpEx, $auth;

	$type = 'parent';
	$forum_rows = array();

	if (!($forum_branch = get_forum_branch($forum_id)))
	{
		trigger_error($user->lang['Forum_not_exist']);
	}

	$s_has_subforums = FALSE;
	foreach ($forum_branch as $row)
	{
		if ($type == 'parent')
		{
			$link = ($row['forum_status'] == ITEM_CATEGORY) ? 'index.' . $phpEx . $SID . '&amp;c=' . $row['forum_id'] : 'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'];

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=>	$row['forum_name'],
				'U_VIEW_FORUM'	=>	$link
			));

			if ($row['forum_id'] == $forum_id)
			{
				$branch_root_id = 0;
				$forum_data = $row;
				$type = 'child';
			}
		}
		else
		{
			if ($row['parent_id'] == $forum_data['forum_id'])
			{
				// Root-level forum
				$forum_rows[] = $row;
				$parent_id = $row['forum_id'];

				if ($row['forum_status'] == ITEM_CATEGORY)
				{
					$branch_root_id = $row['forum_id'];
				}
				else
				{
					$s_has_subforums = TRUE;
				}
			}
			elseif ($row['parent_id'] == $branch_root_id)
			{
				// Forum directly under a category
				$forum_rows[] = $row;
				$parent_id = $row['forum_id'];

				if ($row['forum_status'] != ITEM_CATEGORY)
				{
					$s_has_subforums = TRUE;
				}
			}
			elseif ($row['forum_status'] != ITEM_CATEGORY)
			{
				// Subforum
				if ($auth->acl_get('f_list', $row['forum_id']))
				{
					$subforums[$parent_id][] = $row;
				}
			}
		}
	}

	return $s_has_subforums;
}

// Obtain list of moderators of each forum
// First users, then groups ... broken into two queries
// We could cache this ... certainly into a DB table. Would
// better allow the admin to decide which moderators are
// displayed(?)
function cache_moderators($type = false, $id = false)
{

}

function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $SID, $db, $acl_options, $phpEx;

	$forum_sql = ( $forum_id ) ? 'AND m.forum_id = ' . $forum_id : '';
/*
	$sql = "SELECT m.forum_id, u.user_id, u.username, g.group_id, g.group_name
		FROM phpbb_moderators m
		LEFT JOIN phpbb_users u ON u.user_id = m.user_id
		LEFT JOIN phpbb_groups g ON g.group_id = m.group_id
		WHERE m.display_on_index = 1
			$forum_sql";
	$result = $db->sql_query($sql);

	while ( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = ( !empty($row['user_id']) ) ? '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>' : '<a href="groupcp.' . $phpEx . $SID . '&amp;g=' . $row['group_id'] . '">' . $row['group_name'] . '</a>';
	}*/

	$sql = "SELECT au.forum_id, u.user_id, u.username
		FROM  " . ACL_OPTIONS_TABLE . "  o, " . ACL_USERS_TABLE . " au,  " . USERS_TABLE . "  u
		WHERE au.auth_option_id = o.auth_option_id
			AND au.user_id = u.user_id
			AND o.auth_value = 'm_'
			AND au.auth_allow_deny = 1
			$forum_sql";
	$result = $db->sql_query($sql);

	while ( $row = $db->sql_fetchrow($result) )
	{
		$forum_moderators[$row['forum_id']][] = '<a href="profile.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
	}

	$sql = "SELECT ag.forum_id, g.group_name, g.group_id
		FROM  " . ACL_OPTIONS_TABLE . "  o, " . ACL_GROUPS_TABLE . " ag,  " . GROUPS_TABLE . "  g
		WHERE ag.auth_option_id = o.auth_option_id
			AND ag.group_id = g.group_id
			AND o.auth_value = 'm_'
			AND ag.auth_allow_deny = 1
			AND g.group_type <> " . GROUP_HIDDEN . "
			$forum_sql";
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
	global $SID, $auth, $user;

	$rules .= ( ( $auth->acl_get('f_post', $forum_id) ) ? $user->lang['Rules_post_can'] : $user->lang['Rules_post_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_reply', $forum_id) ) ? $user->lang['Rules_reply_can'] : $user->lang['Rules_reply_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_edit', $forum_id) ) ? $user->lang['Rules_edit_can'] : $user->lang['Rules_edit_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_delete', $forum_id) || $auth->acl_get('m_delete', $forum_id) ) ? $user->lang['Rules_delete_can'] : $user->lang['Rules_delete_cannot'] ) . '<br />';
	$rules .= ( ( $auth->acl_get('f_attach', $forum_id) ) ? $user->lang['Rules_attach_can'] : $user->lang['Rules_attach_cannot'] ) . '<br />';

	return;
}

function make_jumpbox($action, $forum_id = false)
{
	global $auth, $template, $user, $db, $nav_links, $phpEx;

	$boxstring = '<select name="f" onChange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"><option value="-1">' . $user->lang['Select_forum'] . '</option><option value="-1">&nbsp;</option>';

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

			// TODO: do not add empty categories to nav links
			$nav_links['chapter forum'][$row['forum_id']] = array (
				'url' => ($row['forum_status'] == ITEM_CATEGORY) ? "index.$phpEx$SIDc=" : "viewforum.$phpEx$SID&f=" . $row['forum_id'],
				'title' => $row['forum_name']
			);
		}

	}
	$db->sql_freeresult($result);

	if (!$right)
	{
		$boxstring .= '<option value="-1">' . $user->lang['No_forums'] . '</option>';
	}
	$boxstring .= '</select>';

	$template->assign_vars(array(
		'L_GO' => $user->lang['Go'],
		'L_JUMP_TO' => $user->lang['Jump_to'],

		'S_JUMPBOX_SELECT' => $boxstring,
		'S_JUMPBOX_ACTION' => $action)
	);

	return;
}

// Pick a language, any language ...
function language_select($default, $select_name = "language", $dirname="language")
{
	global $phpEx;

	$dir = @opendir($dirname);

	$user = array();
	while ( $file = readdir($dir) )
	{
		if (!is_dir($dirname . '/' . $file))
		{
			continue;
		}
		if ( @file_exists($dirname . '/' . $file . '/iso.txt') )
		{
			list($displayname) = file($dirname . '/' . $file . '/iso.txt');
			$lang[$displayname] = $file;
		}
	}
	@closedir($dir);

	@asort($lang);
	@reset($lang);

	$user_select = '<select name="' . $select_name . '">';
	foreach ( $lang as $displayname => $filename )
	{
		$selected = ( strtolower($default) == strtolower($filename) ) ? ' selected="selected"' : '';
		$user_select .= '<option value="' . $filename . '"' . $selected . '>' . ucwords($displayname) . '</option>';
	}
	$user_select .= '</select>';

	return $user_select;
}

// Pick a template/theme combo,
function style_select($default_style, $select_name = 'style', $dirname = 'templates')
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

// Pick a timezone
function tz_select($default, $select_name = 'timezone')
{
	global $sys_timezone, $user;

	$tz_select = '<select name="' . $select_name . '">';
	foreach ( $user->lang['tz'] as $offset => $zone )
	{
		$selected = ( $offset == $default ) ? ' selected="selected"' : '';
		$tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
	}
	$tz_select .= '</select>';

	return $tz_select;
}

// Topic and forum watching common code
function watch_topic_forum($mode, &$s_watching, &$s_watching_img, $user_id, $match_id)
{
	global $template, $db, $user, $phpEx, $SID, $start;

	$table_sql = ( $mode == 'forum' ) ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ( $mode == 'forum' ) ? 'forum_id' : 'topic_id';
	$u_url = ( $mode == 'forum' ) ? 'f' : 't';

	// Is user watching this thread?
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

				$message = $user->lang['No_longer_watching_' . $mode] . '<br /><br />' . sprintf($user->lang['Click_return_' . $mode], '<a href="' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">', '</a>');
				trigger_error($message);
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

				$message = $user->lang['You_are_watching_' . $mode] . '<br /><br />' . sprintf($user->lang['Click_return_' . $mode], '<a href="' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">', '</a>');
				trigger_error($message);
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
		$s_watching = ( $is_watching ) ? '<a href="' . "view$mode." . $phpEx . $SID . '&amp;' . $u_url . "=$match_id&amp;unwatch=$mode&amp;start=$start" . '">' . $user->lang['Stop_watching_' . $mode] . '</a>' : '<a href="' . "view$mode." . $phpEx . $SID . '&amp;' . $u_url . "=$match_id&amp;watch=$mode&amp;start=$start" . '">' . $user->lang['Start_watching_' . $mode] . '</a>';
	}

	return;
}

// Pagination routine, generates page number sequence
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{
	global $user;

	$total_pages = ceil($num_items/$per_page);

	if ( $total_pages == 1 || !$num_items )
	{
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = ( $on_page == 1 ) ? '<b>1</b>' : '<a href="' . $base_url . "&amp;start=" . ( ( $on_page - 2 ) * $per_page ) . '">' . $user->lang['Previous'] . '</a>&nbsp;&nbsp;<a href="' . $base_url . '">1</a>';

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

	$page_string .= ( $on_page == $total_pages ) ? '<b>' . $total_pages . '</b>' : '<a href="' . $base_url . '&amp;start=' . ( ( $total_pages - 1 ) * $per_page ) . '">' . $total_pages . '</a>&nbsp;&nbsp;<a href="' . $base_url . "&amp;start=" . ( $on_page * $per_page ) . '">' . $user->lang['Next'] . '</a>';

	$page_string = $user->lang['Goto_page'] . ' ' . $page_string;

	return $page_string;
}

function on_page($num_items, $per_page, $start)
{
	global $user;

	return sprintf($user->lang['Page_of'], floor( $start / $per_page ) + 1, max(ceil( $num_items / $per_page ), 1) );
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

	while ( $row = $db->sql_fetchrow($result) )
	{
		$orig_word[] = '#\b(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')\b#i';
		$replacement_word[] = $row['replacement'];
	}

	return true;
}

// Redirects the user to another page then exits the script nicely
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

// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
function validate_username($username)
{
	global $db, $user;

	$username = sql_quote($username);

	$sql = "SELECT username
		FROM " . USERS_TABLE . "
		WHERE LOWER(username) = '" . strtolower($username) . "'";
	$result = $db->sql_query($sql);

	if (($row = $db->sql_fetchrow($result)) && $row['username'] != $user->data['username'])
	{
		return $user->lang['Username_taken'];
	}

	$sql = "SELECT group_name
		FROM " . GROUPS_TABLE . "
		WHERE LOWER(group_name) = '" . strtolower($username) . "'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		return $user->lang['Username_taken'];
	}

	$sql = "SELECT disallow_username
		FROM " . DISALLOW_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#\b(' . str_replace('\*', '.*?', preg_quote($row['disallow_username'])) . ')\b#i', $username))
		{
			return $user->lang['Username_disallowed'];
		}
	}

	$sql = "SELECT word
		FROM  " . WORDS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#\b(' . str_replace('\*', '.*?', preg_quote($row['word'])) . ')\b#i', $username))
		{
			return $user->lang['Username_disallowed'];
		}
	}

	// Don't allow " in username.
	if (strstr($username, '"'))
	{
		return $user->lang['Username_invalid'];
	}

	return false;
}

// Check to see if email address is banned or already present in the DB
function validate_email($email)
{
	global $db, $user;

	if ($email != '')
	{
		if (preg_match('/^[a-z0-9\.\-_\+]+@[a-z0-9\-_]+\.([a-z0-9\-_]+\.)*?[a-z]+$/is', $email))
		{
			$sql = "SELECT ban_email
				FROM " . BANLIST_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (preg_match('/^' . str_replace('*', '.*?', $row['ban_email']) . '$/is', $email))
				{
					return $user->lang['Email_banned'];
				}
			}

			$sql = "SELECT user_email
				FROM " . USERS_TABLE . "
				WHERE user_email = '" . sql_quote($email) . "'";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				return $user->lang['Email_taken'];
			}

			return false;
		}
	}

	return $user->lang['Email_invalid'];
}

// Does supplementary validation of optional profile fields. This
// expects common stuff like trim() and strip_tags() to have already
// been run. Params are passed by-ref, so we can set them to the empty
// string if they fail.
function validate_optional_fields(&$icq, &$aim, &$msnm, &$yim, &$website, &$location, &$occupation, &$interests, &$sig)
{
	$check_var_length = array('aim', 'msnm', 'yim', 'location', 'occupation', 'interests', 'sig');

	for($i = 0; $i < count($check_var_length); $i++)
	{
		if ( strlen($$check_var_length[$i]) < 2 )
		{
			$$check_var_length[$i] = '';
		}
	}

	// ICQ number has to be only numbers.
	if ( !preg_match('/^[0-9]+$/', $icq) )
	{
		$icq = '';
	}

	// website has to start with http://, followed by something with length at least 3 that
	// contains at least one dot.
	if ( $website != '' )
	{
		if ( !preg_match('#^http:\/\/#i', $website) )
		{
			$website = 'http://' . $website;
		}

		if ( !preg_match('#^http\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $website) )
		{
			$website = '';
		}
	}

	return;
}

// This is general replacement for die(), allows templated output in users (or default)
// language, etc. $msg_code can be one of these constants:
//
// -> MESSAGE : Use for any simple text message, eg. results of an operation, authorisation
//    failures, etc.
// -> ERROR : Use for any error, a simple page will be output
function message_die($msg_code, $msg_text = '', $msg_title = '')
{
	global $db, $auth, $template, $config, $user, $nav_links;
	global $phpEx, $phpbb_root_path, $starttime;

	switch ( $msg_code )
	{
		case MESSAGE:
			$msg_title = ( $msg_title == '' ) ? $user->lang['Information'] : $msg_title;
			$msg_text = ( !empty($user->lang[$msg_text]) ) ? $user->lang[$msg_text] : $msg_text;

			if ( !defined('HEADER_INC') )
			{
				if ( empty($user->lang) )
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

			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8869-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="../admin/subSilver.css" type="text/css"><style type="text/css">';
			echo 'th { background-image: url(\'../admin/images/cellpic3.gif\') }';
			echo 'td.cat	{ background-image: url(\'../admin/images/cellpic1.gif\') }';
			echo '</style><title>' . $msg_title . '</title></head><body><table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td colspan="2" height="25" align="right" nowrap="nowrap"><span class="subtitle">&#0187; <i>' . $msg_title . '</i></span> &nbsp;&nbsp;</td></tr></table><table width="95%" cellspacing="0" cellpadding="0" border="0" align="center"><tr><td><br clear="all" />' . $msg_text . '</td></tr></table><br clear="all" /></body></html>';
			break;
	}

	exit;
}

// Error and message handler, call with trigger_error if reqd
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $db, $auth, $template, $config, $user, $nav_links;
	global $phpEx, $phpbb_root_path, $starttime;

	switch ($errno)
	{
		case E_WARNING:
//			if (defined('DEBUG'))
//			{
//				echo "PHP Warning on line <b>$errline</b> in <b>$errfile</b> :: <b>$msg_text</b>";
//			}
			break;

		case E_NOTICE:
//			if (defined('DEBUG_EXTRA'))
//			{
//				echo "PHP Notice on line <b>$errline</b> in <b>$errfile</b> :: <b>$msg_text</b>";
//			}
			break;

		case E_USER_ERROR:
			if (isset($db))
			{
				$db->sql_close();
			}

			echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8869-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="' . $phpbb_root_path . 'admin/subSilver.css" type="text/css"><style type="text/css">' . "\n";
			echo 'th { background-image: url(\'' . $phpbb_root_path . 'admin/images/cellpic3.gif\') }' . "\n";
			echo 'td.cat	{ background-image: url(\'' . $phpbb_root_path . 'admin/images/cellpic1.gif\') }' . "\n";
			echo '</style><title>' . $msg_title . '</title></head><body>';
			echo '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td><img src="' . $phpbb_root_path . 'admin/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td><td width="100%" background="' . $phpbb_root_path . 'admin/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">General Error</span> &nbsp; &nbsp; &nbsp;</td></tr></table><br clear="all" /><table width="85%" cellspacing="0" cellpadding="0" border="0" align="center"><tr><td><br clear="all" />' . $msg_text . '<hr />Please notify the board administrator or webmaster : <a href="mailto:' . $config['board_email'] . '">' . $config['board_email'] . '</a></td></tr></table><br clear="all" /></body></html>';

			exit;
			break;

		case E_USER_NOTICE:
			if (empty($user->session_id))
			{
				$user->start();
			}
			if (empty($user->lang))
			{
				$user->setup();
			}

			$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;

			if (!defined('HEADER_INC'))
			{
				if (defined('IN_ADMIN'))
				{
					page_header('', '', false);
				}
				else
				{
					include($phpbb_root_path . 'includes/page_header.' . $phpEx);
				}
			}

			if (defined('IN_ADMIN'))
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
					'MESSAGE_TITLE'	=> $msg_title,
					'MESSAGE_TEXT'	=> $msg_text)
				);

				include($phpbb_root_path . 'includes/page_tail.' . $phpEx);
			}
			break;
	}
}

?>