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

function set_config($config_name, $config_value, $is_dynamic = FALSE)
{
	global $db, $cache, $config;

	if (isset($config[$config_name]))
	{
		$sql = 'UPDATE ' . CONFIG_TABLE . "
			SET config_value = '" . $db->sql_escape($config_value) . "'
			WHERE config_name = '$config_name'";
		$db->sql_query($sql);
	}
	else
	{
		$db->sql_query('DELETE FROM ' . CONFIG_TABLE . ' 
			WHERE config_name = "' . $config_name . '"');

		$sql = 'INSERT INTO ' . CONFIG_TABLE . " (config_name, config_value)
			VALUES ('$config_name', '" . $db->sql_escape($config_value) . "')";
		$db->sql_query($sql);
	}

	$config[$config_name] = $config_value;

	if (!$is_dynamic)
	{
		$cache->put('config', $config);
	}
}

function get_userdata($user)
{
	global $db;

	$sql = "SELECT *
		FROM " . USERS_TABLE . "
		WHERE ";
	$sql .= ((is_int($user)) ? "user_id = $user" : "username = '" .  $db->sql_escape($user) . "'") . " AND user_id <> " . ANONYMOUS;
	$result = $db->sql_query($sql);

	return ($row = $db->sql_fetchrow($result)) ? $row : false;
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
		FROM (' . FORUMS_TABLE . ' f1
		LEFT JOIN ' . FORUMS_TABLE . " f2 ON $condition)
		WHERE f1.forum_id = $forum_id
		ORDER BY f2.left_id " . (($order == 'descending') ? 'ASC' : 'DESC');
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

// Create forum navigation links for given forum, create parent
// list if currently null, assign basic forum info to template
function generate_forum_nav(&$forum_data)
{
	global $db, $user, $template, $phpEx, $SID;

	// Get forum parents
	$forum_parents = array();
	if ($forum_data['parent_id'] > 0)
	{
		if (empty($forum_data['forum_parents']))
		{
			$sql = 'SELECT forum_id, forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE left_id < ' . $forum_data['left_id'] . '
					  AND right_id > ' . $forum_data['right_id'] . '
					ORDER BY left_id ASC';

			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_parents[$row['forum_id']] = $row['forum_name'];
			}

			$sql = 'UPDATE ' . FORUMS_TABLE . "
					SET forum_parents = '" . $db->sql_escape(serialize($forum_parents)) . "'
					WHERE parent_id = " . $forum_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$forum_parents = unserialize($forum_data['forum_parents']);
		}
	}

	// Build navigation links
	foreach ($forum_parents as $parent_forum_id => $parent_name)
	{
		$template->assign_block_vars('navlinks', array(
			'FORUM_NAME'	=>	$parent_name,
			'U_VIEW_FORUM'	=>	'viewforum.' . $phpEx . $SID . '&amp;f=' . $parent_forum_id
		));
	}
	$template->assign_block_vars('navlinks', array(
		'FORUM_NAME'	=>	$forum_data['forum_name'],
		'U_VIEW_FORUM'	=>	'viewforum.' . $phpEx . $SID . '&amp;f=' . $forum_data['forum_id']
	));

	$template->assign_vars(array(
		'FORUM_ID' 		=> $forum_data['forum_id'],
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_DESC'	=> strip_tags($forum_data['forum_desc'])
	));

	return;
}

// Obtain list of moderators of each forum
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $cache, $SID, $db, $acl_options, $phpEx;

	if (!empty($forum_id) && is_array($forum_id))
	{
		$forum_sql = 'AND forum_id IN (' . implode(', ', $forum_id) . ')';
	}
	else
	{
		$forum_sql = ($forum_id) ? 'AND forum_id = ' . $forum_id : '';
	}

	$sql = 'SELECT *
		FROM ' . MODERATOR_TABLE . "
		WHERE display_on_index = 1
			$forum_sql";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_moderators[$row['forum_id']][] = (!empty($row['user_id'])) ? '<a href="ucp.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>' : '<a href="groupcp.' . $phpEx . $SID . '&amp;g=' . $row['group_id'] . '">' . $row['groupname'] . '</a>';
	}
	$db->sql_freeresult($result);

	return;
}

