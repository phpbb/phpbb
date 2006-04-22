<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* Recalculate Binary Tree
*/
function recalc_btree($sql_id, $sql_table, $module_class = '')
{
	global $db;

	/* Init table, id's, etc...
	$sql_id = 'module_id'; // 'forum_id'
	$sql_table = MODULES_TABLE; // FORUMS_TABLE
	*/

	if (!$sql_id || !$sql_table)
	{
		return;
	}

	$sql_where = ($module_class) ? " WHERE module_class = '" . $db->sql_escape($module_class) . "'" : '';

	// Reset to minimum possible left and right id
	$sql = "SELECT MIN(left_id) as min_left_id, MIN(right_id) as min_right_id
		FROM $sql_table
		$sql_where";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$substract = (int) (min($row['min_left_id'], $row['min_right_id']) - 1);

	if ($substract > 0)
	{
		$sql = "UPDATE $sql_table 
			SET left_id = left_id - $substract, right_id = right_id - $substract
			$sql_where";
		$db->sql_query($sql);
	}

	$sql = "SELECT $sql_id, parent_id, left_id, right_id 
		FROM $sql_table
		$sql_where
		ORDER BY left_id ASC, parent_id ASC, $sql_id ASC";
	$f_result = $db->sql_query($sql);

	while ($item_data = $db->sql_fetchrow($f_result))
	{
		if ($item_data['parent_id'])
		{
			$sql = "SELECT left_id, right_id
				FROM $sql_table
				$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
					$sql_id = {$item_data['parent_id']}";
			$result = $db->sql_query($sql);

			if (!$row = $db->sql_fetchrow($result))
			{
				$sql = "UPDATE $sql_table SET parent_id = 0 WHERE $sql_id = " . $item_data[$sql_id];
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);

			$sql = "UPDATE $sql_table
				SET left_id = left_id + 2, right_id = right_id + 2
				$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
					left_id > {$row['right_id']}";
			$db->sql_query($sql);

			$sql = "UPDATE $sql_table
				SET right_id = right_id + 2
				$sql_where " . (($sql_where) ? 'AND' : 'WHERE') . "
					{$row['left_id']} BETWEEN left_id AND right_id";
			$db->sql_query($sql);

			$item_data['left_id'] = $row['right_id'];
			$item_data['right_id'] = $row['right_id'] + 1;
		}
		else
		{
			$sql = "SELECT MAX(right_id) AS right_id
				FROM $sql_table
				$sql_where";
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$item_data['left_id'] = $row['right_id'] + 1;
			$item_data['right_id'] = $row['right_id'] + 2;
		}
	
		$sql = "UPDATE $sql_table
			SET left_id = {$item_data['left_id']}, right_id = {$item_data['right_id']}
			WHERE $sql_id = " . $item_data[$sql_id];
		$db->sql_query($sql);
	}
	$db->sql_freeresult($f_result);
}

/**
* Simple version of jumpbox, just lists authed forums
*/
function make_forum_select($select_id = false, $ignore_id = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $return_array = false)
{
	global $db, $user, $auth;

	$acl = ($ignore_acl) ? '' : array('f_list', 'a_forum', 'a_forumadd', 'a_forumdel');

	// This query is identical to the jumpbox one
	$sql = 'SELECT forum_id, parent_id, forum_name, forum_type, forum_status, left_id, right_id
		FROM ' . FORUMS_TABLE . '
		ORDER BY left_id ASC';
	$result = $db->sql_query($sql);

	$right = $iteration = 0;
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

		if ($acl && !$auth->acl_gets($acl, $row['forum_id']))
		{
			continue;
		}

		if ((is_array($ignore_id) && in_array($row['forum_id'], $ignore_id)) || $row['forum_id'] == $ignore_id)
		{
			continue;
		}

		if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']) && $ignore_emptycat)
		{
			// Non-postable forum with no subforums, don't display
			continue;
		}

		if ($row['forum_type'] != FORUM_POST && $ignore_nonpost)
		{
			continue;
		}

		if ($return_array)
		{
			// Include some more informations...
			$selected = (is_array($select_id)) ? ((in_array($row['forum_id'], $select_id)) ? true : false) : (($row['forum_id'] == $select_id) ? true : false);
			$forum_list[$row['forum_id']] = array_merge(array('padding' => $padding, 'selected' => $selected), $row);
		}
		else
		{
			$selected = (is_array($select_id)) ? ((in_array($row['forum_id'], $select_id)) ? ' selected="selected"' : '') : (($row['forum_id'] == $select_id) ? ' selected="selected"' : '');
			$forum_list .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option>';
		}

		$iteration++;
	}
	unset($padding_store);

	return $forum_list;
}

