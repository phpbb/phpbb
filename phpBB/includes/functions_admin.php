<?php
/***************************************************************************
 *                            functions_admin.php
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

// Simple version of jumpbox, just lists authed forums
function make_forum_select($forum_id = false, $ignore_forum = false, $add_select = true)
{
	global $db, $user, $auth;

	$sql = "SELECT forum_id, forum_name, left_id, right_id
		FROM " . FORUMS_TABLE . "
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql);

	$right = $cat_right = 0;
	$forum_list = $padding = $holding = '';
	
	while ($row = $db->sql_fetchrow($result))
	{
		if (!$auth->acl_gets('f_list', 'm_', 'a_', intval($row['forum_id'])) || $row['forum_id'] == $ignore_forum)
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

			$holding .= '<option class="sep" value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . '+ ' . $row['forum_name'] . '</option>';
		}
		else
		{
			$forum_list .= $holding . '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . '- ' . $row['forum_name'] . '</option>';
			$holding = '';
		}
	}

	if (!$right)
	{
		$forum_list .= '<option value="-1">' . $user->lang['NO_FORUMS'] . '</option>';
	}
	else if ($add_select)
	{
		$forum_list = '<option value="-1">' . $user->lang['SELECT_FORUM'] . '</option><option value="-1">-----------------</option>' . $forum_list;
	}

	$db->sql_freeresult($result);

	return $forum_list;
}

// Synchronise functions for forums/topics
function sync($type, $id)
{
	global $db;

	switch($type)
	{
		case 'all forums':
			$sql = "SELECT forum_id
				FROM " . FORUMS_TABLE;
			$result = $db->sql_query($sql);

			while( $row = $db->sql_fetchrow($result) )
			{
				sync('forum', $row['forum_id']);
			}
		   	break;

		case 'all topics':
			$sql = "SELECT topic_id
				FROM " . TOPICS_TABLE;
			$result = $db->sql_query($sql);

			while( $row = $db->sql_fetchrow($result) )
			{
				sync('topic', $row['topic_id']);
			}
			break;

	  	case 'forum':
			$sql = "SELECT MAX(p.post_id) AS last_post, COUNT(p.post_id) AS total
				FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE  . " t
				WHERE p.forum_id = $id
					AND t.topic_id = p.topic_id
					AND t.topic_status <> " . ITEM_MOVED;
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				$last_post = ( $row['last_post'] ) ? $row['last_post'] : 0;
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
					AND topic_status <> " . ITEM_MOVED;
			$result = $db->sql_query($sql);

			$total_topics = ( $row = $db->sql_fetchrow($result) ) ? ( ( $row['total'] ) ? $row['total'] : 0 ) : 0;

			$sql = "UPDATE " . FORUMS_TABLE . "
				SET forum_last_post_id = $last_post, forum_posts = $total_posts, forum_topics = $total_topics
				WHERE forum_id = $id";
			$db->sql_query($sql);
			break;

		case 'topic':
			$sql = "SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM " . POSTS_TABLE . "
				WHERE topic_id = $id";
			$result = $db->sql_query($sql);

			if ( $row = $db->sql_fetchrow($result) )
			{
				$sql = "UPDATE " . TOPICS_TABLE . "
					SET topic_replies = " . ( $row['total_posts'] - 1 ) . ", topic_first_post_id = " . $row['first_post'] . ", topic_last_post_id = " . $row['last_post'] . "
					WHERE topic_id = $id";
				$db->sql_query($sql);
			}

		case 'post':
			break;

			break;
	}

	return true;
}

function prune($forum_id, $prune_date, $sql_topics = '')
{
	global $db, $lang, $phpEx, $phpbb_root_path;

	if ($sql_topics = '')
	{
		// Those without polls ...
		$sql = "SELECT t.topic_id
			FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t
			WHERE t.forum_id = $forum_id
				AND t.topic_vote = 0
				AND t.topic_type <> " . POST_ANNOUNCE . "
				AND (p.post_id = t.topic_last_post_id
					OR t.topic_last_post_id = 0)";
		if ($prune_date != '')
		{
			$sql .= " AND p.post_time < $prune_date";
		}
		$result = $db->sql_query($sql);

		$sql_topics = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_topics .= (($sql_topics != '') ? ', ' : '') . $row['topic_id'];
		}
		$db->sql_freeresult($result);
	}

	if ($sql_topics != '')
	{
		$sql = "SELECT post_id
			FROM " . POSTS_TABLE . "
			WHERE forum_id = $forum_id
				AND topic_id IN ($sql_topics)";
		$result = $db->sql_query($sql);

		$sql_posts = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$sql_posts .= (($sql_posts != '') ? ', ' : '') . $row['post_id'];
		}

		if ($sql_post != '')
		{
			$db->sql_transaction();

			$sql = "DELETE FROM " . TOPICS_WATCH_TABLE . " 
				WHERE topic_id IN ($sql_topics)";
			$db->sql_query($sql);

			$sql = "DELETE FROM " . TOPICS_TABLE . "
				WHERE topic_id IN ($sql_topics)";
			$db->sql_query($sql);

			$pruned_topics = $db->sql_affectedrows();

			$sql = "DELETE FROM " . POSTS_TABLE . "
				WHERE post_id IN ($sql_posts)";
			$db->sql_query($sql);

			$pruned_posts = $db->sql_affectedrows();

/*			$sql = "DELETE FROM " . POSTS_TEXT_TABLE . "
				WHERE post_id IN ($sql_posts)";
			$db->sql_query($sql);*/

			$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "
				WHERE post_id IN ($sql_posts)";
			$db->sql_query($sql);

			sync('forum', $forum_id);

			$db->sql_transaction('commit');

			return array ('topics' => $pruned_topics, 'posts' => $pruned_posts);
		}
	}

	return array('topics' => 0, 'posts' => 0);
}

