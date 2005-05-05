<?php
/** 
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Set variable, used by {@link request_var the request_var function}
*/
function set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type == 'string')
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", '\xFF'), array("\n", "\n", ' '), $result)));
//		$result = preg_replace("#\n{3,}#", "\n\n", $result);
		$result = (STRIP) ? stripslashes($result) : $result;
		if ($multibyte)
		{
			$result = preg_replace('#&amp;(\#[0-9]+;)#', '&\1', $result);
		}
	}
}

/**
* request_var
*
* Used to get passed variable
*/
function request_var($var_name, $default, $multibyte = false)
{
	if (!isset($_REQUEST[$var_name]))
	{
		return $default;
	}
	else
	{
		$var = $_REQUEST[$var_name];
		$type = gettype($default);

		if (is_array($var))
		{
			foreach ($var as $k => $v)
			{
				if (is_array($v))
				{
					foreach ($v as $_k => $_v)
					{
						set_var($var[$k][$_k], $_v, $type, $multibyte);
					}
				}
				else
				{
					set_var($var[$k], $v, $type, $multibyte);
				}
			}
		}
		else
		{
			set_var($var, $var, $type, $multibyte);
		}

		return $var;
	}
}

/**
* Set config value. Creates missing config entry.
*/
function set_config($config_name, $config_value, $is_dynamic = false)
{
	global $db, $cache, $config;

	$sql = 'UPDATE ' . CONFIG_TABLE . "
		SET config_value = '" . $db->sql_escape($config_value) . "'
		WHERE config_name = '" . $db->sql_escape($config_name) . "'";
	$db->sql_query($sql);

	if (!$db->sql_affectedrows() && !isset($config[$config_name]))
	{
		$sql = 'INSERT INTO ' . CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'config_name'	=> $config_name,
			'config_value'	=> $config_value,
			'is_dynamic'	=> ($is_dynamic) ? 1 : 0));
		$db->sql_query($sql);
	}

	$config[$config_name] = $config_value;

	if (!$is_dynamic)
	{
		$cache->destroy('config');
		$cache->save();
	}
}

/**
* Generates an alphanumeric random string of given length
*/
function gen_rand_string($num_chars)
{
	$chars = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J',  'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',  'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9');

	list($sec, $usec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));

	$max_chars = sizeof($chars) - 1;
	$rand_str = '';
	for ($i = 0; $i < $num_chars; $i++)
	{
		$rand_str .= $chars[mt_rand(0, $max_chars)];
	}

	return $rand_str;
}

/**
* Return unique id
*/
function unique_id()
{
	list($sec, $usec) = explode(' ', microtime());
	mt_srand((float) $sec + ((float) $usec * 100000));
	return uniqid(mt_rand(), true);
}

/**
* Get userdata
* @param mixed $user user id or username
*/
function get_userdata($user)
{
	global $db;

	$sql = 'SELECT *
		FROM ' . USERS_TABLE . '
		WHERE ';
	$sql .= ((is_integer($user)) ? "user_id = $user" : "username = '" .  $db->sql_escape($user) . "'") . " AND user_id <> " . ANONYMOUS;
	$result = $db->sql_query($sql);

	return ($row = $db->sql_fetchrow($result)) ? $row : false;
}

/**
* Create forum rules for given forum
*/
function generate_forum_rules(&$forum_data)
{
	if (!$forum_data['forum_rules'] && !$forum_data['forum_rules_link'])
	{
		return;
	}

	global $template, $phpbb_root_path, $phpEx;

	if ($forum_data['forum_rules'])
	{
		include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);
		$bbcode = new bbcode($forum_data['forum_rules_bbcode_bitfield']);

		$bbcode->bbcode_second_pass($forum_data['forum_rules'], $forum_data['forum_rules_bbcode_uid']);

		$forum_data['forum_rules'] = smiley_text($forum_data['forum_rules'], !($forum_data['forum_rules_flags'] & 2));
		$forum_data['forum_rules'] = str_replace("\n", '<br />', censor_text($forum_data['forum_rules']));
		unset($bbcode);
	}

	$template->assign_vars(array(
		'S_FORUM_RULES'	=> true,
		'U_FORUM_RULES'	=> $forum_data['forum_rules_link'],
		'FORUM_RULES'	=> $forum_data['forum_rules'])
	);
}

/**
* Create forum navigation links for given forum, create parent
* list if currently null, assign basic forum info to template
*/
function generate_forum_nav(&$forum_data)
{
	global $db, $user, $template, $phpEx, $SID, $phpbb_root_path;

	// Get forum parents
	$forum_parents = get_forum_parents($forum_data);

	// Build navigation links
	foreach ($forum_parents as $parent_forum_id => $parent_data)
	{
		list($parent_name, $parent_type) = array_values($parent_data);

		$template->assign_block_vars('navlinks', array(
			'S_IS_CAT'		=> ($parent_type == FORUM_CAT) ? true : false,
			'S_IS_LINK'		=> ($parent_type == FORUM_LINK) ? true : false,
			'S_IS_POST'		=> ($parent_type == FORUM_POST) ? true : false,
			'FORUM_NAME'	=> $parent_name,
			'FORUM_ID'		=> $parent_forum_id,
			'U_VIEW_FORUM'	=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=$parent_forum_id")
		);
	}

	$template->assign_block_vars('navlinks', array(
		'S_IS_CAT'		=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
		'S_IS_LINK'		=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
		'S_IS_POST'		=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_ID'		=> $forum_data['forum_id'],
		'U_VIEW_FORUM'	=> "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $forum_data['forum_id'])
	);

	$template->assign_vars(array(
		'FORUM_ID' 		=> $forum_data['forum_id'],
		'FORUM_NAME'	=> $forum_data['forum_name'],
		'FORUM_DESC'	=> strip_tags($forum_data['forum_desc']))
	);

	return;
}

/**
* Returns forum parents as an array. Get them from forum_data if available, or update the database otherwise
*/
function get_forum_parents(&$forum_data)
{
	global $db;

	$forum_parents = array();

	if ($forum_data['parent_id'] > 0)
	{
		if ($forum_data['forum_parents'] == '')
		{
			$sql = 'SELECT forum_id, forum_name, forum_type
				FROM ' . FORUMS_TABLE . '
				WHERE left_id < ' . $forum_data['left_id'] . '
					AND right_id > ' . $forum_data['right_id'] . '
				ORDER BY left_id ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$forum_parents[$row['forum_id']] = array($row['forum_name'], (int) $row['forum_type']);
			}
			$db->sql_freeresult($result);

			$forum_data['forum_parents'] = serialize($forum_parents);

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET forum_parents = '" . $db->sql_escape($forum_data['forum_parents']) . "'
				WHERE parent_id = " . $forum_data['parent_id'];
			$db->sql_query($sql);
		}
		else
		{
			$forum_parents = unserialize($forum_data['forum_parents']);
		}
	}

	return $forum_parents;
}

/**
* Obtain list of moderators of each forum
*/
function get_moderators(&$forum_moderators, $forum_id = false)
{
	global $config, $template, $db, $phpEx, $SID;

	// Have we disabled the display of moderators? If so, then return
	// from whence we came ...
	if (empty($config['load_moderators']))
	{
		return;
	}

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
		$forum_moderators[$row['forum_id']][] = (!empty($row['user_id'])) ? '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=viewprofile&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>' : '<a href="memberlist.' . $phpEx . $SID . '&amp;mode=group&amp;g=' . $row['group_id'] . '">' . $row['groupname'] . '</a>';
	}
	$db->sql_freeresult($result);

	return;
}