// User authorisation levels output
function get_forum_rules($mode, &$rules, &$forum_id)
{
	global $SID, $auth, $user;

	$rules .= (($auth->acl_gets('f_post', 'm_', 'a_', $forum_id)) ? $user->lang['Rules_post_can'] : $user->lang['Rules_post_cannot']) . '<br />';
	$rules .= (($auth->acl_gets('f_reply', 'm_', 'a_', $forum_id)) ? $user->lang['Rules_reply_can'] : $user->lang['Rules_reply_cannot']) . '<br />';
	$rules .= (($auth->acl_gets('f_edit', 'm_', 'a_', $forum_id)) ? $user->lang['Rules_edit_can'] : $user->lang['Rules_edit_cannot']) . '<br />';
	$rules .= (($auth->acl_gets('f_delete', 'm_', 'a_', $forum_id) || $auth->acl_get('m_delete', $forum_id)) ? $user->lang['Rules_delete_can'] : $user->lang['Rules_delete_cannot']) . '<br />';
	$rules .= (($auth->acl_gets('f_attach', 'm_', 'a_', $forum_id)) ? $user->lang['Rules_attach_can'] : $user->lang['Rules_attach_cannot']) . '<br />';

	return;
}

function make_jumpbox($action, $forum_id = false, $extra_form_fields = array())
{
	global $auth, $template, $user, $db, $nav_links, $phpEx;

	$boxstring = '<select name="f" onChange="if(this.options[this.selectedIndex].value != -1){ forms[\'jumpbox\'].submit() }"><option value="-1">' . $user->lang['Select_forum'] . '</option><option value="-1">-----------------</option>';

	$sql = 'SELECT forum_id, forum_name, forum_postable, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	$right = $cat_right = 0;
	$padding = $forum_list = $holding = '';
	while ($row = $db->sql_fetchrow($result))
	{
		if (!$row['forum_postable'] && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if (!$auth->acl_gets('f_list', 'm_', 'a_', intval($row['forum_id'])))
		{
			// if the user does not have permissions to list this forum skip
			continue;
		}

		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = substr($padding, 0, -13 * ($row['left_id'] - $right + 1));
		}

		$right = $row['right_id'];

		$selected = ($row['forum_id'] == $forum_id) ? ' selected="selected"' : '';

		if ($row['left_id'] > $cat_right)
		{
			$holding = '';
		}

		if ($row['right_id'] - $row['left_id'] > 1)
		{
			$cat_right = max($cat_right, $row['right_id']);

			$holding .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . '+ ' . $row['forum_name'] . '</option>';
		}
		else
		{
			$boxstring .= $holding . '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . '- ' . $row['forum_name'] . '</option>';
			$holding = '';
		}

		$nav_links['chapter forum'][$row['forum_id']] = array (
			'url' => "viewforum.$phpEx$SID&f=" . $row['forum_id'],
			'title' => $row['forum_name']
		);
	}
	$db->sql_freeresult($result);

	if (!$right)
	{
		$boxstring .= '<option value="-1">' . $user->lang['No_forums'] . '</option>';
	}

	$boxstring .= '</select>';

	$extra_form_fields['sid'] = $user->session_id;
	foreach ($extra_form_fields as $key => $val)
	{
		$boxstring .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($val) . '" />';
	}

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
	while ($file = readdir($dir))
	{
		if (!is_dir($dirname . '/' . $file))
		{
			continue;
		}
		if (@file_exists($dirname . '/' . $file . '/iso.txt'))
		{
			list($displayname) = file($dirname . '/' . $file . '/iso.txt');
			$lang[$displayname] = $file;
		}
	}
	@closedir($dir);

	@asort($lang);
	@reset($lang);

	$user_select = '<select name="' . $select_name . '">';
	foreach ($lang as $displayname => $filename)
	{
		$selected = (strtolower($default) == strtolower($filename)) ? ' selected="selected"' : '';
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
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['style_id'] == $default_style) ? ' selected="selected"' : '';

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
	foreach ($user->lang['tz'] as $offset => $zone)
	{
		$selected = ($offset == $default) ? ' selected="selected"' : '';
		$tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
	}
	$tz_select .= '</select>';

	return $tz_select;
}