// Function auto_prune(), this function will read the configuration data from
// the auto_prune table and call the prune function with the necessary info.
function auto_prune($forum_id = 0)
{
	global $db, $lang;

	$sql = "SELECT prune_freq, prune_days
		FROM " . FORUMS_TABLE . "
		WHERE forum_id = $forum_id";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		if ($row['prune_freq'] && $row['prune_days'])
		{
			$prune_date = time() - ($row['prune_days'] * 86400);
			$next_prune = time() + ($row['prune_freq'] * 86400);

			prune($forum_id, $prune_date);

			$sql = "UPDATE " . FORUMS_TABLE . "
				SET prune_next = $next_prune
				WHERE forum_id = $forum_id";
			$db->sql_query($sql);
		}
	}

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
		if (preg_match('/^\/\*/', preg_quote($lines[$i])))
		{
			$in_comment = true;
		}

		if (!$in_comment)
		{
			$output .= $lines[$i] . "\n";
		}

		if (preg_match('/\*\/$/', preg_quote($lines[$i])))
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
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes,
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

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
					$total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
					// Counts single quotes that are preceded by an odd number of backslashes,
					// which means they're escaped quotes.
					$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

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

// Cache moderators, called whenever permissions are
// changed via admin_permissions. Changes of username
// and group names must be carried through for the
// moderators table
function cache_moderators()
{
	global $db;

	// Clear table
	$db->sql_query('TRUNCATE ' . MODERATOR_TABLE);

	// Holding array
	$m_sql = array();
	$user_id_sql = '';

	$sql = "SELECT a.forum_id, u.user_id, u.username
		FROM  " . ACL_OPTIONS_TABLE . "  o, " . ACL_USERS_TABLE . " a,  " . USERS_TABLE . "  u
		WHERE o.auth_value = 'm_'
			AND a.auth_option_id = o.auth_option_id
			AND a.auth_allow_deny = 1
			AND u.user_id = a.user_id";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$m_sql['f_' . $row['forum_id'] . '_u_' . $row['user_id']] = $row['forum_id'] . ', ' . $row['user_id'] . ', \'' . $row['username'] . '\', NULL, NULL';
		$user_id_sql .= (($user_id_sql) ? ', ' : '') . $row['user_id'];
	}
	$db->sql_freeresult($result);

	// Remove users who have group memberships with DENY moderator permissions
	if ($user_id_sql)
	{
		$sql = "SELECT a.forum_id, ug.user_id
			FROM  " . ACL_OPTIONS_TABLE . "  o, " . ACL_GROUPS_TABLE . " a,  " . USER_GROUP_TABLE . "  ug
			WHERE o.auth_value = 'm_'
				AND a.auth_option_id = o.auth_option_id
				AND a.auth_allow_deny = 0
				AND a.group_id = ug.group_id
				AND ug.user_id IN ($user_id_sql)";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			unset($m_sql['f_' . $row['forum_id'] . '_u_' . $row['user_id']]);
		}
		$db->sql_freeresult($result);
	}

	$sql = "SELECT a.forum_id, g.group_name, g.group_id
		FROM  " . ACL_OPTIONS_TABLE . "  o, " . ACL_GROUPS_TABLE . " a,  " . GROUPS_TABLE . "  g
		WHERE o.auth_value = 'm_'
			AND a.auth_option_id = o.auth_option_id
			AND a.auth_allow_deny = 1
			AND g.group_id = a.group_id
			AND g.group_type NOT IN (" . GROUP_HIDDEN . ", " . GROUP_SPECIAL . ")";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$m_sql['f_' . $row['forum_id'] . '_g_' . $row['group_id']] = $row['forum_id'] . ', NULL, NULL, ' . $row['group_id'] . ', \'' . $row['group_name'] . '\'';
	}
	$db->sql_freeresult($result);

	if (sizeof($m_sql))
	{
		switch (SQL_LAYER)
		{
			case 'mysql':
			case 'mysql4':
				$sql = 'INSERT INTO ' . MODERATOR_TABLE . ' (forum_id, user_id, username, group_id, groupname) VALUES ' . implode(', ', preg_replace('#^(.*)$#', '(\1)',  $m_sql));
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				break;

			case 'mssql':
				$sql = 'INSERT INTO ' . MODERATOR_TABLE . ' (forum_id, user_id, username, group_id, groupname)
					VALUES ' . implode(' UNION ALL ', preg_replace('#^(.*)$#', 'SELECT \1',  $m_sql));
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
				break;

			default:
				foreach ($m_sql as $k => $sql)
				{
					$result = $db->sql_query('INSERT INTO ' . MODERATOR_TABLE . " (forum_id, user_id, username, group_id, groupname) VALUES ($sql)");
					$db->sql_freeresult($result);
				}
		}
	}
}

