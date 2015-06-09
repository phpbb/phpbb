<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Recalculate Nested Sets
*
* @param int	$new_id	first left_id (should start with 1)
* @param string	$pkey	primary key-column (containing the id for the parent_id of the children)
* @param string	$table	constant or fullname of the table
* @param int	$parent_id parent_id of the current set (default = 0)
* @param array	$where	contains strings to compare closer on the where statement (additional)
*/
function recalc_nested_sets(&$new_id, $pkey, $table, $parent_id = 0, $where = array())
{
	global $db;

	$sql = 'SELECT *
		FROM ' . $table . '
		WHERE parent_id = ' . (int) $parent_id .
		((!empty($where)) ? ' AND ' . implode(' AND ', $where) : '') . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		// First we update the left_id for this module
		if ($row['left_id'] != $new_id)
		{
			$db->sql_query('UPDATE ' . $table . ' SET ' . $db->sql_build_array('UPDATE', array('left_id' => $new_id)) . " WHERE $pkey = {$row[$pkey]}");
		}
		$new_id++;

		// Then we go through any children and update their left/right id's
		recalc_nested_sets($new_id, $pkey, $table, $row[$pkey], $where);

		// Then we come back and update the right_id for this module
		if ($row['right_id'] != $new_id)
		{
			$db->sql_query('UPDATE ' . $table . ' SET ' . $db->sql_build_array('UPDATE', array('right_id' => $new_id)) . " WHERE $pkey = {$row[$pkey]}");
		}
		$new_id++;
	}
	$db->sql_freeresult($result);
}

/**
* Simple version of jumpbox, just lists authed forums
*/
function make_forum_select($select_id = false, $ignore_id = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $only_acl_post = false, $return_array = false)
{
	global $db, $user, $auth;

	// This query is identical to the jumpbox one
	$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, forum_flags, forum_options, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql, 600);

	$right = 0;
	$padding_store = array('0' => '');
	$padding = '';
	$forum_list = ($return_array) ? array() : '';

	// Sometimes it could happen that forums will be displayed here not be displayed within the index page
	// This is the result of forums not displayed at index, having list permissions and a parent of a forum with no permissions.
	// If this happens, the padding could be "broken"

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp;';
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
		}

		$right = $row['right_id'];
		$disabled = false;

		if (!$ignore_acl && $auth->acl_gets(array('f_list', 'a_forum', 'a_forumadd', 'a_forumdel'), $row['forum_id']))
		{
			if ($only_acl_post && !$auth->acl_get('f_post', $row['forum_id']) || (!$auth->acl_get('m_approve', $row['forum_id']) && !$auth->acl_get('f_noapprove', $row['forum_id'])))
			{
				$disabled = true;
			}
		}
		else if (!$ignore_acl)
		{
			continue;
		}

		if (
			((is_array($ignore_id) && in_array($row['forum_id'], $ignore_id)) || $row['forum_id'] == $ignore_id)
			||
			// Non-postable forum with no subforums, don't display
			($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']) && $ignore_emptycat)
			||
			($row['forum_type'] != FORUM_POST && $ignore_nonpost)
			)
		{
			$disabled = true;
		}

		if ($return_array)
		{
			// Include some more information...
			$selected = (is_array($select_id)) ? ((in_array($row['forum_id'], $select_id)) ? true : false) : (($row['forum_id'] == $select_id) ? true : false);
			$forum_list[$row['forum_id']] = array_merge(array('padding' => $padding, 'selected' => ($selected && !$disabled), 'disabled' => $disabled), $row);
		}
		else
		{
			$selected = (is_array($select_id)) ? ((in_array($row['forum_id'], $select_id)) ? ' selected="selected"' : '') : (($row['forum_id'] == $select_id) ? ' selected="selected"' : '');
			$forum_list .= '<option value="' . $row['forum_id'] . '"' . (($disabled) ? ' disabled="disabled" class="disabled-option"' : $selected) . '>' . $padding . $row['forum_name'] . '</option>';
		}
	}
	$db->sql_freeresult($result);
	unset($padding_store);

	return $forum_list;
}

/**
* Generate size select options
*/
function size_select_options($size_compare)
{
	global $user;

	$size_types_text = array($user->lang['BYTES'], $user->lang['KIB'], $user->lang['MIB']);
	$size_types = array('b', 'kb', 'mb');

	$s_size_options = '';

	for ($i = 0, $size = sizeof($size_types_text); $i < $size; $i++)
	{
		$selected = ($size_compare == $size_types[$i]) ? ' selected="selected"' : '';
		$s_size_options .= '<option value="' . $size_types[$i] . '"' . $selected . '>' . $size_types_text[$i] . '</option>';
	}

	return $s_size_options;
}

/**
* Generate list of groups (option fields without select)
*
* @param int $group_id The default group id to mark as selected
* @param array $exclude_ids The group ids to exclude from the list, false (default) if you whish to exclude no id
* @param int $manage_founder If set to false (default) all groups are returned, if 0 only those groups returned not being managed by founders only, if 1 only those groups returned managed by founders only.
*
* @return string The list of options.
*/
function group_select_options($group_id, $exclude_ids = false, $manage_founder = false)
{
	global $db, $user, $config;

	$exclude_sql = ($exclude_ids !== false && sizeof($exclude_ids)) ? 'WHERE ' . $db->sql_in_set('group_id', array_map('intval', $exclude_ids), true) : '';
	$sql_and = (!$config['coppa_enable']) ? (($exclude_sql) ? ' AND ' : ' WHERE ') . "group_name <> 'REGISTERED_COPPA'" : '';
	$sql_founder = ($manage_founder !== false) ? (($exclude_sql || $sql_and) ? ' AND ' : ' WHERE ') . 'group_founder_manage = ' . (int) $manage_founder : '';

	$sql = 'SELECT group_id, group_name, group_type
		FROM ' . GROUPS_TABLE . "
		$exclude_sql
		$sql_and
		$sql_founder
		ORDER BY group_type DESC, group_name ASC";
	$result = $db->sql_query($sql);

	$s_group_options = '';
	while ($row = $db->sql_fetchrow($result))
	{
		$selected = ($row['group_id'] == $group_id) ? ' selected="selected"' : '';
		$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
	}
	$db->sql_freeresult($result);

	return $s_group_options;
}

/**
* Obtain authed forums list
*/
function get_forum_list($acl_list = 'f_list', $id_only = true, $postable_only = false, $no_cache = false)
{
	global $db, $auth;
	static $forum_rows;

	if (!isset($forum_rows))
	{
		// This query is identical to the jumpbox one
		$expire_time = ($no_cache) ? 0 : 600;

		$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
			FROM ' . FORUMS_TABLE . '
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql, $expire_time);

		$forum_rows = array();

		$right = $padding = 0;
		$padding_store = array('0' => 0);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['left_id'] < $right)
			{
				$padding++;
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				// Ok, if the $padding_store for this parent is empty there is something wrong. For now we will skip over it.
				// @todo digging deep to find out "how" this can happen.
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : $padding;
			}

			$right = $row['right_id'];
			$row['padding'] = $padding;

			$forum_rows[] = $row;
		}
		$db->sql_freeresult($result);
		unset($padding_store);
	}

	$rowset = array();
	foreach ($forum_rows as $row)
	{
		if ($postable_only && $row['forum_type'] != FORUM_POST)
		{
			continue;
		}

		if ($acl_list == '' || ($acl_list != '' && $auth->acl_gets($acl_list, $row['forum_id'])))
		{
			$rowset[] = ($id_only) ? (int) $row['forum_id'] : $row;
		}
	}

	return $rowset;
}

/**
* Get forum branch
*/
function get_forum_branch($forum_id, $type = 'all', $order = 'descending', $include_forum = true)
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
		break;
	}

	$rows = array();

	$sql = 'SELECT f2.*
		FROM ' . FORUMS_TABLE . ' f1
		LEFT JOIN ' . FORUMS_TABLE . " f2 ON ($condition)
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
	$db->sql_freeresult($result);

	return $rows;
}

/**
* Copies permissions from one forum to others
*
* @param int	$src_forum_id		The source forum we want to copy permissions from
* @param array	$dest_forum_ids		The destination forum(s) we want to copy to
* @param bool	$clear_dest_perms	True if destination permissions should be deleted
* @param bool	$add_log			True if log entry should be added
*
* @return bool						False on error
*/
function copy_forum_permissions($src_forum_id, $dest_forum_ids, $clear_dest_perms = true, $add_log = true)
{
	global $db;

	// Only one forum id specified
	if (!is_array($dest_forum_ids))
	{
		$dest_forum_ids = array($dest_forum_ids);
	}

	// Make sure forum ids are integers
	$src_forum_id = (int) $src_forum_id;
	$dest_forum_ids = array_map('intval', $dest_forum_ids);

	// No source forum or no destination forums specified
	if (empty($src_forum_id) || empty($dest_forum_ids))
	{
		return false;
	}

	// Check if source forum exists
	$sql = 'SELECT forum_name
		FROM ' . FORUMS_TABLE . '
		WHERE forum_id = ' . $src_forum_id;
	$result = $db->sql_query($sql);
	$src_forum_name = $db->sql_fetchfield('forum_name');
	$db->sql_freeresult($result);

	// Source forum doesn't exist
	if (empty($src_forum_name))
	{
		return false;
	}

	// Check if destination forums exists
	$sql = 'SELECT forum_id, forum_name
		FROM ' . FORUMS_TABLE . '
		WHERE ' . $db->sql_in_set('forum_id', $dest_forum_ids);
	$result = $db->sql_query($sql);

	$dest_forum_ids = $dest_forum_names = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$dest_forum_ids[]	= (int) $row['forum_id'];
		$dest_forum_names[]	= $row['forum_name'];
	}
	$db->sql_freeresult($result);

	// No destination forum exists
	if (empty($dest_forum_ids))
	{
		return false;
	}

	// From the mysql documentation:
	// Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear
	// in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
	// Due to this we stay on the safe side if we do the insertion "the manual way"

	// Rowsets we're going to insert
	$users_sql_ary = $groups_sql_ary = array();

	// Query acl users table for source forum data
	$sql = 'SELECT user_id, auth_option_id, auth_role_id, auth_setting
		FROM ' . ACL_USERS_TABLE . '
		WHERE forum_id = ' . $src_forum_id;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$row = array(
			'user_id'			=> (int) $row['user_id'],
			'auth_option_id'	=> (int) $row['auth_option_id'],
			'auth_role_id'		=> (int) $row['auth_role_id'],
			'auth_setting'		=> (int) $row['auth_setting'],
		);

		foreach ($dest_forum_ids as $dest_forum_id)
		{
			$users_sql_ary[] = $row + array('forum_id' => $dest_forum_id);
		}
	}
	$db->sql_freeresult($result);

	// Query acl groups table for source forum data
	$sql = 'SELECT group_id, auth_option_id, auth_role_id, auth_setting
		FROM ' . ACL_GROUPS_TABLE . '
		WHERE forum_id = ' . $src_forum_id;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$row = array(
			'group_id'			=> (int) $row['group_id'],
			'auth_option_id'	=> (int) $row['auth_option_id'],
			'auth_role_id'		=> (int) $row['auth_role_id'],
			'auth_setting'		=> (int) $row['auth_setting'],
		);

		foreach ($dest_forum_ids as $dest_forum_id)
		{
			$groups_sql_ary[] = $row + array('forum_id' => $dest_forum_id);
		}
	}
	$db->sql_freeresult($result);

	$db->sql_transaction('begin');

	// Clear current permissions of destination forums
	if ($clear_dest_perms)
	{
		$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $dest_forum_ids);
		$db->sql_query($sql);

		$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $dest_forum_ids);
		$db->sql_query($sql);
	}

	$db->sql_multi_insert(ACL_USERS_TABLE, $users_sql_ary);
	$db->sql_multi_insert(ACL_GROUPS_TABLE, $groups_sql_ary);

	if ($add_log)
	{
		add_log('admin', 'LOG_FORUM_COPIED_PERMISSIONS', $src_forum_name, implode(', ', $dest_forum_names));
	}

	$db->sql_transaction('commit');

	return true;
}