// Topic and forum watching common code
function watch_topic_forum($mode, &$s_watching, &$s_watching_img, $user_id, $match_id, $notify_status = 'unset')
{
	global $template, $db, $user, $phpEx, $SID, $start;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$u_url = ($mode == 'forum') ? 'f' : 't';

	// Is user watching this thread?
	if ($user_id)
	{
		$can_watch = TRUE;

		if ($notify_status == 'unset')
		{
			$sql = "SELECT notify_status
				FROM $table_sql
				WHERE $where_sql = $match_id
					AND user_id = $user_id";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$notify_status = $row['notify_status'];
			}
			else
			{
				$notify_status = NULL;
			}
		}

		if (!is_null($notify_status))
		{
			if (isset($_GET['unwatch']))
			{
				if ($_GET['unwatch'] == $mode)
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

				if ($notify_status)
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
			if (isset($_GET['watch']))
			{
				if ($_GET['watch'] == $mode)
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
		if (isset($_GET['unwatch']))
		{
			if ($_GET['unwatch'] == $mode)
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

	if ($can_watch)
	{
		$s_watching = ($is_watching) ? '<a href="' . "view$mode." . $phpEx . $SID . '&amp;' . $u_url . "=$match_id&amp;unwatch=$mode&amp;start=$start" . '">' . $user->lang['Stop_watching_' . $mode] . '</a>' : '<a href="' . "view$mode." . $phpEx . $SID . '&amp;' . $u_url . "=$match_id&amp;watch=$mode&amp;start=$start" . '">' . $user->lang['Start_watching_' . $mode] . '</a>';
	}

	return;
}

// Marks a topic or form as read in the 'lastread' table.
function markread($mode, $forum_id = 0, $topic_id = 0, $post_id = 0)
{
	global $db, $user;
	
	if ($user->data['user_id'] == ANONYMOUS)
	{
		return;
	}

	switch ($mode)
	{
		case 'mark':
			// Mark one forum as read.
			// Do this by inserting a record with -$forum_id in the 'forum_id' field.
			$sql = "SELECT forum_id 
				FROM " . LASTREAD_TABLE . "
				WHERE user_id = " . $user->data['user_id'] . " 
					AND forum_id = -$forum_id";
			$result = $db->sql_query($sql);

			if ($db->sql_fetchrow($result))
			{
				// User has marked this topic as read before: Update the record
				$sql = "UPDATE " . LASTREAD_TABLE . "
					SET lastread_time = " . time() . "
					WHERE user_id = " . $user->data['user_id'] . "
						AND forum_id = -$forum_id";
				$db->sql_query($sql);
			}
			else
			{
				// User is marking this forum for the first time.
				// Insert dummy topic_id to satisfy PRIMARY KEY (user_id, topic_id)
				// dummy id = -forum_id
				$sql = "INSERT INTO " . LASTREAD_TABLE . "
					(user_id, forum_id, topic_id, lastread_time)
					VALUES
					(" . $user->data['user_id'] . ", -$forum_id, -$forum_id, " . time() . ")";
				$db->sql_query($sql);
			}
			break;

		case 'markall':
			// Mark all forums as read.
			// Select all forum_id's that are not yet in the lastread table
			$sql = "SELECT f.forum_id
				FROM " . FORUMS_TABLE . " f
				LEFT JOIN (" . LASTREAD_TABLE . " lr ON (
					lr.user_id = " . $user->data['user_id'] . "
					AND f.forum_id = -lr.forum_id))
				WHERE lr.forum_id IS NULL";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				// Some forum_id's are missing. We are not taking into account
				// the auth data, even forums the user can't see are marked as read.
				$sql = "INSERT INTO " . LASTREAD_TABLE . "
					(user_id, forum_id, topic_id, lastread_time)
					VALUES\n";
				$forum_insert = array();

				do 				
				{
					// Insert dummy topic_id to satisfy PRIMARY KEY
					// dummy id = -forum_id
					$forum_insert[] = "(" . $user->data['user_id'] . ", -".$row['forum_id'].", -".$row['forum_id'].", " . time() . ")";
				}
				while ($row = $db->sql_fetchrow($result));

				$forum_insert = implode(",\n", $forum_insert);
				$sql .= $forum_insert;

				$db->sql_query($sql);
			}

			// Mark all forums as read
			$sql = "UPDATE " . LASTREAD_TABLE . "
				SET lastread_time = " . time() . "
				WHERE user_id = " . $user->data['user_id'] . "
					AND forum_id < 0";
			$db->sql_query($sql);
			break;

		case 'post':
			// Mark a topic as read and mark it as a topic where the user has made a post.
			$type = 1;

		case 'topic':
			// Mark a topic as read.

			// Type:
			// 0 = Normal topic
			// 1 = user made a post in this topic
			$type_update = (isset($type) && $type = 1) ? 'lastread_type = 1,' : '';
			$sql = "UPDATE " . LASTREAD_TABLE . "
				SET $type_update forum_id = $forum_id, lastread_time = " . time() . "
				WHERE topic_id = $topic_id
					AND user_id = " . $user->data['user_id'];
			$db->sql_query($sql);

			if ($db->sql_affectedrows($result) == 0)
			{
				// Couldn't update. Row probably doesn't exist. Insert one.
				if(isset($type) && $type = 1)
				{
					$type_name = 'lastread_type, ';
					$type_value = '1, ';
				}
				else
				{
					$type_name = '';
					$type_value = '';
				}

				$sql = "INSERT INTO " . LASTREAD_TABLE . "
					(user_id, topic_id, forum_id, $type_name lastread_time)
					VALUES
					(" . $user->data['user_id'] . ", $topic_id, $forum_id, $type_value " . time() . ")";
				$db->sql_query($sql);
			}
			break;
	}
}

// Pagination routine, generates page number sequence
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = TRUE)
{
	global $user;

	$total_pages = ceil($num_items/$per_page);

	if ($total_pages == 1 || !$num_items)
	{
		return '';
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = ($on_page == 1) ? '<b>1</b>' : '<a href="' . $base_url . "&amp;start=" . (($on_page - 2) * $per_page) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;<a href="' . $base_url . '">1</a>';

	if ($total_pages > 5)
	{
		$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 4), 6);

		$page_string .= ($start_cnt > 1) ? ' ... ' : ', ';

		for($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $end_cnt - 1)
			{
				$page_string .= ', ';
			}
		}

		$page_string .= ($end_cnt < $total_pages) ? ' ... ' : ', ';
	}
	else
	{
		$page_string .= ', ';

		for($i = 2; $i < $total_pages; $i++)
		{
			$page_string .= ($i == $on_page) ? '<b>' . $i . '</b>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $total_pages)
			{
				$page_string .= ', ';
			}
		}
	}

	$page_string .= ($on_page == $total_pages) ? '<b>' . $total_pages . '</b>' : '<a href="' . $base_url . '&amp;start=' . (($total_pages - 1) * $per_page) . '">' . $total_pages . '</a>&nbsp;&nbsp;<a href="' . $base_url . "&amp;start=" . ($on_page * $per_page) . '">' . $user->lang['NEXT'] . '</a>';

	$page_string = $user->lang['GOTO_PAGE'] . ' ' . $page_string;

	return $page_string;
}