// Extension of auth class for changing permissions
class auth_admin extends auth
{
	// Set a user or group ACL record
	function acl_set($mode, &$forum_id, &$ug_id, &$auth)
	{
		global $db;

		// Set any flags as required
		foreach ($auth as $auth_value => $allow)
		{
			$flag = substr($auth_value, 0, strpos($auth_value, '_') + 1);
			if (empty($auth[$flag]))
			{
				$auth[$flag] = $allow;
			}
		}

		$sql = "SELECT auth_option_id, auth_value
			FROM " . ACL_OPTIONS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$option_ids[$row['auth_value']] = $row['auth_option_id'];
		}
		$db->sql_freeresult($result);

		// One or more forums
		if (!is_array($forum_id))
		{
			$forum_id = array($forum_id);
		}
		// NOTE THIS USED TO BE IN ($forum_id, 0) ...
		$forum_sql = 'AND a.forum_id IN (' . implode(', ', $forum_id) . ')';

		$sql = ($mode == 'user') ? "SELECT o.auth_option_id, o.auth_value, a.forum_id, a.auth_allow_deny FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $forum_sql AND a.user_id = $ug_id" :"SELECT o.auth_option_id, o.auth_value, a.forum_id, a.auth_allow_deny FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $forum_sql AND a.group_id = $ug_id";
		$result = $db->sql_query($sql);

