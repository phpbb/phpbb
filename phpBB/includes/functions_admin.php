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

			$holding .= '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . '+ ' . $row['forum_name'] . '</option>';
		}
		else
		{
			$forum_list .= $holding . '<option value="' . $row['forum_id'] . '"' . $selected . '>' . $padding . '- ' . $row['forum_name'] . '</option>';
			$holding = '';
		}
	}

	if (!$right)
	{
		$forum_list .= '<option value="-1">' . $user->lang['No_forums'] . '</option>';
	}
	else if ($add_select)
	{
		$forum_list = '<option value="-1">' . $user->lang['Select_forum'] . '</option><option value="-1">-----------------</option>' . $forum_list;
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

function prune($forum_id, $prune_date)
{
	global $db, $lang, $phpEx, $phpbb_root_path;

	// Those without polls ...
	$sql = "SELECT t.topic_id
		FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t
		WHERE t.forum_id = $forum_id
			AND t.topic_vote = 0
			AND t.topic_type <> " . POST_ANNOUNCE . "
			AND ( p.post_id = t.topic_last_post_id
				OR t.topic_last_post_id = 0 )";
	if ( $prune_date != '' )
	{
		$sql .= " AND p.post_time < $prune_date";
	}
	$result = $db->sql_query($sql);

	$sql_topics = '';
	while ( $row = $db->sql_fetchrow($result) )
	{
		$sql_topics .= ( ( $sql_topics != '' ) ? ', ' : '' ) . $row['topic_id'];
	}

	if ( $sql_topics != '' )
	{
		$sql = "SELECT post_id
			FROM " . POSTS_TABLE . "
			WHERE forum_id = $forum_id
				AND topic_id IN ($sql_topics)";
		$result = $db->sql_query($sql);

		$sql_post = '';
		while ( $row = $db->sql_fetchrow($result) )
		{
			$sql_post .= ( ( $sql_post != '' ) ? ', ' : '' ) . $row['post_id'];
		}

		if ( $sql_post != '' )
		{
			$db->sql_transaction();

			$sql = "DELETE FROM " . TOPICS_TABLE . "
				WHERE topic_id IN ($sql_topics)";
			$db->sql_query($sql);

			$pruned_topics = $db->sql_affectedrows();

			$sql = "DELETE FROM " . POSTS_TABLE . "
				WHERE post_id IN ($sql_post)";
			$db->sql_query($sql);

			$pruned_posts = $db->sql_affectedrows();

			$sql = "DELETE FROM " . POSTS_TEXT_TABLE . "
				WHERE post_id IN ($sql_post)";
			$db->sql_query($sql);

			$sql = "DELETE FROM " . SEARCH_MATCH_TABLE . "
				WHERE post_id IN ($sql_post)";
			$db->sql_query($sql);

			remove_search_post($sql_post);

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

	if ( $row = $db->sql_fetchrow($result) )
	{
		if ( $row['prune_freq'] && $row['prune_days'] )
		{
			$prune_date = time() - ( $row['prune_days'] * 86400 );
			$next_prune = time() + ( $row['prune_freq'] * 86400 );

			prune($forum_id, $prune_date);
			sync('forum', $forum_id);

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
		if ( preg_match('/^\/\*/', preg_quote($lines[$i])) )
		{
			$in_comment = true;
		}

		if ( !$in_comment )
		{
			$output .= $lines[$i] . "\n";
		}

		if ( preg_match('/\*\/$/', preg_quote($lines[$i])) )
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
		if ( $i != $linecount - 1 || strlen($lines[$i]) > 0 )
		{
			$output .= ( $lines[$i][0] != '#' ) ? $lines[$i] . "\n" : "\n";
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
		if ( $i != $token_count - 1 || strlen($tokens[$i] > 0) )
		{
			// This is the total number of single quotes in the token.
			$total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
			// Counts single quotes that are preceded by an odd number of backslashes,
			// which means they're escaped quotes.
			$escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

			$unescaped_quotes = $total_quotes - $escaped_quotes;

			// If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
			if ( !($unescaped_quotes % 2) )
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

					if ( ($unescaped_quotes % 2) == 1 )
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

// Rebuild board_config array in cache file
function config_config($config = false)
{
	global $db, $phpbb_root_path, $phpEx;

	if ( !$config )
	{
		$config = array();

		$sql = "SELECT *
			FROM " . CONFIG_TABLE . "
			WHERE is_dynamic <> 1";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$config[$row['config_name']] = $row['config_value'];
		}
	}

	$cache_str = "\$config = array(\n";
	foreach ($config as $config_name => $config_value)
	{
		$cache_str .= "\t'$config_name' => " . ( ( is_numeric($config_value) ) ? $config_value : '"' . addslashes($config_value) . '"' ) . ",\n";
	}
	$cache_str .= ");";

	config_cache_write('\$config = array\(.*?\);', $cache_str);

	return $config;
}

// Update config cache file
function config_cache_write($match, $data)
{
	global $phpbb_root_path, $phpEx, $user;

	if (!is_writeable($phpbb_root_path . 'config_cache.'.$phpEx))
	{
		trigger_error($user->lang['Cache_writeable']);
	}

	if (!($fp = @fopen($phpbb_root_path . 'config_cache.'.$phpEx, 'r+')))
	{
		trigger_error('Failed opening config_cache. Please ensure the file exists', E_USER_ERROR);
	}

	$config_file = fread($fp, filesize($phpbb_root_path . 'config_cache.'.$phpEx));

	fseek($fp, 0);
	@flock($fp, LOCK_EX);
	if (!fwrite($fp, preg_replace('#' . $match . '#s', $data, $config_file)))
	{
		trigger_error('Could not write out config data to cache', E_USER_ERROR);
	}
	@flock($fp, LOCK_UN);
	fclose($fp);

	return;
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
			if ( empty($auth[$flag]) )
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
		if ( !is_array($forum_id) )
		{
			$forum_id = array($forum_id);
		}
		// NOTE THIS USED TO BE IN ($forum_id, 0) ...
		$forum_sql = 'AND a.forum_id IN (' . implode(', ', $forum_id) . ')';

		$sql = ( $mode == 'user' ) ? "SELECT o.auth_option_id, o.auth_value, a.forum_id, a.auth_allow_deny FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $forum_sql AND a.user_id = $ug_id" :"SELECT o.auth_option_id, o.auth_value, a.forum_id, a.auth_allow_deny FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o WHERE a.auth_option_id = o.auth_option_id $forum_sql AND a.group_id = $ug_id";
		$result = $db->sql_query($sql);

		$cur_auth = array();
		while ( $row = $db->sql_fetchrow($result) )
		{
			$cur_auth[$row['forum_id']][$row['auth_option_id']] = $row['auth_allow_deny'];
		}
		$db->sql_freeresult($result);

		$table = ( $mode == 'user' ) ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
		$id_field  = $mode . '_id';

		foreach ( $forum_id as $forum)
		{
			foreach ( $auth as $auth_value => $allow )
			{
				$auth_option_id = $option_ids[$auth_value];

				if ( !empty($cur_auth[$forum]) )
				{
					$sql_ary[] = ( !isset($cur_auth[$forum][$auth_option_id]) ) ? "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_allow_deny) VALUES ($ug_id, $forum, $auth_option_id, $allow)" : ( ( $cur_auth[$forum][$auth_option_id] != $allow ) ? "UPDATE " . $table . " SET auth_allow_deny = $allow WHERE $id_field = $ug_id AND forum_id = $forum AND auth_option_id = $auth_option_id" : '' );
				}
				else
				{
					$sql_ary[] = "INSERT INTO $table ($id_field, forum_id, auth_option_id, auth_allow_deny) VALUES ($ug_id, $forum, $auth_option_id, $allow)";
				}
			}
		}
		unset($forum_id);
		unset($user_auth);

		foreach ( $sql_ary as $sql )
		{
			if ( $sql != '' )
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
				$auth_sql .= ( ( $auth_sql != '' ) ? ', ' : '' ) . $auth_ids[$i];
			}
			$auth_sql = " AND auth_option_id IN ($auth_sql)";
		}

		$table = ( $mode == 'user' ) ? ACL_USERS_TABLE : ACL_GROUPS_TABLE;
		$id_field  = $mode . '_id';

		$sql = "DELETE FROM $table
			WHERE $id_field = $ug_id
				AND forum_id = $forum_id
				$auth_sql";
		$db->sql_query($sql);

		$this->acl_clear_prefetch();
	}

	function acl_clear_prefetch($user_id = false)
	{
		global $db;

		$where_sql = ( $user_id ) ? "WHERE user_id = $user_id" : '';

		$sql = "UPDATE " . USERS_TABLE . "
			SET user_permissions = ''
			$where_sql";
		$db->sql_query($sql);

		return;
	}

	// Add a new option to the list ... $options is a hash of form ->
	// $options = array(
	//	'local'		=> array('option1', 'option2', ...),
	//	'global'	=> array('optionA', 'optionB', ...)
	// );
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
				ORDER BY is_global, is_local, auth_value";
		$result = $db->sql_query($sql);

		while ( $row = $db->sql_fetchrow($result) )
		{
			if ( !empty($row['is_global']) )
			{
				$cur_options['global'][] = $row['auth_value'];
			}
			if ( !empty($row['is_local']) )
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
						$sql .= ( ($sql != '') ? ', ' : '' ) . "('$option', " . $type_sql[$type] . ")";
						break;
					case 'mssql':
						$sql .= ( ($sql != '') ? ' UNION ALL ' : '' ) . " SELECT '$option', " . $type_sql[$type];
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

		if ( $sql != '' )
		{
			$sql = "INSERT INTO " . ACL_OPTIONS_TABLE . " (auth_value, is_global, is_local)
				VALUES $sql";
			$result = $db->sql_query($sql);
		}

		$this->acl_cache_options($options);
	}

	function acl_cache_options($options = false)
	{
		global $db;

		$options = array();

		if ( !$options )
		{
			$sql = "SELECT auth_value, is_global, is_local
				FROM " . ACL_OPTIONS_TABLE . "
				ORDER BY is_global, is_local, auth_value";
			$result = $db->sql_query($sql);

			$global = $local = 0;
			while ( $row = $db->sql_fetchrow($result) )
			{
				if ( !empty($row['is_global']) )
				{
					$options['global'][$row['auth_value']] = $global++;
				}
				if ( !empty($row['is_local']) )
				{
					$options['local'][$row['auth_value']] = $local++;
				}
			}
			$db->sql_freeresult($result);
		}

		// Re-cache options
		$cache_str = "\$acl_options = array(\n";
		foreach ($options as $type => $options_ary)
		{
			$cache_str .= "\t'$type' => array(\n";
			foreach ($options_ary as $option_value => $option_id)
			{
				$cache_str .= "\t\t'$option_value' => " . $option_id . ",\n";
			}
			$cache_str .= "\t),\n";
		}
		$cache_str .= ");";

		config_cache_write('\$acl_options = array\(.*?\);', $cache_str);
		$this->acl_clear_prefetch();

		return $options;
	}
}

?>