/**
* User authorisation levels output
*/
function gen_forum_auth_level($mode, $forum_id)
{
	global $SID, $template, $auth, $user;

	$rules = array(
		($auth->acl_get('f_post', $forum_id)) ? $user->lang['RULES_POST_CAN'] : $user->lang['RULES_POST_CANNOT'],
		($auth->acl_get('f_reply', $forum_id)) ? $user->lang['RULES_REPLY_CAN'] : $user->lang['RULES_REPLY_CANNOT'],
		($auth->acl_gets('f_edit', 'm_edit', $forum_id)) ? $user->lang['RULES_EDIT_CAN'] : $user->lang['RULES_EDIT_CANNOT'],
		($auth->acl_gets('f_delete', 'm_delete', $forum_id)) ? $user->lang['RULES_DELETE_CAN'] : $user->lang['RULES_DELETE_CANNOT'],
		($auth->acl_get('f_attach', $forum_id) && $auth->acl_get('u_attach', $forum_id)) ? $user->lang['RULES_ATTACH_CAN'] : $user->lang['RULES_ATTACH_CANNOT']
	);

	foreach ($rules as $rule)
	{
		$template->assign_block_vars('rules', array('RULE' => $rule));
	}

	return;
}

/**
* Generate sort selection fields
*/
function gen_sort_selects(&$limit_days, &$sort_by_text, &$sort_days, &$sort_key, &$sort_dir, &$s_limit_days, &$s_sort_key, &$s_sort_dir, &$u_sort_param)
{
	global $user;

	$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

	$s_limit_days = '<select name="st">';
	foreach ($limit_days as $day => $text)
	{
		$selected = ($sort_days == $day) ? ' selected="selected"' : '';
		$s_limit_days .= '<option value="' . $day . '"' . $selected . '>' . $text . '</option>';
	}
	$s_limit_days .= '</select>';

	$s_sort_key = '<select name="sk">';
	foreach ($sort_by_text as $key => $text)
	{
		$selected = ($sort_key == $key) ? ' selected="selected"' : '';
		$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $text . '</option>';
	}
	$s_sort_key .= '</select>';

	$s_sort_dir = '<select name="sd">';
	foreach ($sort_dir_text as $key => $value)
	{
		$selected = ($sort_dir == $key) ? ' selected="selected"' : '';
		$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
	}
	$s_sort_dir .= '</select>';

	$u_sort_param = "st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir";

	return;
}

/**
* Generate Jumpbox
*/
function make_jumpbox($action, $forum_id = false, $select_all = false, $acl_list = false)
{
	global $config, $auth, $template, $user, $db, $phpEx, $SID;

	if (!$config['load_jumpbox'])
	{
		return;
	}

	$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 600);

	$right = $padding = 0;
	$padding_store = array('0' => 0);
	$display_jumpbox = false;
	$iteration = 0;

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['left_id'] < $right)
		{
			$padding++;
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = $padding_store[$row['parent_id']];
		}

		$right = $row['right_id'];

		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if (!$auth->acl_get('f_list', $row['forum_id']))
		{
			// if the user does not have permissions to list this forum skip
			continue;
		}

		if ($acl_list && !$auth->acl_gets($acl_list, $row['forum_id']))
		{
			continue;
		}

		if (!$display_jumpbox)
		{
			$template->assign_block_vars('jumpbox_forums', array(
				'FORUM_ID'		=> ($select_all) ? 0 : -1,
				'FORUM_NAME'	=> ($select_all) ? $user->lang['ALL_FORUMS'] : $user->lang['SELECT_FORUM'],
				'S_FORUM_COUNT'	=> $iteration)
			);

			$iteration++;
			$display_jumpbox = true;
		}

		$template->assign_block_vars('jumpbox_forums', array(
			'FORUM_ID'		=> $row['forum_id'],
			'FORUM_NAME'	=> $row['forum_name'],
			'SELECTED'		=> ($row['forum_id'] == $forum_id) ? ' selected="selected"' : '',
			'S_FORUM_COUNT'	=> $iteration,
			'S_IS_CAT'		=> ($row['forum_type'] == FORUM_CAT) ? true : false,
			'S_IS_LINK'		=> ($row['forum_type'] == FORUM_LINK) ? true : false,
			'S_IS_POST'		=> ($row['forum_type'] == FORUM_POST) ? true : false)
		);

		for ($i = 0; $i < $padding; $i++)
		{
			$template->assign_block_vars('jumpbox_forums.level', array());
		}
		$iteration++;
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	$template->assign_vars(array(
		'S_DISPLAY_JUMPBOX'	=> $display_jumpbox,
		'S_JUMPBOX_ACTION'	=> $action)
	);

	return;
}

/**
* Pick a language, any language ...
*/
function language_select($default = '')
{
	global $db;

	$sql = 'SELECT lang_iso, lang_local_name
		FROM ' . LANG_TABLE . '
		ORDER BY lang_english_name';
	$result = $db->sql_query($sql);

	$lang_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['lang_iso'] == $default) ? ' selected="selected"' : '';
		$lang_options .= '<option value="' . $row['lang_iso'] . '"' . $selected . '>' . $row['lang_local_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	return $lang_options;
}

/** 
* Pick a template/theme combo,
*/
function style_select($default = '', $all = false)
{
	global $db;

	$sql_where = (!$all) ? 'WHERE style_active = 1 ' : '';
	$sql = 'SELECT style_id, style_name
		FROM ' . STYLES_TABLE . "
		$sql_where
		ORDER BY style_name";
	$result = $db->sql_query($sql);

	$style_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['style_id'] == $default) ? ' selected="selected"' : '';
		$style_options .= '<option value="' . $row['style_id'] . '"' . $selected . '>' . $row['style_name'] . '</option>';
	}
	$db->sql_freeresult($result);

	return $style_options;
}

/**
* Pick a timezone
*/
function tz_select($default = '')
{
	global $sys_timezone, $user;

	$tz_select = '';
	foreach ($user->lang['tz']['zones'] as $offset => $zone)
	{
		if (is_numeric($offset))
		{
			$selected = ($offset == $default) ? ' selected="selected"' : '';
			$tz_select .= '<option value="' . $offset . '"' . $selected . '>' . $zone . '</option>';
		}
	}

	return $tz_select;
}

