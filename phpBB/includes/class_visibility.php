<?php

class topic_visibility
{
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

	public function set_topic_visibility($visibility, $topic_id, $forum_id)
	{
		global $db;

		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET topic_visibility = ' . (int) $visibility . '
			WHERE topic_id = ' . (int) $topic_id;
		$db->sql_query($sql);

		if ($visibility != ITEM_APPROVED)
		{
			$sql = 'SELECT post_id FROM ' . POSTS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id;
			$result = $db->sql_query($sql);

			$status = true;
			while ($row = $db->sql_fetchrow($result))
			{
				$status = min($status, self::set_post_visibility($visibility, false, $topic_id, $forum_id, true, true));
			}
		}
		else
		{
			// TOOD: figure out which posts we actually care about
			$status = self::set_post_visibility($visibility, 0, false, $forum_id, true, true);
		}

		return $status;
	}

	public function set_post_visibility($visibility, $post_id, $topic_id, $forum_id, $is_starter, $is_latest)
	{
		global $db;

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

		if ($is_starter || $is_latest)
		{
			update_post_information('topic', $topic_id, false);
			update_post_information('forum', $forum_id, false);
		}

		// if we're changing the starter, we need to change the rest of the topic
		if ($is_starter && !$is_latest)
		{
			self::set_topic_visibility($visibility, $topic_id, $forum_id);
		}
	}
}
?>
