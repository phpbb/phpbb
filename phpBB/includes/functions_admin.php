<?php
/***************************************************************************
 *                            functions_admin.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : © 2001 The phpBB Group
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

// Simple version of jumpbox, just lists authed forums
function make_forum_select($select_id = false, $ignore_id = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true)
{
	global $db, $user, $auth;

	$acl = ($ignore_acl) ? '' : array('f_list', 'a_forum', 'a_forumadd', 'a_forumdel');
	$rowset = get_forum_list($acl, false, $ignore_nonpost, true);

	$right = $cat_right = 0;
	$forum_list = $padding = $holding = '';
	$padding_store = array('0' => '');
	foreach ($rowset as $row)
	{
		if ((is_array($ignore_id) && in_array($row['forum_id'], $ignore_id)) || 
			$row['forum_id'] == $ignore_id)
		{
			continue;
		}

		if ($row['left_id'] < $right)
		{
			$padding .= '&nbsp; &nbsp; &nbsp;';
			$padding_store[$row['parent_id']] = $padding;
		}
		else if ($row['left_id'] > $right + 1)
		{
			$padding = $padding_store[$row['parent_id']];
		}

		$right = $row['right_id'];

		$selected = (is_array($select_id)) ? ((in_array($row['forum_id'], $select_id)) ? ' selected="selected"' : '') : (($row['forum_id'] == $select_id) ? ' selected="selected"' : '');

		if ($row['left_id'] > $cat_right)
		{
			$holding = '';
		}

		if ($row['right_id'] - $row['left_id'] > 1 && $ignore_emptycat)
		{
			$cat_right = max($cat_right, $row['right_id']);

			$holding .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option>';
		}
		else
		{
			$forum_list .= $holding . '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . $row['forum_name'] . '</option>';
			$holding = '';
		}
	}

	if (!$right)
	{
		$forum_list .= '<option value="-1">' . $user->lang['NO_FORUMS'] . '</option>';
	}

	return $forum_list;
}

// Obtain authed forums list
function get_forum_list($acl_list = 'f_list', $id_only = TRUE, $postable_only = FALSE, $no_cache = FALSE)
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
		$db->sql_freeresult();
	}

	$rowset = array();
	foreach ($forum_rows as $row)
	{
		if ($postable_only && $row['forum_type'] == FORUM_CAT)
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
	$db->sql_freeresult($result);

	return $rows;
}

function filelist($rootdir, $dir = '', $type = 'gif|jpg|jpeg|png')
{
	$matches = array();

	$dh = opendir($rootdir . $dir);

	while ($fname = readdir($dh))
	{
		if (is_file($rootdir . $dir . '/' . $fname) && 
			preg_match('#\.' . $type . '$#i', $fname) &&  
			filesize($rootdir . $dir . '/' . $fname))
		{
			$matches[$dir][] = $fname;
		}
		else if ($fname{0} != '.' && is_dir($rootdir . $dir . '/' . $fname))
		{
			$matches += filelist($rootdir, $dir . '/'. $fname, $type);
		}
	}
	
	closedir($dh);

	return $matches;
}

// Posts and topics manipulation
function move_topics($topic_ids, $forum_id, $auto_sync = TRUE)
{
	global $db;

	$forum_ids = array($forum_id);
	$sql_where = (is_array($topic_ids)) ? 'IN (' . implode(', ', $topic_ids) . ')' : '= ' . $topic_ids;

	$sql = 'DELETE FROM ' . TOPICS_TABLE . "
		WHERE topic_moved_id $sql_where
			AND forum_id = " . $forum_id;
	$db->sql_query($sql);

	$table_ary = array(TOPICS_TABLE, POSTS_TABLE, LOG_MOD_TABLE);
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
		$sql = 'SELECT DISTINCT forum_id
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id ' . $sql_where;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
		}
		$db->sql_freeresult($result);

		sync('forum', 'forum_id', $forum_ids, TRUE);
		unset($forum_ids);
	}
}

function move_posts($post_ids, $topic_id, $auto_sync = TRUE)
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

	$sql = 'SELECT * 
		FROM ' . TOPICS_TABLE . ' 
		WHERE topic_id = ' . $topic_id;
	$result = $db->sql_query($sql);

	if (!($row = $db->sql_fetchrow($result)))
	{
		trigger_error('NO_TOPIC');
	}
	$db->sql_freeresult($result);

	$sql = 'UPDATE ' . POSTS_TABLE . '
		SET forum_id = ' . $row['forum_id'] . ", topic_id = $topic_id
		WHERE post_id IN (" . implode(', ', $post_ids) . ')';
	$db->sql_query($sql);

	if ($auto_sync)
	{
		$forum_ids[] = $row['forum_id'];

		sync('reported', 'topic_id', $topic_ids);
		sync('topic', 'topic_id', $topic_ids, TRUE);
		sync('forum', 'forum_id', $forum_ids, TRUE);
	}
}

function delete_topics($where_type, $where_ids, $auto_sync = TRUE)
{
	global $db;
	$forum_ids = $topic_ids = array();

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}

	if (!count($where_ids))
	{
		return array('topics' => 0, 'posts' => '0');
	}

	$return = array(
		'posts'	=>	delete_posts($where_type, $where_ids, FALSE)
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

	$return['topics'] = count($topic_ids);

	if (!count($topic_ids))
	{
		return $return;
	}

	// TODO: probably some other stuff too

	$sql_where = ' IN (' . implode(', ', $topic_ids) . ')';

	$db->sql_transaction('begin');

	$table_ary = array(TOPICS_TRACK_TABLE, POLL_VOTES_TABLE, POLL_OPTIONS_TABLE, TOPICS_WATCH_TABLE, TOPICS_TABLE);
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
		sync('forum', 'forum_id', $forum_ids, TRUE);
		sync('topic_reported', $where_type, $where_ids);
	}

	// Optimize/vacuum tables
	switch (SQL_LAYER)
	{
		case 'mysql':
		case 'mysql4':
			$table_ary = array(TOPICS_TRACK_TABLE, POLL_VOTES_TABLE, POLL_OPTIONS_TABLE, TOPICS_WATCH_TABLE, TOPICS_TABLE);
			$sql = 'OPTIMIZE TABLE ' . implode(', ', $table_ary);
			$db->sql_query($sql);
			unset($table_ary);

			break;

		case 'postgresql':
			$db->sql_query('VACUUM');
			break;
	}

	return $return;
}

function delete_posts($where_type, $where_ids, $auto_sync = TRUE)
{
	global $db;

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}
	if (!count($where_ids))
	{
		return false;
	}

	$post_ids = $topic_ids = $forum_ids = array();

	$sql = 'SELECT post_id, topic_id, forum_id
		FROM ' . POSTS_TABLE . "
		WHERE $where_type " . ((!is_array($where_ids)) ? "= $where_ids" : 'IN (' . implode(', ', $where_ids) . ')');

	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$post_ids[] = $row['post_id'];
		$topic_ids[] = $row['topic_id'];
		$forum_ids[] = $row['forum_id'];
	}

	if (!count($post_ids))
	{
		return false;
	}

	$sql_where = implode(', ', $post_ids);

	$db->sql_transaction('begin');

	$table_ary = array(POSTS_TABLE, RATINGS_TABLE, REPORTS_TABLE, SEARCH_MATCH_TABLE);
	foreach ($table_ary as $table)
	{
		$sql = "DELETE FROM $table 
			WHERE post_id IN ($sql_where)";
		$db->sql_query($sql);
	}
	unset($table_ary);

	$db->sql_transaction('commit');

//	delete_attachment($post_ids);

	if ($auto_sync)
	{
		sync('reported', 'topic_id', $topic_ids);
		sync('topic', 'topic_id', $topic_ids, TRUE);
		sync('forum', 'forum_id', $forum_ids, TRUE);
	}

	// Optimize/vacuum tables
	switch (SQL_LAYER)
	{
		case 'mysql':
		case 'mysql4':
			$table_ary = array(POSTS_TABLE, RATINGS_TABLE, REPORTS_TABLE, SEARCH_MATCH_TABLE);
			$sql = 'OPTIMIZE TABLE ' . implode(', ', $table_ary);
			$db->sql_query($sql);
			unset($table_ary);

			break;

		case 'postgresql':
			$db->sql_query('VACUUM');
			break;
	}

	return count($post_ids);
}

// All-encompasing sync function
//
// Usage:
// sync('topic', 'topic_id', 123);			<= resynch topic #123
// sync('topic', 'forum_id', array(2, 3));	<= resynch topics from forum #2 and #3
// sync('topic');							<= resynch all topics
function sync($mode, $where_type = '', $where_ids = '', $resync_parents = FALSE, $sync_extra = FALSE)
{
	global $db;

	if (is_array($where_ids))
	{
		$where_ids = array_unique($where_ids);
	}
	else
	{
		$where_ids = array($where_ids);
	}

	if ($mode == 'forum' || $mode == 'topic')
	{
		if (!$where_type)
		{
			$where_sql = '';
			$where_sql_and = 'WHERE';
		}
		else
		{
			$where_sql = 'WHERE ' . $mode{0} . ".$where_type IN (" . implode(', ', $where_ids) . ')';
			$where_sql_and = $where_sql . ' AND';
		}
	}
	else
	{
		$where_sql = "WHERE t.$where_type IN (" . implode(', ', $where_ids) . ')';
		$where_sql_and = $where_sql . ' AND';
	}

	switch ($mode)
	{
		case 'topic_approved':
			$sql = 'SELECT t.topic_id, p.post_approved
				FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				$where_sql_and p.post_id = t.topic_first_post_id
					AND p.post_approved <> t.topic_approved";
			$result = $db->sql_query($sql);

			$topic_ids = $approved_unapproved_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$approved_unapproved_ids[] = $row['topic_id'];
			}
			$db->sql_freeresult();

			if (!count($approved_unapproved_ids))
			{
				return;
			}

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_approved = 1 - topic_approved
				WHERE topic_id IN (' . implode(', ', $approved_unapproved_ids) . ')';
			$db->sql_query($sql);
			break;

		case 'post_attachment':
			$post_ids = array();

			switch (SQL_LAYER)
			{
				case 'oracle':
					//TODO
					break;

				default:
					$sql = 'SELECT t.post_id, t.post_attachment, COUNT(a.attach_id) AS attachments
						FROM ' . POSTS_TABLE . ' t
						LEFT JOIN ' . ATTACHMENTS_TABLE . " a ON t.post_id = a.post_id
						$where_sql
						GROUP BY t.post_id";
			}
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (($row['post_attachment'] && !$row['attachments']) || ($row['attachments'] && !$row['post_attachment']))
				{
					$post_ids[] = $row['post_id'];
				}
			}
			$db->sql_freeresult($result);

			if (!count($post_ids))
			{
				return;
			}

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 1 - post_attachment
				WHERE post_id IN (' . implode(', ', $post_ids) . ')';
			$db->sql_query($sql);
			break;

		case 'topic_attachment':

			switch (SQL_LAYER)
			{
				case 'oracle':
					//TODO
					break;

				default:
					$sql = 'SELECT t.topic_id, t.topic_attachment, a.attach_id
						FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
						LEFT JOIN ' . ATTACHMENTS_TABLE . " a ON p.post_id = a.post_id
						$where_sql_and t.topic_id = p.topic_id
							AND	((t.topic_attachment = 1 AND a.attach_id IS NULL)
								OR	 (t.topic_attachment = 0 AND a.attach_id > 0))
						GROUP BY p.topic_id";
			}
			$result = $db->sql_query($sql);

			$topic_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
					$topic_ids[] = $row['topic_id'];
			}
			$db->sql_freeresult($result);

			if (!count($topic_ids))
			{
				return;
			}

			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_attachment = 1 - topic_attachment
				WHERE topic_id IN (' . implode(', ', $topic_ids) . ')';
			$db->sql_query($sql);
			break;

		case 'reported':
			$topic_data = $topic_ids = $post_ids = array();

			if ($sync_extra)
			{
				switch (SQL_LAYER)
				{
					case 'oracle':
						//TODO
						break;

					default:
						$sql = 'SELECT p.post_id
							FROM ' . POSTS_TABLE . ' t
							LEFT JOIN ' . REPORTS_TABLE . " r ON r.post_id = t.post_id
							$where_sql
								AND	((t.post_reported = 1 AND r.post_id IS NULL)
									OR (t.post_reported = 0 AND r.post_id > 0))
							GROUP p.post_id";
				}
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					do
					{
						$post_ids[] = $row['post_id'];
					}
					while ($row = $db->sql_fetchrow($result));

					$sql = 'UPDATE ' . POSTS_TABLE . '
						SET post_reported = 1 - post_reported
						WHERE post_id IN (' . implode(', ', $post_ids) . ')';
					$db->sql_query($sql);
					unset($post_ids);
				}
				$db->sql_freeresult();
			}

			switch (SQL_LAYER)
			{
				case 'oracle':
					//TODO
					break;

				default:
					$sql = 'SELECT t.topic_id, t.topic_reported, p.post_reported
						FROM ' . TOPICS_TABLE . ' t
						LEFT JOIN ' . POSTS_TABLE . " p ON p.topic_id = t.topic_id
						$where_sql
						GROUP BY p.topic_id, p.post_reported";
			}
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				do
				{
					if (!isset($topic_data[$row['topic_id']]))
					{
						$topic_data[$row['topic_id']] = array(
							'topic_reported' => $row['topic_reported'],
							'post_reported' => $row['post_reported']
						);
					}
					else
					{
						$topic_data[$row['topic_id']]['post_reported'] |= $row['post_reported'];
					}
				}
				while ($row = $db->sql_fetchrow($result));

				foreach ($topic_data as $topic_id => $row)
				{
					if ($row['post_reported'] != $row['topic_reported'])
					{
						$topic_ids[] = $topic_id;
					}
				}

				if (count($topic_ids))
				{
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET topic_reported = 1 - topic_reported
						WHERE topic_id IN (' . implode(', ', $topic_ids) . ')';
					$db->sql_query($sql);
				}
			}
			$db->sql_freeresult($result);

			return;
			break;

		case 'forum':
			if ($resync_parents)
			{
				$sql = 'SELECT f2.forum_id
					FROM ' . FORUMS_TABLE .  ' f, ' . FORUMS_TABLE . " f2
					$where_sql_and f.left_id BETWEEN f2.left_id AND f2.right_id";
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$forum_ids = array();
					do
					{
						$forum_ids[] = $row['forum_id'];
					}
					while ($row = $db->sql_fetchrow($result));

					sync('forum', 'forum_id', $forum_ids, FALSE);
					unset($forum_ids);
				}
				$db->sql_freeresult($result);

				return;
			}

			// 1° Get the list of all forums and their children
			$sql = 'SELECT f.*, f2.forum_id AS id
				FROM ' . FORUMS_TABLE . ' f, ' . FORUMS_TABLE . " f2
				$where_sql_and f2.left_id BETWEEN f.left_id AND f.right_id";
			$result = $db->sql_query($sql);

			$forum_data = $forum_ids = $post_ids = $subforum_list = $post_count = $last_post_id = $post_info = $topic_count = $topic_count_real = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['forum_type'] == FORUM_LINK)
				{
					continue;
				}

				$forum_ids[$row['id']] = $row['id'];
				if (!isset($subforum_list[$row['forum_id']]))
				{
					$forum_data[$row['forum_id']] = $row;
					$forum_data[$row['forum_id']]['posts'] = 0;
					$forum_data[$row['forum_id']]['topics'] = 0;
					$forum_data[$row['forum_id']]['last_post_id'] = 0;
					$forum_data[$row['forum_id']]['last_post_time'] = 0;
					$forum_data[$row['forum_id']]['last_poster_id'] = 0;
					$forum_data[$row['forum_id']]['last_poster_name'] = '';
					$subforum_list[$row['forum_id']] = array($row['id']);
				}
				else
				{
					$subforum_list[$row['forum_id']][] = $row['id'];
				}
			}

			// 2° Get topic counts for each forum
			$sql = 'SELECT forum_id, topic_approved, COUNT(topic_id) AS forum_topics
				FROM ' . TOPICS_TABLE . '
				WHERE forum_id IN (' . implode(', ', $forum_ids) . ')
				GROUP BY forum_id, topic_approved';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (!isset($topic_count_real[$row['forum_id']]))
				{
					$topic_count_real[$row['forum_id']] = $row['forum_topics'];
					$topic_count[$row['forum_id']] = 0;
				}
				else
				{
					$topic_count_real[$row['forum_id']] += $row['forum_topics'];
				}

				if ($row['topic_approved'])
				{
					$topic_count[$row['forum_id']] = $row['forum_topics'];
				}
			}

			// 3° Get post counts for each forum
			$sql = 'SELECT forum_id, COUNT(post_id) AS forum_posts, MAX(post_id) AS last_post_id
				FROM ' . POSTS_TABLE . '
				WHERE forum_id IN (' . implode(', ', $forum_ids) . ')
				GROUP BY forum_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$post_count[$row['forum_id']] = intval($row['forum_posts']);
				$last_post_id[$row['forum_id']] = intval($row['last_post_id']);
			}

			// 4° Do the math
			foreach ($subforum_list as $forum_id => $subforums)
			{
				foreach ($subforums as $subforum_id)
				{
					if (isset($topic_count[$subforum_id]))
					{
						$forum_data[$forum_id]['topics'] += $topic_count[$subforum_id];
						$forum_data[$forum_id]['topics_real'] += $topic_count_real[$subforum_id];
					}

					if (isset($post_count[$subforum_id]))
					{
						$forum_data[$forum_id]['posts'] += $post_count[$subforum_id];
						$forum_data[$forum_id]['last_post_id'] = max($forum_data[$forum_id]['last_post_id'], $last_post_id[$subforum_id]);
					}
				}
			}

			// 5° Retrieve last_post infos
			foreach ($forum_data as $forum_id => $data)
			{
				if ($data['last_post_id'])
				{
					$post_ids[] = $data['last_post_id'];
				}
			}

			if (count($post_ids))
			{
				$sql = 'SELECT p.post_id, p.poster_id, u.username, p.post_time
					FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
					WHERE p.post_id IN (' . implode(', ', $post_ids) . ')
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
						$forum_data[$forum_id]['last_post_time'] = $post_info[$data['last_post_id']]['post_time'];
						$forum_data[$forum_id]['last_poster_id'] = $post_info[$data['last_post_id']]['poster_id'];
						$forum_data[$forum_id]['last_poster_name'] = $post_info[$data['last_post_id']]['username'];
					}
				}
			}

			$fieldnames = array('posts', 'topics', 'topics_real', 'last_post_id', 'last_post_time', 'last_poster_id', 'last_poster_name');

			foreach ($forum_data as $forum_id => $row)
			{
				$need_update = FALSE;

				foreach ($fieldnames as $fieldname)
				{
					verify_data('forum', $fieldname, $need_update, $row);
				}

				if ($need_update)
				{
					$sql = array();
					foreach ($fieldnames as $fieldname)
					{
						if (preg_match('#name$#', $fieldname))
						{
							$sql['forum_' . $fieldname] = (isset($row[$fieldname])) ? (string) $row[$fieldname] : '';
						}
						else
						{
							$sql['forum_' . $fieldname] = (isset($row[$fieldname])) ? (int) $row[$fieldname] : 0;
						}
					}

					$sql = 'UPDATE ' . FORUMS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql) . '
							WHERE forum_id = ' . $forum_id;
					$db->sql_query($sql);
				}
			}
			break;

		case 'topic':
			$topic_data = $topic_ids = $post_ids = $approved_unapproved_ids = $resync_forums = array();

			$sql = 'SELECT t.*, p.post_approved, COUNT(p.post_id) AS total_posts, MIN(p.post_id) AS first_post_id, MAX(p.post_id) AS last_post_id
				FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
				$where_sql_and p.topic_id = t.topic_id
				GROUP BY p.topic_id, p.post_approved";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$row['total_posts'] = intval($row['total_posts']);
				$row['first_post_id'] = intval($row['first_post_id']);
				$row['last_post_id'] = intval($row['last_post_id']);
				$row['replies'] = $row['total_posts'] - 1;

				if (!isset($topic_data[$row['topic_id']]))
				{
					$topic_ids[$row['topic_id']] = '';
					$topic_data[$row['topic_id']] = $row;
					$topic_data[$row['topic_id']]['reported'] = 0;
					$topic_data[$row['topic_id']]['replies_real'] += $row['total_posts'] - 1;
				}
				else
				{
					$topic_data[$row['topic_id']]['replies_real'] += $row['total_posts'];

					if ($topic_data[$row['topic_id']]['first_post_id'] > $row['first_post_id'])
					{
						$topic_data[$row['topic_id']]['first_post_id'] = $row['first_post_id'];
					}
					if ($row['post_approved'])
					{
						$topic_data[$row['topic_id']]['replies'] = $row['replies'];
						$topic_data[$row['topic_id']]['last_post_id'] = $row['last_post_id'];
					}
				}
			}
			$db->sql_freeresult();

			foreach ($topic_data as $topic_id => $row)
			{
				$post_ids[] = $row['first_post_id'];
				if ($row['first_post_id'] != $row['last_post_id'])
				{
					$post_ids[] = $row['last_post_id'];
				}
			}

			// Now we delete empty topics
			// NOTE: we'll need another function to take care of orphans (posts with no valid topic and topics with no forum)
			if (!count($topic_ids))
			{
				// If we get there, topic ids were invalid or topics did not contain any posts
				delete_topics($where_type, $where_ids);
				return;
			}
			else
			{
				$delete_ids = array();

				if (count($where_ids) && $where_type == 'topic_id')
				{
					// If we get there, we already have a range of topic_ids; make a diff and delete topics
					// that didn't return any row
					$delete_ids = array_diff($where_ids, array_keys($topic_ids));
				}
				else
				{
					// We're resync'ing by forum_id so we'll have to determine which topics we have to delete
					// NOTE: if there are too many topics, the query can get too long and may crash the server
					if (count($topic_ids) < 100)
					{
						$sql = 'SELECT topic_id
							FROM ' . TOPICS_TABLE . "
							$where_sql_and topic_id NOT IN (" . implode(',', array_keys($topic_ids)) . ')';
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$delete_ids[] = $row['topic_id'];
						}
					}
					else
					{
						$sql = 'SELECT topic_id
							FROM ' . TOPICS_TABLE . "
							$where_sql";
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							if (!isset($topic_ids[$row['topic_id']]))
							{
								$delete_ids[] = $row['topic_id'];
							}
						}
					}
					$db->sql_freeresult();
				}

				unset($topic_ids);
				if (count($delete_ids))
				{
					delete_topics('topic_id', $delete_ids);
				}
			}

			if (!count($post_ids))
			{
				return;
			}

			$sql = 'SELECT p.post_id, p.topic_id, p.post_approved, p.poster_id, p.post_username, p.post_time, u.username
				FROM ' . POSTS_TABLE . ' p, ' . USERS_TABLE . ' u
				WHERE p.post_id IN (' . implode(', ', $post_ids) . ')
					AND u.user_id = p.poster_id';
			$result = $db->sql_query($sql);

			$post_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['post_id'] == $topic_data[$row['topic_id']]['first_post_id'])
				{
					if ($topic_data[$row['topic_id']]['topic_approved'] != $row['post_approved'])
					{
						if ($row['post_approved'])
						{
							$approved_unapproved_ids[] = $row['topic_id'];
						}
					}
					$topic_data[$row['topic_id']]['time'] = $row['post_time'];
					$topic_data[$row['topic_id']]['poster'] = $row['poster_id'];
					$topic_data[$row['topic_id']]['first_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
				}
				if ($row['post_id'] == $topic_data[$row['topic_id']]['last_post_id'])
				{
					$topic_data[$row['topic_id']]['last_poster_id'] = $row['poster_id'];
					$topic_data[$row['topic_id']]['last_post_time'] = $row['post_time'];
					$topic_data[$row['topic_id']]['last_poster_name'] = ($row['poster_id'] == ANONYMOUS) ? $row['post_username'] : $row['username'];
				}
			}
			$db->sql_freeresult();

			// approved becomes unapproved, and vice-versa
			if (count($approved_unapproved_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET topic_approved = 1 - topic_approved
					WHERE topic_id IN (' . implode(', ', $approved_unapproved_ids) . ')';
				$db->sql_query($sql);
			}

			// These are field that will be synchronised
			$fieldnames = array('time', 'replies', 'replies_real', 'poster', 'first_post_id', 'first_poster_name', 'last_post_id', 'last_post_time', 'last_poster_id', 'last_poster_name');

			if ($sync_extra)
			{
				// This routine assumes that post_reported values are correct
				// if they are not, use sync('reported') instead
				$sql = 'SELECT t.topic_id, p.post_id
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					$where_sql_and p.topic_id = t.topic_id
						AND p.post_reported = 1
					GROUP t.topic_id";
				$result = $db->sql_query($sql);

				$fieldnames[] = 'reported';
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_data[$row['topic_id']]['reported'] = 1;
				}
				$db->sql_freeresult($result);

				// This routine assumes that post_attachment values are correct
				// if they are not, use sync('post_attachment') instead
				$sql = 'SELECT t.topic_id, p.post_id
					FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . " p
					$where_sql_and p.topic_id = t.topic_id
						AND p.post_attachment = 1
					GROUP t.topic_id";
				$result = $db->sql_query($sql);

				$fieldnames[] = 'attachment';
				while ($row = $db->sql_fetchrow($result))
				{
					$topic_data[$row['topic_id']]['attachment'] = 1;
				}
				$db->sql_freeresult($result);
			}

			foreach ($topic_data as $topic_id => $row)
			{
				$need_update = FALSE;

				foreach ($fieldnames as $fieldname)
				{
					verify_data('topic', $fieldname, $need_update, $row);
				}

				if ($need_update)
				{
					$sql = array();
					foreach ($fieldnames as $fieldname)
					{
						if (preg_match('#name$#', $fieldname))
						{
							$sql['topic_' . $fieldname] = (string) $row[$fieldname];
						}
						else
						{
							$sql['topic_' . $fieldname] = (int) $row[$fieldname];
						}
					}

					// NOTE: should shadow topics be updated as well?
					$sql = 'UPDATE ' . TOPICS_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql) . '
						WHERE topic_id = ' . $topic_id;
					$db->sql_query($sql);

					$resync_forums[] = $row['forum_id'];
				}
			}
			unset($topic_data);

			// if some topics have been resync'ed then resync parent forums
			if ($resync_parents && count($resync_forums))
			{
				$sql = 'SELECT f.forum_id
					FROM ' .FORUMS_TABLE . ' f, ' . FORUMS_TABLE . ' f2
					WHERE f.left_id BETWEEN f2.left_id AND f2.right_id
						AND f2.forum_id IN (' . implode(', ', array_unique($resync_forums)) . ')
					GROUP BY f.forum_id';
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$forum_ids = array();
					do
					{
						$forum_ids[] = $row['forum_id'];
					}
					while ($row = $db->sql_fetchrow($result));

					sync('forum', 'forum_id', $forum_ids, FALSE);
					unset($forum_ids);
				}
				$db->sql_freeresult($result);
			}
			break;
	}
}

// This function is used by the sync() function
function verify_data($type, $fieldname, &$need_update, &$data)
{
	// Check if the corresponding data actually exists. Must not fail when equal to zero.
	if (!isset($data[$fieldname]) || is_null($data[$fieldname]))
	{
		return;
	}

	if ($data[$fieldname] != $data[$type . '_' . $fieldname])
	{
		$need_update = TRUE;
		$data[$type . '_' . $fieldname] = $data[$fieldname];
	}
}

function prune($forum_id, $prune_date, $prune_flags = 0, $auto_sync = true)
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

	$sql = 'SELECT topic_id
		FROM ' . TOPICS_TABLE . "
		WHERE forum_id $sql_forum
			AND topic_last_post_time < $prune_date 
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
				AND topic_last_post_time < $prune_date 
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

// Function auto_prune(), this function now relies on passed vars
function auto_prune($forum_id, $prune_flags, $prune_days, $prune_freq)
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

		prune($forum_id, $prune_date, $prune_flags, true);

		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET prune_next = $next_prune
			WHERE forum_id = $forum_id";
		$db->sql_query($sql);

		add_log('admin', 'LOG_AUTO_PRUNE', $row['forum_name']);
	}
	$db->sql_freeresult($result);

	return;
}

// remove_comments will strip the sql comment lines out of an uploaded sql file
// specifically for mssql and postgres type files in the install....
function remove_comments(&$output)
{
	$lines = explode("\n", $output);
	$output = '';

	// try to keep mem. use down
	$linecount = count($lines);

	$in_comment = false;
	for($i = 0; $i < $linecount; $i++)
	{
		if (preg_match('#^\/\*#', preg_quote($lines[$i])))
		{
			$in_comment = true;
		}

		if (!$in_comment)
		{
			$output .= $lines[$i] . "\n";
		}

		if (preg_match('#\*\/$#', preg_quote($lines[$i])))
		{
			$in_comment = false;
		}
	}

	unset($lines);
	return $output;
}

// remove_remarks will strip the sql comment lines out of an uploaded sql file
function remove_remarks($sql)
{
	$lines = explode("\n", $sql);

	// try to keep mem. use down
	$sql = '';

	$linecount = count($lines);
	$output = '';

	for ($i = 0; $i < $linecount; $i++)
	{
		if ($i != $linecount - 1 || strlen($lines[$i]) > 0)
		{
			$output .= ($lines[$i]{0} != '#') ? $lines[$i] . "\n" : "\n";
			// Trading a bit of speed for lower mem. use here.
			$lines[$i] = '';
		}
	}

	return $output;

}

// split_sql_file will split an uploaded sql file into single sql statements.
// Note: expects trim() to have already been run on $sql.
function split_sql_file($sql, $delimiter)
{
	// Split up our string into "possible" SQL statements.
	$tokens = explode($delimiter, $sql);

	// try to save mem.
	$sql = '';
	$output = array();

	// we don't actually care about the matches preg gives us.
	$matches = array();

	// this is faster than calling count($oktens) every time thru the loop.
	$token_count = count($tokens);
	for ($i = 0; $i < $token_count; $i++)
	{
		// Don't wanna add an empty string as the last thing in the array.
		if ($i != $token_count - 1 || strlen($tokens[$i] > 0))
		{
			// This is the total number of single quotes in the token.
			$total_quotes = preg_match_all("#'#", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes,
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("#(?<!\\\\)(\\\\\\\\)*\\\\'#", $tokens[$i], $matches);

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
					$escaped_quotes = preg_match_all("#(?<!\\\\)(\\\\\\\\)*\\\\'#", $tokens[$j], $matches);

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

// Cache moderators, called whenever permissions are changed via admin_permissions. Changes of username
// and group names must be carried through for the moderators table
function cache_moderators()
{
	global $db;

	// Clear table
	$sql = (SQL_LAYER != 'sqlite') ? 'TRUNCATE ' . MODERATOR_TABLE : 'DELETE FROM ' . MODERATOR_TABLE;
	$db->sql_query($sql);

	// Holding array
	$m_sql = array();
	$user_id_sql = '';

	$sql = 'SELECT a.forum_id, u.user_id, u.username
		FROM  ' . ACL_OPTIONS_TABLE . '  o, ' . ACL_USERS_TABLE . ' a,  ' . USERS_TABLE . "  u
		WHERE o.auth_option = 'm_'
			AND a.auth_option_id = o.auth_option_id
			AND a.auth_setting = " . ACL_YES . ' 
			AND u.user_id = a.user_id';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$m_sql['f_' . $row['forum_id'] . '_u_' . $row['user_id']] = $row['forum_id'] . ', ' . $row['user_id'] . ", '" . $row['username'] . "', NULL, NULL";
		$user_id_sql .= (($user_id_sql) ? ', ' : '') . $row['user_id'];
	}
	$db->sql_freeresult($result);

	// Remove users who have group memberships with DENY moderator permissions
	if ($user_id_sql)
	{
		$sql = 'SELECT a.forum_id, ug.user_id
			FROM  ' . ACL_OPTIONS_TABLE . '  o, ' . ACL_GROUPS_TABLE . ' a,  ' . USER_GROUP_TABLE . "  ug
			WHERE o.auth_option = 'm_'
				AND a.auth_option_id = o.auth_option_id
				AND a.auth_setting = " . ACL_NO . " 
				AND a.group_id = ug.group_id
				AND ug.user_id IN ($user_id_sql)";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			unset($m_sql['f_' . $row['forum_id'] . '_u_' . $row['user_id']]);
		}
		$db->sql_freeresult($result);
	}

	$sql = 'SELECT a.forum_id, g.group_name, g.group_id
		FROM  ' . ACL_OPTIONS_TABLE . '  o, ' . ACL_GROUPS_TABLE . ' a,  ' . GROUPS_TABLE . "  g
		WHERE o.auth_option = 'm_'
			AND a.auth_option_id = o.auth_option_id
			AND a.auth_setting = " . ACL_YES . '
			AND g.group_id = a.group_id
			AND g.group_type NOT IN (' . GROUP_HIDDEN . ', ' . GROUP_SPECIAL . ')';
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$m_sql['f_' . $row['forum_id'] . '_g_' . $row['group_id']] = $row['forum_id'] . ', NULL, NULL, ' . $row['group_id'] . ", '" . $row['group_name'] . "'";
	}
	$db->sql_freeresult($result);

	if (sizeof($m_sql))
	{
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql = 'INSERT INTO ' . MODERATOR_TABLE . ' (forum_id, user_id, username, group_id, groupname) 
					VALUES ' . implode(', ', preg_replace('#^(.*)$#', '(\1)',  $m_sql));
				$db->sql_query($sql);
				break;

			case 'mssql':
			case 'sqlite':
				$sql = 'INSERT INTO ' . MODERATOR_TABLE . ' (forum_id, user_id, username, group_id, groupname)
					 ' . implode(' UNION ALL ', preg_replace('#^(.*)$#', 'SELECT \1',  $m_sql));
				$db->sql_query($sql);
				break;

			default:
				foreach ($m_sql as $k => $sql)
				{
					$sql = 'INSERT INTO ' . MODERATOR_TABLE . " (forum_id, user_id, username, group_id, groupname) 
						VALUES ($sql)";
					$db->sql_query($sql);
				}
		}
	}
}

// Logging functions
function add_log()
{
	global $db, $user;

	$args = func_get_args();

	$mode		= array_shift($args);
	$forum_id	= ($mode == 'mod') ? intval(array_shift($args)) : '';
	$topic_id	= ($mode == 'mod') ? intval(array_shift($args)) : '';
	$action		= array_shift($args);
	$data		= (!sizeof($args)) ? '' : addslashes(serialize($args));

	switch ($mode)
	{
		case 'admin':
			$sql = "INSERT INTO " . LOG_TABLE . " (log_type, user_id, log_ip, log_time, log_operation, log_data)
				VALUES (" . LOG_ADMIN . ", " . $user->data['user_id'] . ", '$user->ip', " . time() . ", '$action', '$data')";
			break;
		
		case 'mod':
			$sql = "INSERT INTO " . LOG_TABLE . " (log_type, user_id, forum_id, topic_id, log_ip, log_time, log_operation, log_data)
				VALUES (" . LOG_MOD . ", " . $user->data['user_id'] . ", $forum_id, $topic_id, '$user->ip', " . time() . ", '$action', '$data')";
			break;

		case 'critical':
			$sql = "INSERT INTO " . LOG_TABLE . " (log_type, user_id, log_ip, log_time, log_operation, log_data)
				VALUES (" . LOG_CRITICAL . ", " . $user->data['user_id'] . ", '$user->ip', " . time() . ", '$action', '$data')";
			break;
		
		default:
			return;
	}

	$db->sql_query($sql);
	return;
}

function view_log($mode, &$log, &$log_count, $limit = 0, $offset = 0, $forum_id = 0, $topic_id = 0, $limit_days = 0, $sort_by = 'l.log_time DESC')
{
	global $db, $user, $auth, $phpEx, $SID;

	$topic_id_list = $is_auth = $is_mod = array();

	$profile_url = (defined('IN_ADMIN')) ? "admin_users.$phpEx$SID" : "memberlist.$phpEx$SID&amp;mode=viewprofile";

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

		$log[$i]['action'] = (!empty($user->lang[$row['log_operation']])) ? $user->lang[$row['log_operation']] : ucfirst(str_replace('_', ' ', $row['log_operation']));

		if (!empty($row['log_data']))
		{
			$log_data_ary = unserialize(stripslashes($row['log_data']));

			if (!empty($user->lang[$row['log_operation']]))
			{
				foreach ($log_data_ary as $log_data)
				{
					$log[$i]['action'] = preg_replace('#%s#', $log_data, $log[$i]['action'], 1);
				}
			}
			else
			{
				$log[$i]['action'] = implode('', $log_data_ary);
			}
		}

		$i++;
	}
	$db->sql_freeresult($result);

	if (count($topic_id_list))
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
				// DEBUG!!
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
			$log[$key]['viewtopic'] = (isset($is_auth[$row['topic_id']])) ? ((defined('IN_ADMIN')) ? '../' : '') . "viewtopic.$phpEx$SID&amp;f=" . $is_auth[$row['topic_id']] . '&amp;t=' . $row['topic_id'] : '';
			$log[$key]['viewlogs'] = (isset($is_mod[$row['topic_id']])) ? ((defined('IN_ADMIN')) ? '../' : '') . "mcp.$phpEx$SID&amp;mode=viewlogs&amp;t=" . $row['topic_id'] : '';
		}
	}

	$sql = "SELECT COUNT(*) AS total_entries
		FROM " . LOG_TABLE . " l
		WHERE l.log_type = $log_type
			AND l.log_time >= $limit_days
			$sql_forum";
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$log_count =  $row['total_entries'];

	return;
}

// Extension of auth class for changing permissions
if (class_exists('auth'))
{
	class auth_admin extends auth
	{
		// Set a user or group ACL record
		function acl_set($ug_type, &$forum_id, &$ug_id, &$auth)
		{
			global $db;

			// One or more forums
			if (!is_array($forum_id))
			{
				$forum_id = array($forum_id);
			}

			// Set any flags as required
			foreach ($auth as $auth_option => $setting)
			{
				$flag = substr($auth_option, 0, strpos($auth_option, '_') + 1);
				if (empty($auth[$flag]))
				{
					$auth[$flag] = $setting;
				}
			}

			$sql = 'SELECT auth_option_id, auth_option
				FROM ' . ACL_OPTIONS_TABLE;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$option_ids[$row['auth_option']] = $row['auth_option_id'];
			}
			$db->sql_freeresult($result);

			$sql_forum = 'AND a.forum_id IN (' . implode(', ', array_map('intval', $forum_id)) . ')';

			$sql = ($ug_type == 'user') ? 'SELECT o.auth_option_id, o.auth_option, a.forum_id, a.auth_setting FROM ' . ACL_USERS_TABLE . ' a, ' . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $sql_forum AND a.user_id = $ug_id" : 'SELECT o.auth_option_id, o.auth_option, a.forum_id, a.auth_setting FROM ' . ACL_GROUPS_TABLE . ' a, ' . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $sql_forum AND a.group_id = $ug_id";
			$result = $db->sql_query($sql);

			$cur_auth = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$cur_auth[$row['forum_id']][$row['auth_option_id']] = $row['auth_setting'];
			}
			$db->sql_freeresult($result);

			$table = ($ug_type == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
			$id_field  = $ug_type . '_id';

			$sql_ary = array();
			foreach ($forum_id as $forum)
			{
				foreach ($auth as $auth_option => $setting)
				{
					$auth_option_id = $option_ids[$auth_option];

					switch ($setting)
					{
						case ACL_UNSET:
							if (isset($cur_auth[$forum][$auth_option_id]))
							{
								$sql_ary['delete'][] = "DELETE FROM $table 
									WHERE forum_id = $forum
										AND auth_option_id = $auth_option_id
										AND $id_field = $ug_id";
							}
							break;

						default:
							if (!isset($cur_auth[$forum][$auth_option_id]))
							{
								$sql_ary['insert'][] = "$ug_id, $forum, $auth_option_id, $setting";
							}
							else if ($cur_auth[$forum][$auth_option_id] != $setting)
							{
								$sql_ary['update'][] = "UPDATE " . $table . " 
									SET auth_setting = $setting 
									WHERE $id_field = $ug_id 
										AND forum_id = $forum 
										AND auth_option_id = $auth_option_id";
							}
					}
				}
			}
			unset($cur_auth);

			$sql = '';
			foreach ($sql_ary as $sql_type => $sql_subary)
			{
				switch ($sql_type)
				{
					case 'insert':
						switch (SQL_LAYER)
						{
							case 'mysql':
							case 'mysql4':
								$sql = 'VALUES ' . implode(', ', preg_replace('#^(.*?)$#', '(\1)', $sql_subary));
								break;

							case 'mssql':
							case 'sqlite':
								$sql = implode(' UNION ALL ', preg_replace('#^(.*?)$#', 'SELECT \1', $sql_subary));
								break;

							default:
								foreach ($sql_subary as $sql)
								{
									$sql = "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_setting) VALUES ($sql)";
									$db->sql_query($sql);
									$sql = '';
								}
						}

						if ($sql != '')
						{
							$sql = "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_setting) $sql";
							$db->sql_query($sql);
						}
						break;

					case 'update':
					case 'delete':
						foreach ($sql_subary as $sql)
						{
							$result = $db->sql_query($sql);
							$sql = '';
						}
						break;
				}
				unset($sql_ary[$sql_type]);
			}
			unset($sql_ary);

			$this->acl_clear_prefetch();
		}

		function acl_delete($mode, &$forum_id, &$ug_id, $auth_ids = false)
		{
			global $db;

			// One or more forums
			if (!is_array($forum_id))
			{
				$forum_id = array($forum_id);
			}

			$auth_sql = ($auth_ids) ? ' AND auth_option_id IN (' . implode(', ', array_map('intval', $auth_ids)) . ')' : '';

			$table = ($mode == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
			$id_field  = $mode . '_id';

			foreach ($forum_id as $forum)
			{
				$sql = "DELETE FROM $table
					WHERE $id_field = $ug_id
						AND forum_id = $forum
						$auth_sql";
				$db->sql_query($sql);
			}

			$this->acl_clear_prefetch();
		}

		// Add a new option to the list ... $options is a hash of form ->
		// $options = array(
		//	'local'		=> array('option1', 'option2', ...),
		//	'global'	=> array('optionA', 'optionB', ...)
		//);
		function acl_add_option($options)
		{
			global $db;

			if (!is_array($new_options))
			{
				trigger_error('Incorrect parameter for acl_add_option', E_USER_ERROR);
			}

			$cur_options = array();

			$sql = "SELECT auth_option, is_global, is_local
				FROM " . ACL_OPTIONS_TABLE . "
				ORDER BY auth_option_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				if (!empty($row['is_global']))
				{
					$cur_options['global'][] = $row['auth_option'];
				}

				if (!empty($row['is_local']))
				{
					$cur_options['local'][] = $row['auth_option'];
				}
			}
			$db->sql_freeresult($result);

			if (!is_array($options))
			{
				trigger_error('Incorrect parameter for acl_add_option', E_USER_ERROR);
			}

			// Here we need to insert new options ... this requires discovering whether
			// an options is global, local or both and whether we need to add an option
			// type flag (x_)
			$new_options = array();
			foreach ($options as $type => $option_ary)
			{
				$option_ary = array_unique($option_ary);
				foreach ($option_ary as $option_value)
				{
					if (!in_array($option_value, $cur_options[$type]))
					{
						$new_options[$type][] = $option_value;
					}

					$flag = substr($option_value, 0, strpos($option_value, '_') + 1);
					if (!in_array($flag, $cur_options[$type]) && !in_array($flag, $new_options[$type]))
					{
						$new_options[$type][] = $flag;
					}
				}
			}
			unset($options);

			$options = array();
			$options['local'] = array_diff($new_options['local'], $new_options['global']);
			$options['global'] = array_diff($new_options['global'], $new_options['local']);
			$options['local_global'] = array_intersect($new_options['local'], $new_options['global']);

			$type_sql = array('local' => '0, 1', 'global' => '1, 0', 'local_global' => '1, 1');

			$sql = '';
			foreach ($options as $type => $option_ary)
			{
				foreach ($option_ary as $option)
				{
					switch (SQL_LAYER)
					{
						case 'mysql':
						case 'mysql4':
							$sql .= (($sql != '') ? ', ' : '') . "('$option', " . $type_sql[$type] . ")";
							break;
						case 'mssql':
							$sql .= (($sql != '') ? ' UNION ALL ' : '') . " SELECT '$option', " . $type_sql[$type];
							break;
						default:
							$sql = 'INSERT INTO ' . ACL_OPTIONS_TABLE . " (auth_option, is_global, is_local)
								VALUES ($option, " . $type_sql[$type] . ")";
							$db->sql_query($sql);
							$sql = '';
					}
				}
			}

			if ($sql != '')
			{
				$sql = 'INSERT INTO ' . ACL_OPTIONS_TABLE . " (auth_option, is_global, is_local)
					VALUES $sql";
				$db->sql_query($sql);
			}

			$cache->destroy('acl_options');
		}
	}
}



?>