/**
* Topic and forum watching common code
*/
function watch_topic_forum($mode, &$s_watching, &$s_watching_img, $user_id, $match_id, $notify_status = 'unset', $start = 0)
{
	global $template, $db, $user, $phpEx, $SID, $start, $phpbb_root_path;

	$table_sql = ($mode == 'forum') ? FORUMS_WATCH_TABLE : TOPICS_WATCH_TABLE;
	$where_sql = ($mode == 'forum') ? 'forum_id' : 'topic_id';
	$u_url = ($mode == 'forum') ? 'f' : 't';

	// Is user watching this thread?
	if ($user_id != ANONYMOUS)
	{
		$can_watch = TRUE;

		if ($notify_status == 'unset')
		{
			$sql = "SELECT notify_status
				FROM $table_sql
				WHERE $where_sql = $match_id
					AND user_id = $user_id";
			$result = $db->sql_query($sql);

			$notify_status = ($row = $db->sql_fetchrow($result)) ? $row['notify_status'] : NULL;
			$db->sql_freeresult($result);
		}

		if (!is_null($notify_status))
		{
			if (isset($_GET['unwatch']))
			{
				if ($_GET['unwatch'] == $mode)
				{
					$is_watching = 0;

					$sql = 'DELETE FROM ' . $table_sql . "
						WHERE $where_sql = $match_id
							AND user_id = $user_id";
					$db->sql_query($sql);
				}

				meta_refresh(3, "view$mode.$phpEx$SID&amp;$u_url=$match_id&amp;start=$start");

				$message = $user->lang['NOT_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				$is_watching = TRUE;

				if ($notify_status)
				{
					$sql = 'UPDATE ' . $table_sql . "
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

					$sql = 'INSERT INTO ' . $table_sql . " (user_id, $where_sql, notify_status)
						VALUES ($user_id, $match_id, 0)";
					$db->sql_query($sql);
				}

				meta_refresh(3, "view$mode.$phpEx$SID&amp;$u_url=$match_id&amp;start=$start");

				$message = $user->lang['ARE_WATCHING_' . strtoupper($mode)] . '<br /><br />' . sprintf($user->lang['RETURN_' . strtoupper($mode)], '<a href="' . "view$mode.$phpEx$SID&amp;" . $u_url . "=$match_id&amp;start=$start" . '">', '</a>');
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
		if (isset($_GET['unwatch']) && $_GET['unwatch'] == $mode)
		{
			login_box();
		}
		else
		{
			$can_watch = 0;
			$is_watching = 0;
		}
	}

	if ($can_watch)
	{
		$s_watching['link'] = "{$phpbb_root_path}view$mode.$phpEx$SID&amp;$u_url=$match_id&amp;" . (($is_watching) ? 'unwatch' : 'watch') . "=$mode&amp;start=$start";
		$s_watching['title'] = $user->lang[(($is_watching) ? 'STOP' : 'START') . '_WATCHING_' . strtoupper($mode)];
	}

	return;
}

/**
* Marks a topic or form as read
*/
function markread($mode, $forum_id = 0, $topic_id = 0, $marktime = false)
{
	global $config, $db, $user;

	if (!$user->data['is_registered'])
	{
		return;
	}

	if (!is_array($forum_id))
	{
		$forum_id = array($forum_id);
	}

	// Default tracking type
	$type = TRACK_NORMAL;
	$current_time = ($marktime) ? $marktime : time();
	$topic_id = (int) $topic_id;

	switch ($mode)
	{
		case 'mark':
			if ($config['load_db_lastread'])
			{
				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TRACK_TABLE . '
					WHERE user_id = ' . $user->data['user_id'] . '
						AND forum_id IN (' . implode(', ', array_map('intval', $forum_id)) . ')';
				$result = $db->sql_query($sql);

				$sql_update = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$sql_update[] = $row['forum_id'];
				}
				$db->sql_freeresult($result);

				if (sizeof($sql_update))
				{
					$sql = 'UPDATE ' . FORUMS_TRACK_TABLE . "
						SET mark_time = $current_time
						WHERE user_id = " . $user->data['user_id'] . '
							AND forum_id IN (' . implode(', ', $sql_update) . ')';
					$db->sql_query($sql);

					$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . '
						WHERE user_id = ' . $user->data['user_id'] . '
							AND forum_id IN (' . implode(', ', $sql_update) . ')
							AND mark_type = ' . TRACK_NORMAL;
					$db->sql_query($sql);
				}

				if ($sql_insert = array_diff($forum_id, $sql_update))
				{
					foreach ($sql_insert as $forum_id)
					{
						$sql = '';
						switch (SQL_LAYER)
						{
							case 'mysql':
								$sql .= (($sql != '') ? ', ' : '') . '(' . $user->data['user_id'] . ", $forum_id, $current_time)";
								$sql = 'VALUES ' . $sql;
								break;

							case 'mysql4':
							case 'mysqli':
							case 'mssql':
							case 'sqlite':
								$sql .= (($sql != '') ? ' UNION ALL ' : '') . ' SELECT ' . $user->data['user_id'] . ", $forum_id, $current_time";
								break;

							default:
								$sql = 'INSERT INTO ' . FORUMS_TRACK_TABLE . ' (user_id, forum_id, mark_time)
									VALUES (' . $user->data['user_id'] . ", $forum_id, $current_time)";
								$db->sql_query($sql);
								$sql = '';
						}

						if ($sql)
						{
							$sql = 'INSERT INTO ' . FORUMS_TRACK_TABLE . " (user_id, forum_id, mark_time) $sql";
							$db->sql_query($sql);
						}

						$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . '
							WHERE user_id = ' . $user->data['user_id'] . '
								AND forum_id = ' . $forum_id . '
								AND mark_type = ' . TRACK_NORMAL;
						$db->sql_query($sql);
					}
				}
				unset($sql_update);
				unset($sql_insert);
			}
			else
			{
				$tracking = (isset($_COOKIE[$config['cookie_name'] . '_track'])) ? unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track'])) : array();

				foreach ($forum_id as $f_id)
				{
					unset($tracking[$f_id]);
					$tracking[$f_id][0] = base_convert($current_time - $config['board_startdate'], 10, 36);
				}

				$user->set_cookie('track', serialize($tracking), time() + 31536000);
				unset($tracking);
			}
			break;

		case 'post':
			// Mark a topic as read and mark it as a topic where the user has made a post.
			$type = TRACK_POSTED;

		case 'topic':
			if (!isset($type))
			{
				$type = TRACK_NORMAL;
			}
		
			$forum_id =	(int) $forum_id[0];

			/// Mark a topic as read
			if ($config['load_db_lastread'] || ($config['load_db_track'] && $type == TRACK_POSTED))
			{
				$track_type = ($type == TRACK_POSTED) ? ', mark_type = ' . $type : '';
				$sql = 'UPDATE ' . TOPICS_TRACK_TABLE . "
					SET forum_id = $forum_id, mark_time = $current_time $track_type
					WHERE topic_id = $topic_id
						AND user_id = {$user->data['user_id']}
						AND mark_time < $current_time";
				if (!$db->sql_query($sql) || !$db->sql_affectedrows())
				{
					$db->sql_return_on_error(true);

					$sql_ary = array(
						'user_id'		=> $user->data['user_id'],
						'topic_id'		=> $topic_id,
						'forum_id'		=> $forum_id,
						'mark_type'		=> $type,
						'mark_time'		=> $current_time
					);
					
					$db->sql_query('INSERT INTO ' . TOPICS_TRACK_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));

					$db->sql_return_on_error(false);
				}
			}
			
			if (!$config['load_db_lastread'])
			{
				$tracking = array();
				if (isset($_COOKIE[$config['cookie_name'] . '_track']))
				{
					$tracking = unserialize(stripslashes($_COOKIE[$config['cookie_name'] . '_track']));

					// If the cookie grows larger than 2000 characters we will remove
					// the smallest value
					if (strlen($_COOKIE[$config['cookie_name'] . '_track']) > 2000)
					{
						foreach ($tracking as $f => $t_ary)
						{
							if (!isset($m_value) || min($t_ary) < $m_value)
							{
								$m_value = min($t_ary);
								$m_tkey = array_search($m_value, $t_ary);
								$m_fkey = $f;
							}
						}
						unset($tracking[$m_fkey][$m_tkey]);
					}
				}

				if (isset($tracking[$forum_id]) && base_convert($tracking[$forum_id][0], 36, 10) < $current_time)
				{
					$tracking[$forum_id][base_convert($topic_id, 10, 36)] = base_convert($current_time - $config['board_startdate'], 10, 36);

					$user->set_cookie('track', serialize($tracking), time() + 31536000);
				}
				else if (!isset($tracking[$forum_id]))
				{
					$tracking[$forum_id][0] = base_convert($current_time - $config['board_startdate'], 10, 36);
					$user->set_cookie('track', serialize($tracking), time() + 31536000);
				}
				unset($tracking);
			}
			break;
	}
}

/**
* Pagination routine, generates page number sequence
* tpl_prefix is for using different pagination blocks at one page
*/
function generate_pagination($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = false, $tpl_prefix = '')
{
	global $template, $user;

	$seperator = $user->theme['primary']['pagination_sep'];

	$total_pages = ceil($num_items/$per_page);

	if ($total_pages == 1 || !$num_items)
	{
		return false;
	}

	$on_page = floor($start_item / $per_page) + 1;

	$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';

	if ($total_pages > 5)
	{
		$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
		$end_cnt = max(min($total_pages, $on_page + 4), 6);

		$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

		for($i = $start_cnt + 1; $i < $end_cnt; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $end_cnt - 1)
			{
				$page_string .= $seperator;
			}
		}

		$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
	}
	else
	{
		$page_string .= $seperator;

		for($i = 2; $i < $total_pages; $i++)
		{
			$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "&amp;start=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
			if ($i < $total_pages)
			{
				$page_string .= $seperator;
			}
		}
	}

	$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . $base_url . '&amp;start=' . (($total_pages - 1) * $per_page) . '">' . $total_pages . '</a>';

	if ($add_prevnext_text)
	{
		if ($on_page != 1) 
		{
			$page_string = '<a href="' . $base_url . '&amp;start=' . (($on_page - 2) * $per_page) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
		}

		if ($on_page != $total_pages)
		{
			$page_string .= '&nbsp;&nbsp;<a href="' . $base_url . '&amp;start=' . ($on_page * $per_page) . '">' . $user->lang['NEXT'] . '</a>';
		}
	}

	$template->assign_vars(array(
		$tpl_prefix . 'BASE_URL'	=> $base_url,
		$tpl_prefix . 'PER_PAGE'	=> $per_page,

		$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 1) ? '' : $base_url . '&amp;start=' . (($on_page - 2) * $per_page),
		$tpl_prefix . 'NEXT_PAGE'	=> ($on_page == $total_pages) ? '' : $base_url . '&amp;start=' . ($on_page * $per_page))
	);

	return $page_string;
}