/**
* Get physical file listing
*/
function filelist($rootdir, $dir = '', $type = 'gif|jpg|jpeg|png')
{
	$matches = array($dir => array());

	// Remove initial / if present
	$rootdir = (substr($rootdir, 0, 1) == '/') ? substr($rootdir, 1) : $rootdir;
	// Add closing / if not present
	$rootdir = ($rootdir && substr($rootdir, -1) != '/') ? $rootdir . '/' : $rootdir;

	// Remove initial / if present
	$dir = (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
	// Add closing / if not present
	$dir = ($dir && substr($dir, -1) != '/') ? $dir . '/' : $dir;

	if (!is_dir($rootdir . $dir))
	{
		return $matches;
	}

	$dh = @opendir($rootdir . $dir);

	if (!$dh)
	{
		return $matches;
	}

	while (($fname = readdir($dh)) !== false)
	{
		if (is_file("$rootdir$dir$fname"))
		{
			if (filesize("$rootdir$dir$fname") && preg_match('#\.' . $type . '$#i', $fname))
			{
				$matches[$dir][] = $fname;
			}
		}
		else if ($fname[0] != '.' && is_dir("$rootdir$dir$fname"))
		{
			$matches += filelist($rootdir, "$dir$fname", $type);
		}
	}
	closedir($dh);

	return $matches;
}

/**
* Move topic(s)
*/
function move_topics($topic_ids, $forum_id, $auto_sync = true)
{
	global $db, $phpbb_dispatcher;

	if (empty($topic_ids))
	{
		return;
	}

	$forum_ids = array($forum_id);

	if (!is_array($topic_ids))
	{
		$topic_ids = array($topic_ids);
	}

	$sql = 'DELETE FROM ' . TOPICS_TABLE . '
		WHERE ' . $db->sql_in_set('topic_moved_id', $topic_ids) . '
			AND forum_id = ' . $forum_id;
	$db->sql_query($sql);

	if ($auto_sync)
	{
		$sql = 'SELECT DISTINCT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
		}
		$db->sql_freeresult($result);
	}

	$table_ary = array(TOPICS_TABLE, POSTS_TABLE, LOG_TABLE, DRAFTS_TABLE, TOPICS_TRACK_TABLE);

	/**
	 * Perform additional actions before topics move
	 *
	 * @event core.move_topics_before_query
	 * @var	array	table_ary	Array of tables from which forum_id will be updated for all rows that hold the moved topics
	 * @var	array	topic_ids	Array of the moved topic ids
	 * @var	string	forum_id	The forum id from where the topics are moved
	 * @var	array	forum_ids	Array of the forums where the topics are moving (includes also forum_id)
	 * @var bool	auto_sync	Whether or not to perform auto sync
	 * @since 3.1.5-RC1
	 */
	$vars = array(
			'table_ary',
			'topic_ids',
			'forum_id',
			'forum_ids',
			'auto_sync',
	);
	extract($phpbb_dispatcher->trigger_event('core.move_topics_before_query', compact($vars)));

	foreach ($table_ary as $table)
	{
		$sql = "UPDATE $table
			SET forum_id = $forum_id
			WHERE " . $db->sql_in_set('topic_id', $topic_ids);
		$db->sql_query($sql);
	}
	unset($table_ary);

	if ($auto_sync)
	{
		sync('forum', 'forum_id', $forum_ids, true, true);
		unset($forum_ids);
	}
}

/**
* Move post(s)
*/
function move_posts($post_ids, $topic_id, $auto_sync = true)
{
	global $db;

	if (!is_array($post_ids))
	{
		$post_ids = array($post_ids);
	}

	$forum_ids = array();
	$topic_ids = array($topic_id);

	$sql = 'SELECT DISTINCT topic_id, forum_id
		FROM ' . POSTS_TABLE . '
		WHERE ' . $db->sql_in_set('post_id', $post_ids);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = (int) $row['forum_id'];
		$topic_ids[] = (int) $row['topic_id'];
	}
	$db->sql_freeresult($result);

	$sql = 'SELECT forum_id
		FROM ' . TOPICS_TABLE . '
		WHERE topic_id = ' . $topic_id;
	$result = $db->sql_query($sql);
	$forum_row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if (!$forum_row)
	{
		trigger_error('NO_TOPIC');
	}

	$sql = 'UPDATE ' . POSTS_TABLE . '
		SET forum_id = ' . (int) $forum_row['forum_id'] . ", topic_id = $topic_id
		WHERE " . $db->sql_in_set('post_id', $post_ids);
	$db->sql_query($sql);

	$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
		SET topic_id = $topic_id, in_message = 0
		WHERE " . $db->sql_in_set('post_msg_id', $post_ids);
	$db->sql_query($sql);

	if ($auto_sync)
	{
		$forum_ids[] = (int) $forum_row['forum_id'];

		sync('topic_reported', 'topic_id', $topic_ids);
		sync('topic_attachment', 'topic_id', $topic_ids);
		sync('topic', 'topic_id', $topic_ids, true);
		sync('forum', 'forum_id', $forum_ids, true, true);
	}

	// Update posted information
	update_posted_info($topic_ids);
}

/**
* Remove topic(s)
*/
function delete_topics($where_type, $where_ids, $auto_sync = true, $post_count_sync = true, $call_delete_posts = true)
{
	global $db, $config, $phpbb_container, $phpbb_dispatcher;

	$approved_topics = 0;
	$forum_ids = $topic_ids = array();

	if ($where_type === 'range')
	{
		$where_clause = $where_ids;
	}
	else
	{
		$where_ids = (is_array($where_ids)) ? array_unique($where_ids) : array($where_ids);

		if (!sizeof($where_ids))
		{
			return array('topics' => 0, 'posts' => 0);
		}

		$where_clause = $db->sql_in_set($where_type, $where_ids);
	}

	// Making sure that delete_posts does not call delete_topics again...
	$return = array(
		'posts' => ($call_delete_posts) ? delete_posts($where_type, $where_ids, false, true, $post_count_sync, false) : 0,
	);

	$sql = 'SELECT topic_id, forum_id, topic_visibility, topic_moved_id
		FROM ' . TOPICS_TABLE . '
		WHERE ' . $where_clause;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = $row['forum_id'];
		$topic_ids[] = $row['topic_id'];

		if ($row['topic_visibility'] == ITEM_APPROVED && !$row['topic_moved_id'])
		{
			$approved_topics++;
		}
	}
	$db->sql_freeresult($result);

	$return['topics'] = sizeof($topic_ids);

	if (!sizeof($topic_ids))
	{
		return $return;
	}

	$db->sql_transaction('begin');

	$table_ary = array(BOOKMARKS_TABLE, TOPICS_TRACK_TABLE, TOPICS_POSTED_TABLE, POLL_VOTES_TABLE, POLL_OPTIONS_TABLE, TOPICS_WATCH_TABLE, TOPICS_TABLE);

	/**
	 * Perform additional actions before topic(s) deletion
	 *
	 * @event core.delete_topics_before_query
	 * @var	array	table_ary	Array of tables from which all rows will be deleted that hold a topic_id occuring in topic_ids
	 * @var	array	topic_ids	Array of topic ids to delete
	 * @since 3.1.4-RC1
	 */
	$vars = array(
			'table_ary',
			'topic_ids',
	);
	extract($phpbb_dispatcher->trigger_event('core.delete_topics_before_query', compact($vars)));

	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table
			WHERE " . $db->sql_in_set('topic_id', $topic_ids);
		$db->sql_query($sql);
	}
	unset($table_ary);

	/**
	 * Perform additional actions after topic(s) deletion
	 *
	 * @event core.delete_topics_after_query
	 * @var	array	topic_ids	Array of topic ids that were deleted
	 * @since 3.1.4-RC1
	 */
	$vars = array(
			'topic_ids',
	);
	extract($phpbb_dispatcher->trigger_event('core.delete_topics_after_query', compact($vars)));

	$moved_topic_ids = array();

	// update the other forums
	$sql = 'SELECT topic_id, forum_id
		FROM ' . TOPICS_TABLE . '
		WHERE ' . $db->sql_in_set('topic_moved_id', $topic_ids);
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = $row['forum_id'];
		$moved_topic_ids[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	if (sizeof($moved_topic_ids))
	{
		$sql = 'DELETE FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $moved_topic_ids);
		$db->sql_query($sql);
	}

	$db->sql_transaction('commit');

	if ($auto_sync)
	{
		sync('forum', 'forum_id', array_unique($forum_ids), true, true);
		sync('topic_reported', $where_type, $where_ids);
	}

	if ($approved_topics)
	{
		set_config_count('num_topics', $approved_topics * (-1), true);
	}

	$phpbb_notifications = $phpbb_container->get('notification_manager');

	$phpbb_notifications->delete_notifications(array(
		'notification.type.topic',
		'notification.type.approve_topic',
		'notification.type.topic_in_queue',
	), $topic_ids);

	return $return;
}