function on_page($num_items, $per_page, $start)
{
	global $user;

	return sprintf($user->lang['PAGE_OF'], floor($start / $per_page) + 1, max(ceil($num_items / $per_page), 1));
}

// Obtain list of naughty words and build preg style replacement arrays for use by the
// calling script, note that the vars are passed as references this just makes it easier
// to return both sets of arrays
function obtain_word_list(&$censors)
{
	global $db, $cache;

	if ($cache->exists('word_censors'))
	{
		$censors = $cache->get('word_censors'); // transfer to just if (!(...)) ? works fine for me
	}
	else
	{
		$sql = "SELECT word, replacement
			FROM  " . WORDS_TABLE;
		$result = $db->sql_query($sql);

		$censors = array();
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$censors['match'][] = '#\b(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')\b#i';
				$censors['replace'][] = $row['replacement'];
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
		$cache->put('word_censors', $censors);
	}

	return true;
}

// Obtain currently listed icons, re-caching if necessary
function obtain_icons(&$icons)
{
	global $db, $cache;

	if ($cache->exists('icons'))
	{
		$icons = $cache->get('icons');
	}
	else
	{
		// Topic icons
		$sql = "SELECT *
			FROM " . ICONS_TABLE . " 
			ORDER BY icons_order";
		$result = $db->sql_query($sql);

		$icons = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$icons[$row['icons_id']]['img'] = $row['icons_url'];
			$icons[$row['icons_id']]['width'] = $row['icons_width'];
			$icons[$row['icons_id']]['height'] = $row['icons_height'];
			$icons[$row['icons_id']]['display'] = $row['display_on_posting'];
		}
		$db->sql_freeresult($result);

		$cache->put('icons', $icons);
	}

	return;
}