/**
* Return current page (pagination)
*/
function on_page($num_items, $per_page, $start)
{
	global $template, $user;

	$on_page = floor($start / $per_page) + 1;

	$template->assign_var('ON_PAGE', $on_page);

	return sprintf($user->lang['PAGE_OF'], $on_page, max(ceil($num_items / $per_page), 1));
}

/**
* Obtain list of naughty words and build preg style replacement arrays for use by the
* calling script
*/
function obtain_word_list(&$censors)
{
	global $db, $cache, $user;

	if (!$user->optionget('viewcensors') && $config['allow_nocensors'])
	{
		return;
	}

	if ($cache->exists('word_censors'))
	{
		$censors = $cache->get('word_censors');
	}
	else
	{
		$sql = 'SELECT word, replacement
			FROM  ' . WORDS_TABLE;
		$result = $db->sql_query($sql);

		$censors = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$censors['match'][] = '#\b(' . str_replace('\*', '\w*?', preg_quote($row['word'], '#')) . ')\b#i';
			$censors['replace'][] = $row['replacement'];
		}
		$db->sql_freeresult($result);

		$cache->put('word_censors', $censors);
	}

	return true;
}

/**
* Obtain currently listed icons
*/
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
		$sql = 'SELECT *
			FROM ' . ICONS_TABLE . '
			ORDER BY icons_order';
		$result = $db->sql_query($sql);

		$icons = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$icons[$row['icons_id']]['img'] = $row['icons_url'];
			$icons[$row['icons_id']]['width'] = (int) $row['icons_width'];
			$icons[$row['icons_id']]['height'] = (int) $row['icons_height'];
			$icons[$row['icons_id']]['display'] = (bool) $row['display_on_posting'];
		}
		$db->sql_freeresult($result);

		$cache->put('icons', $icons);
	}

	return;
}

/**
* Obtain ranks
*/
function obtain_ranks(&$ranks)
{
	global $db, $cache;

	if ($cache->exists('ranks'))
	{
		$ranks = $cache->get('ranks');
	}
	else
	{
		$sql = 'SELECT *
			FROM ' . RANKS_TABLE . '
			ORDER BY rank_min DESC';
		$result = $db->sql_query($sql);

		$ranks = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['rank_special'])
			{
				$ranks['special'][$row['rank_id']] = array(
					'rank_title'	=>	$row['rank_title'],
					'rank_image'	=>	$row['rank_image']
				);
			}
			else
			{
				$ranks['normal'][] = array(
					'rank_title'	=>	$row['rank_title'],
					'rank_min'		=>	$row['rank_min'],
					'rank_image'	=>	$row['rank_image']
				);
			}
		}
		$db->sql_freeresult($result);

		$cache->put('ranks', $ranks);
	}
}

/**
* Obtain allowed extensions
*/
function obtain_attach_extensions(&$extensions, $forum_id = false)
{
	global $db, $cache;

	if ($cache->exists('extensions'))
	{
		$extensions = $cache->get('extensions');
	}
	else
	{
		// The rule is to only allow those extensions defined. ;)
		$sql = 'SELECT e.extension, g.*
			FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
			WHERE e.group_id = g.group_id
				AND g.allow_group = 1';
		$result = $db->sql_query($sql);

		$extensions = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$extension = strtolower(trim($row['extension']));

			$extensions[$extension]['display_cat']		= (int) $row['cat_id'];
			$extensions[$extension]['download_mode']	= (int) $row['download_mode'];
			$extensions[$extension]['upload_icon']		= trim($row['upload_icon']);
			$extensions[$extension]['max_filesize']		= (int) $row['max_filesize'];

			$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : array();

			if ($row['allow_in_pm'])
			{
				$allowed_forums = array_merge($allowed_forums, array(0));
			}

			// Store allowed extensions forum wise
			$extensions['_allowed_'][$extension] = (!sizeof($allowed_forums)) ? 0 : $allowed_forums;
		}
		$db->sql_freeresult($result);

		$cache->put('extensions', $extensions);
	}

	if ($forum_id !== false)
	{
		$return = array();

		foreach ($extensions['_allowed_'] as $extension => $check)
		{
			$allowed = false;

			if (is_array($check))
			{
				// Check for private messaging
				if (sizeof($check) == 1 && $check[0] == 0)
				{
					$allowed = true;
					continue;
				}

				$allowed = (!in_array($forum_id, $check)) ? false : true;
			}
			else
			{
				$allowed = ($forum_id == 0) ? false : true;
			}
			
			if ($allowed)
			{
				$return['_allowed_'][$extension] = 0;
				$return[$extension] = $extensions[$extension];
			}
		}

		$extensions = $return;
	}

	return;
}

/**
* Obtain active bots
*/
function obtain_bots(&$bots)
{
	global $db, $cache;

	if ($cache->exists('bots'))
	{
		$bots = $cache->get('bots');
	}
	else
	{
		$sql = 'SELECT user_id, bot_agent, bot_ip 
			FROM ' . BOTS_TABLE . '
			WHERE bot_active = 1';
		$result = $db->sql_query($sql);
		
		while ($row = $db->sql_fetchrow($result))
		{
			$bots[] = $row;
		}
		$db->sql_freeresult($result);

		$cache->put('bots', $bots);
	}
	
	return;
}

/**
* Generate board url
*/
function generate_board_url()
{
	global $config;

	$path = preg_replace('#^/?(.*?)/?$#', '\1', trim($config['script_path']));

	return (($config['cookie_secure']) ? 'https://' : 'http://') . preg_replace('#^/?(.*?)/?$#', '\1', trim($config['server_name'])) . (($config['server_port'] <> 80) ? ':' . trim($config['server_port']) : '') . (($path) ? '/' . $path : '');
}

/**
* Redirects the user to another page then exits the script nicely
*/
function redirect($url)
{
	global $db, $cache, $config, $user;

	if (isset($db))
	{
		$db->sql_close();
	}

	if (isset($cache))
	{
		$cache->unload();
	}

	// Make sure no &amp;'s are in, this will break the redirect
	$url = str_replace('&amp;', '&', $url);

	// If relative path, prepend board url
	if (strpos($url, '://') === false && $url{0} != '/')
	{
		$url = generate_board_url() . preg_replace('#^/?(.*?)/?$#', '/\1', trim($url));
	}

	// Redirect via an HTML form for PITA webservers
	if (@preg_match('#Microsoft|WebSTAR|Xitami#', getenv('SERVER_SOFTWARE')))
	{
		header('Refresh: 0; URL=' . $url);
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="refresh" content="0; url=' . $url . '"><title>Redirect</title></head><body><div align="center">' . sprintf($user->lang['URL_REDIRECT'], '<a href="' . $url . '">', '</a>') . '</div></body></html>';

		exit;
	}

	// Behave as per HTTP/1.1 spec for others
	header('Location: ' . $url);
	exit;
}