/**
* Generate size select form
*/
function size_select_options($size_compare)
{
	global $user;

	$size_types_text = array($user->lang['BYTES'], $user->lang['KB'], $user->lang['MB']);
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
* Generate list of groups
*/
function group_select_options($group_id, $exclude_ids = false)
{
	global $db, $user, $config;

	$exclude_sql = ($exclude_ids !== false && sizeof($exclude_ids)) ? 'WHERE group_id NOT IN (' . implode(', ', array_map('intval', $exclude_ids)) . ')' : '';
	$sql_and = ($config['coppa_hide_groups']) ? (($exclude_sql) ? ' AND ' : ' WHERE ') . "group_name NOT IN ('INACTIVE_COPPA', 'REGISTERED_COPPA')" : '';

	$sql = 'SELECT group_id, group_name, group_type 
		FROM ' . GROUPS_TABLE . "
		$exclude_sql
		$sql_and
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
		$expire_time = ($no_cache) ? 0 : 120;
		$sql = 'SELECT forum_id, parent_id, forum_name, forum_type, left_id, right_id
			FROM ' . FORUMS_TABLE . '
			ORDER BY left_id ASC';
		$result = $db->sql_query($sql, $expire_time);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_rows[] = $row;
		}
		$db->sql_freeresult($result);
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
			$rowset[] = ($id_only) ? $row['forum_id'] : $row;
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
* Get physical file listing
*/
function filelist($rootdir, $dir = '', $type = 'gif|jpg|jpeg|png')
{
	$matches = array();

	// Remove initial / if present
	$rootdir = (substr($rootdir, 0, 1) == '/') ? substr($rootdir, 1) : $rootdir;
	// Add closing / if present
	$rootdir = ($rootdir && substr($rootdir, -1) != '/') ? $rootdir . '/' : $rootdir;

	// Remove initial / if present
	$dir = (substr($dir, 0, 1) == '/') ? substr($dir, 1) : $dir;
	// Add closing / if present
	$dir = ($dir && substr($dir, -1) != '/') ? $dir . '/' : $dir;

	if (!is_dir($rootdir . $dir))
	{
		return false;
	}

	$dh = opendir($rootdir . $dir);
	while (($fname = readdir($dh)) !== false)
	{
		if (is_file("$rootdir$dir$fname"))
		{
			if (filesize("$rootdir$dir$fname") && preg_match('#\.' . $type . '$#i', $fname))
			{
				$matches[$dir][] = $fname;
			}
		}
		else if ($fname{0} != '.' && is_dir("$rootdir$dir$fname"))
		{
			$matches += filelist($rootdir, "$dir$fname", $type);
		}
	}
	closedir($dh);

	return $matches;
}

/*
* Move topic(s)
*/
function move_topics($topic_ids, $forum_id, $auto_sync = true)
{
	global $db;

	$forum_ids = array($forum_id);
	$sql_where = (is_array($topic_ids)) ? 'IN (' . implode(', ', $topic_ids) . ')' : '= ' . $topic_ids;

	$sql = 'DELETE FROM ' . TOPICS_TABLE . "
		WHERE topic_moved_id $sql_where
			AND forum_id = " . $forum_id;
	$db->sql_query($sql);

	if ($auto_sync)
	{
		$sql = 'SELECT DISTINCT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id ' . $sql_where;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
		}
		$db->sql_freeresult($result);
	}
	
	$table_ary = array(TOPICS_TABLE, POSTS_TABLE, LOG_TABLE);
	foreach ($table_ary as $table)
	{
		$sql = "UPDATE $table
			SET forum_id = $forum_id
			WHERE topic_id " . $sql_where;
		$db->sql_query($sql);
	}
	unset($table_ary);

	if ($auto_sync)
	{
		sync('forum', 'forum_id', $forum_ids, true);
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

	if ($auto_sync)
	{
		$forum_ids = array();
		$topic_ids = array($topic_id);

		$sql = 'SELECT DISTINCT topic_id, forum_id
			FROM ' . POSTS_TABLE . '
			WHERE post_id IN (' . implode(', ', $post_ids) . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
			$topic_ids[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);
	}

	$sql = 'SELECT forum_id 
		FROM ' . TOPICS_TABLE . ' 
		WHERE topic_id = ' . $topic_id;
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error('NO_TOPIC');
	}
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . POSTS_TABLE . '
		SET forum_id = ' . $row['forum_id'] . ", topic_id = $topic_id
		WHERE post_id IN (" . implode(', ', $post_ids) . ')';
	$db->sql_query($sql);

	$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
		SET topic_id = $topic_id
			AND in_message = 0
		WHERE post_msg_id IN (" . implode(', ', $post_ids) . ')';
	$db->sql_query($sql);

	if ($auto_sync)
	{
		$forum_ids[] = $row['forum_id'];

		sync('topic_reported', 'topic_id', $topic_ids);
		sync('topic', 'topic_id', $topic_ids, true);
		sync('forum', 'forum_id', $forum_ids, true);
	}
}

/**
* Remove topic(s)
*/
function delete_topics($where_type, $where_ids, $auto_sync = true)
{
	global $db;
	$forum_ids = $topic_ids = array();

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}

	if (!sizeof($where_ids))
	{
		return array('topics' => 0, 'posts' => 0);
	}

	$return = array(
		'posts'	=>	delete_posts($where_type, $where_ids, false)
	);

	$sql = 'SELECT topic_id, forum_id
		FROM ' . TOPICS_TABLE . "
		WHERE $where_type " . ((!is_array($where_ids)) ? "= $where_ids" : 'IN (' . implode(', ', $where_ids) . ')');
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$forum_ids[] = $row['forum_id'];
		$topic_ids[] = $row['topic_id'];
	}
	$db->sql_freeresult();

	$return['topics'] = sizeof($topic_ids);

	if (!sizeof($topic_ids))
	{
		return $return;
	}

	// TODO: probably some other stuff too

	$sql_where = ' IN (' . implode(', ', $topic_ids) . ')';

	$db->sql_transaction('begin');

	$table_ary = array(TOPICS_TRACK_TABLE, TOPICS_POSTED_TABLE, POLL_VOTES_TABLE, POLL_OPTIONS_TABLE, TOPICS_WATCH_TABLE, TOPICS_TABLE);
	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table 
			WHERE topic_id $sql_where";
		$db->sql_query($sql);
	}
	unset($table_ary);

	$sql = 'DELETE FROM ' . TOPICS_TABLE . ' 
		WHERE topic_moved_id' . $sql_where;
	$db->sql_query($sql);

	$db->sql_transaction('commit');

	if ($auto_sync)
	{
		sync('forum', 'forum_id', $forum_ids, true);
		sync('topic_reported', $where_type, $where_ids);
	}

	return $return;
}

/**
* Remove post(s)
*/
function delete_posts($where_type, $where_ids, $auto_sync = true)
{
	global $db, $config, $phpbb_root_path, $phpEx;

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}
	if (empty($where_ids))
	{
		return false;
	}
	$post_ids = $topic_ids = $forum_ids = array();

	$sql = 'SELECT post_id, poster_id, topic_id, forum_id
		FROM ' . POSTS_TABLE . "
		WHERE $where_type " . ((!is_array($where_ids)) ? "= $where_ids" : 'IN (' . implode(', ', $where_ids) . ')');
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$post_ids[] = $row['post_id'];
		$poster_ids[] = $row['poster_id'];
		$topic_ids[] = $row['topic_id'];
		$forum_ids[] = $row['forum_id'];
	}

	if (!sizeof($post_ids))
	{
		return false;
	}

	$sql_where = implode(', ', $post_ids);

	$db->sql_transaction('begin');

	$table_ary = array(POSTS_TABLE, REPORTS_TABLE);

	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table 
			WHERE post_id IN ($sql_where)";
		$db->sql_query($sql);
	}
	unset($table_ary);

	// Remove the message from the search index
	$search_type = $config['search_type'];

	if (!file_exists($phpbb_root_path . 'includes/search/' . $search_type . '.' . $phpEx))
	{
		trigger_error('NO_SUCH_SEARCH_MODULE');
	}

	require("{$phpbb_root_path}includes/search/$search_type.$phpEx");

	$error = false;
	$search = new $search_type($error);

	if ($error)
	{
		trigger_error($error);
	}

	$search->index_remove($post_ids, $poster_ids);

	delete_attachments('post', $post_ids, false);

	$db->sql_transaction('commit');

	if ($auto_sync)
	{
		sync('topic_reported', 'topic_id', $topic_ids);
		sync('topic', 'topic_id', $topic_ids, true);
		sync('forum', 'forum_id', $forum_ids, true);
	}

	return sizeof($post_ids);
}