		$cur_auth = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$cur_auth[$row['forum_id']][$row['auth_option_id']] = $row['auth_allow_deny'];
		}
		$db->sql_freeresult($result);

		$table = ($mode == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
		$id_field  = $mode . '_id';

		foreach ($forum_id as $forum)
		{
			foreach ($auth as $auth_value => $allow)
			{
				$auth_option_id = $option_ids[$auth_value];

				if (!empty($cur_auth[$forum]))
				{
					$sql_ary[] = (!isset($cur_auth[$forum][$auth_option_id])) ? "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_allow_deny) VALUES ($ug_id, $forum, $auth_option_id, $allow)" : (($cur_auth[$forum][$auth_option_id] != $allow) ? "UPDATE " . $table . " SET auth_allow_deny = $allow WHERE $id_field = $ug_id AND forum_id = $forum AND auth_option_id = $auth_option_id" : '');
				}
				else
				{
					$sql_ary[] = "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_allow_deny) VALUES ($ug_id, $forum, $auth_option_id, $allow)";
				}
			}
		}
		unset($forum_id);
		unset($user_auth);

		foreach ($sql_ary as $sql)
		{
			if ($sql != '')
			{
				$result = $db->sql_query($sql);
				$db->sql_freeresult($result);
			}
		}
		unset($sql_ary);

		$this->acl_clear_prefetch();
	}

	function acl_delete($mode, &$forum_id, &$ug_id, $auth_ids = false)
	{
		global $db;

		$auth_sql = '';
		if ($auth_ids)
		{
			for($i = 0; $i < count($auth_ids); $i++)
			{
				$auth_sql .= (($auth_sql != '') ? ', ' : '') . intval($auth_ids[$i]);
			}
			$auth_sql = " AND auth_option_id IN ($auth_sql)";
		}

		$table = ($mode == 'user') ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
		$id_field  = $mode . '_id';

		$sql = "DELETE FROM $table
			WHERE $id_field = $ug_id
				AND forum_id = $forum_id
				$auth_sql";
		$db->sql_query($sql);

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

		$sql = "SELECT auth_value, is_global, is_local
			FROM " . ACL_OPTIONS_TABLE . "
			ORDER BY auth_option_id";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if (!empty($row['is_global']))
			{
				$cur_options['global'][] = $row['auth_value'];
			}
			if (!empty($row['is_local']))
			{
				$cur_options['local'][] = $row['auth_value'];
			}
		}
		$db->sql_freeresult($result);

		if (!is_array($options))
		{
			trigger_error('Incorrect parameter for acl_add_option', E_USER_ERROR);
		}

		// Here we need to insert new options ... this requires
		// discovering whether an options is global, local or both
		// and whether we need to add an option type flag (x_)
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
						$sql = "INSERT INTO " . ACL_OPTIONS_TABLE . " (auth_value, is_global, is_local)
							VALUES ($option, " . $type_sql[$type] . ")";
						$result = $db->sql_query($sql);
						$db->sql_freeresult($result);
						$sql = '';
				}
			}
		}

		if ($sql != '')
		{
			$sql = "INSERT INTO " . ACL_OPTIONS_TABLE . " (auth_value, is_global, is_local)
				VALUES $sql";
			$result = $db->sql_query($sql);
		}

		$cache->destroy('acl_options');
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

	if ($mode == 'admin')
	{
		$sql = 'INSERT INTO ' . LOG_ADMIN_TABLE . ' (user_id, log_ip, log_time, log_operation, log_data)
				VALUES (' . $user->data['user_id'] . ", '$user->ip', " . time() . ", '$action', '$data')";
	}
	else
	{
		$sql = 'INSERT INTO ' . LOG_MOD_TABLE . ' (user_id, forum_id, topic_id, log_ip, log_time, log_operation, log_data)
				VALUES (' . $user->data['user_id'] . ", $forum_id, $topic_id, '$user->ip', " . time() . ", '$action', '$data')";
	}

	$db->sql_query($sql);
	return;
}

function view_log($mode, &$log, &$log_count, $limit = 0, $offset = 0, $forum_id = 0, $limit_days = 0, $sort_by = 'l.log_time DESC')
{
	global $db, $user, $phpEx, $SID;

	$table_sql = ($mode == 'admin') ? LOG_ADMIN_TABLE : LOG_MOD_TABLE;
	$forum_sql = ($mode == 'mod' && $forum_id) ? "AND l.forum_id = $forum_id" : '';
	$limit_sql = ($limit) ? (($offset) ? "LIMIT $offset, $limit" : "LIMIT $limit") : '';

	$sql = "SELECT l.log_id, l.user_id, l.log_ip, l.log_time, l.log_operation, l.log_data, u.username
		FROM $table_sql l, " . USERS_TABLE . " u
		WHERE u.user_id = l.user_id
			AND l.log_time >= $limit_days
			$forum_sql
		ORDER BY $sort_by
		$limit_sql";
	$result = $db->sql_query($sql);

	$log = array();
	if ($row = $db->sql_fetchrow($result))
	{
		$i = 0;
		do
		{
			$log[$i]['id'] = $row['log_id'];
			$log[$i]['username'] = '<a href="admin_users.'.$phpEx . $SID . '&amp;u=' . $row['user_id'] . '">' . $row['username'] . '</a>';
			$log[$i]['ip'] = $row['log_ip'];
			$log[$i]['time'] = $row['log_time'];

			$log[$i]['action'] = (!empty($user->lang[$row['log_operation']])) ? $user->lang[$row['log_operation']] : ucfirst(str_replace('_', ' ', $row['log_operation']));

			if (!empty($row['log_data']))
			{
				$log_data_ary = unserialize(stripslashes($row['log_data']));

				foreach ($log_data_ary as $log_data)
				{
					$log[$i]['action'] = preg_replace('#%s#', $log_data, $log[$i]['action'], 1);
				}
			}

			$i++;
		}
		while ($row = $db->sql_fetchrow($result));
	}

	$db->sql_freeresult($result);

	$sql = "SELECT COUNT(*) AS total_entries
		FROM $table_sql l
		WHERE l.log_time >= $limit_days
			$forum_sql";
	$result = $db->sql_query($sql);

	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	$log_count =  $row['total_entries'];

	return;
}

// Event system
// Outputs standard event definition table, storing passed
// data in hidden fields
function event_define()
{
	global $phpEx, $db, $auth, $user;

	$arguments = func_get_args();

	$form_action = array_shift($arguments);

	$s_hidden_fields = '';
	foreach ($arguments as $arg)
	{
		foreach ($arg as $name => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $sub_name => $sub_value)
				{
					$s_hidden_fields .= '<input type="hidden" name="' . $name . '[' . $sub_name .']" value="' . $sub_value . '" />';
				}
			}
			else
			{
				$s_hidden_fields .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
			}
		}
	}
	unset($arguments);

	$on_select = '<select name="evt_on[]">';
	$on_list = array('days' => 'DAYS_REG', 'posts' => 'POST_COUNT', 'karma' => 'KARMA');
	foreach ($on_list as $value => $text)
	{
		$on_select .= '<option value="' . $value . '">' . $user->lang['EVT_' . $text] . '</option>';
	}
	$on_select .= '</select>';

	$andor_select = '<select name="evt_andor[]"><option value="">-----</option>';
	$andor_list = array('and' => 'AND', 'or' => 'OR');
	foreach ($andor_list as $value => $text)
	{
		$andor_select .= '<option value="' . $value . '">' . $user->lang['EVT_' . $text] . '</option>';
	}
	$andor_select .= '</select>';

	$cond_select = '<select name="evt_cond[]">';
	$cond_list = array('lt' => '&lt;', 'eq' => '=', 'gt' => '&gt;');
	foreach ($cond_list as $value => $text)
	{
		$cond_select .= '<option value="' . $value . '">' . $text . '</option>';
	}
	$cond_select .= '</select>';

	$forum_list = '<option value="">---------------</option>' . make_forum_select(false, false, false);

	page_header($user->lang['EVT_DEFINE']);