/**
* Meta refresh assignment
*/
function meta_refresh($time, $url)
{
	global $template;

	$template->assign_vars(array(
		'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '">')
	);
}

/**
* Build Confirm box
*/
function confirm_box($check, $title = '', $hidden = '', $html_body = 'confirm_body.html')
{
	global $user, $template, $db;
	global $SID, $phpEx;

	if (isset($_POST['cancel']))
	{
		return false;
	}

	$confirm = false;
	if (isset($_POST['confirm']))
	{
		// language frontier
		if ($_POST['confirm'] == $user->lang['YES'])
		{
			$confirm = true;
		}
	}

	if ($check && $confirm)
	{
		$user_id = request_var('user_id', 0);
		$session_id = request_var('sess', '');
		$confirm_key = request_var('confirm_key', '');

		if ($user_id != $user->data['user_id'] || $session_id != $user->session_id || !$confirm_key || !$user->data['user_last_confirm_key'] || $confirm_key != $user->data['user_last_confirm_key'])
		{
			return false;
		}

		// Reset user_last_confirm_key
		$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = ''
			WHERE user_id = " . $user->data['user_id'];
		$db->sql_query($sql);

		return true;
	}
	else if ($check)
	{
		return false;
	}

	$s_hidden_fields = '<input type="hidden" name="user_id" value="' . $user->data['user_id'] . '" /><input type="hidden" name="sess" value="' . $user->session_id . '" /><input type="hidden" name="sid" value="' . $SID . '" />';

	// generate activation key
	$confirm_key = gen_rand_string(10);

	page_header($user->lang[$title]);

	$template->set_filenames(array(
		'body' => $html_body)
	);

	// If activation key already exist, we better do not re-use the key (something very strange is going on...)
	if (request_var('confirm_key', ''))
	{
		// This should not occur, therefore we cancel the operation to safe the user
		return false;
	}

	// re-add $SID
	$u_action = (strpos($user->page, ".{$phpEx}?") !== false) ? str_replace(".{$phpEx}?", ".$phpEx$SID&", $user->page) . '&' : $user->page . '?';
	$u_action .= 'confirm_key=' . $confirm_key;

	$template->assign_vars(array(
		'MESSAGE_TITLE'		=> $user->lang[$title],
		'MESSAGE_TEXT'		=> $user->lang[$title . '_CONFIRM'],

		'YES_VALUE'			=> $user->lang['YES'],
		'S_CONFIRM_ACTION'	=> $u_action,
		'S_HIDDEN_FIELDS'	=> $hidden . $s_hidden_fields)
	);

	$sql = 'UPDATE ' . USERS_TABLE . " SET user_last_confirm_key = '" . $db->sql_escape($confirm_key) . "'
		WHERE user_id = " . $user->data['user_id'];
	$db->sql_query($sql);

	page_footer();
}

/**
* Generate login box or verify password
*/
function login_box($redirect = '', $l_explain = '', $l_success = '', $admin = false, $s_display = true)
{
	global $SID, $db, $user, $template, $auth, $phpEx, $phpbb_root_path, $config;

	$err = '';

	if (isset($_POST['login']))
	{
		$username	= request_var('username', '');
		$password	= request_var('password', '');
		$autologin	= (!empty($_POST['autologin'])) ? TRUE : FALSE;
		$viewonline = (!empty($_POST['viewonline'])) ? 0 : 1;
		$admin 		= ($admin) ? 1 : 0;

		// If authentication is successful we redirect user to previous page
		if (($result = $auth->login($username, $password, $autologin, $viewonline, $admin)) === true)
		{
			// If admin authentication
			if ($admin)
			{
				add_log('admin', 'LOG_ADMIN_AUTH_SUCCESS');
			}
						
			$redirect = request_var('redirect', "index.$phpEx$SID");
			meta_refresh(3, $redirect);

			$message = (($l_success) ? $l_success : $user->lang['LOGIN_REDIRECT']) . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a> ');
			trigger_error($message);
		}

		if ($admin)
		{
			add_log('admin', 'LOG_ADMIN_AUTH_FAIL');
		}

		// If we get a non-numeric (e.g. string) value we output an error
		if (is_string($result))
		{
			trigger_error($result, E_USER_ERROR);
		}

		// If we get an integer zero then we are inactive, else the username/password is wrong
		$err = ($result === 0) ? $user->lang['ACTIVE_ERROR'] : $user->lang['LOGIN_ERROR'];
	}

	if (!$redirect)
	{
		$split_page = array();
		preg_match_all('#^.*?([a-z]+?)\.' . $phpEx . '\?(.*?)$#i', $user->page, $split_page, PREG_SET_ORDER);

		// No script name set? Assume index
		if (empty($split_page[0][1]))
		{
			$split_page[0][1] = 'index';
		}

		// Current page correctly formatted for (login) redirects
		$redirect = htmlspecialchars($split_page[0][1] . '.' . $phpEx . $SID . ((!empty($split_page[0][2])) ? '&' . $split_page[0][2] : ''));
	}

	$s_hidden_fields = '<input type="hidden" name="redirect" value="' . $redirect . '" />';
	$s_hidden_fields .= '<input type="hidden" name="sid" value="' . $SID . '" />';

	$template->assign_vars(array(
		'LOGIN_ERROR'		=> $err,
		'LOGIN_EXPLAIN'		=> $l_explain,

		'U_SEND_PASSWORD' 		=> ($config['email_enable']) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=sendpassword" : '',
		'U_RESEND_ACTIVATION'	=> ($config['require_activation'] != USER_ACTIVATION_NONE && $config['email_enable']) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=resend_act" : '',
		'U_TERMS_USE'			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=terms",
		'U_PRIVACY'				=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=privacy",

		'S_DISPLAY_FULL_LOGIN'	=> ($s_display) ? true : false,
		'S_LOGIN_ACTION'		=> (!$admin) ? "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=login" : "index.$phpEx$SID",
		'S_HIDDEN_FIELDS' 		=> $s_hidden_fields)
	);

	page_header($user->lang['LOGIN']);

	$template->set_filenames(array(
		'body' => 'login_body.html')
	);
	make_jumpbox("{$phpbb_root_path}viewforum.$phpEx");

	page_footer();
}

/**
* Generate forum login box
*/
function login_forum_box(&$forum_data)
{
	global $db, $config, $user, $template, $phpEx;

	$password = request_var('password', '');

	$sql = 'SELECT forum_id
		FROM ' . FORUMS_ACCESS_TABLE . '
		WHERE forum_id = ' . $forum_data['forum_id'] . '
			AND user_id = ' . $user->data['user_id'] . "
			AND session_id = '$user->session_id'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$db->sql_freeresult($result);
		return true;
	}
	$db->sql_freeresult($result);

	if ($password)
	{
		// Remove expired authorised sessions
		$sql = 'SELECT session_id
			FROM ' . SESSIONS_TABLE;
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = "'" . $db->sql_escape($row['session_id']) . "'";
			}
			while ($row = $db->sql_fetchrow($result));

			// Remove expired sessions
			$sql = 'DELETE FROM ' . FORUMS_ACCESS_TABLE . '
				WHERE session_id NOT IN (' . implode(', ', $sql_in) . ')';
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);

		if ($password == $forum_data['forum_password'])
		{
			$sql = 'INSERT INTO ' . FORUMS_ACCESS_TABLE . ' (forum_id, user_id, session_id)
				VALUES (' . $forum_data['forum_id'] . ', ' . $user->data['user_id'] . ", '" . $db->sql_escape($user->session_id) . "')";
			$db->sql_query($sql);

			return true;
		}

		$template->assign_var('LOGIN_ERROR', $user->lang['WRONG_PASSWORD']);
	}

	page_header();
	$template->set_filenames(array(
		'body' => 'login_forum.html')
	);
	page_footer();
}

/**
* Bump Topic Check - used by posting and viewtopic
*/
function bump_topic_allowed($forum_id, $topic_bumped, $last_post_time, $topic_poster, $last_topic_poster)
{
	global $config, $auth, $user;

	// Check permission and make sure the last post was not already bumped
	if (!$auth->acl_get('f_bump', $forum_id) || $topic_bumped)
	{
		return false;
	}

	// Check bump time range, is the user really allowed to bump the topic at this time?
	$bump_time = ($config['bump_type'] == 'm') ? $config['bump_interval'] * 60 : (($config['bump_type'] == 'h') ? $config['bump_interval'] * 3600 : $config['bump_interval'] * 86400);

	// Check bump time
	if ($last_post_time + $bump_time > time())
	{
		return false;
	}

	// Check bumper, only topic poster and last poster are allowed to bump
	if ($topic_poster != $user->data['user_id'] && $last_topic_poster != $user->data['user_id'] && !$auth->acl_get('m_', $forum_id))
	{
		return false;
	}

	// A bump time of 0 will completely disable the bump feature... not intended but might be useful.
	return $bump_time;
}