/**
* Remove post(s)
*/
function delete_posts($where_type, $where_ids, $auto_sync = true, $posted_sync = true, $post_count_sync = true, $call_delete_topics = true)
{
	global $db, $config, $phpbb_root_path, $phpEx, $auth, $user, $phpbb_container, $phpbb_dispatcher;

	// Notifications types to delete
	$delete_notifications_types = array(
		'notification.type.quote',
		'notification.type.approve_post',
		'notification.type.post_in_queue',
	);

	/**
	* Perform additional actions before post(s) deletion
	*
	* @event core.delete_posts_before
	* @var	string	where_type					Variable containing posts deletion mode
	* @var	mixed	where_ids					Array or comma separated list of posts ids to delete
	* @var	bool	auto_sync					Flag indicating if topics/forums should be synchronized
	* @var	bool	posted_sync					Flag indicating if topics_posted table should be resynchronized
	* @var	bool	post_count_sync				Flag indicating if posts count should be resynchronized
	* @var	bool	call_delete_topics			Flag indicating if topics having no posts should be deleted
	* @var	array	delete_notifications_types	Array with notifications types to delete
	* @since 3.1.0-a4
	*/
	$vars = array(
		'where_type',
		'where_ids',
		'auto_sync',
		'posted_sync',
		'post_count_sync',
		'call_delete_topics',
		'delete_notifications_types',
	);
	extract($phpbb_dispatcher->trigger_event('core.delete_posts_before', compact($vars)));

	if ($where_type === 'range')
	{
		$where_clause = $where_ids;
	}
	else
	{
		if (is_array($where_ids))
		{
			$where_ids = array_unique($where_ids);
		}
		else
		{
			$where_ids = array($where_ids);
		}

		if (!sizeof($where_ids))
		{
			return false;
		}

		$where_ids = array_map('intval', $where_ids);

/*		Possible code for splitting post deletion
		if (sizeof($where_ids) >= 1001)
		{
			// Split into chunks of 1000
			$chunks = array_chunk($where_ids, 1000);

			foreach ($chunks as $_where_ids)
			{
				delete_posts($where_type, $_where_ids, $auto_sync, $posted_sync, $post_count_sync, $call_delete_topics);
			}

			return;
		}*/

		$where_clause = $db->sql_in_set($where_type, $where_ids);
	}

	$approved_posts = 0;
	$post_ids = $topic_ids = $forum_ids = $post_counts = $remove_topics = array();

	$sql = 'SELECT post_id, poster_id, post_visibility, post_postcount, topic_id, forum_id
		FROM ' . POSTS_TABLE . '
		WHERE ' . $where_clause;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$post_ids[] = (int) $row['post_id'];
		$poster_ids[] = (int) $row['poster_id'];
		$topic_ids[] = (int) $row['topic_id'];
		$forum_ids[] = (int) $row['forum_id'];

		if ($row['post_postcount'] && $post_count_sync && $row['post_visibility'] == ITEM_APPROVED)
		{
			$post_counts[$row['poster_id']] = (!empty($post_counts[$row['poster_id']])) ? $post_counts[$row['poster_id']] + 1 : 1;
		}

		if ($row['post_visibility'] == ITEM_APPROVED)
		{
			$approved_posts++;
		}
	}
	$db->sql_freeresult($result);

	if (!sizeof($post_ids))
	{
		return false;
	}

	$db->sql_transaction('begin');

	$table_ary = array(POSTS_TABLE, REPORTS_TABLE);

	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table
			WHERE " . $db->sql_in_set('post_id', $post_ids);
		$db->sql_query($sql);
	}
	unset($table_ary);

	// Adjust users post counts
	if (sizeof($post_counts) && $post_count_sync)
	{
		foreach ($post_counts as $poster_id => $substract)
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_posts = 0
				WHERE user_id = ' . $poster_id . '
				AND user_posts < ' . $substract;
			$db->sql_query($sql);

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_posts = user_posts - ' . $substract . '
				WHERE user_id = ' . $poster_id . '
				AND user_posts >= ' . $substract;
			$db->sql_query($sql);
		}
	}

	// Remove topics now having no posts?
	if (sizeof($topic_ids))
	{
		$sql = 'SELECT topic_id
			FROM ' . POSTS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $topic_ids) . '
			GROUP BY topic_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$remove_topics[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);

		// Actually, those not within remove_topics should be removed. ;)
		$remove_topics = array_diff($topic_ids, $remove_topics);
	}

	// Remove the message from the search index
	$search_type = $config['search_type'];

	if (!class_exists($search_type))
	{
		trigger_error('NO_SUCH_SEARCH_MODULE');
	}

	$error = false;
	$search = new $search_type($error, $phpbb_root_path, $phpEx, $auth, $config, $db, $user, $phpbb_dispatcher);

	if ($error)
	{
		trigger_error($error);
	}

	$search->index_remove($post_ids, $poster_ids, $forum_ids);

	delete_attachments('post', $post_ids, false);

	/**
	* Perform additional actions during post(s) deletion
	*
	* @event core.delete_posts_in_transaction
	* @var	array	post_ids					Array with deleted posts' ids
	* @var	array	poster_ids					Array with deleted posts' author ids
	* @var	array	topic_ids					Array with deleted posts' topic ids
	* @var	array	forum_ids					Array with deleted posts' forum ids
	* @var	string	where_type					Variable containing posts deletion mode
	* @var	mixed	where_ids					Array or comma separated list of posts ids to delete
	* @var	array	delete_notifications_types	Array with notifications types to delete
	* @since 3.1.0-a4
	*/
	$vars = array(
		'post_ids',
		'poster_ids',
		'topic_ids',
		'forum_ids',
		'where_type',
		'where_ids',
		'delete_notifications_types',
	);
	extract($phpbb_dispatcher->trigger_event('core.delete_posts_in_transaction', compact($vars)));

	$db->sql_transaction('commit');

	/**
	* Perform additional actions after post(s) deletion
	*
	* @event core.delete_posts_after
	* @var	array	post_ids					Array with deleted posts' ids
	* @var	array	poster_ids					Array with deleted posts' author ids
	* @var	array	topic_ids					Array with deleted posts' topic ids
	* @var	array	forum_ids					Array with deleted posts' forum ids
	* @var	string	where_type					Variable containing posts deletion mode
	* @var	mixed	where_ids					Array or comma separated list of posts ids to delete
	* @var	array	delete_notifications_types	Array with notifications types to delete
	* @since 3.1.0-a4
	*/
	$vars = array(
		'post_ids',
		'poster_ids',
		'topic_ids',
		'forum_ids',
		'where_type',
		'where_ids',
		'delete_notifications_types',
	);
	extract($phpbb_dispatcher->trigger_event('core.delete_posts_after', compact($vars)));

	// Resync topics_posted table
	if ($posted_sync)
	{
		update_posted_info($topic_ids);
	}

	if ($auto_sync)
	{
		sync('topic_reported', 'topic_id', $topic_ids);
		sync('topic', 'topic_id', $topic_ids, true);
		sync('forum', 'forum_id', $forum_ids, true, true);
	}

	if ($approved_posts && $post_count_sync)
	{
		set_config_count('num_posts', $approved_posts * (-1), true);
	}

	// We actually remove topics now to not be inconsistent (the delete_topics function calls this function too)
	if (sizeof($remove_topics) && $call_delete_topics)
	{
		delete_topics('topic_id', $remove_topics, $auto_sync, $post_count_sync, false);
	}

	$phpbb_notifications = $phpbb_container->get('notification_manager');

	$phpbb_notifications->delete_notifications($delete_notifications_types, $post_ids);

	return sizeof($post_ids);
}

/**
* Delete Attachments
*
* @param string $mode can be: post|message|topic|attach|user
* @param mixed $ids can be: post_ids, message_ids, topic_ids, attach_ids, user_ids
* @param bool $resync set this to false if you are deleting posts or topics
*/
function delete_attachments($mode, $ids, $resync = true)
{
	global $db, $config;

	// 0 is as bad as an empty array
	if (empty($ids))
	{
		return false;
	}

	if (is_array($ids))
	{
		$ids = array_unique($ids);
		$ids = array_map('intval', $ids);
	}
	else
	{
		$ids = array((int) $ids);
	}

	$sql_where = '';

	switch ($mode)
	{
		case 'post':
		case 'message':
			$sql_id = 'post_msg_id';
			$sql_where = ' AND in_message = ' . ($mode == 'message' ? 1 : 0);
		break;

		case 'topic':
			$sql_id = 'topic_id';
		break;

		case 'user':
			$sql_id = 'poster_id';
		break;

		case 'attach':
		default:
			$sql_id = 'attach_id';
			$mode = 'attach';
		break;
	}

	$post_ids = $message_ids = $topic_ids = $physical = array();

	// Collect post and topic ids for later use if we need to touch remaining entries (if resync is enabled)
	$sql = 'SELECT post_msg_id, topic_id, in_message, physical_filename, thumbnail, filesize, is_orphan
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set($sql_id, $ids);

	$sql .= $sql_where;

	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		// We only need to store post/message/topic ids if resync is enabled and the file is not orphaned
		if ($resync && !$row['is_orphan'])
		{
			if (!$row['in_message'])
			{
				$post_ids[] = $row['post_msg_id'];
				$topic_ids[] = $row['topic_id'];
			}
			else
			{
				$message_ids[] = $row['post_msg_id'];
			}
		}

		$physical[] = array('filename' => $row['physical_filename'], 'thumbnail' => $row['thumbnail'], 'filesize' => $row['filesize'], 'is_orphan' => $row['is_orphan']);
	}
	$db->sql_freeresult($result);

	// Delete attachments
	$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . '
		WHERE ' . $db->sql_in_set($sql_id, $ids);

	$sql .= $sql_where;

	$db->sql_query($sql);
	$num_deleted = $db->sql_affectedrows();

	if (!$num_deleted)
	{
		return 0;
	}

	// Delete attachments from filesystem
	$space_removed = $files_removed = 0;
	foreach ($physical as $file_ary)
	{
		if (phpbb_unlink($file_ary['filename'], 'file', true) && !$file_ary['is_orphan'])
		{
			// Only non-orphaned files count to the file size
			$space_removed += $file_ary['filesize'];
			$files_removed++;
		}

		if ($file_ary['thumbnail'])
		{
			phpbb_unlink($file_ary['filename'], 'thumbnail', true);
		}
	}

	if ($space_removed || $files_removed)
	{
		set_config_count('upload_dir_size', $space_removed * (-1), true);
		set_config_count('num_files', $files_removed * (-1), true);
	}

	// If we do not resync, we do not need to adjust any message, post, topic or user entries
	if (!$resync)
	{
		return $num_deleted;
	}

	// No more use for the original ids
	unset($ids);

	// Now, we need to resync posts, messages, topics. We go through every one of them
	$post_ids = array_unique($post_ids);
	$message_ids = array_unique($message_ids);
	$topic_ids = array_unique($topic_ids);

	// Update post indicators for posts now no longer having attachments
	if (sizeof($post_ids))
	{
		// Just check which posts are still having an assigned attachment not orphaned by querying the attachments table
		$sql = 'SELECT post_msg_id
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $post_ids) . '
				AND in_message = 0
				AND is_orphan = 0';
		$result = $db->sql_query($sql);

		$remaining_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$remaining_ids[] = $row['post_msg_id'];
		}
		$db->sql_freeresult($result);

		// Now only unset those ids remaining
		$post_ids = array_diff($post_ids, $remaining_ids);

		if (sizeof($post_ids))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 0
				WHERE ' . $db->sql_in_set('post_id', $post_ids);
			$db->sql_query($sql);
		}
	}

	// Update message table if messages are affected
	if (sizeof($message_ids))
	{
		// Just check which messages are still having an assigned attachment not orphaned by querying the attachments table
		$sql = 'SELECT post_msg_id
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_msg_id', $message_ids) . '
				AND in_message = 1
				AND is_orphan = 0';
		$result = $db->sql_query($sql);

		$remaining_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$remaining_ids[] = $row['post_msg_id'];
		}
		$db->sql_freeresult($result);

		// Now only unset those ids remaining
		$message_ids = array_diff($message_ids, $remaining_ids);

		if (sizeof($message_ids))
		{
			$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
				SET message_attachment = 0
				WHERE ' . $db->sql_in_set('msg_id', $message_ids);
			$db->sql_query($sql);
		}
	}

	// Now update the topics. This is a bit trickier, because there could be posts still having attachments within the topic
	if (sizeof($topic_ids))
	{
		// Just check which topics are still having an assigned attachment not orphaned by querying the attachments table (much less entries expected)
		$sql = 'SELECT topic_id
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $db->sql_in_set('topic_id', $topic_ids) . '
				AND is_orphan = 0';
		$result = $db->sql_query($sql);

		$remaining_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$remaining_ids[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);

		// Now only unset those ids remaining
		$topic_ids = array_diff($topic_ids, $remaining_ids);

		if (sizeof($topic_ids))
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_attachment = 0
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
			$db->sql_query($sql);
		}
	}

	return $num_deleted;
}

/**
* Deletes shadow topics pointing to a specified forum.
*
* @param int		$forum_id		The forum id
* @param string		$sql_more		Additional WHERE statement, e.g. t.topic_time < (time() - 1234)
* @param bool		$auto_sync		Will call sync() if this is true
*
* @return array		Array with affected forums
*/
function delete_topic_shadows($forum_id, $sql_more = '', $auto_sync = true)
{
	global $db;

	if (!$forum_id)
	{
		// Nothing to do.
		return;
	}

	// Set of affected forums we have to resync
	$sync_forum_ids = array();

	// Amount of topics we select and delete at once.
	$batch_size = 500;

	do
	{
		$sql = 'SELECT t2.forum_id, t2.topic_id
			FROM ' . TOPICS_TABLE . ' t2, ' . TOPICS_TABLE . ' t
			WHERE t2.topic_moved_id = t.topic_id
				AND t.forum_id = ' . (int) $forum_id . '
				' . (($sql_more) ? 'AND ' . $sql_more : '');
		$result = $db->sql_query_limit($sql, $batch_size);

		$topic_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ids[] = (int) $row['topic_id'];

			$sync_forum_ids[(int) $row['forum_id']] = (int) $row['forum_id'];
		}
		$db->sql_freeresult($result);

		if (!empty($topic_ids))
		{
			$sql = 'DELETE FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
			$db->sql_query($sql);
		}
	}
	while (sizeof($topic_ids) == $batch_size);

	if ($auto_sync)
	{
		sync('forum', 'forum_id', $sync_forum_ids, true, true);
	}

	return $sync_forum_ids;
}

