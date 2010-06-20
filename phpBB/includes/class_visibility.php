<?php
/**
*
* @package phpbb
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
* phpbb_visibility
* Handle fetching and setting the visibility for topics and posts
* @package phpbb
*/
class phpbb_visibility
{
	/**
	* Create topic/post visibility SQL for a given forum ID
	* @param $mode string - either "topic" or "post"
	* @param $forum_id int - current forum ID
	* @param $table_alias string - Table alias to prefix in SQL queries
	* @return string with the appropriate combination SQL logic for topic/post_visibility
	*/
	public function get_visibility_sql($mode, $forum_id, $table_alias = '')
	{
		global $auth, $db, $user;

		$status_ary = array(ITEM_APPROVED);
		if ($auth->acl_get('m_approve', $forum_id))
		{
			$status_ary[] = ITEM_UNAPPROVED;
		}

		if ($auth->acl_get('m_restore', $forum_id))
		{
			$status_ary[] = ITEM_DELETED;
		}

		$clause = $db->sql_in_set($table_alias . $mode . '_visibility', $status_ary);

		// only allow the user to view deleted posts he himself made
		if ($auth->acl_get('f_restore', $forum_id))
		{
			$clause = 'AND (' . $clause . "
				OR ($table_alias{$mode}_visibility = " . ITEM_DELETED . "
					AND {$table_alias}poster_id = " . $user->data['user_id'] . '))';

		}

		return $clause;
	}

	/**
	* Fetch visibility SQL for all forums on the board.
	* @param $mode string - either "topic" or "post"
	* @param $exclude_forum_ids - int array - 
	* @param $table_alias string - Table alias to prefix in SQL queries
	* @return string with the appropriate combination SQL logic for topic/post_visibility
	*/
	public function get_visibility_sql_global($mode, $exclude_forum_ids = array(), $table_alias = '')
	{
		global $auth, $db, $user;

		// users can always see approved posts
		$where_sql = "($table_alias{$mode}_visibility = " . ITEM_APPROVED;

		// in set notation: {approve_forums} = {m_approve} - {exclude_forums}
		$approve_forums = array_diff(array_keys($auth->acl_getf('m_approve', true)), $exclude_forum_ids);
		if (sizeof($approve_forums))
		{
			// users can view unapproved topics in certain forums. specify them.
			$where_sql .= " OR ($table_alias{$mode}_visibility = " . ITEM_UNAPPROVED . '
				AND ' . $db->sql_in_set($table_alias . 'forum_id', $approve_forums) . ')';
		}

		// this is exactly the same logic as for approve forums, above
		$restore_forums = array_diff(array_keys($auth->acl_getf('m_restore', true)), $exclude_forum_ids);
		if (sizeof($restore_forums))
		{
			$where_sql .= " OR ($table_alias{$mode}_visibility = " . ITEM_DELETED . '
				AND ' . $db->sql_in_set($table_alias . 'forum_id', $restore_forums) . ')';
		}

		// we also allow the user to view deleted posts he himself made
		$user_restore_forums = array_diff(array_keys($auth->acl_getf('f_restore', true)), $exclude_forum_ids);
		if (sizeof($user_restore_forums))
		{
			// specify the poster ID, the visibility type, and the forums we're interested in
			$where_sql .= " OR ($table_alias{$mode}poster_id = " . $user->data['user_id'] . "
				AND $table_alias{$mode}_visibility = " . ITEM_DELETED . "
				AND " . $db->sql_in_set($table_alias . 'forum_id', $user_restore_forums) . ')';
		}

		$where_sql .= ')';

		return $where_sql;
	}

	/**
	* Description: Allows approving (which is akin to undeleting), unapproving (!) or soft deleting an entire topic.
	* Calls set_post_visibility as needed.
	* @param $visibility - int - element of {ITEM_UNAPPROVED, ITEM_APPROVED, ITEM_DELETED}
	* @param $topic_id - int - topic ID to act on
	* @param $forum_id - int - forum ID where $topic_id resides
	* @return bool true = success, false = fail
	*/
	public function set_topic_visibility($visibility, $topic_id, $forum_id)
	{
		global $db;

		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET topic_visibility = ' . (int) $visibility . '
			WHERE topic_id = ' . (int) $topic_id;
		$db->sql_query($sql);

		// if we're approving, disapproving, or deleteing a topic, assume that
		// we are adjusting _all_ posts in that topic.
		$status = self::set_post_visibility($visibility, false, $topic_id, $forum_id, true, true);


		return $status;
	}

	/**
	* @param $visibility - int - element of {ITEM_UNAPPROVED, ITEM_APPROVED, ITEM_DELETED}
	* @param $post_id - int - the post ID to act on
	* @param $topic_id - int - forum where $post_id is found
	* @param $forum_id - int - forum ID where $topic_id resides
	* @param $is_starter - bool - is this the first post of the topic
	* @param $is_latest - bool - is this the last post of the topic
	*/
	public function set_post_visibility($visibility, $post_id, $topic_id, $forum_id, $is_starter, $is_latest)
	{
		global $db;

		// if we're changing the starter, we need to change the rest of the topic
		if ($is_starter && !$is_latest)
		{
			return self::set_topic_visibility($visibility, $topic_id, $forum_id);
		}

		if ($post_id)
		{
			$where_sql = 'post_id = ' . (int) $post_id;
		}
		else if ($topic_id)
		{
			$where_sql = 'topic_id = ' . (int) $topic_id;
		}
		else
		{
			// throw new MissingArgumentsException(); <-- a nice idea
			return false;
		}

		$sql = 'UPDATE ' . POSTS_TABLE . ' SET post_visibility = ' . (int) $visibility . '
			WHERE ' . $where_sql;
		$db->sql_query($sql);

		// Sync the first/last topic information if needed
		if ($is_starter || $is_latest)
		{
			update_post_information('topic', $topic_id, false);
			update_post_information('forum', $forum_id, false);
		}
	}
}
?>