/**
* Censoring
*/
function censor_text($text)
{
	global $censors, $user;

	if (!isset($censors))
	{
		$censors = array();

		// TODO: For ANONYMOUS, this option should be enabled by default
		if ($user->optionget('viewcensors'))
		{
			obtain_word_list($censors);
		}
	}

	if (sizeof($censors) && $user->optionget('viewcensors'))
	{
		return preg_replace($censors['match'], $censors['replace'], $text);
	}

	return $text;
}

/**
* Smiley processing
*/
function smiley_text($text, $force_option = false)
{
	global $config, $user, $phpbb_root_path;

	return ($force_option || !$config['allow_smilies'] || !$user->optionget('viewsmilies')) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $text) : str_replace('<img src="{SMILIES_PATH}', '<img src="' . $phpbb_root_path . $config['smilies_path'], $text);
}

/**
* Inline Attachment processing
*/
function parse_inline_attachments(&$text, &$attachments, &$update_count, $forum_id = 0, $preview = false)
{
	global $config, $user;

	$attachments = display_attachments($forum_id, NULL, $attachments, $update_count, $preview, true);
	$tpl_size = sizeof($attachments);

	$unset_tpl = array();

	preg_match_all('#<!\-\- ia([0-9]+) \-\->(.*?)<!\-\- ia\1 \-\->#', $text, $matches, PREG_PATTERN_ORDER);

	$replace = array();
	foreach ($matches[0] as $num => $capture)
	{
		// Flip index if we are displaying the reverse way
		$index = ($config['display_order']) ? ($tpl_size-($matches[1][$num] + 1)) : $matches[1][$num];

		$replace['from'][] = $matches[0][$num];
		$replace['to'][] = (isset($attachments[$index])) ? $attachments[$index] : sprintf($user->lang['MISSING_INLINE_ATTACHMENT'], $matches[2][array_search($index, $matches[1])]);

		$unset_tpl[] = $index;
	}

	if (isset($replace['from']))
	{
		$text = str_replace($replace['from'], $replace['to'], $text);
	}

	return array_unique($unset_tpl);
}

/**
* Check if extension is allowed to be posted within forum X (forum_id 0 == private messaging)
*/
function extension_allowed($forum_id, $extension, &$extensions)
{
	if (!sizeof($extensions))
	{
		$extensions = array();
		obtain_attach_extensions($extensions);
	}

	if (!isset($extensions['_allowed_'][$extension]))
	{
		return false;
	}

	$check = $extensions['_allowed_'][$extension];

	if (is_array($check))
	{
		// Check for private messaging
		if (sizeof($check) == 1 && $check[0] == 0)
		{
			return true;
		}

		return (!in_array($forum_id, $check)) ? false : true;
	}
	else
	{
		return ($forum_id == 0) ? false : true;
	}

	return false;
}

/**
* Error and message handler, call with trigger_error if reqd
*/
function msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $cache, $db, $auth, $template, $config, $user;
	global $phpEx, $phpbb_root_path, $starttime, $display_header, $msg_title;

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:
			if (defined('DEBUG_EXTRA'))
			{
				if (!strstr($errfile, 'cache') && !strstr($errfile, 'template.php'))
				{
					echo "<b>PHP Notice</b>: in file <b>$errfile</b> on line <b>$errline</b>: <b>$msg_text</b><br>";
				}
			}
			break;

		case E_USER_ERROR:
			if (isset($db))
			{
				$db->sql_close();
			}

			if (isset($cache))
			{
				$cache->unload();
			}

			if (!defined('HEADER_INC'))
			{
				echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8869-1"><meta http-equiv="Content-Style-Type" content="text/css"><link rel="stylesheet" href="' . $phpbb_root_path . 'adm/subSilver.css" type="text/css"><style type="text/css">' . "\n";
				echo 'th { background-image: url(\'' . $phpbb_root_path . 'adm/images/cellpic3.gif\') }' . "\n";
				echo 'td.cat	{ background-image: url(\'' . $phpbb_root_path . 'adm/images/cellpic1.gif\') }' . "\n";
				echo '</style><title>' . $msg_title . '</title></head><body>';
				echo '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr><td><img src="' . $phpbb_root_path . 'adm/images/header_left.jpg" width="200" height="60" alt="phpBB Logo" title="phpBB Logo" border="0"/></td><td width="100%" background="' . $phpbb_root_path . 'adm/images/header_bg.jpg" height="60" align="right" nowrap="nowrap"><span class="maintitle">General Error</span> &nbsp; &nbsp; &nbsp;</td></tr></table>';
			}
			echo '<br clear="all" /><table width="85%" cellspacing="0" cellpadding="0" border="0" align="center"><tr><td><br clear="all" />' . $msg_text . '<hr />Please notify the board administrator or webmaster : <a href="mailto:' . $config['board_contact'] . '">' . $config['board_contact'] . '</a></td></tr></table><br clear="all" /></body></html>';

			exit;
			break;

		case E_USER_NOTICE:
			if (empty($user->data))
			{
				$user->start();
			}
			if (empty($user->lang))
			{
				$user->setup();
			}

			if (!defined('HEADER_INC'))
			{
				if (defined('IN_ADMIN') && $user->data['session_admin'])
				{
					adm_page_header('', '', false);
				}
				else
				{
					page_header();
				}
			}

			$msg_text = (!empty($user->lang[$msg_text])) ? $user->lang[$msg_text] : $msg_text;
			$msg_title = (!isset($msg_title)) ? $user->lang['INFORMATION'] : ((!empty($user->lang[$msg_title])) ? $user->lang[$msg_title] : $msg_title);
			$display_header = (!isset($display_header)) ? false : (bool) $display_header;

			if (defined('IN_ADMIN') && $user->data['session_admin'])
			{
				adm_page_message($msg_title, $msg_text, $display_header);
				adm_page_footer();
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

				// We do not want the cron script to be called on error messages
				define('IN_CRON', true);
				page_footer();
			}
			exit;
			break;
	}
}