?>

<h1><?php echo $user->lang['EVT_DEFINE']; ?></h1>

<p><?php echo $user->lang['EVT_DEFINE_EXPLAIN']; ?></p>

<form action="<?php echo $form_action . '.' . $phpEx; ?>" method="post"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2">&nbsp;</th>
	</tr>
	<tr>
		<td class="row2"><table width="100%" cellspacing="5" cellpadding="0" border="0">
			<tr>
				<td></td>
				<td><?php echo $on_select; ?></td>
				<td><?php echo $cond_select; ?></td>
				<td><input type="text" name="evt_value[]" size="4" /></td>
				<td><?php echo $user->lang['EVT_IN']; ?></td>
				<td><select name="evt_f[]"><?php echo $forum_list; ?></select></td>
			</tr>
			<tr>
				<td><?php echo $andor_select; ?></td>
				<td><?php echo $on_select; ?></td>
				<td><?php echo $cond_select; ?></td>
				<td><input type="text" name="evt_value[]" size="4" /></td>
				<td><?php echo $user->lang['EVT_IN']; ?></td>
				<td><select name="evt_f[]"><?php echo $forum_list; ?></select></td>
			</tr>
			<tr>
				<td><?php echo $andor_select; ?></td>
				<td><?php echo $on_select; ?></td>
				<td><?php echo $cond_select; ?></td>
				<td><input type="text" name="evt_value[]" size="4" /></td>
				<td><?php echo $user->lang['EVT_IN']; ?></td>
				<td><select name="evt_f[]"><?php echo $forum_list; ?></select></td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="hidden" name="runas" value="evt" /><input class="mainoption" type="submit" name="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp; <input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table>