/**
* Update/Sync posted information for topics
*/
function update_posted_info(&$topic_ids)
{
	global $db, $config;

	if (empty($topic_ids) || !$config['load_db_track'])
	{
		return;
	}

	// First of all, let us remove any posted information for these topics
	$sql = 'DELETE FROM ' . TOPICS_POSTED_TABLE . '
		WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
	$db->sql_query($sql);

	// Now, let us collect the user/topic combos for rebuilding the information
	$sql = 'SELECT poster_id, topic_id
		FROM ' . POSTS_TABLE . '
		WHERE ' . $db->sql_in_set('topic_id', $topic_ids) . '
			AND poster_id <> ' . ANONYMOUS . '
		GROUP BY poster_id, topic_id';
	$result = $db->sql_query($sql);

	$posted = array();
	while ($row = $db->sql_fetchrow($result))
	{
		// Add as key to make them unique (grouping by) and circumvent empty keys on array_unique
		$posted[$row['poster_id']][] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	// Now add the information...
	$sql_ary = array();
	foreach ($posted as $user_id => $topic_row)
	{
		foreach ($topic_row as $topic_id)
		{
			$sql_ary[] = array(
				'user_id'		=> (int) $user_id,
				'topic_id'		=> (int) $topic_id,
				'topic_posted'	=> 1,
			);
		}
	}
	unset($posted);

	$db->sql_multi_insert(TOPICS_POSTED_TABLE, $sql_ary);
}

/**
* Delete attached file
*/
function phpbb_unlink($filename, $mode = 'file', $entry_removed = false)
{
	global $db, $phpbb_root_path, $config;

	// Because of copying topics or modifications a physical filename could be assigned more than once. If so, do not remove the file itself.
	$sql = 'SELECT COUNT(attach_id) AS num_entries
		FROM ' . ATTACHMENTS_TABLE . "
		WHERE physical_filename = '" . $db->sql_escape(utf8_basename($filename)) . "'";
	$result = $db->sql_query($sql);
	$num_entries = (int) $db->sql_fetchfield('num_entries');
	$db->sql_freeresult($result);

	// Do not remove file if at least one additional entry with the same name exist.
	if (($entry_removed && $num_entries > 0) || (!$entry_removed && $num_entries > 1))
	{
		return false;
	}

	$filename = ($mode == 'thumbnail') ? 'thumb_' . utf8_basename($filename) : utf8_basename($filename);
	return @unlink($phpbb_root_path . $config['upload_path'] . '/' . $filename);
}

/**
* All-encompasing sync function
*
* Exaples:
* <code>
* sync('topic', 'topic_id', 123);			// resync topic #123
* sync('topic', 'forum_id', array(2, 3));	// resync topics from forum #2 and #3
* sync('topic');							// resync all topics
* sync('topic', 'range', 'topic_id BETWEEN 1 AND 60');	// resync a range of topics/forums (only available for 'topic' and 'forum' modes)
* </code>
*
* Modes:
* - forum				Resync complete forum
* - topic				Resync topics
* - topic_moved			Removes topic shadows that would be in the same forum as the topic they link to
* - topic_visibility	Resyncs the topic_visibility flag according to the status of the first post
* - post_reported		Resyncs the post_reported flag, relying on actual reports
* - topic_reported		Resyncs the topic_reported flag, relying on post_reported flags
* - post_attachement	Same as post_reported, but with attachment flags
* - topic_attachement	Same as topic_reported, but with attachment flags
*/
function sync($mode, $where_type = '', $where_ids = '', $resync_parents = false, $sync_extra = false)
{
	global $db;

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
		$where_ids = array_map('intval', $where_ids);
	}
	else if ($where_type != 'range')
	{
		$where_ids = ($where_ids) ? array((int) $where_ids) : array();
	}

	if ($mode == 'forum' || $mode == 'topic' || $mode == 'topic_visibility' || $mode == 'topic_reported' || $mode == 'post_reported')
	{
		if (!$where_type)
		{
			$where_sql = '';
			$where_sql_and = 'WHERE';
		}
		else if ($where_type == 'range')
		{
			// Only check a range of topics/forums. For instance: 'topic_id BETWEEN 1 AND 60'
			$where_sql = 'WHERE (' . $mode[0] . ".$where_ids)";
			$where_sql_and = $where_sql . "\n\tAND";
		}
		else
		{
			// Do not sync the "global forum"
			$where_ids = array_diff($where_ids, array(0));

			if (!sizeof($where_ids))
			{
				// Empty array with IDs. This means that we don't have any work to do. Just return.
				return;
			}

			// Limit the topics/forums we are syncing, use specific topic/forum IDs.
			// $where_type contains the field for the where clause (forum_id, topic_id)
			$where_sql = 'WHERE ' . $db->sql_in_set($mode[0] . '.' . $where_type, $where_ids);
			$where_sql_and = $where_sql . "\n\tAND";
		}
	}
	else
	{
		if (!sizeof($where_ids))
		{
			return;
		}

		// $where_type contains the field for the where clause (forum_id, topic_id)
		$where_sql = 'WHERE ' . $db->sql_in_set($mode[0] . '.' . $where_type, $where_ids);
		$where_sql_and = $where_sql . "\n\tAND";
	}

	switch ($mode)
	{
		case 'topic_moved':
			$db->sql_transaction('begin');
			switch ($db->get_sql_layer())
			{
				case 'mysql4':
				case 'mysqli':
					$sql = 'DELETE FROM ' . TOPICS_TABLE . '
						USING ' . TOPICS_TABLE . ' t1, ' . TOPICS_TABLE . " t2
						WHERE t1.topic_moved_id = t2.topic_id
							AND t1.forum_id = t2.forum_id";
					$db->sql_query($sql);
				break;

				default:
					$sql = 'SELECT t1.topic_id
						FROM ' .TOPICS_TABLE . ' t1, ' . TOPICS_TABLE . " t2
						WHERE t1.topic_moved_id = t2.topic_id
							AND t1.forum_id = t2.forum_id";
					$result = $db->sql_query($sql);

					$topic_id_ary = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$topic_id_ary[] = $row['topic_id'];
					}
					$db->sql_freeresult($result);

					if (!sizeof($topic_id_ary))
					{
						return;
					}

					$sql = 'DELETE FROM ' . TOPICS_TABLE . '
						WHERE ' . $db->sql_in_set('topic_id', $topic_id_ary);
					$db->sql_query($sql);

				break;
			}

			$db->sql_transaction('commit');
			break;

		case 'topic_visibility':

			$db->sql_transaction('begin');

			$sql = 'SELECT t.topic_id, p.post_visibility
				FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				$where_sql_and p.topic_id = t.topic_id
					AND p.post_visibility = " . ITEM_APPROVED;
			$result = $db->sql_query($sql);

			$topics_approved = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topics_approved[] = (int) $row['topic_id'];
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT t.topic_id, p.post_visibility
				FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				$where_sql_and " . $db->sql_in_set('t.topic_id', $topics_approved, true, true) . '
					AND p.topic_id = t.topic_id
					AND p.post_visibility = ' . ITEM_DELETED;
			$result = $db->sql_query($sql);

			$topics_softdeleted = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topics_softdeleted[] = (int) $row['topic_id'];
			}
			$db->sql_freeresult($result);

			$topics_softdeleted = array_diff($topics_softdeleted, $topics_approved);
			$topics_not_unapproved = array_merge($topics_softdeleted, $topics_approved);

			$update_ary = array(
				ITEM_UNAPPROVED	=> (!empty($topics_not_unapproved)) ? $where_sql_and . ' ' . $db->sql_in_set('topic_id', $topics_not_unapproved, true) : '',
				ITEM_APPROVED	=> (!empty($topics_approved)) ? ' WHERE ' . $db->sql_in_set('topic_id', $topics_approved) : '',
				ITEM_DELETED	=> (!empty($topics_softdeleted)) ? ' WHERE ' . $db->sql_in_set('topic_id', $topics_softdeleted) : '',
			);

			foreach ($update_ary as $visibility => $sql_where)
			{
				if ($sql_where)
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_visibility = ' . $visibility . '
						' . $sql_where;
					$db->sql_query($sql);
				}
			}

			$db->sql_transaction('commit');
			break;

		case 'post_reported':
			$post_ids = $post_reported = array();

			$db->sql_transaction('begin');

			$sql = 'SELECT p.post_id, p.post_reported
				FROM ' . POSTS_TABLE . " p
				$where_sql
				GROUP BY p.post_id, p.post_reported";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$post_ids[$row['post_id']] = $row['post_id'];
				if ($row['post_reported'])
				{
					$post_reported[$row['post_id']] = 1;
				}
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT DISTINCT(post_id)
				FROM ' . REPORTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_id', $post_ids) . '
					AND report_closed = 0';
			$result = $db->sql_query($sql);

			$post_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($post_reported[$row['post_id']]))
				{
					$post_ids[] = $row['post_id'];
				}
				else
				{
					unset($post_reported[$row['post_id']]);
				}
			}
			$db->sql_freeresult($result);

			// $post_reported should be empty by now, if it's not it contains
			// posts that are falsely flagged as reported
			foreach ($post_reported as $post_id => $void)
			{
				$post_ids[] = $post_id;
			}

			if (sizeof($post_ids))
			{
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET post_reported = 1 - post_reported
					WHERE ' . $db->sql_in_set('post_id', $post_ids);
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');
			break;

		case 'topic_reported':
			if ($sync_extra)
			{
				sync('post_reported', $where_type, $where_ids);
			}

			$topic_ids = $topic_reported = array();

			$db->sql_transaction('begin');

			$sql = 'SELECT DISTINCT(t.topic_id)
				FROM ' . POSTS_TABLE . " t
				$where_sql_and t.post_reported = 1";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$topic_reported[$row['topic_id']] = 1;
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT t.topic_id, t.topic_reported
				FROM ' . TOPICS_TABLE . " t
				$where_sql";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['topic_reported'] ^ isset($topic_reported[$row['topic_id']]))
				{
					$topic_ids[] = $row['topic_id'];
				}
			}
			$db->sql_freeresult($result);

			if (sizeof($topic_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_reported = 1 - topic_reported
					WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');
			break;

		case 'post_attachment':
			$post_ids = $post_attachment = array();

			$db->sql_transaction('begin');

			$sql = 'SELECT p.post_id, p.post_attachment
				FROM ' . POSTS_TABLE . " p
				$where_sql
				GROUP BY p.post_id, p.post_attachment";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$post_ids[$row['post_id']] = $row['post_id'];
				if ($row['post_attachment'])
				{
					$post_attachment[$row['post_id']] = 1;
				}
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT DISTINCT(post_msg_id)
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE ' . $db->sql_in_set('post_msg_id', $post_ids) . '
					AND in_message = 0';
			$result = $db->sql_query($sql);

			$post_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($post_attachment[$row['post_msg_id']]))
				{
					$post_ids[] = $row['post_msg_id'];
				}
				else
				{
					unset($post_attachment[$row['post_msg_id']]);
				}
			}
			$db->sql_freeresult($result);

			// $post_attachment should be empty by now, if it's not it contains
			// posts that are falsely flagged as having attachments
			foreach ($post_attachment as $post_id => $void)
			{
				$post_ids[] = $post_id;
			}

			if (sizeof($post_ids))
			{
				$sql = 'UPDATE ' . POSTS_TABLE . '
					SET post_attachment = 1 - post_attachment
					WHERE ' . $db->sql_in_set('post_id', $post_ids);
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');
			break;

		case 'topic_attachment':
			if ($sync_extra)
			{
				sync('post_attachment', $where_type, $where_ids);
			}

			$topic_ids = $topic_attachment = array();

			$db->sql_transaction('begin');

			$sql = 'SELECT DISTINCT(t.topic_id)
				FROM ' . POSTS_TABLE . " t
				$where_sql_and t.post_attachment = 1";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$topic_attachment[$row['topic_id']] = 1;
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT t.topic_id, t.topic_attachment
				FROM ' . TOPICS_TABLE . " t
				$where_sql";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['topic_attachment'] ^ isset($topic_attachment[$row['topic_id']]))
				{
					$topic_ids[] = $row['topic_id'];
				}
			}
			$db->sql_freeresult($result);

			if (sizeof($topic_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_attachment = 1 - topic_attachment
					WHERE ' . $db->sql_in_set('topic_id', $topic_ids);
				$db->sql_query($sql);
			}

			$db->sql_transaction('commit');

			break;

		case 'forum':

			$db->sql_transaction('begin');

			// 1: Get the list of all forums
			$sql = 'SELECT f.*
				FROM ' . FORUMS_TABLE . " f
				$where_sql";
			$result = $db->sql_query($sql);

			$forum_data = $forum_ids = $post_ids = $last_post_id = $post_info = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['forum_type'] == FORUM_LINK)
				{
					continue;
				}

				$forum_id = (int) $row['forum_id'];
				$forum_ids[$forum_id] = $forum_id;

				$forum_data[$forum_id] = $row;
				if ($sync_extra)
				{
					$forum_data[$forum_id]['posts_approved'] = 0;
					$forum_data[$forum_id]['posts_unapproved'] = 0;
					$forum_data[$forum_id]['posts_softdeleted'] = 0;
					$forum_data[$forum_id]['topics_approved'] = 0;
					$forum_data[$forum_id]['topics_unapproved'] = 0;
					$forum_data[$forum_id]['topics_softdeleted'] = 0;
				}
				$forum_data[$forum_id]['last_post_id'] = 0;
				$forum_data[$forum_id]['last_post_subject'] = '';
				$forum_data[$forum_id]['last_post_time'] = 0;
				$forum_data[$forum_id]['last_poster_id'] = 0;
				$forum_data[$forum_id]['last_poster_name'] = '';
				$forum_data[$forum_id]['last_poster_colour'] = '';
			}
			$db->sql_freeresult($result);

			if (!sizeof($forum_ids))
			{
				break;
			}

			$forum_ids = array_values($forum_ids);

			// 2: Get topic counts for each forum (optional)
			if ($sync_extra)
			{
				$sql = 'SELECT forum_id, topic_visibility, COUNT(topic_id) AS total_topics
					FROM ' . TOPICS_TABLE . '
					WHERE ' . $db->sql_in_set('forum_id', $forum_ids) . '
					GROUP BY forum_id, topic_visibility';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = (int) $row['forum_id'];

					if ($row['topic_visibility'] == ITEM_APPROVED)
					{
						$forum_data[$forum_id]['topics_approved'] = $row['total_topics'];
					}
					else if ($row['topic_visibility'] == ITEM_UNAPPROVED || $row['topic_visibility'] == ITEM_REAPPROVE)
					{
						$forum_data[$forum_id]['topics_unapproved'] = $row['total_topics'];
					}
					else if ($row['topic_visibility'] == ITEM_DELETED)
					{
						$forum_data[$forum_id]['topics_softdeleted'] = $row['total_topics'];
					}
				}
				$db->sql_freeresult($result);
			}

			// 3: Get post count for each forum (optional)
			if ($sync_extra)
			{
				if (sizeof($forum_ids) == 1)
				{
					$sql = 'SELECT SUM(t.topic_posts_approved) AS forum_posts_approved, SUM(t.topic_posts_unapproved) AS forum_posts_unapproved, SUM(t.topic_posts_softdeleted) AS forum_posts_softdeleted
						FROM ' . TOPICS_TABLE . ' t
						WHERE ' . $db->sql_in_set('t.forum_id', $forum_ids) . '
							AND t.topic_status <> ' . ITEM_MOVED;
				}
				else
				{
					$sql = 'SELECT t.forum_id, SUM(t.topic_posts_approved) AS forum_posts_approved, SUM(t.topic_posts_unapproved) AS forum_posts_unapproved, SUM(t.topic_posts_softdeleted) AS forum_posts_softdeleted
						FROM ' . TOPICS_TABLE . ' t
						WHERE ' . $db->sql_in_set('t.forum_id', $forum_ids) . '
							AND t.topic_status <> ' . ITEM_MOVED . '
						GROUP BY t.forum_id';
				}

				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$forum_id = (sizeof($forum_ids) == 1) ? (int) $forum_ids[0] : (int) $row['forum_id'];

					$forum_data[$forum_id]['posts_approved'] = (int) $row['forum_posts_approved'];
					$forum_data[$forum_id]['posts_unapproved'] = (int) $row['forum_posts_unapproved'];
					$forum_data[$forum_id]['posts_softdeleted'] = (int) $row['forum_posts_softdeleted'];
				}
				$db->sql_freeresult($result);
			}

			// 4: Get last_post_id for each forum
			if (sizeof($forum_ids) == 1)
			{
				$sql = 'SELECT MAX(t.topic_last_post_id) as last_post_id
					FROM ' . TOPICS_TABLE . ' t
					WHERE ' . $db->sql_in_set('t.forum_id', $forum_ids) . '
						AND t.topic_visibility = ' . ITEM_APPROVED;
			}
			else
			{
				$sql = 'SELECT t.forum_id, MAX(t.topic_last_post_id) as last_post_id
					FROM ' . TOPICS_TABLE . ' t
					WHERE ' . $db->sql_in_set('t.forum_id', $forum_ids) . '
						AND t.topic_visibility = ' . ITEM_APPROVED . '
					GROUP BY t.forum_id';
			}

			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$forum_id = (sizeof($forum_ids) == 1) ? (int) $forum_ids[0] : (int) $row['forum_id'];

				$forum_data[$forum_id]['last_post_id'] = (int) $row['last_post_id'];

				$post_ids[] = $row['last_post_id'];
			}
			$db->sql_freeresult($result);

			// 5: Retrieve last_post infos
			if (sizeof($post_ids))
			{
				$sql = 'SELECT p.post_id, p.poster_id, p.post_subject, p.post_time, p.post_username, u.username, u.user_colour
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
					WHERE ' . $db->sql_in_set('p.post_id', $post_ids) . '
						AND p.poster_id = u.user_id';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$post_info[$row['post_id']] = $row;
				}
				$db->sql_freeresult($result);

				foreach ($forum_data as $forum_id => $data)
				{
					if ($data['last_post_id'])
					{
						if (isset($post_info[$data['last_post_id']]))
						{
							$forum_data[$forum_id]['last_post_subject'] = $post_info[$data['last_post_id']]['post_subject'];
							$forum_data[$forum_id]['last_post_time'] = $post_info[$data['last_post_id']]['post_time'];
							$forum_data[$forum_id]['last_poster_id'] = $post_info[$data['last_post_id']]['poster_id'];
							$forum_data[$forum_id]['last_poster_name'] = ($post_info[$data['last_post_id']]['poster_id'] != ANONYMOUS) ? $post_info[$data['last_post_id']]['username'] : $post_info[$data['last_post_id']]['post_username'];
							$forum_data[$forum_id]['last_poster_colour'] = $post_info[$data['last_post_id']]['user_colour'];
						}
						else
						{
							// For some reason we did not find the post in the db
							$forum_data[$forum_id]['last_post_id'] = 0;
							$forum_data[$forum_id]['last_post_subject'] = '';
							$forum_data[$forum_id]['last_post_time'] = 0;
							$forum_data[$forum_id]['last_poster_id'] = 0;
							$forum_data[$forum_id]['last_poster_name'] = '';
							$forum_data[$forum_id]['last_poster_colour'] = '';
						}
					}
				}
				unset($post_info);
			}

			// 6: Now do that thing
			$fieldnames = array('last_post_id', 'last_post_subject', 'last_post_time', 'last_poster_id', 'last_poster_name', 'last_poster_colour');

			if ($sync_extra)
			{
				array_push($fieldnames, 'posts_approved', 'posts_unapproved', 'posts_softdeleted', 'topics_approved', 'topics_unapproved', 'topics_softdeleted');
			}

			foreach ($forum_data as $forum_id => $row)
			{
				$sql_ary = array();

				foreach ($fieldnames as $fieldname)
				{
					if ($row['forum_' . $fieldname] != $row[$fieldname])
					{
						if (preg_match('#(name|colour|subject)$#', $fieldname))
						{
							$sql_ary['forum_' . $fieldname] = (string) $row[$fieldname];
						}
						else
						{
							$sql_ary['forum_' . $fieldname] = (int) $row[$fieldname];
						}
					}
				}

				if (sizeof($sql_ary))
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
				}
			}

			$db->sql_transaction('commit');
			break;

		case 'topic':
			$topic_data = $post_ids = $resync_forums = $delete_topics = $delete_posts = $moved_topics = array();

			$db->sql_transaction('begin');

			$sql = 'SELECT t.topic_id, t.forum_id, t.topic_moved_id, t.topic_visibility, ' . (($sync_extra) ? 't.topic_attachment, t.topic_reported, ' : '') . 't.topic_poster, t.topic_time, t.topic_posts_approved, t.topic_posts_unapproved, t.topic_posts_softdeleted, t.topic_first_post_id, t.topic_first_poster_name, t.topic_first_poster_colour, t.topic_last_post_id, t.topic_last_post_subject, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_poster_colour, t.topic_last_post_time
				FROM ' . TOPICS_TABLE . " t
				$where_sql";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['topic_moved_id'])
				{
					$moved_topics[] = $row['topic_id'];
					continue;
				}

				$topic_id = (int) $row['topic_id'];
				$topic_data[$topic_id] = $row;
				$topic_data[$topic_id]['visibility'] = ITEM_UNAPPROVED;
				$topic_data[$topic_id]['posts_approved'] = 0;
				$topic_data[$topic_id]['posts_unapproved'] = 0;
				$topic_data[$topic_id]['posts_softdeleted'] = 0;
				$topic_data[$topic_id]['first_post_id'] = 0;
				$topic_data[$topic_id]['last_post_id'] = 0;
				unset($topic_data[$topic_id]['topic_id']);

				// This array holds all topic_ids
				$delete_topics[$topic_id] = '';

				if ($sync_extra)
				{
					$topic_data[$topic_id]['reported'] = 0;
					$topic_data[$topic_id]['attachment'] = 0;
				}
			}
			$db->sql_freeresult($result);

			// Use "t" as table alias because of the $where_sql clause
			// NOTE: 't.post_visibility' in the GROUP BY is causing a major slowdown.
			$sql = 'SELECT t.topic_id, t.post_visibility, COUNT(t.post_id) AS total_posts, MIN(t.post_id) AS first_post_id, MAX(t.post_id) AS last_post_id
				FROM ' . POSTS_TABLE . " t
				$where_sql
				GROUP BY t.topic_id, t.post_visibility";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id = (int) $row['topic_id'];

				$row['first_post_id'] = (int) $row['first_post_id'];
				$row['last_post_id'] = (int) $row['last_post_id'];

				if (!isset($topic_data[$topic_id]))
				{
					// Hey, these posts come from a topic that does not exist
					$delete_posts[$topic_id] = '';
				}
				else
				{
					// Unset the corresponding entry in $delete_topics
					// When we'll be done, only topics with no posts will remain
					unset($delete_topics[$topic_id]);

					if ($row['post_visibility'] == ITEM_APPROVED)
					{
						$topic_data[$topic_id]['posts_approved'] = $row['total_posts'];
					}
					else if ($row['post_visibility'] == ITEM_UNAPPROVED || $row['post_visibility'] == ITEM_REAPPROVE)
					{
						$topic_data[$topic_id]['posts_unapproved'] = $row['total_posts'];
					}
					else if ($row['post_visibility'] == ITEM_DELETED)
					{
						$topic_data[$topic_id]['posts_softdeleted'] = $row['total_posts'];
					}

					if ($row['post_visibility'] == ITEM_APPROVED)
					{
						$topic_data[$topic_id]['visibility'] = ITEM_APPROVED;
						$topic_data[$topic_id]['first_post_id'] = $row['first_post_id'];
						$topic_data[$topic_id]['last_post_id'] = $row['last_post_id'];
					}
					else if ($topic_data[$topic_id]['visibility'] != ITEM_APPROVED)
					{
						// If there is no approved post, we take the min/max of the other visibilities
						// for the last and first post info, because it is only visible to moderators anyway
						$topic_data[$topic_id]['first_post_id'] = (!empty($topic_data[$topic_id]['first_post_id'])) ? min($topic_data[$topic_id]['first_post_id'], $row['first_post_id']) : $row['first_post_id'];
						$topic_data[$topic_id]['last_post_id'] = max($topic_data[$topic_id]['last_post_id'], $row['last_post_id']);

						if ($topic_data[$topic_id]['visibility'] == ITEM_UNAPPROVED || $topic_data[$topic_id]['visibility'] == ITEM_REAPPROVE)
						{
							// Soft delete status is stronger than unapproved.
							$topic_data[$topic_id]['visibility'] = $row['post_visibility'];
						}
					}
				}
			}
			$db->sql_freeresult($result);

			foreach ($topic_data as $topic_id => $row)
			{
				$post_ids[] = $row['first_post_id'];
				if ($row['first_post_id'] != $row['last_post_id'])
				{
					$post_ids[] = $row['last_post_id'];
				}
			}

			// Now we delete empty topics and orphan posts
			if (sizeof($delete_posts))
			{
				delete_posts('topic_id', array_keys($delete_posts), false);
				unset($delete_posts);
			}

			if (!sizeof($topic_data))
			{
				// If we get there, topic ids were invalid or topics did not contain any posts
				delete_topics($where_type, $where_ids, true);
				return;
			}

			if (sizeof($delete_topics))
			{
				$delete_topic_ids = array();
				foreach ($delete_topics as $topic_id => $void)
				{
					unset($topic_data[$topic_id]);
					$delete_topic_ids[] = $topic_id;
				}

				delete_topics('topic_id', $delete_topic_ids, false);
				unset($delete_topics, $delete_topic_ids);
			}

			$sql = 'SELECT p.post_id, p.topic_id, p.post_visibility, p.poster_id, p.post_subject, p.post_username, p.post_time, u.username, u.user_colour
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE ' . $db->sql_in_set('p.post_id', $post_ids) . '
					AND u.user_id = p.poster_id';
			$result = $db->sql_query($sql);

			$post_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id = intval($row['topic_id']);

				if ($row['post_id'] == $topic_data[$topic_id]['first_post_id'])
				{
					$topic_data[$topic_id]['time'] = $row['post_time'];
					$topic_data[$topic_id]['poster'] = $row['poster_id'];
					$topic_data[$topic_id]['first_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
					$topic_data[$topic_id]['first_poster_colour'] = $row['user_colour'];
				}

				if ($row['post_id'] == $topic_data[$topic_id]['last_post_id'])
				{
					$topic_data[$topic_id]['last_poster_id'] = $row['poster_id'];
					$topic_data[$topic_id]['last_post_subject'] = $row['post_subject'];
					$topic_data[$topic_id]['last_post_time'] = $row['post_time'];
					$topic_data[$topic_id]['last_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
					$topic_data[$topic_id]['last_poster_colour'] = $row['user_colour'];
				}
			}
			$db->sql_freeresult($result);

			// Make sure shadow topics do link to existing topics
			if (sizeof($moved_topics))
			{
				$delete_topics = array();

				$sql = 'SELECT t1.topic_id, t1.topic_moved_id
					FROM ' . TOPICS_TABLE . ' t1
					LEFT JOIN ' . TOPICS_TABLE . ' t2 ON (t2.topic_id = t1.topic_moved_id)
					WHERE ' . $db->sql_in_set('t1.topic_id', $moved_topics) . '
						AND t2.topic_id IS NULL';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$delete_topics[] = $row['topic_id'];
				}
				$db->sql_freeresult($result);

				if (sizeof($delete_topics))
				{
					delete_topics('topic_id', $delete_topics, false);
				}
				unset($delete_topics);

				// Make sure shadow topics having no last post data being updated (this only rarely happens...)
				$sql = 'SELECT topic_id, topic_moved_id, topic_last_post_id, topic_first_post_id
					FROM ' . TOPICS_TABLE . '
					WHERE ' . $db->sql_in_set('topic_id', $moved_topics) . '
						AND topic_last_post_time = 0';
				$result = $db->sql_query($sql);

				$shadow_topic_data = $post_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$shadow_topic_data[$row['topic_moved_id']] = $row;
					$post_ids[] = $row['topic_last_post_id'];
					$post_ids[] = $row['topic_first_post_id'];
				}
				$db->sql_freeresult($result);

				$sync_shadow_topics = array();
				if (sizeof($post_ids))
				{
					$sql = 'SELECT p.post_id, p.topic_id, p.post_visibility, p.poster_id, p.post_subject, p.post_username, p.post_time, u.username, u.user_colour
						FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
						WHERE ' . $db->sql_in_set('p.post_id', $post_ids) . '
							AND u.user_id = p.poster_id';
					$result = $db->sql_query($sql);

					$post_ids = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$topic_id = (int) $row['topic_id'];

						// Ok, there should be a shadow topic. If there isn't, then there's something wrong with the db.
						// However, there's not much we can do about it.
						if (!empty($shadow_topic_data[$topic_id]))
						{
							if ($row['post_id'] == $shadow_topic_data[$topic_id]['topic_first_post_id'])
							{
								$orig_topic_id = $shadow_topic_data[$topic_id]['topic_id'];

								if (!isset($sync_shadow_topics[$orig_topic_id]))
								{
									$sync_shadow_topics[$orig_topic_id] = array();
								}

								$sync_shadow_topics[$orig_topic_id]['topic_time'] = $row['post_time'];
								$sync_shadow_topics[$orig_topic_id]['topic_poster'] = $row['poster_id'];
								$sync_shadow_topics[$orig_topic_id]['topic_first_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
								$sync_shadow_topics[$orig_topic_id]['topic_first_poster_colour'] = $row['user_colour'];
							}

							if ($row['post_id'] == $shadow_topic_data[$topic_id]['topic_last_post_id'])
							{
								$orig_topic_id = $shadow_topic_data[$topic_id]['topic_id'];

								if (!isset($sync_shadow_topics[$orig_topic_id]))
								{
									$sync_shadow_topics[$orig_topic_id] = array();
								}

								$sync_shadow_topics[$orig_topic_id]['topic_last_poster_id'] = $row['poster_id'];
								$sync_shadow_topics[$orig_topic_id]['topic_last_post_subject'] = $row['post_subject'];
								$sync_shadow_topics[$orig_topic_id]['topic_last_post_time'] = $row['post_time'];
								$sync_shadow_topics[$orig_topic_id]['topic_last_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
								$sync_shadow_topics[$orig_topic_id]['topic_last_poster_colour'] = $row['user_colour'];
							}
						}
					}
					$db->sql_freeresult($result);

					$shadow_topic_data = array();

					// Update the information we collected
					if (sizeof($sync_shadow_topics))
					{
						foreach ($sync_shadow_topics as $sync_topic_id => $sql_ary)
						{
							$sql = 'UPDATE ' . TOPICS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE topic_id = ' . $sync_topic_id;
							$db->sql_query($sql);
						}
					}
				}

				unset($sync_shadow_topics, $shadow_topic_data);
			}

			// These are fields that will be synchronised
			$fieldnames = array('time', 'visibility', 'posts_approved', 'posts_unapproved', 'posts_softdeleted', 'poster', 'first_post_id', 'first_poster_name', 'first_poster_colour', 'last_post_id', 'last_post_subject', 'last_post_time', 'last_poster_id', 'last_poster_name', 'last_poster_colour');

			if ($sync_extra)
			{
				// This routine assumes that post_reported values are correct
				// if they are not, use sync('post_reported') first
				$sql = 'SELECT t.topic_id, p.post_id
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					$where_sql_and p.topic_id = t.topic_id
						AND p.post_reported = 1
					GROUP BY t.topic_id, p.post_id";
				$result = $db->sql_query($sql);

				$fieldnames[] = 'reported';
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_data[intval($row['topic_id'])]['reported'] = 1;
				}
				$db->sql_freeresult($result);

				// This routine assumes that post_attachment values are correct
				// if they are not, use sync('post_attachment') first
				$sql = 'SELECT t.topic_id, p.post_id
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					$where_sql_and p.topic_id = t.topic_id
						AND p.post_attachment = 1
					GROUP BY t.topic_id, p.post_id";
				$result = $db->sql_query($sql);

				$fieldnames[] = 'attachment';
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_data[intval($row['topic_id'])]['attachment'] = 1;
				}
				$db->sql_freeresult($result);
			}

			foreach ($topic_data as $topic_id => $row)
			{
				$sql_ary = array();

				foreach ($fieldnames as $fieldname)
				{
					if (isset($row[$fieldname]) && isset($row['topic_' . $fieldname]) && $row['topic_' . $fieldname] != $row[$fieldname])
					{
						$sql_ary['topic_' . $fieldname] = $row[$fieldname];
					}
				}

				if (sizeof($sql_ary))
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE topic_id = ' . $topic_id;
					$db->sql_query($sql);

					$resync_forums[$row['forum_id']] = $row['forum_id'];
				}
			}
			unset($topic_data);

			$db->sql_transaction('commit');

			// if some topics have been resync'ed then resync parent forums
			// except when we're only syncing a range, we don't want to sync forums during
			// batch processing.
			if ($resync_parents && sizeof($resync_forums) && $where_type != 'range')
			{
				sync('forum', 'forum_id', array_values($resync_forums), true, true);
			}
			break;
	}

	return;
}