/**
* Generate page header
*/
function page_header($page_title = '')
{
	global $db, $config, $template, $SID, $user, $auth, $phpEx, $phpbb_root_path;

	define('HEADER_INC', TRUE);

	// gzip_compression
	if ($config['gzip_compress'])
	{
		if (extension_loaded('zlib') && !headers_sent())
		{
			ob_start('ob_gzhandler');
		}
	}

	// Generate logged in/logged out status
	if ($user->data['user_id'] != ANONYMOUS)
	{
		$u_login_logout = "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=logout";
		$l_login_logout = sprintf($user->lang['LOGOUT_USER'], $user->data['username']);
	}
	else
	{
		$u_login_logout = "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=login";
		$l_login_logout = $user->lang['LOGIN'];
	}

	// Last visit date/time
	$s_last_visit = ($user->data['user_id'] != ANONYMOUS) ? $user->format_date($user->data['session_last_visit']) : '';

	// Get users online list ... if required
	$l_online_users = $online_userlist = $l_online_record = '';

	if ($config['load_online'] && $config['load_online_time'])
	{
		$userlist_ary = $userlist_visible = array();
		$logged_visible_online = $logged_hidden_online = $guests_online = $prev_user_id = 0;
		$prev_session_ip = $reading_sql = '';

		if (!empty($_REQUEST['f']))
		{
			$f = request_var('f', 0);
			$reading_sql = " AND s.session_page LIKE '%f=$f%'";
		}

		// Get number of online guests
		if (!$config['load_online_guests'])
		{
			$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . (time() - ($config['load_online_time'] * 60)) . 
					$reading_sql;
			$result = $db->sql_query($sql);
			$guests_online = (int) $db->sql_fetchfield('num_guests', 0, $result);
			$db->sql_freeresult($result);
		}

		$sql = 'SELECT u.username, u.user_id, u.user_type, u.user_allow_viewonline, u.user_colour, s.session_ip, s.session_viewonline
			FROM ' . USERS_TABLE . ' u, ' . SESSIONS_TABLE . ' s
			WHERE s.session_time >= ' . (time() - (intval($config['load_online_time']) * 60)) . 
				$reading_sql .
				((!$config['load_online_guests']) ? ' AND s.session_user_id <> ' . ANONYMOUS : '') . '
				AND u.user_id = s.session_user_id 
			ORDER BY u.username ASC, s.session_ip ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// User is logged in and therefore not a guest
			if ($row['user_id'] != ANONYMOUS)
			{
				// Skip multiple sessions for one user
				if ($row['user_id'] != $prev_user_id)
				{
					if ($row['user_colour'])
					{
						$row['username'] = '<b style="color:#' . $row['user_colour'] . '">' . $row['username'] . '</b>';
					}

					if ($row['user_allow_viewonline'] && $row['session_viewonline'])
					{
						$user_online_link = $row['username'];
						$logged_visible_online++;
					}
					else
					{
						$user_online_link = '<i>' . $row['username'] . '</i>';
						$logged_hidden_online++;
					}

					if ($row['user_allow_viewonline'] || $auth->acl_get('u_viewonline'))
					{
						$user_online_link = ($row['user_type'] <> USER_IGNORE) ? "<a href=\"{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id'] . '">' . $user_online_link . '</a>' : $user_online_link;
						$online_userlist .= ($online_userlist != '') ? ', ' . $user_online_link : $user_online_link;
					}
				}

				$prev_user_id = $row['user_id'];
			}
			else
			{
				// Skip multiple sessions for one user
				if ($row['session_ip'] != $prev_session_ip)
				{
					$guests_online++;
				}
			}

			$prev_session_ip = $row['session_ip'];
		}
		$db->sql_freeresult($result);

		if (!$online_userlist)
		{
			$online_userlist = $user->lang['NONE'];
		}

		if (empty($_REQUEST['f']))
		{
			$online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $online_userlist;
		}
		else
		{
			$l_online = ($guests_online == 1) ? $user->lang['BROWSING_FORUM_GUEST'] : $user->lang['BROWSING_FORUM_GUESTS'];
			$online_userlist = sprintf($l_online, $online_userlist, $guests_online);
		}

		$total_online_users = $logged_visible_online + $logged_hidden_online + $guests_online;

		if ($total_online_users > $config['record_online_users'])
		{
			set_config('record_online_users', $total_online_users, TRUE);
			set_config('record_online_date', time(), TRUE);
		}

		// Build online listing
		$vars_online = array(
			'ONLINE'=> array('total_online_users', 'l_t_user_s'),
			'REG'	=> array('logged_visible_online', 'l_r_user_s'),
			'HIDDEN'=> array('logged_hidden_online', 'l_h_user_s'),
			'GUEST'	=> array('guests_online', 'l_g_user_s')
		);

		foreach ($vars_online as $l_prefix => $var_ary)
		{
			switch (${$var_ary[0]})
			{
				case 0:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_ZERO_TOTAL'];
					break;

				case 1:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USER_TOTAL'];
					break;

				default:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_TOTAL'];
					break;
			}
		}
		unset($vars_online);

		$l_online_users = sprintf($l_t_user_s, $total_online_users);
		$l_online_users .= sprintf($l_r_user_s, $logged_visible_online);
		$l_online_users .= sprintf($l_h_user_s, $logged_hidden_online);
		$l_online_users .= sprintf($l_g_user_s, $guests_online);

		$l_online_record = sprintf($user->lang['RECORD_ONLINE_USERS'], $config['record_online_users'], $user->format_date($config['record_online_date']));

		$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
		$l_online_time = sprintf($user->lang[$l_online_time], $config['load_online_time']);
	}
	else
	{
		$l_online_time = '';
	}

	$l_privmsgs_text = $l_privmsgs_text_unread = '';
	$s_privmsg_new = false;

	// Obtain number of new private messages if user is logged in
	if ($user->data['is_registered'])
	{
		if ($user->data['user_new_privmsg'])
		{
			$l_message_new = ($user->data['user_new_privmsg'] == 1) ? $user->lang['NEW_PM'] : $user->lang['NEW_PMS'];
			$l_privmsgs_text = sprintf($l_message_new, $user->data['user_new_privmsg']);

			if (!$user->data['user_last_privmsg'] || $user->data['user_last_privmsg'] > $user->data['session_last_visit'])
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_last_privmsg = ' . $user->data['session_last_visit'] . '
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				$s_privmsg_new = true;
			}
			else
			{
				$s_privmsg_new = false;
			}
		}
		else
		{
			$l_privmsgs_text = $user->lang['NO_NEW_PM'];
			$s_privmsg_new = false;
		}

		$l_privmsgs_text_unread = '';

		if ($user->data['user_unread_privmsg'] && $user->data['user_unread_privmsg'] != $user->data['user_new_privmsg'])
		{
			$l_message_unread = ($user->data['user_unread_privmsg'] == 1) ? $user->lang['UNREAD_PM'] : $user->lang['UNREAD_PMS'];
			$l_privmsgs_text_unread = sprintf($l_message_unread, $user->data['user_unread_privmsg']);
		}
	}

	// Which timezone?
	$tz = ($user->data['user_id'] != ANONYMOUS) ? strval(doubleval($user->data['user_timezone'])) : strval(doubleval($config['board_timezone']));
	
	// The following assigns all _common_ variables that may be used at any point
	// in a template.
	$template->assign_vars(array(
		'SITENAME' 						=> $config['sitename'],
		'SITE_DESCRIPTION' 				=> $config['site_desc'],
		'PAGE_TITLE' 					=> $page_title,
		'SCRIPT_NAME'					=> substr($user->page, 0, strpos($user->page, '.')),
		'LAST_VISIT_DATE' 				=> sprintf($user->lang['YOU_LAST_VISIT'], $s_last_visit),
		'CURRENT_TIME' 					=> sprintf($user->lang['CURRENT_TIME'], $user->format_date(time(), false, true)),
		'TOTAL_USERS_ONLINE' 			=> $l_online_users,
		'LOGGED_IN_USER_LIST' 			=> $online_userlist,
		'RECORD_USERS' 					=> $l_online_record,
		'PRIVATE_MESSAGE_INFO' 			=> $l_privmsgs_text,
		'PRIVATE_MESSAGE_INFO_UNREAD' 	=> $l_privmsgs_text_unread,
		'SID'							=> $SID,

		'L_LOGIN_LOGOUT' 	=> $l_login_logout,
		'L_INDEX' 			=> $user->lang['FORUM_INDEX'],
		'L_ONLINE_EXPLAIN'	=> $l_online_time,

		'U_PRIVATEMSGS'			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=pm&amp;mode=" . (($user->data['user_new_privmsg'] || $l_privmsgs_text_unread) ? 'unread' : 'view_messages'),
		'U_RETURN_INBOX'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=pm&amp;folder=inbox",
		'U_JS_RETURN_INBOX'		=> "{$phpbb_root_path}ucp.$phpEx$SID&i=pm&folder=inbox",
		'U_POPUP_PM'			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=pm&amp;mode=popup",
		'U_JS_POPUP_PM'			=> "{$phpbb_root_path}ucp.$phpEx$SID&i=pm&mode=popup",
		'U_MEMBERLIST' 			=> "{$phpbb_root_path}memberlist.$phpEx$SID",
		'U_VIEWONLINE' 			=> "{$phpbb_root_path}viewonline.$phpEx$SID",
		'U_MEMBERSLIST'			=> "{$phpbb_root_path}memberlist.$phpEx$SID",
		'U_LOGIN_LOGOUT'		=> $u_login_logout,
		'U_INDEX' 				=> "{$phpbb_root_path}index.$phpEx$SID",
		'U_SEARCH' 				=> "{$phpbb_root_path}search.$phpEx$SID",
		'U_REGISTER' 			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=register",
		'U_PROFILE' 			=> "{$phpbb_root_path}ucp.$phpEx$SID",
		'U_MODCP' 				=> "{$phpbb_root_path}mcp.$phpEx$SID",
		'U_FAQ' 				=> "{$phpbb_root_path}faq.$phpEx$SID",
		'U_SEARCH_SELF'			=> "{$phpbb_root_path}search.$phpEx$SID&amp;search_id=egosearch",
		'U_SEARCH_NEW' 			=> "{$phpbb_root_path}search.$phpEx$SID&amp;search_id=newposts",
		'U_SEARCH_UNANSWERED'	=> "{$phpbb_root_path}search.$phpEx$SID&amp;search_id=unanswered",
		'U_DELETE_COOKIES'		=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;mode=delete_cookies",

		'S_USER_LOGGED_IN' 		=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
		'S_USER_PM_POPUP' 		=> $user->optionget('popuppm'),
		'S_USER_LANG'			=> $user->data['user_lang'],
		'S_USER_BROWSER' 		=> (isset($user->data['session_browser'])) ? $user->data['session_browser'] : $user->lang['UNKNOWN_BROWSER'],
		'S_CONTENT_DIRECTION' 	=> $user->lang['DIRECTION'],
		'S_CONTENT_ENCODING' 	=> $user->lang['ENCODING'],
		'S_CONTENT_DIR_LEFT' 	=> $user->lang['LEFT'],
		'S_CONTENT_DIR_RIGHT' 	=> $user->lang['RIGHT'],
		'S_TIMEZONE' 			=> ($user->data['user_dst'] || ($user->data['user_id'] == ANONYMOUS && $config['board_dst'])) ? sprintf($user->lang['ALL_TIMES'], $user->lang['tz'][$tz], $user->lang['tz']['dst']) : sprintf($user->lang['ALL_TIMES'], $user->lang['tz'][$tz], ''),
		'S_DISPLAY_ONLINE_LIST'	=> ($config['load_online']) ? 1 : 0,
		'S_DISPLAY_SEARCH'		=> ($config['load_search']) ? 1 : 0,
		'S_DISPLAY_PM'			=> ($config['allow_privmsg'] && $user->data['is_registered']) ? 1 : 0,
		'S_DISPLAY_MEMBERLIST'	=> (isset($auth)) ? $auth->acl_get('u_viewprofile') : 0,
		'S_NEW_PM'				=> ($s_privmsg_new) ? 1 : 0,

		'T_THEME_PATH'			=> "{$phpbb_root_path}styles/" . $user->theme['primary']['theme_path'] . '/theme',
		'T_TEMPLATE_PATH'		=> "{$phpbb_root_path}styles/" . $user->theme['primary']['template_path'] . '/template',
		'T_IMAGESET_PATH'		=> "{$phpbb_root_path}styles/" . $user->theme['primary']['imageset_path'] . '/imageset',
		'T_IMAGESET_LANG_PATH'	=> "{$phpbb_root_path}styles/" . $user->theme['primary']['imageset_path'] . '/imageset/' . $user->data['user_lang'],
		'T_SMILIES_PATH'		=> "{$phpbb_root_path}{$config['smilies_path']}/",
		'T_AVATAR_PATH'			=> "{$phpbb_root_path}{$config['avatar_path']}/",
		'T_AVATAR_GALLERY_PATH'	=> "{$phpbb_root_path}{$config['avatar_gallery_path']}/",
		'T_ICONS_PATH'			=> "{$phpbb_root_path}{$config['icons_path']}/",
		'T_RANKS_PATH'			=> "{$phpbb_root_path}{$config['ranks_path']}/",
		'T_UPLOAD_PATH'			=> "{$phpbb_root_path}{$config['upload_path']}/",
		'T_STYLESHEET_LINK'		=> (!$user->theme['primary']['theme_storedb']) ? "{$phpbb_root_path}styles/" . $user->theme['primary']['theme_path'] . '/theme/stylesheet.css' : "{$phpbb_root_path}style.$phpEx?sid=$user->session_id&amp;id=" . $user->theme['primary']['theme_id'],
		'T_STYLESHEET_NAME'		=> $user->theme['primary']['theme_name'],
		'T_THEME_DATA'			=> (!$user->theme['primary']['theme_storedb']) ? '' : $user->theme['primary']['theme_data'])
	);

	if (!empty($config['send_encoding']))
	{
		header('Content-type: text/html; charset: ' . $user->lang['ENCODING']);
	}
	header('Cache-Control: private, no-cache="set-cookie", pre-check=0, post-check=0');
	header('Expires: 0');
	header('Pragma: no-cache');

	return;
}