/**
* Delete Attachments
* mode => (post, topic, attach, user)
* ids => (post_ids, topic_ids, attach_ids, user_ids)
* resync => set this to false if you are deleting posts or topics...
*/
function delete_attachments($mode, $ids, $resync = true)
{
	global $db, $config;

	if (is_array($ids))
	{
		$ids = array_unique($ids);
	}
	
	if (!sizeof($ids))
	{
		return false;
	}

	$sql_id = ($mode == 'user') ? 'poster_id' : (($mode == 'post') ? 'post_msg_id' : (($mode == 'topic') ? 'topic_id' : 'attach_id'));

	$post_ids = $topic_ids = $physical = array();

	// Collect post and topics ids for later use
	if ($mode == 'attach' || $mode == 'user' || ($mode == 'topic' && $resync))
	{
		$sql = 'SELECT post_msg_id as post_id, topic_id, physical_filename, thumbnail, filesize
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $sql_id . ' IN (' . implode(', ', $ids) . ')';
		$result = $db->sql_query($sql);
			
		while ($row = $db->sql_fetchrow($result))
		{
			$post_ids[] = $row['post_id'];
			$topic_ids[] = $row['topic_id'];
			$physical[] = array('filename' => $row['physical_filename'], 'thumbnail' => $row['thumbnail'], 'filesize' => $row['filesize']);
		}
		$db->sql_freeresult($result);
	}

	if ($mode == 'post')
	{
		$sql = 'SELECT topic_id, physical_filename, thumbnail, filesize
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE post_msg_id IN (' . implode(', ', $ids) . ')
				AND in_message = 0';
		$result = $db->sql_query($sql);
			
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
			$physical[] = array('filename' => $row['physical_filename'], 'thumbnail' => $row['thumbnail'], 'filesize' => $row['filesize']);
		}
		$db->sql_freeresult($result);
	}

	// Delete attachments
	$db->sql_query('DELETE FROM ' . ATTACHMENTS_TABLE . ' WHERE ' . $sql_id . ' IN (' . implode(', ', $ids) . ')');
	$num_deleted = $db->sql_affectedrows();

	if (!$num_deleted)
	{
		return 0;
	}
	
	// Delete attachments from filesystem
	$space_removed = $files_removed = 0;
	foreach ($physical as $file_ary)
	{
		if (phpbb_unlink($file_ary['filename'], 'file'))
		{
			$space_removed += $file_ary['filesize'];
			$files_removed++;
		}

		if ($file_ary['thumbnail'])
		{
			phpbb_unlink($file_ary['filename'], 'thumbnail');
		}
	}
	set_config('upload_dir_size', $config['upload_dir_size'] - $space_removed, true);
	set_config('num_files', $config['num_files'] - $files_removed, true);

	if ($mode == 'topic' && !$resync)
	{
		return $num_deleted;
	}

	if ($mode == 'post')
	{
		$post_ids = $ids;
	}
	unset($ids);

	$post_ids = array_unique($post_ids);
	$topic_ids = array_unique($topic_ids);

	// Update post indicators
	if (sizeof($post_ids))
	{
		if ($mode == 'post' || $mode == 'topic')
		{
			$db->sql_query('UPDATE ' . POSTS_TABLE . ' 
				SET post_attachment = 0
				WHERE post_id IN (' . implode(', ', $post_ids) . ')');
		}

		if ($mode == 'user' || $mode == 'attach')
		{
			$remaining = array();

			$sql = 'SELECT post_msg_id
					FROM ' . ATTACHMENTS_TABLE . ' 
					WHERE post_msg_id IN (' . implode(', ', $post_ids) . ')
						AND in_message = 0';
			$result = $db->sql_query($sql);
					
			while ($row = $db->sql_fetchrow($result))
			{
				$remaining[] = $row['post_msg_id'];		
			}
			$db->sql_freeresult($result);

			$unset_ids = array_diff($post_ids, $remaining);
			if (sizeof($unset_ids))
			{
				$db->sql_query('UPDATE ' . POSTS_TABLE . ' 
					SET post_attachment = 0
					WHERE post_id IN (' . implode(', ', $unset_ids) . ')');
			}

			$remaining = array();

			$sql = 'SELECT post_msg_id
					FROM ' . ATTACHMENTS_TABLE . ' 
					WHERE post_msg_id IN (' . implode(', ', $post_ids) . ')
						AND in_message = 1';
			$result = $db->sql_query($sql);
					
			while ($row = $db->sql_fetchrow($result))
			{
				$remaining[] = $row['post_msg_id'];		
			}
			$db->sql_freeresult($result);

			$unset_ids = array_diff($post_ids, $remaining);
			if (sizeof($unset_ids))
			{
				$db->sql_query('UPDATE ' . PRIVMSGS_TABLE . ' 
					SET message_attachment = 0
					WHERE msg_id IN (' . implode(', ', $unset_ids) . ')');
			}
		}
	}

	if (sizeof($topic_ids))
	{
		// Update topic indicator
		if ($mode == 'topic')
		{
			$db->sql_query('UPDATE ' . TOPICS_TABLE . '
				SET topic_attachment = 0
				WHERE topic_id IN (' . implode(', ', $topic_ids) . ')');
		}

		if ($mode == 'post' || $mode == 'user' || $mode == 'attach')
		{
			$remaining = array();

			$sql = 'SELECT topic_id
					FROM ' . ATTACHMENTS_TABLE . ' 
					WHERE topic_id IN (' . implode(', ', $topic_ids) . ')';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$remaining[] = $row['topic_id'];		
			}
			$db->sql_freeresult($result);

			$unset_ids = array_diff($topic_ids, $remaining);
			if (sizeof($unset_ids))
			{
				$db->sql_query('UPDATE ' . TOPICS_TABLE . ' 
					SET topic_attachment = 0
					WHERE topic_id IN (' . implode(', ', $unset_ids) . ')');
			}
		}
	}

	return $num_deleted;
}

/**
* Remove topic shadows
*/
function delete_topic_shadows($max_age, $forum_id = '', $auto_sync = true)
{
	$where = (is_array($forum_id)) ? 'AND t.forum_id IN (' . implode(', ', $forum_id) . ')' : (($forum_id) ? "AND t.forum_id = $forum_id" : '');

	switch (SQL_LAYER)
	{
		case 'mysql4':
		case 'mysqli':
			$sql = 'DELETE t.*
				FROM ' . TOPICS_TABLE . ' t, ' . TOPICS_TABLE . ' t2
				WHERE t.topic_moved_id = t2.topic_id
					AND t.topic_time < ' . (time() - $max_age)
				. $where;
			$db->sql_query($sql);
		break;
	
		default:
			$sql = 'SELECT t.topic_id
				FROM ' . TOPICS_TABLE . ' t, ' . TOPICS_TABLE . ' t2
				WHERE t.topic_moved_id = t2.topic_id
					AND t.topic_time < ' . (time() - $max_age)
				. $where;
			$result = $db->sql_query($sql);
			
			$topic_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_ids[] = $row['topic_id'];
			}

			if (sizeof($topic_ids))
			{
				$sql = 'DELETE FROM ' . TOPICS_TABLE . '
					WHERE topic_id IN (' . implode(',', $topic_ids) . ')';
				$db->sql_query($sql);
			}
	}

	if ($auto_sync)
	{
		$where_type = ($forum_id) ? 'forum_id' : '';
		sync('forum', $where_type, $forum_id, true);
	}
}

/**
* Delete File
*/
function phpbb_unlink($filename, $mode = 'file')
{
	global $config, $user, $phpbb_root_path;

	$filename = ($mode == 'thumbnail') ? $phpbb_root_path . $config['upload_path'] . '/thumb_' . basename($filename) : $phpbb_root_path . $config['upload_path'] . '/' . basename($filename);
	return @unlink($filename);
}

/**
* All-encompasing sync function
*
* Usage:
* sync('topic', 'topic_id', 123);			<= resync topic #123
* sync('topic', 'forum_id', array(2, 3));	<= resync topics from forum #2 and #3
* sync('topic');							<= resync all topics
* sync('topic', 'range', 'topic_id BETWEEN 1 AND 60');	<= resync a range of topics/forums (only available for 'topic' and 'forum' modes)
*
* Modes:
* - topic_moved		Removes topic shadows that would be in the same forum as the topic they link to
* - topic_approved		Resyncs the topic_approved flag according to the status of the first post
* - post_reported		Resyncs the post_reported flag, relying on actual reports
* - topic_reported		Resyncs the topic_reported flag, relying on post_reported flags
* - post_attachement	Same as post_reported, thanks to a quick Search/Replace
* - topic_attachement	Same as topic_reported, thanks to a quick Search/Replace
*/
function sync($mode, $where_type = '', $where_ids = '', $resync_parents = false, $sync_extra = false)
{
	global $db;

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}
	else if ($where_type != 'range')
	{
		$where_ids = ($where_ids) ? array($where_ids) : array();
	}

	if ($mode == 'forum' || $mode == 'topic')
	{
		if (!$where_type)
		{
			$where_sql = '';
			$where_sql_and = 'WHERE';
		}
		else if ($where_type == 'range')
		{
			// Only check a range of topics/forums. For instance: 'topic_id BETWEEN 1 AND 60'
			$where_sql = 'WHERE (' . $mode{0} . ".$where_ids)";
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
			$where_sql = 'WHERE ' . $mode{0} . ".$where_type IN (" . implode(', ', $where_ids) . ')';
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
		$where_sql = 'WHERE ' . $mode{0} . ".$where_type IN (" . implode(', ', $where_ids) . ')';
		$where_sql_and = $where_sql . "\n\tAND";
	}

	switch ($mode)
	{
		case 'topic_moved':
			switch (SQL_LAYER)
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

					if ($row = $db->sql_fetchrow($result))
					{
						$topic_id_ary = array();
						do
						{
							$topic_id_ary[] = $row['topic_id'];
						}
						while ($row = $db->sql_fetchrow($result));

						$sql = 'DELETE FROM ' . TOPICS_TABLE . '
							WHERE topic_id IN (' . implode(', ', $topic_id_ary) . ')';
						$db->sql_query($sql);
						unset($topic_id_ary);
					}
					$db->sql_freeresult($result);
			}
			break;

		case 'topic_approved':
			switch (SQL_LAYER)
			{
				case 'mysql4':
				case 'mysqli':
					$sql = 'UPDATE ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
						SET t.topic_approved = p.post_approved
						$where_sql_and t.topic_first_post_id = p.post_id";
					$db->sql_query($sql);
				break;
			
				default:
					$sql = 'SELECT t.topic_id, p.post_approved
						FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
						$where_sql_and p.post_id = t.topic_first_post_id
							AND p.post_approved <> t.topic_approved";
					$result = $db->sql_query($sql);

					$topic_ids = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$topic_ids[] = $row['topic_id'];
					}
					$db->sql_freeresult();

					if (!sizeof($topic_ids))
					{
						return;
					}

					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_approved = 1 - topic_approved
						WHERE topic_id IN (' . implode(', ', $topic_ids) . ')';
					$db->sql_query($sql);
			}
			break;

		case 'post_reported':
			$post_ids = $post_reported = array();

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

			$sql = 'SELECT DISTINCT(post_id)
				FROM ' . REPORTS_TABLE . '
				WHERE post_id IN (' . implode(', ', $post_ids) . ')';
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
					WHERE post_id IN (' . implode(', ', $post_ids) . ')';
				$db->sql_query($sql);
			}
			break;

		case 'topic_reported':
			if ($sync_extra)
			{
				sync('post_reported', $where_type, $where_ids);
			}

			$topic_ids = $topic_reported = array();

			$sql = 'SELECT DISTINCT(t.topic_id)
				FROM ' . POSTS_TABLE . " t
				$where_sql_and t.post_reported = 1";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_reported[$row['topic_id']] = 1;
			}

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

			if (sizeof($topic_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_reported = 1 - topic_reported
					WHERE topic_id IN (' . implode(', ', $topic_ids) . ')';
				$db->sql_query($sql);
			}
			break;

		case 'post_attachment':
			$post_ids = $post_attachment = array();

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

			$sql = 'SELECT DISTINCT(post_msg_id)
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE post_msg_id IN (' . implode(', ', $post_ids) . ')
					AND in_message = 0';

			$post_ids = array();
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($post_attachment[$row['post_id']]))
				{
					$post_ids[] = $row['post_id'];
				}
				else
				{
					unset($post_attachment[$row['post_id']]);
				}
			}

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
					WHERE post_id IN (' . implode(', ', $post_ids) . ')';
				$db->sql_query($sql);
			}
			break;

		case 'topic_attachment':
			if ($sync_extra)
			{
				sync('post_attachment', $where_type, $where_ids);
			}

			$topic_ids = $topic_attachment = array();

			$sql = 'SELECT DISTINCT(t.topic_id)
				FROM ' . POSTS_TABLE . " t
				$where_sql_and t.post_attachment = 1";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_attachment[$row['topic_id']] = 1;
			}

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

			if (sizeof($topic_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_attachment = 1 - topic_attachment
					WHERE topic_id IN (' . implode(', ', $topic_ids) . ')';
				$db->sql_query($sql);
			}
			break;

		case 'forum':
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
				$forum_data[$forum_id]['posts'] = 0;
				$forum_data[$forum_id]['topics'] = 0;
				$forum_data[$forum_id]['topics_real'] = 0;
				$forum_data[$forum_id]['last_post_id'] = 0;
				$forum_data[$forum_id]['last_post_time'] = 0;
				$forum_data[$forum_id]['last_poster_id'] = 0;
				$forum_data[$forum_id]['last_poster_name'] = '';
			}

			// 2: Get topic counts for each forum
			$sql = 'SELECT forum_id, topic_approved, COUNT(topic_id) AS forum_topics
				FROM ' . TOPICS_TABLE . '
				WHERE forum_id IN (' . implode(', ', $forum_ids) . ')
				GROUP BY forum_id, topic_approved';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_id = (int) $row['forum_id'];
				$forum_data[$forum_id]['topics_real'] += $row['forum_topics'];

				if ($row['topic_approved'])
				{
					$forum_data[$forum_id]['topics'] = $row['forum_topics'];
				}
			}

			// 3: Get post count and last_post_id for each forum
			$sql = 'SELECT forum_id, COUNT(post_id) AS forum_posts, MAX(post_id) AS last_post_id
				FROM ' . POSTS_TABLE . '
				WHERE forum_id IN (' . implode(', ', $forum_ids) . ')
					AND post_approved = 1
				GROUP BY forum_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$forum_id = (int) $row['forum_id'];

				$forum_data[$forum_id]['posts'] = intval($row['forum_posts']);
				$forum_data[$forum_id]['last_post_id'] = intval($row['last_post_id']);

				$post_ids[] = $row['last_post_id'];
			}

			// 4: Retrieve last_post infos
			if (sizeof($post_ids))
			{
				$sql = 'SELECT p.post_id, p.poster_id, p.post_time, p.post_username, u.username
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
					WHERE p.post_id IN (' . implode(', ', $post_ids) . ')
						AND p.poster_id = u.user_id';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$post_info[intval($row['post_id'])] = $row;
				}
				$db->sql_freeresult($result);

				foreach ($forum_data as $forum_id => $data)
				{
					if ($data['last_post_id'])
					{
						if (isset($post_info[$data['last_post_id']]))
						{
							$forum_data[$forum_id]['last_post_time'] = $post_info[$data['last_post_id']]['post_time'];
							$forum_data[$forum_id]['last_poster_id'] = $post_info[$data['last_post_id']]['poster_id'];
							$forum_data[$forum_id]['last_poster_name'] = ($post_info[$data['last_post_id']]['poster_id'] != ANONYMOUS) ? $post_info[$data['last_post_id']]['username'] : $post_info[$data['last_post_id']]['post_username'];
						}
						else
						{
							// For some reason we did not find the post in the db
							$forum_data[$forum_id]['last_post_id'] = 0;
							$forum_data[$forum_id]['last_post_time'] = 0;
							$forum_data[$forum_id]['last_poster_id'] = 0;
							$forum_data[$forum_id]['last_poster_name'] = '';
						}
					}
				}
				unset($post_info);
			}

			// 5: Now do that thing
			$fieldnames = array('posts', 'topics', 'topics_real', 'last_post_id', 'last_post_time', 'last_poster_id', 'last_poster_name');

			foreach ($forum_data as $forum_id => $row)
			{
				$sql = array();

				foreach ($fieldnames as $fieldname)
				{
					if ($row['forum_' . $fieldname] != $row[$fieldname])
					{
						if (preg_match('#name$#', $fieldname))
						{
							$sql['forum_' . $fieldname] = (string) $row[$fieldname];
						}
						else
						{
							$sql['forum_' . $fieldname] = (int) $row[$fieldname];
						}
					}
				}

				if (sizeof($sql))
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql) . '
						WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
				}
			}
			break;

		case 'topic':
			$topic_data = $post_ids = $approved_unapproved_ids = $resync_forums = $delete_topics = $delete_posts = array();

			$sql = 'SELECT t.topic_id, t.forum_id, t.topic_approved, ' . (($sync_extra) ? 't.topic_attachment, t.topic_reported, ' : '') . 't.topic_poster, t.topic_time, t.topic_replies, t.topic_replies_real, t.topic_first_post_id, t.topic_first_poster_name, t.topic_last_post_id, t.topic_last_poster_id, t.topic_last_poster_name, t.topic_last_post_time
				FROM ' . TOPICS_TABLE . " t
				$where_sql";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id = (int) $row['topic_id'];
				$topic_data[$topic_id] = $row;
				$topic_data[$topic_id]['replies_real'] = -1;
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
			// NOTE: 't.post_approved' in the GROUP BY is causing a major slowdown.
			$sql = 'SELECT t.topic_id, t.post_approved, COUNT(t.post_id) AS total_posts, MIN(t.post_id) AS first_post_id, MAX(t.post_id) AS last_post_id
				FROM ' . POSTS_TABLE . " t
				$where_sql
				GROUP BY t.topic_id"; //, t.post_approved";
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

					$topic_data[$topic_id]['replies_real'] += $row['total_posts'];
					$topic_data[$topic_id]['first_post_id'] = (!$topic_data[$topic_id]['first_post_id']) ? $row['first_post_id'] : min($topic_data[$topic_id]['first_post_id'], $row['first_post_id']);

					if ($row['post_approved'] || !$topic_data[$topic_id]['last_post_id'])
					{
						$topic_data[$topic_id]['replies'] = $row['total_posts'] - 1;
						$topic_data[$topic_id]['last_post_id'] = $row['last_post_id'];
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

			$sql = 'SELECT p.post_id, p.topic_id, p.post_approved, p.poster_id, p.post_username, p.post_time, u.username
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE p.post_id IN (' . implode(',', $post_ids) . ')
					AND u.user_id = p.poster_id';
			$result = $db->sql_query($sql);

			$post_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id = intval($row['topic_id']);

				if ($row['post_id'] == $topic_data[$topic_id]['first_post_id'])
				{
					if ($topic_data[$topic_id]['topic_approved'] != $row['post_approved'])
					{
						$approved_unapproved_ids[] = $topic_id;
					}
					$topic_data[$topic_id]['time'] = $row['post_time'];
					$topic_data[$topic_id]['poster'] = $row['poster_id'];
					$topic_data[$topic_id]['first_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
				}
				if ($row['post_id'] == $topic_data[$topic_id]['last_post_id'])
				{
					$topic_data[$topic_id]['last_poster_id'] = $row['poster_id'];
					$topic_data[$topic_id]['last_post_time'] = $row['post_time'];
					$topic_data[$topic_id]['last_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
				}
			}
			$db->sql_freeresult($result);

			// approved becomes unapproved, and vice-versa
			if (sizeof($approved_unapproved_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_approved = 1 - topic_approved
					WHERE topic_id IN (' . implode(', ', $approved_unapproved_ids) . ')';
				$db->sql_query($sql);
			}
			unset($approved_unapproved_ids);

			// These are fields that will be synchronised
			$fieldnames = array('time', 'replies', 'replies_real', 'poster', 'first_post_id', 'first_poster_name', 'last_post_id', 'last_post_time', 'last_poster_id', 'last_poster_name');

			if ($sync_extra)
			{
				// This routine assumes that post_reported values are correct
				// if they are not, use sync('post_reported') first
				$sql = 'SELECT t.topic_id, p.post_id
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					$where_sql_and p.topic_id = t.topic_id
						AND p.post_reported = 1
					GROUP BY t.topic_id";
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
					GROUP BY t.topic_id";
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
				$sql = array();

				foreach ($fieldnames as $fieldname)
				{
					if ($row['topic_' . $fieldname] != $row[$fieldname])
					{
						$sql['topic_' . $fieldname] = $row[$fieldname];
					}
				}

				if (sizeof($sql))
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql) . '
						WHERE topic_id = ' . $topic_id;
					$db->sql_query($sql);

					$resync_forums[$row['forum_id']] = $row['forum_id'];
				}
			}
			unset($topic_data);

			// if some topics have been resync'ed then resync parent forums
			// except when we're only syncing a range, we don't want to sync forums during
			// batch processing.
			if ($resync_parents && sizeof($resync_forums) && $where_type != 'range')
			{
				sync('forum', 'forum_id', $resync_forums, true);
			}
			break;
	}
}