<?php echo $s_hidden_fields; ?></form>

<?php

	page_footer();

	exit;
}

// Takes input data and creates relevant Event
function event_create()
{
	global $phpEx, $db, $auth, $user;

	$arguments = func_get_args();

	$evt_code = array_shift($arguments);
	$type = array_shift($arguments);
	$type_ids = array_shift($arguments);

	$evt_data = '';
	foreach ($arguments as $arg)
	{
		foreach ($arg as $name => $value)
		{
			if (is_array($value))
			{
				$evt_data .= "\$evt_$name = array();";
				foreach ($value as $sub_name => $sub_value)
				{
					$evt_data .= '$evt_' . '$name[\'' . $sub_name . '\'] = "' . $sub_value .'";'; // Don't alter this line!
				}
			}
			else
			{
				$evt_data .= "\$evt_$name = \"$value\";";
			}
		}
	}
	unset($arguments);

	$event_sql = $having_sql = '';
	$evt_andor = $evt_cond = $evt_on = $evt_value = '';
	for ($i = 0; $i < sizeof($_POST['evt_on']); $i++)
	{
		if (empty($_POST['evt_on'][$i]) || empty($_POST['evt_value'][$i]))
		{
			continue;
		}

		switch ($_POST['evt_andor'][$i - 1])
		{
			case 'or':
				$event_sql .= ' OR ';
				$evt_andor .= 'or,';
				break;
			case 'and':
				$event_sql .= ' AND ';
				$evt_andor .= 'and,';
				break;
			default:
				$event_sql .= ' AND (';
				$evt_andor .= 'and,';
		}

		switch ($_POST['evt_cond'][$i])
		{
			case 'lt':
				$event_cond_sql = ($_POST['evt_on'][$i] == 'days') ? '>' : '<';
				break;
			case 'eq':
				$event_cond_sql = '=';
				break;
			case 'gt':
				$event_cond_sql = ($_POST['evt_on'][$i] == 'days') ? '<' : '>';
				break;
		}
		$evt_cond .= $_POST['evt_cond'][$i] . ',';

		switch ($_POST['evt_on'][$i])
		{
			case 'days':
				$event_sql .= 'u.user_regdate ' . $event_cond_sql . ' \' . (time() - ' . (intval($_POST['evt_value'][$i]) * 3600 * 24) . ') . \' ';
				break;

			case 'posts':
				if (empty($_POST['evt_f'][$i]))
				{
					$event_sql .= 'u.post_count ' . $event_cond_sql . ' ' . intval($_POST['evt_value'][$i]) . ' ';
				}
				else
				{
					$event_sql .= '(p.poster_id = u.user_id AND p.forum_id = ' . intval($_POST['evt_f'][$i]) . ') ';
					$having_sql = ' GROUP BY p.poster_id HAVING COUNT(p.post_id) > ' . intval($_POST['evt_value'][$i]);
					$from_sql = ', \' . POSTS_TABLE . \' p';
				}
				break;

			case 'karma':
				$event_sql .= 'u.user_karma ' . $event_cond_sql . ' ' . intval($_POST['evt_value'][$i]) . ' ';
				break;

		}
		$evt_on .= $_POST['evt_on'][$i] . ','; 
		$evt_value .= $_POST['evt_value'][$i] . ',';
	}

	$sql = 'SELECT u.user_id FROM \' . USERS_TABLE . \' u' . $from_sql;
	switch ($type)
	{
		case 'user':
			$sql .= ' WHERE u.user_id IN (' . implode(', ', preg_replace('#^[\s]*?([0-9])+[\s]*?$#', '\1', $type_ids)) . ')';
			break;

		case 'group':
			$sql .= ', \' . USER_GROUP_TABLE . \' ug WHERE ug.group_id IN (' . implode(', ', preg_replace('#^[\s]*?([0-9]+)[\s]*?$#', '\1', $type_ids)) . ') AND u.user_id = ug.user_id';
			break;
	}

	$evt_sql = "\$sql = '" . $sql . $event_sql . " ) " . $having_sql . "';";

	$sql = "INSERT INTO phpbb_22x_events (event_type, event_trigger, event_cond, event_value, event_combine, event_sql, event_code, event_data) VALUES ('$type', '$evt_on', '$evt_cond', '$evt_value', '$evt_andor', '" . $db->sql_escape($evt_sql) . "', '" . $db->sql_escape($evt_code) . "', '" . $db->sql_escape($evt_data) . "')";
	$db->sql_query($sql);

	trigger_error($user->lang['EVT_CREATED']);
}

function event_execute($mode)
{
	global $db;

	$sql = "SELECT event_sql, event_code, event_data 
		FROM phpbb_22x_events 
		WHERE event_trigger LIKE '%$mode%'";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		$event_sql = $event_data = $event_code = array();
		do
		{
			$db->sql_return_on_error(true);
			if (empty($row['event_sql']) || empty($row['event_data']) || empty($row['event_code']))
			{
				continue;
			}

			$sql = '';
			eval($row['event_sql']);
			$evt_result = $db->sql_query($sql);

			if ($evt_row = $db->sql_fetchrow($evt_result))
			{
				$user_id_ary = array();

				do
				{
					$user_id_ary[] = $evt_row['user_id'];
				}
				while ($evt_row = $db->sql_fetchrow($evt_result));
				unset($evt_row);

//				eval($row['event_data']);
//				eval($row['event_code']);
			}
			$db->sql_freeresult($evt_result);
			$db->sql_return_on_error(false);
		}
		while ($row = $db->sql_fetchrow($result));

	}
	$db->sql_freeresult($result);

	return;
}

?>