/**
* Prune function
*/
function prune($forum_id, $prune_mode, $prune_date, $prune_flags = 0, $auto_sync = true)
{
	global $db, $phpbb_dispatcher;

	if (!is_array($forum_id))
	{
		$forum_id = array($forum_id);
	}

	if (!sizeof($forum_id))
	{
		return;
	}

	$sql_and = '';

	if (!($prune_flags & FORUM_FLAG_PRUNE_ANNOUNCE))
	{
		$sql_and .= ' AND topic_type <> ' . POST_ANNOUNCE;
		$sql_and .= ' AND topic_type <> ' . POST_GLOBAL;
	}

	if (!($prune_flags & FORUM_FLAG_PRUNE_STICKY))
	{
		$sql_and .= ' AND topic_type <> ' . POST_STICKY;
	}

	if ($prune_mode == 'posted')
	{
		$sql_and .= " AND topic_last_post_time < $prune_date";
	}

	if ($prune_mode == 'viewed')
	{
		$sql_and .= " AND topic_last_view_time < $prune_date";
	}

	if ($prune_mode == 'shadow')
	{
		$sql_and .= ' AND topic_status = ' . ITEM_MOVED . " AND topic_last_post_time < $prune_date";
	}

	/**
	* Use this event to modify the SQL that selects topics to be pruned
	*
	* @event core.prune_sql
	* @var string	forum_id		The forum id
	* @var string	prune_mode		The prune mode
	* @var string	prune_date		The prune date
	* @var int		prune_flags		The prune flags
	* @var bool		auto_sync		Whether or not to perform auto sync
	* @var string	sql_and			SQL text appended to where clause
	* @since 3.1.3-RC1
	*/
	$vars = array('forum_id', 'prune_mode', 'prune_date', 'prune_flags', 'auto_sync', 'sql_and');
	extract($phpbb_dispatcher->trigger_event('core.prune_sql', compact($vars)));

	$sql = 'SELECT topic_id
		FROM ' . TOPICS_TABLE . '
		WHERE ' . $db->sql_in_set('forum_id', $forum_id) . "
			AND poll_start = 0
			$sql_and";
	$result = $db->sql_query($sql);

	$topic_list = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$topic_list[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	if ($prune_flags & FORUM_FLAG_PRUNE_POLL)
	{
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . '
			WHERE ' . $db->sql_in_set('forum_id', $forum_id) . "
				AND poll_start > 0
				AND poll_last_vote < $prune_date
				$sql_and";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$topic_list[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);

		$topic_list = array_unique($topic_list);
	}

	return delete_topics('topic_id', $topic_list, $auto_sync, false);
}

/**
* Function auto_prune(), this function now relies on passed vars
*/
function auto_prune($forum_id, $prune_mode, $prune_flags, $prune_days, $prune_freq)
{
	global $db;

	$sql = 'SELECT forum_name
		FROM ' . FORUMS_TABLE . "
		WHERE forum_id = $forum_id";
	$result = $db->sql_query($sql, 3600);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	if ($row)
	{
		$prune_date = time() - ($prune_days * 86400);
		$next_prune = time() + ($prune_freq * 86400);

		prune($forum_id, $prune_mode, $prune_date, $prune_flags, true);

		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET prune_next = $next_prune
			WHERE forum_id = $forum_id";
		$db->sql_query($sql);

		add_log('admin', 'LOG_AUTO_PRUNE', $row['forum_name']);
	}

	return;
}

/**
* Cache moderators. Called whenever permissions are changed
* via admin_permissions. Changes of usernames and group names
* must be carried through for the moderators table.
*
* @param \phpbb\db\driver\driver_interface $db Database connection
* @param \phpbb\cache\driver\driver_interface Cache driver
* @param \phpbb\auth\auth $auth Authentication object
* @return null
*/
function phpbb_cache_moderators($db, $cache, $auth)
{
	// Remove cached sql results
	$cache->destroy('sql', MODERATOR_CACHE_TABLE);

	// Clear table
	switch ($db->get_sql_layer())
	{
		case 'sqlite':
		case 'sqlite3':
			$db->sql_query('DELETE FROM ' . MODERATOR_CACHE_TABLE);
		break;

		default:
			$db->sql_query('TRUNCATE TABLE ' . MODERATOR_CACHE_TABLE);
		break;
	}

	// We add moderators who have forum moderator permissions without an explicit ACL_NEVER setting
	$hold_ary = $ug_id_ary = $sql_ary = array();

	// Grab all users having moderative options...
	$hold_ary = $auth->acl_user_raw_data(false, 'm_%', false);

	// Add users?
	if (sizeof($hold_ary))
	{
		// At least one moderative option warrants a display
		$ug_id_ary = array_keys($hold_ary);

		// Remove users who have group memberships with DENY moderator permissions
		$sql_ary_deny = array(
			'SELECT'	=> 'a.forum_id, ug.user_id, g.group_id',

			'FROM'		=> array(
				ACL_OPTIONS_TABLE	=> 'o',
				USER_GROUP_TABLE	=> 'ug',
				GROUPS_TABLE		=> 'g',
				ACL_GROUPS_TABLE	=> 'a',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(ACL_ROLES_DATA_TABLE => 'r'),
					'ON'	=> 'a.auth_role_id = r.role_id',
				),
			),

			'WHERE'		=> '(o.auth_option_id = a.auth_option_id OR o.auth_option_id = r.auth_option_id)
				AND ((a.auth_setting = ' . ACL_NEVER . ' AND r.auth_setting IS NULL)
					OR r.auth_setting = ' . ACL_NEVER . ')
				AND a.group_id = ug.group_id
				AND g.group_id = ug.group_id
				AND NOT (ug.group_leader = 1 AND g.group_skip_auth = 1)
				AND ' . $db->sql_in_set('ug.user_id', $ug_id_ary) . "
				AND ug.user_pending = 0
				AND o.auth_option " . $db->sql_like_expression('m_' . $db->get_any_char()),
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary_deny);
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (isset($hold_ary[$row['user_id']][$row['forum_id']]))
			{
				unset($hold_ary[$row['user_id']][$row['forum_id']]);
			}
		}
		$db->sql_freeresult($result);

		if (sizeof($hold_ary))
		{
			// Get usernames...
			$sql = 'SELECT user_id, username
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', array_keys($hold_ary));
			$result = $db->sql_query($sql);

			$usernames_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$usernames_ary[$row['user_id']] = $row['username'];
			}
			$db->sql_freeresult($result);

			foreach ($hold_ary as $user_id => $forum_id_ary)
			{
				// Do not continue if user does not exist
				if (!isset($usernames_ary[$user_id]))
				{
					continue;
				}

				foreach ($forum_id_ary as $forum_id => $auth_ary)
				{
					$sql_ary[] = array(
						'forum_id'		=> (int) $forum_id,
						'user_id'		=> (int) $user_id,
						'username'		=> (string) $usernames_ary[$user_id],
						'group_id'		=> 0,
						'group_name'	=> ''
					);
				}
			}
		}
	}

	// Now to the groups...
	$hold_ary = $auth->acl_group_raw_data(false, 'm_%', false);

	if (sizeof($hold_ary))
	{
		$ug_id_ary = array_keys($hold_ary);

		// Make sure not hidden or special groups are involved...
		$sql = 'SELECT group_name, group_id, group_type
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $db->sql_in_set('group_id', $ug_id_ary);
		$result = $db->sql_query($sql);

		$groupnames_ary = array();
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['group_type'] == GROUP_HIDDEN || $row['group_type'] == GROUP_SPECIAL)
			{
				unset($hold_ary[$row['group_id']]);
			}

			$groupnames_ary[$row['group_id']] = $row['group_name'];
		}
		$db->sql_freeresult($result);

		foreach ($hold_ary as $group_id => $forum_id_ary)
		{
			// If there is no group, we do not assign it...
			if (!isset($groupnames_ary[$group_id]))
			{
				continue;
			}

			foreach ($forum_id_ary as $forum_id => $auth_ary)
			{
				$flag = false;
				foreach ($auth_ary as $auth_option => $setting)
				{
					// Make sure at least one ACL_YES option is set...
					if ($setting == ACL_YES)
					{
						$flag = true;
						break;
					}
				}

				if (!$flag)
				{
					continue;
				}

				$sql_ary[] = array(
					'forum_id'		=> (int) $forum_id,
					'user_id'		=> 0,
					'username'		=> '',
					'group_id'		=> (int) $group_id,
					'group_name'	=> (string) $groupnames_ary[$group_id]
				);
			}
		}
	}

	$db->sql_multi_insert(MODERATOR_CACHE_TABLE, $sql_ary);
}