/**
* Prune function
*/
function prune($forum_id, $prune_mode, $prune_date, $prune_flags = 0, $auto_sync = true)
{
	global $db;

	$sql_forum = (is_array($forum_id)) ? ' IN (' . implode(',', $forum_id) . ')' : " = $forum_id";

	$sql_and = '';
	if (!($prune_flags & 4))
	{
		$sql_and .= ' AND topic_type <> ' . POST_ANNOUNCE;
	}

	if (!($prune_flags & 8))
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

	$sql = 'SELECT topic_id
		FROM ' . TOPICS_TABLE . "
		WHERE forum_id $sql_forum
			AND poll_start = 0 
			$sql_and";
	$result = $db->sql_query($sql);

	$topic_list = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$topic_list[] = $row['topic_id'];
	}
	$db->sql_freeresult($result);

	if ($prune_flags & 2)
	{
		$sql = 'SELECT topic_id
			FROM ' . TOPICS_TABLE . "
			WHERE forum_id $sql_forum 
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

	return delete_topics('topic_id', $topic_list, $auto_sync);
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
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
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
	$db->sql_freeresult($result);

	return;
}

/**
* remove_comments will strip the sql comment lines out of an uploaded sql file
* specifically for mssql and postgres type files in the install....
*/
function remove_comments(&$output)
{
	$lines = explode("\n", $output);
	$output = '';

	// try to keep mem. use down
	$linecount = sizeof($lines);

	$in_comment = false;
	for ($i = 0; $i < $linecount; $i++)
	{
		if (trim($lines[$i]) == '/*')
		{
			$in_comment = true;
		}

		if (!$in_comment)
		{
			$output .= $lines[$i] . "\n";
		}

		if (trim($lines[$i]) == '*/')
		{
			$in_comment = false;
		}
	}

	unset($lines);
	return $output;
}