/**
* Generate page footer
*/
function page_footer()
{
	global $db, $config, $template, $SID, $user, $auth, $cache, $messenger, $starttime, $phpbb_root_path, $phpEx;

	// Output page creation time
	if (defined('DEBUG'))
	{
		$mtime = explode(' ', microtime());
		$totaltime = $mtime[0] + $mtime[1] - $starttime;

		if (!empty($_REQUEST['explain']) && $auth->acl_get('a_') && method_exists($db, 'sql_report'))
		{
			$db->sql_report('display');
		}

		$debug_output = sprintf('Time : %.3fs | ' . $db->sql_num_queries() . ' Queries | GZIP : ' .  (($config['gzip_compress']) ? 'On' : 'Off' ) . ' | Load : '  . (($user->load) ? $user->load : 'N/A'), $totaltime);

		if ($auth->acl_get('a_') && defined('DEBUG_EXTRA'))
		{
			if (function_exists('memory_get_usage'))
			{
				if ($memory_usage = memory_get_usage())
				{
					global $base_memory_usage;
					$memory_usage -= $base_memory_usage;
					$memory_usage = ($memory_usage >= 1048576) ? round((round($memory_usage / 1048576 * 100) / 100), 2) . ' ' . $user->lang['MB'] : (($memory_usage >= 1024) ? round((round($memory_usage / 1024 * 100) / 100), 2) . ' ' . $user->lang['KB'] : $memory_usage . ' ' . $user->lang['BYTES']);

					$debug_output .= ' | Memory Usage: ' . $memory_usage;
				}
			}

			$debug_output .= ' | <a href="' . (($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : "index.$phpEx$SID") . ((strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&amp;' : '?') . 'explain=1">Explain</a>';
		}
	}

	$template->assign_vars(array(
		'DEBUG_OUTPUT'	=> (defined('DEBUG')) ? $debug_output : '',

		'U_ACP' => ($auth->acl_get('a_') && $user->data['is_registered']) ? "{$phpbb_root_path}adm/index.$phpEx?sid=" . $user->data['session_id'] : '')
	);

	// Call cron-type script
	if (!defined('IN_CRON'))
	{
		$cron_type = '';

		if (time() - $config['queue_interval'] > $config['last_queue_run'] && !defined('IN_ADMIN') && file_exists($phpbb_root_path . 'cache/queue.' . $phpEx))
		{
			// Process email queue
			$cron_type = 'queue';
		}
		else if (method_exists($cache, 'tidy') && time() - $config['cache_gc'] > $config['cache_last_gc'])
		{
			// Tidy the cache
			$cron_type = 'tidy_cache';
		}
		else if (time() - (7 * 24 * 3600) > $config['database_last_gc'])
		{
			// Tidy some table rows every week
			$cron_type = 'tidy_database';
		}

		if ($cron_type)
		{
			$template->assign_var('RUN_CRON_TASK', '<img src="' . $phpbb_root_path . 'cron.' . $phpEx . '?cron_type=' . $cron_type . '" width="1" height="1" />');
		}
	}

	$template->display('body');

	// Unload cache, must be done before the DB connection if closed
	if (!empty($cache))
	{
		$cache->unload();
	}

	// Close our DB connection.
	$db->sql_close();

	exit;
}

?>