/**
* View log
*
* @param	string	$mode			The mode defines which log_type is used and from which log the entry is retrieved
* @param	array	&$log			The result array with the logs
* @param	mixed	&$log_count		If $log_count is set to false, we will skip counting all entries in the database.
*									Otherwise an integer with the number of total matching entries is returned.
* @param	int		$limit			Limit the number of entries that are returned
* @param	int		$offset			Offset when fetching the log entries, f.e. when paginating
* @param	mixed	$forum_id		Restrict the log entries to the given forum_id (can also be an array of forum_ids)
* @param	int		$topic_id		Restrict the log entries to the given topic_id
* @param	int		$user_id		Restrict the log entries to the given user_id
* @param	int		$log_time		Only get log entries newer than the given timestamp
* @param	string	$sort_by		SQL order option, e.g. 'l.log_time DESC'
* @param	string	$keywords		Will only return log entries that have the keywords in log_operation or log_data
*
* @return	int				Returns the offset of the last valid page, if the specified offset was invalid (too high)
*/
function view_log($mode, &$log, &$log_count, $limit = 0, $offset = 0, $forum_id = 0, $topic_id = 0, $user_id = 0, $limit_days = 0, $sort_by = 'l.log_time DESC', $keywords = '')
{
	global $phpbb_log;

	$count_logs = ($log_count !== false);

	$log = $phpbb_log->get_logs($mode, $count_logs, $limit, $offset, $forum_id, $topic_id, $user_id, $limit_days, $sort_by, $keywords);
	$log_count = $phpbb_log->get_log_count();

	return $phpbb_log->get_valid_offset();
}