/**
* remove_remarks will strip the sql comment lines out of an uploaded sql file
*/
function remove_remarks(&$sql)
{
	$sql = preg_replace('/(\n){2,}/', "\n", preg_replace('/^#.*/m', "\n", $sql));
}

/**
* split_sql_file will split an uploaded sql file into single sql statements.
* Note: expects trim() to have already been run on $sql.
*/
function split_sql_file($sql, $delimiter)
{
	// Split up our string into "possible" SQL statements.
	$tokens = explode($delimiter, $sql);

	// try to save mem.
	$sql = '';
	$output = array();

	// we don't actually care about the matches preg gives us.
	$matches = array();

	// this is faster than calling sizeof($oktens) every time thru the loop.
	for ($i = 0, $token_count = sizeof($tokens); $i < $token_count; $i++)
	{
		// Don't wanna add an empty string as the last thing in the array.
		if ($i != $token_count - 1)
		{
			// This is the total number of single quotes in the token.
			$total_quotes = preg_match_all("#'#", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes,
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("/(?<!\\\\)(?>\\\\{2})*\\\\'/", $tokens[$i], $matches);

			$unescaped_quotes = $total_quotes - $escaped_quotes;

			// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
			if (!($unescaped_quotes % 2))
			{
				// It's a complete sql statement.
				$output[] = $tokens[$i];
				// save memory.
				$tokens[$i] = '';
			}
			else
			{
				// incomplete sql statement. keep adding tokens until we have a complete one.
				// $temp will hold what we have so far.
				$temp = $tokens[$i] . $delimiter;
				// save memory..
				$tokens[$i] = '';

				// Do we have a complete statement yet?
				$complete_stmt = false;

				for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++)
				{
					// This is the total number of single quotes in the token.
					$total_quotes = preg_match_all("#'#", $tokens[$j], $matches);
					// Counts single quotes that are preceded by an odd number of backslashes,
					// which means they're escaped quotes.
					$escaped_quotes = preg_match_all("/(?<!\\\\)(?>\\\\{2})*\\\\'/", $tokens[$j], $matches);

					$unescaped_quotes = $total_quotes - $escaped_quotes;

					if (($unescaped_quotes % 2) == 1)
					{
						// odd number of unescaped quotes. In combination with the previous incomplete
						// statement(s), we now have a complete statement. (2 odds always make an even)
						$output[] = $temp . $tokens[$j];

						// save memory.
						$tokens[$j] = '';
						$temp = '';

						// exit the loop.
						$complete_stmt = true;
						// make sure the outer loop continues at the right point.
						$i = $j;
					}
					else
					{
						// even number of unescaped quotes. We still don't have a complete statement.
						// (1 odd and 1 even always make an odd)
						$temp .= $tokens[$j] . $delimiter;
						// save memory.
						$tokens[$j] = '';
					}
				} // for..
			} // else
		}
	}

	return $output;
}