// Redirects the user to another page then exits the script nicely
function redirect($url)
{
	global $db, $cache, $config;

	if (isset($db))
	{
		$db->sql_close();
	}
	if (isset($cache))
	{
		$cache->save_cache();
	}

	$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
	$server_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($config['server_name']));
	$server_port = ($config['server_port'] <> 80) ? ':' . trim($config['server_port']) . '/' : '/';
	$script_name = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($config['script_path']));
	$url = (($script_name == '') ? '' : '/') . preg_replace('/^\/?(.*?)\/?$/', '\1', trim($url));

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $server_protocol . $server_name . $server_port . $script_name . $url);
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="refresh" content="0; url=' . $server_protocol . $server_name . $server_port . $script_name . $url . '"><title>Redirect</title></head><body><div align="center">If your browser does not support meta redirection please click <a href="' . $server_protocol . $server_name . $server_port . $script_name . $url . '">HERE</a> to be redirected</div></body></html>';
		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $server_protocol . $server_name . $server_port . $script_name . $url);
	exit;
}

// Check to see if the username has been taken, or if it is disallowed.
// Also checks if it includes the " character, which we don't allow in usernames.
// Used for registering, changing names, and posting anonymously with a username
function validate_username($username)
{
	global $db, $user;

	$username = $db->sql_escape($username);

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
		if (preg_match('#\b(' . str_replace('\*', '.*?', preg_quote($row['disallow_username'], '#')) . ')\b#i', $username))
		{
			return $user->lang['Username_disallowed'];
		}
	}

	$sql = "SELECT word
		FROM  " . WORDS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		if (preg_match('#\b(' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . ')\b#i', $username))
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
				if (preg_match('#^' . str_replace('*', '.*?', $row['ban_email']) . '$#is', $email))
				{
					return $user->lang['Email_banned'];
				}
			}

			$sql = "SELECT user_email
				FROM " . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($email) . "'";
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
		if (strlen($$check_var_length[$i]) < 2)
		{
			$$check_var_length[$i] = '';
		}
	}

	// ICQ number has to be only numbers.
	if (!preg_match('/^[0-9]+$/', $icq))
	{
		$icq = '';
	}

	// website has to start with http://, followed by something with length at least 3 that
	// contains at least one dot.
	if ($website != '')
	{
		if (!preg_match('#^http[s]?:\/\/#i', $website))
		{
			$website = 'http://' . $website;
		}

		if (!preg_match('#^http[s]?\\:\\/\\/[a-z0-9\-]+\.([a-z0-9\-]+\.)?[a-z]+#i', $website))
		{
			$website = '';
		}
	}

	return;
}

// Error and message handler, call with trigger_error if reqd
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $cache, $db, $auth, $template, $config, $user, $nav_links;
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
			// 20021125 Bartvb (todo)
			// This is a hack just to show something useful.
			// $msg_text won't contain anything if $user isn't there yet.
			// I ran into this problem when installing without makeing config_cache.php writable
			if (!isset($user))
			{
				die("Unable to show notice, \$user class hasn't been instantiated yet.<br />Error triggered in: " . $errfile .":". $errline);
			}
			
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