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
function make_forum_select($box_name, $ignore_forum = false)
{
	global $db, $userdata, $auth, $lang;

	$sql = "SELECT forum_id, forum_name, left_id, right_id
		FROM " . FORUMS_TABLE . "
		ORDER BY left_id ASC";
	$result = $db->sql_query($sql);

	$right = 0;
	$subforum = '';
	$forum_list = '';
	while ( $row = $db->sql_fetchrow($result) )
	{
		if ( ( $auth->acl_get('f_list', $forum_id) || $auth->acl_get('a_') ) && $ignore_forum != $row['forum_id'] )
		{
			if ( $row['left_id'] < $right  )
			{
				$subforum .= '&nbsp;&nbsp;&nbsp;';
			}
			else if ( $row['left_id'] > $right + 1 )
			{
				$subforum = substr($subforum, 0, -18 * ( $row['left_id'] - $right + 1 ));
			}

			$forum_list .= '<option value="' . $row['forum_id'] . '">' . $subforum . $row['forum_name'] . '</option>';

			$right = $row['right_id'];
		}

	}
	$db->sql_freeresult($result);

	$forum_list = ( $forum_list == '' ) ? '<option value="-1">' . $lang['No_forums'] . '</option>' : '<select name="' . $box_name . '">' . $forum_list . '</select>';

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

	require_once($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

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

// Extension of auth class for changing permissions
class auth_admin extends auth
{
	function acl_set_user(&$forum_id, &$user_id, &$auth)
	{
		global $db;

		$forum_sql = ( $forum_id ) ? "AND a.forum_id IN ($forum_id, 0)" : '';

		$sql = "SELECT o.auth_option_id, o.auth_value, a.auth_allow_deny
			FROM " . ACL_USERS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
			WHERE a.auth_option_id = o.auth_option_id
				$forum_sql
				AND a.user_id = $user_id";
		$result = $db->sql_query($sql);

		$user_auth = array();
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$user_auth[$user_id][$row['auth_option_id']] = $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		foreach ( $auth as $auth_option_id => $allow )
		{
			if ( !empty($user_auth) )
			{
				foreach ( $user_auth as $user => $user_auth_ary )
				{
					$sql_ary[] = ( !isset($user_auth_ary[$auth_option_id]) ) ? "INSERT INTO " . ACL_USERS_TABLE . " (user_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($user_id, $forum_id, $auth_option_id, $allow)" : ( ( $user_auth_ary[$auth_option_id] != $allow ) ? "UPDATE " . ACL_USERS_TABLE . " SET auth_allow_deny = $allow WHERE user_id = $user_id AND forum_id = $forum_id AND auth_option_id = $auth_option_id" : '' );
				}
			}
			else
			{
				$sql_ary[] = "INSERT INTO " . ACL_USERS_TABLE . " (user_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($user_id, $forum_id, $auth_option_id, $allow)";
			}
		}

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		unset($user_auth);
		unset($sql_ary);

		$this->acl_clear_prefetch();
	}

	function acl_set_group(&$forum_id, &$group_id, &$auth)
	{
		global $db;

		$forum_sql = "AND a.forum_id IN ($forum_id, 0)";

		$sql = "SELECT o.auth_option_id, o.auth_value, a.auth_allow_deny
			FROM " . ACL_GROUPS_TABLE . " a, " . ACL_OPTIONS_TABLE . " o
			WHERE a.auth_option_id = o.auth_option_id
				$forum_sql
				AND a.group_id = $group_id";
		$result = $db->sql_query($sql);

		$group_auth = array();
		if ( $row = $db->sql_fetchrow($result) )
		{
			do
			{
				$group_auth[$group_id][$row['auth_option_id']] = $row['auth_allow_deny'];
			}
			while ( $row = $db->sql_fetchrow($result) );
		}
		$db->sql_freeresult($result);

		foreach ( $auth as $auth_option_id => $allow )
		{
			if ( !empty($group_auth) )
			{
				foreach ( $group_auth as $group => $group_auth_ary )
				{
					$sql_ary[] = ( !isset($group_auth_ary[$auth_option_id]) ) ? "INSERT INTO " . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($group_id, $forum_id, $auth_option_id, $allow)" : ( ( $group_auth_ary[$auth_option_id] != $allow ) ? "UPDATE " . ACL_GROUPS_TABLE . " SET auth_allow_deny = $allow WHERE group_id = $group_id AND forum_id = $forum_id and auth_option_id = $auth_option_id" : '' );
				}
			}
			else
			{
				$sql_ary[] = "INSERT INTO " . ACL_GROUPS_TABLE . " (group_id, forum_id, auth_option_id, auth_allow_deny) VALUES ($group_id, $forum_id, $auth_option_id, $allow)";
			}
		}

		foreach ( $sql_ary as $sql )
		{
			$db->sql_query($sql);
		}

		unset($group_auth);
		unset($sql_ary);

		$this->acl_clear_prefetch();
	}

	function acl_delete_user($forum_id, $user_id, $auth_ids = false)
	{
		global $db;

		$auth_sql = '';
		if ( $auth_ids )
		{
			for($i = 0; $i < count($auth_ids); $i++)
			{
				$auth_sql .= ( ( $auth_sql != '' ) ? ', ' : '' ) . $auth_ids[$i];
			}
			$auth_sql = " AND auth_option_id IN ($auth_sql)";
		}

		$sql = "DELETE FROM " . ACL_USERS_TABLE . "
			WHERE user_id = $user_id
				AND forum_id = $forum_id
				$auth_sql";
		$db->sql_query($sql);

		$this->acl_clear_prefetch();
	}

	function acl_delete_group($forum_id, $group_id, $auth_type = false)
	{
		global $db;

		$auth_sql = '';
		if ( $auth_ids )
		{
			for($i = 0; $i < count($auth_ids); $i++)
			{
				$auth_sql .= ( ( $auth_sql != '' ) ? ', ' : '' ) . $auth_ids[$i];
			}
			$auth_sql = " AND auth_option_id IN ($auth_sql)";
		}

		$sql = "DELETE FROM " . ACL_GROUPS_TABLE . "
			WHERE group_id = $group_id
				AND forum_id = $forum_id
				$auth_sql";
		$db->sql_query($sql);

		$this->acl_clear_prefetch();
	}

	function acl_clear_prefetch()
	{
		global $db;

		$sql = "UPDATE " . USERS_TABLE . "
			SET user_permissions = ''";
		$db->sql_query($sql);

		return;
	}
}

?>