/**
* Cache moderators, called whenever permissions are changed via admin_permissions. Changes of username
* and group names must be carried through for the moderators table
*
* @todo let the admin define if he wants to display moderators (forum-based) - display_on_index already present and checked for...
*/
function cache_moderators()
{
	global $db, $cache, $auth, $phpbb_root_path, $phpEx;

	// Clear table
	$sql = (SQL_LAYER != 'sqlite') ? 'TRUNCATE ' . MODERATOR_TABLE : 'DELETE FROM ' . MODERATOR_TABLE;
	$db->sql_query($sql);

	// We add moderators who have forum moderator permissions without an explicit ACL_NO setting
	$hold_ary = $ug_id_ary = $sql_ary = array();

	// Grab all users having moderative options...
	$hold_ary = $auth->acl_user_raw_data(false, 'm_%', false);

	// Add users?
	if (sizeof($hold_ary))
	{
		// At least one moderative option warrants a display
		$ug_id_ary = array_keys($hold_ary);

		// Remove users who have group memberships with DENY moderator permissions
		$sql = 'SELECT a.forum_id, ug.user_id
			FROM  (' . ACL_OPTIONS_TABLE . '  o, ' . ACL_GROUPS_TABLE . ' a,  ' . USER_GROUP_TABLE . '  ug)
			LEFT JOIN ' . ACL_ROLES_DATA_TABLE . ' r ON (a.auth_role_id = r.role_id)
			WHERE (o.auth_option_id = a.auth_option_id OR o.auth_option_id = r.auth_option_id)
				AND ((a.auth_setting = ' . ACL_NO . ' AND r.auth_setting IS NULL)
					OR r.auth_setting = ' . ACL_NO . ')
				AND a.group_id = ug.group_id
				AND ug.user_id IN (' . implode(', ', $ug_id_ary) . ")
				AND o.auth_option LIKE 'm\_%'";
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
				WHERE user_id IN (' . implode(', ', array_keys($hold_ary)) . ')';
			$result = $db->sql_query($sql);

			$usernames_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$usernames_ary[$row['user_id']] = $row['username'];
			}

			foreach ($hold_ary as $user_id => $forum_id_ary)
			{
				foreach ($forum_id_ary as $forum_id => $auth_ary)
				{
					$sql_ary[] = array(
						'forum_id'		=> $forum_id,
						'user_id'		=> $user_id,
						'username'		=> $usernames_ary[$user_id],
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
			FROM  ' . GROUPS_TABLE . '
			WHERE group_id IN (' . implode(', ', $ug_id_ary) . ')';
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
			foreach ($forum_id_ary as $forum_id => $auth_ary)
			{
				$sql_ary[] = array(
					'forum_id'		=> $forum_id,
					'user_id'		=> 0,
					'username'		=> '',
					'group_id'		=> $group_id,
					'group_name'	=> $groupnames_ary[$group_id]
				);
			}
		}
	}

	if (sizeof($sql_ary))
	{
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
			case 'mysqli':
				$db->sql_query('INSERT INTO ' . MODERATOR_TABLE . ' ' . $db->sql_build_array('MULTI_INSERT', $sql_ary));
			break;

			default:
				foreach ($sql_ary as $ary)
				{
					$db->sql_query('INSERT INTO ' . MODERATOR_TABLE . ' ' . $db->sql_build_array('INSERT', $ary));
				}
			break;
		}
	}
}

/**
* View log
*/
function view_log($mode, &$log, &$log_count, $limit = 0, $offset = 0, $forum_id = 0, $topic_id = 0, $user_id = 0, $limit_days = 0, $sort_by = 'l.log_time DESC')
{
	global $db, $user, $auth, $phpEx, $SID, $phpbb_root_path, $phpbb_admin_path;

	$topic_id_list = $is_auth = $is_mod = array();

	$profile_url = (defined('IN_ADMIN')) ? "{$phpbb_admin_path}index.$phpEx$SID&amp;i=users&amp;mode=overview" : "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=viewprofile";

	switch ($mode)
	{
		case 'admin':
			$log_type = LOG_ADMIN;
			$sql_forum = '';
			break;
		
		case 'mod':
			$log_type = LOG_MOD;

			if ($topic_id)
			{
				$sql_forum = 'AND l.topic_id = ' . intval($topic_id);
			}
			else if (is_array($forum_id))
			{
				$sql_forum = 'AND l.forum_id IN (' . implode(', ', array_map('intval', $forum_id)) . ')';
			}
			else
			{
				$sql_forum = ($forum_id) ? 'AND l.forum_id = ' . intval($forum_id) : '';
			}
			break;

		case 'user':
			$log_type = LOG_USERS;
			$sql_forum = 'AND l.reportee_id = ' . intval($user_id);
			break;
		
		case 'critical':
			$log_type = LOG_CRITICAL;
			$sql_forum = '';
			break;
		
		default:
			return;
	}

	$sql = "SELECT l.*, u.username
		FROM " . LOG_TABLE . " l, " . USERS_TABLE . " u
		WHERE l.log_type = $log_type
			AND u.user_id = l.user_id
			" . (($limit_days) ? "AND l.log_time >= $limit_days" : '') . "
			$sql_forum
		ORDER BY $sort_by";
	$result = $db->sql_query_limit($sql, $limit, $offset);

	$i = 0;
	$log = array();
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['topic_id'])
		{
			$topic_id_list[] = $row['topic_id'];
		}

		$log[$i]['id'] = $row['log_id'];
		$log[$i]['username'] = '<a href="' . $profile_url . '&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
		$log[$i]['ip'] = $row['log_ip'];
		$log[$i]['time'] = $row['log_time'];
		$log[$i]['forum_id'] = $row['forum_id'];
		$log[$i]['topic_id'] = $row['topic_id'];
		$log[$i]['viewforum'] = ($row['forum_id'] && $auth->acl_get('f_read', $row['forum_id'])) ? "{$phpbb_root_path}viewforum.$phpEx$SID&amp;f=" . $row['forum_id'] : '';

		$log[$i]['action'] = (isset($user->lang[$row['log_operation']])) ? $user->lang[$row['log_operation']] : '{' . ucfirst(str_replace('_', ' ', $row['log_operation'])) . '}';

		if (!empty($row['log_data']))
		{
			$log_data_ary = unserialize(stripslashes($row['log_data']));

			if (isset($user->lang[$row['log_operation']]))
			{
				foreach ($log_data_ary as $log_data)
				{
					$log_data = str_replace("\n", '<br />', censor_text($log_data));

					$log[$i]['action'] = preg_replace('#%s#', $log_data, $log[$i]['action'], 1);
				}
			}
			else
			{
				$log[$i]['action'] .= '<br />' . implode('', $log_data_ary);
			}
		}

		$i++;
	}
	$db->sql_freeresult($result);

	if (sizeof($topic_id_list))
	{
		$topic_id_list = array_unique($topic_id_list);

		// This query is not really needed if move_topics() updates the forum_id field, 
		// altough it's also used to determine if the topic still exists in the database
		$sql = 'SELECT topic_id, forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id IN (' . implode(', ', array_map('intval', $topic_id_list)) . ')';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($auth->acl_get('f_read', $row['forum_id']))
			{
				// DEBUG!! - global topic
				$config['default_forum_id'] = 2;
				$is_auth[$row['topic_id']] = ($row['forum_id']) ? $row['forum_id'] : $config['default_forum_id'];
			}

			if ($auth->acl_gets('a_', 'm_', $row['forum_id']))
			{
				$is_mod[$row['topic_id']] = $row['forum_id'];
			}
		}

		foreach ($log as $key => $row)
		{
			$log[$key]['viewtopic'] = (isset($is_auth[$row['topic_id']])) ? "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;f=" . $is_auth[$row['topic_id']] . '&amp;t=' . $row['topic_id'] : '';
			$log[$key]['viewlogs'] = (isset($is_mod[$row['topic_id']])) ? "{$phpbb_root_path}mcp.$phpEx$SID&amp;i=logs&amp;mode=topic_logs&amp;t=" . $row['topic_id'] : '';
		}
	}

	$sql = 'SELECT COUNT(l.log_id) AS total_entries
		FROM ' . LOG_TABLE . " l
		WHERE l.log_type = $log_type
			AND l.log_time >= $limit_days
			$sql_forum";
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$log_count =  $row['total_entries'];

	return;
}

