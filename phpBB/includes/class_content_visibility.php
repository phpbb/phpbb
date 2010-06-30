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
class phpbb_content_visibility
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
		if ($auth->acl_get('f_restore', $forum_id) && !$auth->acl_get('m_restore', $forum_id))
		{
			$poster_column = ($mode == 'topic') ? 'topic_poster' : 'poster_id';
			$clause = '(' . $clause . "
				OR ($table_alias{$mode}_visibility = " . ITEM_DELETED . "
					AND $table_alias$poster_column = " . $user->data['user_id'] . '))';

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
		if (sizeof($user_restore_forums) && !sizeof($restore_forums))
		{
			$poster_column = ($mode == 'topic') ? 'topic_poster' : 'poster_id';

			// specify the poster ID, the visibility type, and the forums we're interested in
			$where_sql .= " OR ($table_alias$poster_column = " . $user->data['user_id'] . "
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

	/**
	* Can the current logged-in user soft-delete posts?
	* @param $forum_id - int - the forum ID whose permissions to check
	* @param $poster_id - int - the poster ID of the post in question
	* @param $post_locked - bool - is the post locked?
	* @return bool
	*/
	public function can_soft_delete($forum_id, $poster_id, $post_locked)
	{
		global $auth, $user;

		if ($auth->acl_get('m_softdelete', $forum_id))
		{
			return true;
		}
		else if ($auth->acl_get('f_softdelete', $forum_id) && $poster_id == $user->data['user_id'] && !$post_locked)
		{
			return true;
		}
		return false;
	}

	/**
	* Can the current logged-in user restore soft-deleted posts?
	* @param $forum_id - int - the forum ID whose permissions to check
	* @param $poster_id - int - the poster ID of the post in question
	* @param $post_locked - bool - is the post locked?
	* @return bool
	*/
	public function can_restore($forum_id, $poster_id, $post_locked)
	{
		global $auth, $user;

		if ($auth->acl_get('m_restore', $forum_id))
		{
			return true;
		}
		else if ($auth->acl_get('f_restore', $forum_id) && $poster_id == $user->data['user_id'] && !$post_locked)
		{
			return true;
		}
		return false;
	}

	/**
	* Do the required math to hide a complete topic (going from approved to
	* unapproved or from approved to deleted)
	* @param $topic_id - int - the topic to act on
	* @param $forum_id - int - the forum where the topic resides
	* @param $topic_row - array - data about the topic, may be empty at call time
	* @param $sql_data - array - populated with the SQL changes, may be empty at call time
	* @return void
	*/
	public function hide_topic($topic_id, $forum_id, &$topic_row, &$sql_data)
	{
		global $auth, $config, $db;

		// Do we need to grab some topic informations?
		if (!sizeof($topic_row))
		{
			$sql = 'SELECT topic_type, topic_replies, topic_replies_real, topic_visibility
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $topic_id;
			$result = $db->sql_query($sql);
			$topic_row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
		}

		// If this is the only post remaining we do not need to decrement topic_replies.
		// Also do not decrement if first post - then the topic_replies will not be adjusted if approving the topic again.

		// If this is an edited topic or the first post the topic gets completely disapproved later on...
		$sql_data[FORUMS_TABLE] = 'forum_topics = forum_topics - 1';
		$sql_data[FORUMS_TABLE] .= ', forum_posts = forum_posts - ' . ($topic_row['topic_replies'] + 1);

		set_config_count('num_topics', -1, true);
		set_config_count('num_posts', ($topic_row['topic_replies'] + 1) * (-1), true);

		// Only decrement this post, since this is the one non-approved now
		if ($auth->acl_get('f_postcount', $forum_id))
		{
			$sql_data[USERS_TABLE] = 'user_posts = user_posts - 1';
		}
	}

	/**
	* Do the required math to hide a single post (going from approved to
	* unapproved or from approved to deleted)
	* Notably, we do _not_ need the post ID to do this operation. We're only changing statistic caches
	* @param $forum_id - int - the forum where the topic resides
	* @param $current_time - int - passed for consistency instead of calling time() internally
	* @param $sql_data - array - populated with the SQL changes, may be empty at call time
	* @return void
	*/
	public function hide_post($forum_id, $current_time, &$sql_data)
	{
		global $auth, $config, $db;

		$sql_data[TOPICS_TABLE] = 'topic_replies = topic_replies - 1, topic_last_view_time = ' . $current_time;
		$sql_data[FORUMS_TABLE] = 'forum_posts = forum_posts - 1';

		set_config_count('num_posts', -1, true);

		if ($auth->acl_get('f_postcount', $forum_id))
		{
			$sql_data[USERS_TABLE] = 'user_posts = user_posts - 1';
		}
	}

	/**
	* One function to rule them all ... and unhide posts and topics.  This could
	* reasonably be broken up, I straight copied this code from the mcp_queue.php
	* file here for global access.
	* @param $mode - string - member of the set {'approve', 'restore'}
	* @param $post_info - array - Contains info from post U topics table about
	* 	the posts/topics in question
	* @param $post_id_list - array of ints - the set of posts being worked on
	*/
	public function unhide_posts_topics($mode, $post_info, $post_id_list)
	{
		global $db, $config;

		// If Topic -> total_topics = total_topics+1, total_posts = total_posts+1, forum_topics = forum_topics+1, forum_posts = forum_posts+1
		// If Post -> total_posts = total_posts+1, forum_posts = forum_posts+1, topic_replies = topic_replies+1

		$total_topics = $total_posts = 0;
		$topic_approve_sql = $post_approve_sql = $topic_id_list = $forum_id_list = $approve_log = array();
		$user_posts_sql = $post_approved_list = array();

		foreach ($post_info as $post_id => $post_data)
		{
			if ($post_data['post_visibility'] == ITEM_APPROVED)
			{
				$post_approved_list[] = $post_id;
				continue;
			}

			$topic_id_list[$post_data['topic_id']] = 1;

			if ($post_data['forum_id'])
			{
				$forum_id_list[$post_data['forum_id']] = 1;
			}

			// User post update (we do not care about topic or post, since user posts are strictly connected to posts)
			// But we care about forums where post counts get not increased. ;)
			if ($post_data['post_postcount'])
			{
				$user_posts_sql[$post_data['poster_id']] = (empty($user_posts_sql[$post_data['poster_id']])) ? 1 : $user_posts_sql[$post_data['poster_id']] + 1;
			}

			// Topic or Post. ;)
			if ($post_data['topic_first_post_id'] == $post_id)
			{
				if ($post_data['forum_id'])
				{
					$total_topics++;
				}
				$topic_approve_sql[] = $post_data['topic_id'];

				$approve_log[] = array(
					'type'			=> 'topic',
					'post_subject'	=> $post_data['post_subject'],
					'forum_id'		=> $post_data['forum_id'],
					'topic_id'		=> $post_data['topic_id'],
				);
			}
			else
			{
				$approve_log[] = array(
					'type'			=> 'post',
					'post_subject'	=> $post_data['post_subject'],
					'forum_id'		=> $post_data['forum_id'],
					'topic_id'		=> $post_data['topic_id'],
				);
			}

			if ($post_data['forum_id'])
			{
				$total_posts++;

				// Increment by topic_replies if we approve a topic...
				// This works because we do not adjust the topic_replies when re-approving a topic after an edit.
				if ($post_data['topic_first_post_id'] == $post_id && $post_data['topic_replies'])
				{
					$total_posts += $post_data['topic_replies'];
				}
			}

			$post_approve_sql[] = $post_id;
		}

		$post_id_list = array_values(array_diff($post_id_list, $post_approved_list));
		for ($i = 0, $size = sizeof($post_approved_list); $i < $size; $i++)
		{
			unset($post_info[$post_approved_list[$i]]);
		}

		if (sizeof($topic_approve_sql))
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_visibility = ' . ITEM_APPROVED . '
				WHERE ' . $db->sql_in_set('topic_id', $topic_approve_sql);
			$db->sql_query($sql);
		}

		if (sizeof($post_approve_sql))
		{
			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_visibility = ' . ITEM_APPROVED . '
				WHERE ' . $db->sql_in_set('post_id', $post_approve_sql);
			$db->sql_query($sql);
		}

		unset($topic_approve_sql, $post_approve_sql);

		foreach ($approve_log as $log_data)
		{
			add_log('mod', $log_data['forum_id'], $log_data['topic_id'], ($log_data['type'] == 'topic') ? 'LOG_TOPIC_' . strtoupper($mode) . 'D' : 'LOG_POST_' . strtoupper($mode) . 'D', $log_data['post_subject']);
		}

		if (sizeof($user_posts_sql))
		{
			// Try to minimize the query count by merging users with the same post count additions
			$user_posts_update = array();

			foreach ($user_posts_sql as $user_id => $user_posts)
			{
				$user_posts_update[$user_posts][] = $user_id;
			}

			foreach ($user_posts_update as $user_posts => $user_id_ary)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = user_posts + ' . $user_posts . '
					WHERE ' . $db->sql_in_set('user_id', $user_id_ary);
				$db->sql_query($sql);
			}
		}

		if ($total_topics)
		{
			set_config_count('num_topics', $total_topics, true);
		}

		if ($total_posts)
		{
			set_config_count('num_posts', $total_posts, true);
		}

		if (!function_exists('sync'))
		{
			global $phpbb_root_path, $phpEx;
			include ($phpbb_root_path . 'includes/functions_admin.'.$phpEx);
		}

		sync('topic', 'topic_id', array_keys($topic_id_list), true);
		sync('forum', 'forum_id', array_keys($forum_id_list), true, true);
		unset($topic_id_list, $forum_id_list);

		return true;
	}
}