/**
* Removes moderators and administrators from foe lists.
*
* @param \phpbb\db\driver\driver_interface $db Database connection
* @param \phpbb\auth\auth $auth Authentication object
* @param array|bool $group_id If an array, remove all members of this group from foe lists, or false to ignore
* @param array|bool $user_id If an array, remove this user from foe lists, or false to ignore
* @return null
*/
function phpbb_update_foes($db, $auth, $group_id = false, $user_id = false)
{
	// update foes for some user
	if (is_array($user_id) && sizeof($user_id))
	{
		$sql = 'DELETE FROM ' . ZEBRA_TABLE . '
			WHERE ' . $db->sql_in_set('zebra_id', $user_id) . '
				AND foe = 1';
		$db->sql_query($sql);
		return;
	}

	// update foes for some group
	if (is_array($group_id) && sizeof($group_id))
	{
		// Grab group settings...
		$sql_ary = array(
			'SELECT'	=> 'a.group_id',

			'FROM'		=> array(
				ACL_OPTIONS_TABLE	=> 'ao',
				ACL_GROUPS_TABLE	=> 'a',
			),

			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(ACL_ROLES_DATA_TABLE => 'r'),
					'ON'	=> 'a.auth_role_id = r.role_id',
				),
			),

			'WHERE'		=> '(ao.auth_option_id = a.auth_option_id OR ao.auth_option_id = r.auth_option_id)
				AND ' . $db->sql_in_set('a.group_id', $group_id) . "
				AND ao.auth_option IN ('a_', 'm_')",

			'GROUP_BY'	=> 'a.group_id',
		);
		$sql = $db->sql_build_query('SELECT', $sql_ary);
		$result = $db->sql_query($sql);

		$groups = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$groups[] = (int) $row['group_id'];
		}
		$db->sql_freeresult($result);

		if (!sizeof($groups))
		{
			return;
		}

		switch ($db->get_sql_layer())
		{
			case 'mysqli':
			case 'mysql4':
				$sql = 'DELETE ' . (($db->get_sql_layer() === 'mysqli' || version_compare($db->sql_server_info(true), '4.1', '>=')) ? 'z.*' : ZEBRA_TABLE) . '
					FROM ' . ZEBRA_TABLE . ' z, ' . USER_GROUP_TABLE . ' ug
					WHERE z.zebra_id = ug.user_id
						AND z.foe = 1
						AND ' . $db->sql_in_set('ug.group_id', $groups);
				$db->sql_query($sql);
			break;

			default:
				$sql = 'SELECT user_id
					FROM ' . USER_GROUP_TABLE . '
					WHERE ' . $db->sql_in_set('group_id', $groups);
				$result = $db->sql_query($sql);

				$users = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$users[] = (int) $row['user_id'];
				}
				$db->sql_freeresult($result);

				if (sizeof($users))
				{
					$sql = 'DELETE FROM ' . ZEBRA_TABLE . '
						WHERE ' . $db->sql_in_set('zebra_id', $users) . '
							AND foe = 1';
					$db->sql_query($sql);
				}
			break;
		}

		return;
	}

	// update foes for everyone
	$perms = array();
	foreach ($auth->acl_get_list(false, array('a_', 'm_'), false) as $forum_id => $forum_ary)
	{
		foreach ($forum_ary as $auth_option => $user_ary)
		{
			$perms = array_merge($perms, $user_ary);
		}
	}

	if (sizeof($perms))
	{
		$sql = 'DELETE FROM ' . ZEBRA_TABLE . '
			WHERE ' . $db->sql_in_set('zebra_id', array_unique($perms)) . '
				AND foe = 1';
		$db->sql_query($sql);
	}
	unset($perms);
}