/**
* Lists warned users
*/
function view_warned_users(&$users, &$user_count, $limit = 0, $offset = 0, $limit_days = 0, $sort_by = 'user_warnings DESC')
{
	global $db;

	$sql = 'SELECT user_id, username, user_warnings, user_last_warning
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

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$user_count = $row['user_count'];

	return;
}

/**
* Get database size
* Currently only mysql and mssql are supported
*/
function get_database_size()
{
	global $db, $user, $table_prefix;
	
	// This code is heavily influenced by a similar routine
	// in phpMyAdmin 2.2.0
	if (preg_match('#^mysql#', SQL_LAYER))
	{
		$result = $db->sql_query('SELECT VERSION() AS mysql_version');

		if ($row = $db->sql_fetchrow($result))
		{
			$version = $row['mysql_version'];

			if (preg_match('#(3\.23|[45]\.)#', $version))
			{
				$db_name = (preg_match('#^(?:3\.23\.(?:[6-9]|[1-9]{2}))|[45]\.#', $version)) ? "`{$db->dbname}`" : $db->dbname;

				$sql = "SHOW TABLE STATUS
					FROM " . $db_name;
				$result = $db->sql_query($sql);

				$dbsize = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					if ((isset($row['Type']) && $row['Type'] != 'MRG_MyISAM') || (isset($row['Engine']) && ($row['Engine'] == 'MyISAM' || $row['Engine'] == 'InnoDB')))
					{
						if ($table_prefix != '')
						{
							if (strstr($row['Name'], $table_prefix))
							{
								$dbsize += $row['Data_length'] + $row['Index_length'];
							}
						}
						else
						{
							$dbsize += $row['Data_length'] + $row['Index_length'];
						}
					}
				}
				$db->sql_freeresult($result);
			}
			else
			{
				$dbsize = $user->lang['NOT_AVAILABLE'];
			}
		}
		else
		{
			$dbsize = $user->lang['NOT_AVAILABLE'];
		}
	}
	else if (preg_match('#^mssql#', SQL_LAYER))
	{
		$sql = 'SELECT ((SUM(size) * 8.0) * 1024.0) as dbsize
			FROM sysfiles';
		$result = $db->sql_query($sql);

		$dbsize = ($row = $db->sql_fetchrow($result)) ? intval($row['dbsize']) : $user->lang['NOT_AVAILABLE'];
		$db->sql_freeresult($result);
	}
	else
	{
		$dbsize = $user->lang['NOT_AVAILABLE'];
	}

	if (is_int($dbsize))
	{
		$dbsize = ($dbsize >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($dbsize / 1048576)) : (($dbsize >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($dbsize / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $dbsize));
	}

	return $dbsize;
}

/**
* Retrieve contents from remotely stored file
*/
function get_remote_file($host, $directory, $filename, &$errstr, &$errno, $port = 80, $timeout = 10)
{
	global $user;

	if ($fsock = @fsockopen($host, $port, $errno, $errstr, $timeout))
	{
		@fputs($fsock, "GET $directory/$filename HTTP/1.1\r\n");
		@fputs($fsock, "HOST: $host\r\n");
		@fputs($fsock, "Connection: close\r\n\r\n");
	
		$file_info = '';
		$get_info = false;

		while (!@feof($fsock))
		{
			if ($get_info)
			{
				$file_info .= @fread($fsock, 1024);
			}
			else
			{
				$line = @fgets($fsock, 1024);
				if ($line == "\r\n")
				{
					$get_info = true;
				}
				else if (strpos($line, '404 Not Found') !== false)
				{
					$errstr = $user->lang['FILE_NOT_FOUND'];
					return false;
				}
			}
		}
		@fclose($fsock);
	}
	else
	{
		if ($errstr)
		{
			return false;
		}
		else
		{
			$errstr = 'fsock disabled';
			return false;
		}
	}
	
	return $file_info;
}

/**
* Tidy Warnings
* Remove all warnings which have now expired from the database
* The duration of a warning can be defined by the administrator
* This only removes the warning and reduces the assosciated count,
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
		$user_list[$row['user_id']] = isset($user_list[$row['user_id']]) ? $user_list[$row['user_id']]++ : 0;
	}
	$db->sql_freeresult($result);

	if (sizeof($warning_list))
	{
		$db->sql_transaction('begin');

		$sql_where = ' IN (' . implode(', ', $warning_list) . ')';
		$sql = 'DELETE FROM ' . WARNINGS_TABLE . "
			WHERE warning_id $sql_where";
		$db->sql_query($sql);
	
		foreach ($user_list as $user_id => $value)
		{
			$sql = 'UPDATE ' . USERS_TABLE . " SET user_warnings = user_warnings - $value
				WHERE user_id = $user_id";
			$db->sql_query($sql);
		}

		$db->sql_transaction('commit');
	}

	set_config('warnings_last_gc', time());
}

/**
* Tidy database
* Removes all tracking rows older than 6 months, including mark_posted informations
*/
function tidy_database()
{
	global $db;
/*
	$remove_date = time() - (3 * 62 * 24 * 3600);

	$sql = 'DELETE FROM ' . FORUMS_TRACK_TABLE . '
		WHERE mark_time < ' . $remove_date;
	$db->sql_query($sql);

	$sql = 'DELETE FROM ' . TOPICS_TRACK_TABLE . '
		WHERE mark_time < ' . $remove_date;
	$db->sql_query($sql);
*/
	set_config('database_last_gc', time(), true);
}

?>