/**
* Lists inactive users
*/
function view_inactive_users(&$users, &$user_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'user_inactive_time DESC')
{
	global $db, $user;

	$sql = 'SELECT COUNT(user_id) AS user_count
		FROM ' . USERS_TABLE . '
		WHERE user_type = ' . USER_INACTIVE .
		(($limit_days) ? " AND user_inactive_time >= $limit_days" : '');
	$result = $db->sql_query($sql);
	$user_count = (int) $db->sql_fetchfield('user_count');
	$db->sql_freeresult($result);

	if ($user_count == 0)
	{
		// Save the queries, because there are no users to display
		return 0;
	}

	if ($offset >= $user_count)
	{
		$offset = ($offset - $limit < 0) ? 0 : $offset - $limit;
	}

	$sql = 'SELECT *
		FROM ' . USERS_TABLE . '
		WHERE user_type = ' . USER_INACTIVE .
		(($limit_days) ? " AND user_inactive_time >= $limit_days" : '') . "
		ORDER BY $sort_by";
	$result = $db->sql_query_limit($sql, $limit, $offset);

	while ($row = $db->sql_fetchrow($result))
	{
		$row['inactive_reason'] = $user->lang['INACTIVE_REASON_UNKNOWN'];
		switch ($row['user_inactive_reason'])
		{
			case INACTIVE_REGISTER:
				$row['inactive_reason'] = $user->lang['INACTIVE_REASON_REGISTER'];
			break;

			case INACTIVE_PROFILE:
				$row['inactive_reason'] = $user->lang['INACTIVE_REASON_PROFILE'];
			break;

			case INACTIVE_MANUAL:
				$row['inactive_reason'] = $user->lang['INACTIVE_REASON_MANUAL'];
			break;

			case INACTIVE_REMIND:
				$row['inactive_reason'] = $user->lang['INACTIVE_REASON_REMIND'];
			break;
		}

		$users[] = $row;
	}
	$db->sql_freeresult($result);

	return $offset;
}

/**
* Lists warned users
*/
function view_warned_users(&$users, &$user_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'user_warnings DESC')
{
	global $db;

	$sql = 'SELECT user_id, username, user_colour, user_warnings, user_last_warning
		FROM ' . USERS_TABLE . '
		WHERE user_warnings > 0
		' . (($limit_days) ? "AND user_last_warning >= $limit_days" : '') . "
		ORDER BY $sort_by";
	$result = $db->sql_query_limit($sql, $limit, $offset);
	$users = $db->sql_fetchrowset($result);
	$db->sql_freeresult($result);

	$sql = 'SELECT count(user_id) AS user_count
		FROM ' . USERS_TABLE . '
		WHERE user_warnings > 0
		' . (($limit_days) ? "AND user_last_warning >= $limit_days" : '');
	$result = $db->sql_query($sql);
	$user_count = (int) $db->sql_fetchfield('user_count');
	$db->sql_freeresult($result);

	return;
}

/**
* Get database size
* Currently only mysql and mssql are supported
*/
function get_database_size()
{
	global $db, $user, $table_prefix;

	$database_size = false;

	// This code is heavily influenced by a similar routine in phpMyAdmin 2.2.0
	switch ($db->get_sql_layer())
	{
		case 'mysql':
		case 'mysql4':
		case 'mysqli':
			$sql = 'SELECT VERSION() AS mysql_version';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row)
			{
				$version = $row['mysql_version'];

				if (preg_match('#(3\.23|[45]\.)#', $version))
				{
					$db_name = (preg_match('#^(?:3\.23\.(?:[6-9]|[1-9]{2}))|[45]\.#', $version)) ? "`{$db->get_db_name()}`" : $db->get_db_name();

					$sql = 'SHOW TABLE STATUS
						FROM ' . $db_name;
					$result = $db->sql_query($sql, 7200);

					$database_size = 0;
					while ($row = $db->sql_fetchrow($result))
					{
						if ((isset($row['Type']) && $row['Type'] != 'MRG_MyISAM') || (isset($row['Engine']) && ($row['Engine'] == 'MyISAM' || $row['Engine'] == 'InnoDB')))
						{
							if ($table_prefix != '')
							{
								if (strpos($row['Name'], $table_prefix) !== false)
								{
									$database_size += $row['Data_length'] + $row['Index_length'];
								}
							}
							else
							{
								$database_size += $row['Data_length'] + $row['Index_length'];
							}
						}
					}
					$db->sql_freeresult($result);
				}
			}
		break;

		case 'sqlite':
		case 'sqlite3':
			global $dbhost;

			if (file_exists($dbhost))
			{
				$database_size = filesize($dbhost);
			}

		break;

		case 'mssql':
		case 'mssql_odbc':
		case 'mssqlnative':
			$sql = 'SELECT @@VERSION AS mssql_version';
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$sql = 'SELECT ((SUM(size) * 8.0) * 1024.0) as dbsize
				FROM sysfiles';

			if ($row)
			{
				// Azure stats are stored elsewhere
				if (strpos($row['mssql_version'], 'SQL Azure') !== false)
				{
					$sql = 'SELECT ((SUM(reserved_page_count) * 8.0) * 1024.0) as dbsize
					FROM sys.dm_db_partition_stats';
				}
			}

			$result = $db->sql_query($sql, 7200);
			$database_size = ($row = $db->sql_fetchrow($result)) ? $row['dbsize'] : false;
			$db->sql_freeresult($result);
		break;

		case 'postgres':
			$sql = "SELECT proname
				FROM pg_proc
				WHERE proname = 'pg_database_size'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if ($row['proname'] == 'pg_database_size')
			{
				$database = $db->get_db_name();
				if (strpos($database, '.') !== false)
				{
					list($database, ) = explode('.', $database);
				}

				$sql = "SELECT oid
					FROM pg_database
					WHERE datname = '$database'";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$oid = $row['oid'];

				$sql = 'SELECT pg_database_size(' . $oid . ') as size';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$database_size = $row['size'];
			}
		break;

		case 'oracle':
			$sql = 'SELECT SUM(bytes) as dbsize
				FROM user_segments';
			$result = $db->sql_query($sql, 7200);
			$database_size = ($row = $db->sql_fetchrow($result)) ? $row['dbsize'] : false;
			$db->sql_freeresult($result);
		break;
	}

	$database_size = ($database_size !== false) ? get_formatted_filesize($database_size) : $user->lang['NOT_AVAILABLE'];

	return $database_size;
}

/**
* Retrieve contents from remotely stored file
*
* @deprecated	3.1.2	Use file_downloader instead
*/
function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 6)
{
	global $phpbb_container;

	// Get file downloader and assign $errstr and $errno
	$file_downloader = $phpbb_container->get('file_downloader');

	$file_data = $file_downloader->get($host, $directory, $filename, $port, $timeout);
	$errstr = $file_downloader->get_error_string();
	$errno = $file_downloader->get_error_number();

	return $file_data;
}

/*
* Tidy Warnings
* Remove all warnings which have now expired from the database
* The duration of a warning can be defined by the administrator
* This only removes the warning and reduces the associated count,
* it does not remove the user note recording the contents of the warning
*/
function tidy_warnings()
{
	global $db, $config;

	$expire_date = time() - ($config['warnings_expire_days'] * 86400);
	$warning_list = $user_list = array();

	$sql = 'SELECT * FROM ' . WARNINGS_TABLE . "
		WHERE warning_time < $expire_date";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$warning_list[] = $row['warning_id'];
		$user_list[$row['user_id']] = isset($user_list[$row['user_id']]) ? ++$user_list[$row['user_id']] : 1;
	}
	$db->sql_freeresult($result);

	if (sizeof($warning_list))
	{
		$db->sql_transaction('begin');

		$sql = 'DELETE FROM ' . WARNINGS_TABLE . '
			WHERE ' . $db->sql_in_set('warning_id', $warning_list);
		$db->sql_query($sql);

		foreach ($user_list as $user_id => $value)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET user_warnings = user_warnings - $value
				WHERE user_id = $user_id";
			$db->sql_query($sql);
		}

		$db->sql_transaction('commit');
	}

	set_config('warnings_last_gc', time(), true);
}

/**
* Tidy database, doing some maintanance tasks
*/
function tidy_database()
{
	global $db;

	// Here we check permission consistency

	// Sometimes, it can happen permission tables having forums listed which do not exist
	$sql = 'SELECT forum_id
		FROM ' . FORUMS_TABLE;
	$result = $db->sql_query($sql);

	$forum_ids = array(0);
	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = $row['forum_id'];
	}
	$db->sql_freeresult($result);

	// Delete those rows from the acl tables not having listed the forums above
	$sql = 'DELETE FROM ' . ACL_GROUPS_TABLE . '
		WHERE ' . $db->sql_in_set('forum_id', $forum_ids, true);
	$db->sql_query($sql);

	$sql = 'DELETE FROM ' . ACL_USERS_TABLE . '
		WHERE ' . $db->sql_in_set('forum_id', $forum_ids, true);
	$db->sql_query($sql);

	set_config('database_last_gc', time(), true);
}

/**
* Add permission language - this will make sure custom files will be included
*/
function add_permission_language()
{
	global $user, $phpEx, $phpbb_extension_manager;

	// add permission language files from extensions
	$finder = $phpbb_extension_manager->get_finder();

	$lang_files = $finder
		->prefix('permissions_')
		->suffix(".$phpEx")
		->core_path('language/' . $user->lang_name . '/')
		->extension_directory('/language/' . $user->lang_name)
		->find();

	foreach ($lang_files as $lang_file => $ext_name)
	{
		if ($ext_name === '/')
		{
			$user->add_lang($lang_file);
		}
		else
		{
			$user->add_lang_ext($ext_name, $lang_file);
		}
	}
}

/**
 * Enables a particular flag in a bitfield column of a given table.
 *
 * @param string	$table_name		The table to update
 * @param string	$column_name	The column containing a bitfield to update
 * @param int		$flag			The binary flag which is OR-ed with the current column value
 * @param string	$sql_more		This string is attached to the sql query generated to update the table.
 *
 * @return null
 */
function enable_bitfield_column_flag($table_name, $column_name, $flag, $sql_more = '')
{
	global $db;

	$sql = 'UPDATE ' . $table_name . '
		SET ' . $column_name . ' = ' . $db->sql_bit_or($column_name, $flag) . '
		' . $sql_more;
	$db->sql_query($sql);
}
