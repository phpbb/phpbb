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

namespace phpbb;

/**
* phpbb_visibility
* Handle fetching and setting the visibility for topics and posts
*/
class content_visibility
{
	/**
	* Database object
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Auth object
	* @var \phpbb\auth\auth
	*/
	protected $auth;

	/**
	* config object
	* @var \phpbb\config\config
	*/
	protected $config;

	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $phpbb_dispatcher;

	/**
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP Extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Constructor
	*
	* @param	\phpbb\auth\auth		$auth	Auth object
	* @param	\phpbb\config\config	$config	Config object
	* @param	\phpbb\event\dispatcher_interface	$phpbb_dispatcher	Event dispatcher object
	* @param	\phpbb\db\driver\driver_interface	$db		Database object
	* @param	\phpbb\user		$user			User object
	* @param	string		$phpbb_root_path	Root path
	* @param	string		$php_ext			PHP Extension
	* @param	string		$forums_table		Forums table name
	* @param	string		$posts_table		Posts table name
	* @param	string		$topics_table		Topics table name
	* @param	string		$users_table		Users table name
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\event\dispatcher_interface $phpbb_dispatcher, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $phpbb_root_path, $php_ext, $forums_table, $posts_table, $topics_table, $users_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->db = $db;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->forums_table = $forums_table;
		$this->posts_table = $posts_table;
		$this->topics_table = $topics_table;
		$this->users_table = $users_table;
	}

	/**
	* Can the current logged-in user soft-delete posts?
	*
	* @param $forum_id		int		Forum ID whose permissions to check
	* @param $poster_id		int		Poster ID of the post in question
	* @param $post_locked	bool	Is the post locked?
	* @return bool
	*/
	public function can_soft_delete($forum_id, $poster_id, $post_locked)
	{
		if ($this->auth->acl_get('m_softdelete', $forum_id))
		{
			return true;
		}
		else if ($this->auth->acl_get('f_softdelete', $forum_id) && $poster_id == $this->user->data['user_id'] && !$post_locked)
		{
			return true;
		}

		return false;
	}

	/**
	* Get the topics post count or the forums post/topic count based on permissions
	*
	* @param $mode			string	One of topic_posts, forum_posts or forum_topics
	* @param $data			array	Array with the topic/forum data to calculate from
	* @param $forum_id		int		The forum id is used for permission checks
	* @return int	Number of posts/topics the user can see in the topic/forum
	*/
	public function get_count($mode, $data, $forum_id)
	{
		if (!$this->auth->acl_get('m_approve', $forum_id))
		{
			return (int) $data[$mode . '_approved'];
		}

		return (int) $data[$mode . '_approved'] + (int) $data[$mode . '_unapproved'] + (int) $data[$mode . '_softdeleted'];
	}


	/**
	* Check topic/post visibility for a given forum ID
	*
	* Note: Read permissions are not checked.
	*
	* @param $mode		string	Either "topic" or "post"
	* @param $forum_id	int		The forum id is used for permission checks
	* @param $data		array	Array with item information to check visibility
	* @return bool		True if the item is visible, false if not
	*/
	public function is_visible($mode, $forum_id, $data)
	{
		$visibility = $data[$mode . '_visibility'];
		$poster_key = ($mode === 'topic') ? 'topic_poster' : 'poster_id';
		$is_visible = ($visibility == ITEM_APPROVED) ||
			($this->config['display_unapproved_posts'] &&
				($this->user->data['user_id'] != ANONYMOUS) &&
				($visibility == ITEM_UNAPPROVED || $visibility == ITEM_REAPPROVE) &&
				($this->user->data['user_id'] == $data[$poster_key])) ||
			 $this->auth->acl_get('m_approve', $forum_id);

		/**
		* Allow changing the result of calling is_visible
		*
		* @event core.phpbb_content_visibility_is_visible
		* @var	bool		is_visible			Default visibility condition, to be modified by extensions if needed.
		* @var	string		mode				Either "topic" or "post"
		* @var	int			forum_id			Forum id of the current item
		* @var	array		data				Array of item information
		* @since 3.2.2-RC1
		*/
		$vars = array(
			'is_visible',
			'mode',
			'forum_id',
			'data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.phpbb_content_visibility_is_visible', compact($vars)));

		return $is_visible;
	}

	/**
	* Create topic/post visibility SQL for a given forum ID
	*
	* Note: Read permissions are not checked.
	*
	* @param $mode			string	Either "topic" or "post"
	* @param $forum_id		int		The forum id is used for permission checks
	* @param $table_alias	string	Table alias to prefix in SQL queries
	* @return string	The appropriate combination SQL logic for topic/post_visibility
	*/
	public function get_visibility_sql($mode, $forum_id, $table_alias = '')
	{
		$where_sql = '';

		$get_visibility_sql_overwrite = false;

		/**
		* Allow changing the result of calling get_visibility_sql
		*
		* @event core.phpbb_content_visibility_get_visibility_sql_before
		* @var	string		where_sql						Extra visibility conditions. It must end with either an SQL "AND" or an "OR"
		* @var	string		mode							Either "topic" or "post" depending on the query this is being used in
		* @var	array		forum_id						The forum id in which the search is made.
		* @var	string		table_alias						Table alias to prefix in SQL queries
		* @var	mixed		get_visibility_sql_overwrite	If a string, forces the function to return get_forums_visibility_sql_overwrite after executing the event
		* 													If false, get_visibility_sql continues normally
		* 													It must be either boolean or string
		* @since 3.1.4-RC1
		*/
		$vars = array(
			'where_sql',
			'mode',
			'forum_id',
			'table_alias',
			'get_visibility_sql_overwrite',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.phpbb_content_visibility_get_visibility_sql_before', compact($vars)));

		if ($get_visibility_sql_overwrite !== false)
		{
			return $get_visibility_sql_overwrite;
		}

		if ($this->auth->acl_get('m_approve', $forum_id))
		{
			$where_sql .= '1 = 1';
		}
		else
		{
			$visibility_query = $table_alias . $mode . '_visibility = ';

			$where_sql .= '(' . $visibility_query . ITEM_APPROVED . ')';
			if ($this->config['display_unapproved_posts'] && ($this->user->data['user_id'] != ANONYMOUS))
			{
				$poster_key = ($mode === 'topic') ? 'topic_poster' : 'poster_id';
				$where_sql .= ' OR ((' . $visibility_query . ITEM_UNAPPROVED . ' OR ' . $visibility_query . ITEM_REAPPROVE .')';
				$where_sql .= ' AND ' . $table_alias . $poster_key . ' = ' . ((int) $this->user->data['user_id']) . ')';
			}
		}
		return '(' . $where_sql . ')';
	}

	/**
	* Create topic/post visibility SQL for a set of forums
	*
	* Note: Read permissions are not checked. Forums without read permissions
	*		should not be in $forum_ids
	*
	* @param $mode			string	Either "topic" or "post"
	* @param $forum_ids		array	Array of forum ids which the posts/topics are limited to
	* @param $table_alias	string	Table alias to prefix in SQL queries
	* @return string	The appropriate combination SQL logic for topic/post_visibility
	*/
	public function get_forums_visibility_sql($mode, $forum_ids = array(), $table_alias = '')
	{
		$where_sql = '';

		$approve_forums = array_keys($this->auth->acl_getf('m_approve', true));
		if (!empty($forum_ids) && !empty($approve_forums))
		{
			$approve_forums = array_intersect($forum_ids, $approve_forums);
			$forum_ids = array_diff($forum_ids, $approve_forums);
		}

		$get_forums_visibility_sql_overwrite = false;
		/**
		* Allow changing the result of calling get_forums_visibility_sql
		*
		* @event core.phpbb_content_visibility_get_forums_visibility_before
		* @var	string		where_sql							Extra visibility conditions. It must end with either an SQL "AND" or an "OR"
		* @var	string		mode								Either "topic" or "post" depending on the query this is being used in
		* @var	array		forum_ids							Array of forum ids which the posts/topics are limited to
		* @var	string		table_alias							Table alias to prefix in SQL queries
		* @var	array		approve_forums						Array of forums where the user has m_approve permissions
		* @var	mixed		get_forums_visibility_sql_overwrite	If a string, forces the function to return get_forums_visibility_sql_overwrite after executing the event
		* 														If false, get_forums_visibility_sql continues normally
		* 														It must be either boolean or string
		* @since 3.1.3-RC1
		*/
		$vars = array(
			'where_sql',
			'mode',
			'forum_ids',
			'table_alias',
			'approve_forums',
			'get_forums_visibility_sql_overwrite',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.phpbb_content_visibility_get_forums_visibility_before', compact($vars)));

		if ($get_forums_visibility_sql_overwrite !== false)
		{
			return $get_forums_visibility_sql_overwrite;
		}

		// Moderator can view all posts/topics in the moderated forums
		$where_sql .= '(' . $this->db->sql_in_set($table_alias . 'forum_id', $approve_forums, false, true) . ' OR ';
		// Normal user can view approved items only
		$where_sql .= '(' . $table_alias . $mode . '_visibility = ' . ITEM_APPROVED . '
			AND ' . $this->db->sql_in_set($table_alias . 'forum_id', $forum_ids, false, true) . '))';

		return '(' . $where_sql . ')';
	}

	/**
	* Create topic/post visibility SQL for all forums on the board
	*
	* Note: Read permissions are not checked. Forums without read permissions
	*		should be in $exclude_forum_ids
	*
	* @param $mode				string	Either "topic" or "post"
	* @param $exclude_forum_ids	array	Array of forum ids which are excluded
	* @param $table_alias		string	Table alias to prefix in SQL queries
	* @return string	The appropriate combination SQL logic for topic/post_visibility
	*/
	public function get_global_visibility_sql($mode, $exclude_forum_ids = array(), $table_alias = '')
	{
		$where_sqls = array();

		$approve_forums = array_diff(array_keys($this->auth->acl_getf('m_approve', true)), $exclude_forum_ids);

		$visibility_sql_overwrite = null;

		/**
		* Allow changing the result of calling get_global_visibility_sql
		*
		* @event core.phpbb_content_visibility_get_global_visibility_before
		* @var	array		where_sqls							Array of extra visibility conditions. Will be joined by imploding with "OR".
		* @var	string		mode								Either "topic" or "post" depending on the query this is being used in
		* @var	array		exclude_forum_ids					Array of forum ids the current user doesn't have access to
		* @var	string		table_alias							Table alias to prefix in SQL queries
		* @var	array		approve_forums						Array of forums where the user has m_approve permissions
		* @var	string		visibility_sql_overwrite			If not empty, forces the function to return visibility_sql_overwrite after executing the event
		* @since 3.1.3-RC1
		*/
		$vars = array(
			'where_sqls',
			'mode',
			'exclude_forum_ids',
			'table_alias',
			'approve_forums',
			'visibility_sql_overwrite',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.phpbb_content_visibility_get_global_visibility_before', compact($vars)));

		if ($visibility_sql_overwrite)
		{
			return $visibility_sql_overwrite;
		}

		// Include approved items in all forums but the excluded
		$where_sqls[] = '(' . $this->db->sql_in_set($table_alias . 'forum_id', $exclude_forum_ids, true, true) . '
			AND ' . $table_alias . $mode . '_visibility = ' . ITEM_APPROVED . ')';

		// If user has moderator permissions, add everything in the moderated forums
		if (count($approve_forums))
		{
			$where_sqls[] = $this->db->sql_in_set($table_alias . 'forum_id', $approve_forums);
		}

		return '(' . implode(' OR ', $where_sqls) . ')';
	}

	/**
	* Change visibility status of one post or all posts of a topic
	*
	* @param $visibility	int		Element of {ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE}
	* @param $post_id		mixed	Post ID or array of post IDs to act on,
	*								if it is empty, all posts of topic_id will be modified
	* @param $topic_id		int		Topic where $post_id is found
	* @param $forum_id		int		Forum where $topic_id is found
	* @param $user_id		int		User performing the action
	* @param $time			int		Timestamp when the action is performed
	* @param $reason		string	Reason why the visibility was changed.
	* @param $is_starter	bool	Is this the first post of the topic changed?
	* @param $is_latest		bool	Is this the last post of the topic changed?
	* @param $limit_visibility	mixed	Limit updating per topic_id to a certain visibility
	* @param $limit_delete_time	mixed	Limit updating per topic_id to a certain deletion time
	* @return array		Changed post data, empty array if an error occurred.
	*/
	public function set_post_visibility($visibility, $post_id, $topic_id, $forum_id, $user_id, $time, $reason, $is_starter, $is_latest, $limit_visibility = false, $limit_delete_time = false)
	{
		if (!in_array($visibility, array(ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE)))
		{
			return array();
		}

		if ($post_id)
		{
			if (is_array($post_id))
			{
				$where_sql = $this->db->sql_in_set('post_id', array_map('intval', $post_id));
			}
			else
			{
				$where_sql = 'post_id = ' . (int) $post_id;
			}
			$where_sql .= ' AND topic_id = ' . (int) $topic_id;
		}
		else
		{
			$where_sql = 'topic_id = ' . (int) $topic_id;

			// Limit the posts to a certain visibility and deletion time
			// This allows us to only restore posts, that were approved
			// when the topic got soft deleted. So previous soft deleted
			// and unapproved posts are still soft deleted/unapproved
			if ($limit_visibility !== false)
			{
				$where_sql .= ' AND post_visibility = ' . (int) $limit_visibility;
			}

			if ($limit_delete_time !== false)
			{
				$where_sql .= ' AND post_delete_time = ' . (int) $limit_delete_time;
			}
		}

		$sql = 'SELECT poster_id, post_id, post_postcount, post_visibility
			FROM ' . $this->posts_table . '
			WHERE ' . $where_sql;
		$result = $this->db->sql_query($sql);

		$post_ids = $poster_postcounts = $postcounts = $postcount_visibility = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_ids[] = (int) $row['post_id'];

			if ($row['post_visibility'] != $visibility)
			{
				if ($row['post_postcount'] && !isset($poster_postcounts[(int) $row['poster_id']]))
				{
					$poster_postcounts[(int) $row['poster_id']] = 1;
				}
				else if ($row['post_postcount'])
				{
					$poster_postcounts[(int) $row['poster_id']]++;
				}

				if (!isset($postcount_visibility[$row['post_visibility']]))
				{
					$postcount_visibility[$row['post_visibility']] = 1;
				}
				else
				{
					$postcount_visibility[$row['post_visibility']]++;
				}
			}
		}
		$this->db->sql_freeresult($result);

		if (empty($post_ids))
		{
			return array();
		}

		if (!function_exists('truncate_string'))
		{
			include($this->phpbb_root_path . 'includes/functions_content.' . $this->php_ext);
		}

		$data = array(
			'post_visibility'		=> (int) $visibility,
			'post_delete_user'		=> (int) $user_id,
			'post_delete_time'		=> ((int) $time) ?: time(),
			'post_delete_reason'	=> truncate_string($reason, 255, 255, false),
		);
		/**
		 * Perform actions right before the query to change post visibility
		 *
		 * @event core.set_post_visibility_before_sql
		 * @var			int			visibility		Element of {ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE}
		 * @var			array		post_id			Array containing all post IDs to be modified. If blank, all posts within the topic are modified.
		 * @var			int			topic_id		Topic of the post IDs to be modified.
		 * @var			int			forum_id		Forum ID that the topic_id resides in.
		 * @var			int			user_id			User ID doing this action.
		 * @var			int			time			Timestamp of this action.
		 * @var			string		reason			Reason specified by the user for this change.
		 * @var			bool		is_starter		Are we changing the topic's starter?
		 * @var			bool		is_latest		Are we changing the topic's latest post?
		 * @var			array		data			The data array for this action.
		 * @since 3.1.10-RC1
		 * @changed 3.2.2-RC1 Use time instead of non-existent timestamp
		 */
		$vars = array(
			'visibility',
			'post_id',
			'topic_id',
			'forum_id',
			'user_id',
			'time',
			'reason',
			'is_starter',
			'is_latest',
			'data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.set_post_visibility_before_sql', compact($vars)));
		$sql = 'UPDATE ' . $this->posts_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
		$this->db->sql_query($sql);

		// Group the authors by post count, to reduce the number of queries
		foreach ($poster_postcounts as $poster_id => $num_posts)
		{
			$postcounts[$num_posts][] = $poster_id;
		}

		$postcount_change = 0;

		// Update users postcounts
		foreach ($postcounts as $num_posts => $poster_ids)
		{
			if (in_array($visibility, array(ITEM_REAPPROVE, ITEM_DELETED)))
			{
				$postcount_change -= $num_posts;

				$sql = 'UPDATE ' . $this->users_table . '
					SET user_posts = 0
					WHERE ' . $this->db->sql_in_set('user_id', $poster_ids) . '
						AND user_posts < ' . $num_posts;
				$this->db->sql_query($sql);

				$sql = 'UPDATE ' . $this->users_table . '
					SET user_posts = user_posts - ' . $num_posts . '
					WHERE ' . $this->db->sql_in_set('user_id', $poster_ids) . '
						AND user_posts >= ' . $num_posts;
				$this->db->sql_query($sql);
			}
			else
			{
				$postcount_change += $num_posts;

				$sql = 'UPDATE ' . $this->users_table . '
					SET user_posts = user_posts + ' . $num_posts . '
					WHERE ' . $this->db->sql_in_set('user_id', $poster_ids);
				$this->db->sql_query($sql);
			}
		}

		if ($postcount_change != 0)
		{
			$this->config->increment('num_posts', $postcount_change, false);
		}

		$update_topic_postcount = true;

		// Sync the first/last topic information if needed
		if (!$is_starter && $is_latest)
		{
			if (!function_exists('update_post_information'))
			{
				include($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
			}

			// update_post_information can only update the last post info ...
			if ($topic_id)
			{
				update_post_information('topic', $topic_id, false);
			}
			if ($forum_id)
			{
				update_post_information('forum', $forum_id, false);
			}
		}
		else if ($is_starter && $topic_id)
		{
			if (!function_exists('sync'))
			{
				include($this->phpbb_root_path . 'includes/functions_admin.' . $this->php_ext);
			}

			// ... so we need to use sync, if the first post is changed.
			// The forum is resynced recursive by sync() itself.
			sync('topic', 'topic_id', $topic_id, true);

			// sync recalculates the topic replies and forum posts by itself, so we don't do that.
			$update_topic_postcount = false;
		}

		$topic_update_array = array();
		// Update the topic's reply count and the forum's post count
		if ($update_topic_postcount)
		{
			$field_alias = array(
				ITEM_APPROVED	=> 'posts_approved',
				ITEM_UNAPPROVED	=> 'posts_unapproved',
				ITEM_DELETED	=> 'posts_softdeleted',
				ITEM_REAPPROVE	=> 'posts_unapproved',
			);
			$cur_posts = array_fill_keys($field_alias, 0);

			foreach ($postcount_visibility as $post_visibility => $visibility_posts)
			{
				$cur_posts[$field_alias[(int) $post_visibility]] += $visibility_posts;
			}

			$sql_ary = array();
			$recipient_field = $field_alias[$visibility];

			foreach ($cur_posts as $field => $count)
			{
				// Decrease the count for the old statuses.
				if ($count && $field != $recipient_field)
				{
					$sql_ary[$field] = " - $count";
				}
			}
			// Add up the count from all statuses excluding the recipient status.
			$count_increase = array_sum(array_diff($cur_posts, array($recipient_field)));

			if ($count_increase)
			{
				$sql_ary[$recipient_field] = " + $count_increase";
			}

			if (count($sql_ary))
			{
				$forum_sql = array();

				foreach ($sql_ary as $field => $value_change)
				{
					$topic_update_array[] = 'topic_' . $field . ' = topic_' . $field . $value_change;
					$forum_sql[] = 'forum_' . $field . ' = forum_' . $field . $value_change;
				}

				$sql = 'UPDATE ' . $this->forums_table . '
					SET ' . implode(', ', $forum_sql) . '
					WHERE forum_id = ' . (int) $forum_id;
				$this->db->sql_query($sql);
			}
		}

		if ($post_id)
		{
			$sql = 'SELECT 1 AS has_attachments
				FROM ' . POSTS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id . '
					AND post_attachment = 1
					AND post_visibility = ' . ITEM_APPROVED . '
					AND ' . $this->db->sql_in_set('post_id', $post_id, true);
			$result = $this->db->sql_query_limit($sql, 1);

			$has_attachment = (bool) $this->db->sql_fetchfield('has_attachments');
			$this->db->sql_freeresult($result);

			if ($has_attachment && $visibility == ITEM_APPROVED)
			{
				$topic_update_array[] = 'topic_attachment = 1';
			}
			else if (!$has_attachment && $visibility != ITEM_APPROVED)
			{
				$topic_update_array[] = 'topic_attachment = 0';
			}
		}

		if (!empty($topic_update_array))
		{
			// Update the number for replies and posts, and update the attachments flag
			$sql = 'UPDATE ' . $this->topics_table . '
				SET ' . implode(', ', $topic_update_array) . '
				WHERE topic_id = ' . (int) $topic_id;
			$this->db->sql_query($sql);
		}
		/**
		 * Perform actions after all steps to changing post visibility
		 *
		 * @event core.set_post_visibility_after
		 * @var			int			visibility		Element of {ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE}
		 * @var			array		post_id			Array containing all post IDs to be modified. If blank, all posts within the topic are modified.
		 * @var			int			topic_id		Topic of the post IDs to be modified.
		 * @var			int			forum_id		Forum ID that the topic_id resides in.
		 * @var			int			user_id			User ID doing this action.
		 * @var			int			time			Timestamp of this action.
		 * @var			string		reason			Reason specified by the user for this change.
		 * @var			bool		is_starter		Are we changing the topic's starter?
		 * @var			bool		is_latest		Are we changing the topic's latest post?
		 * @var			array		data			The data array for this action.
		 * @since 3.1.10-RC1
		 * @changed 3.2.2-RC1 Use time instead of non-existent timestamp
		 */
		$vars = array(
			'visibility',
			'post_id',
			'topic_id',
			'forum_id',
			'user_id',
			'time',
			'reason',
			'is_starter',
			'is_latest',
			'data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.set_post_visibility_after', compact($vars)));
		return $data;
	}

	/**
	* Set topic visibility
	*
	* Allows approving (which is akin to undeleting/restore) or soft deleting an entire topic.
	* Calls set_post_visibility as needed.
	*
	* Note: By default, when a soft deleted topic is restored. Only posts that
	*		were approved at the time of soft deleting, are being restored.
	*		Same applies to soft deleting. Only approved posts will be marked
	*		as soft deleted.
	*		If you want to update all posts, use the force option.
	*
	* @param $visibility	int		Element of {ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE}
	* @param $topic_id		mixed	Topic ID to act on
	* @param $forum_id		int		Forum where $topic_id is found
	* @param $user_id		int		User performing the action
	* @param $time			int		Timestamp when the action is performed
	* @param $reason		string	Reason why the visibilty was changed.
	* @param $force_update_all	bool	Force to update all posts within the topic
	* @return array		Changed topic data, empty array if an error occurred.
	*/
	public function set_topic_visibility($visibility, $topic_id, $forum_id, $user_id, $time, $reason, $force_update_all = false)
	{
		if (!in_array($visibility, array(ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE)))
		{
			return array();
		}

		if (!$force_update_all)
		{
			$sql = 'SELECT topic_visibility, topic_delete_time
				FROM ' . $this->topics_table . '
				WHERE topic_id = ' . (int) $topic_id;
			$result = $this->db->sql_query($sql);
			$original_topic_data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$original_topic_data)
			{
				// The topic does not exist...
				return array();
			}
		}

		if (!function_exists('truncate_string'))
		{
			include($this->phpbb_root_path . 'includes/functions_content.' . $this->php_ext);
		}

		// Note, we do not set a reason for the posts, just for the topic
		$data = array(
			'topic_visibility'		=> (int) $visibility,
			'topic_delete_user'		=> (int) $user_id,
			'topic_delete_time'		=> ((int) $time) ?: time(),
			'topic_delete_reason'	=> truncate_string($reason, 255, 255, false),
		);
		/**
		 * Perform actions right before the query to change topic visibility
		 *
		 * @event core.set_topic_visibility_before_sql
		 * @var			int			visibility			Element of {ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE}
		 * @var			int			topic_id			Topic of the post IDs to be modified.
		 * @var			int			forum_id			Forum ID that the topic_id resides in.
		 * @var			int			user_id				User ID doing this action.
		 * @var			int			time				Timestamp of this action.
		 * @var			string		reason				Reason specified by the user for this change.
		 * @var			bool		force_update_all	Force an update on all posts within the topic, regardless of their current approval state.
		 * @var			array		data				The data array for this action.
		 * @since 3.1.10-RC1
		 * @changed 3.2.2-RC1 Use time instead of non-existent timestamp
		 */
		$vars = array(
			'visibility',
			'topic_id',
			'forum_id',
			'user_id',
			'time',
			'reason',
			'force_update_all',
			'data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.set_topic_visibility_before_sql', compact($vars)));
		$sql = 'UPDATE ' . $this->topics_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE topic_id = ' . (int) $topic_id;
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			return array();
		}

		if (!$force_update_all && $original_topic_data['topic_delete_time'] && $original_topic_data['topic_visibility'] == ITEM_DELETED && $visibility == ITEM_APPROVED)
		{
			// If we're restoring a topic we only restore posts, that were soft deleted through the topic soft deletion.
			$this->set_post_visibility($visibility, false, $topic_id, $forum_id, $user_id, $time, '', true, true, $original_topic_data['topic_visibility'], $original_topic_data['topic_delete_time']);
		}
		else if (!$force_update_all && $original_topic_data['topic_visibility'] == ITEM_APPROVED && $visibility == ITEM_DELETED)
		{
			// If we're soft deleting a topic we only mark approved posts as soft deleted.
			$this->set_post_visibility($visibility, false, $topic_id, $forum_id, $user_id, $time, '', true, true, $original_topic_data['topic_visibility']);
		}
		else
		{
			$this->set_post_visibility($visibility, false, $topic_id, $forum_id, $user_id, $time, '', true, true);
		}
		/**
		 * Perform actions after all steps to changing topic visibility
		 *
		 * @event core.set_topic_visibility_after
		 * @var			int			visibility			Element of {ITEM_APPROVED, ITEM_DELETED, ITEM_REAPPROVE}
		 * @var			int			topic_id			Topic of the post IDs to be modified.
		 * @var			int			forum_id			Forum ID that the topic_id resides in.
		 * @var			int			user_id				User ID doing this action.
		 * @var			int			time				Timestamp of this action.
		 * @var			string		reason				Reason specified by the user for this change.
		 * @var			bool		force_update_all	Force an update on all posts within the topic, regardless of their current approval state.
		 * @var			array		data				The data array for this action.
		 * @since 3.1.10-RC1
		 * @changed 3.2.2-RC1 Use time instead of non-existent timestamp
		 */
		$vars = array(
			'visibility',
			'topic_id',
			'forum_id',
			'user_id',
			'time',
			'reason',
			'force_update_all',
			'data',
		);
		extract($this->phpbb_dispatcher->trigger_event('core.set_topic_visibility_after', compact($vars)));
		return $data;
	}

	/**
	* Add post to topic and forum statistics
	*
	* @param $data			array	Contains information from the topics table about given topic
	* @param $sql_data		array	Populated with the SQL changes, may be empty at call time (by reference)
	* @return null
	*/
	public function add_post_to_statistic($data, &$sql_data)
	{
		$sql_data[$this->topics_table] = (($sql_data[$this->topics_table]) ? $sql_data[$this->topics_table] . ', ' : '') . 'topic_posts_approved = topic_posts_approved + 1';

		$sql_data[$this->forums_table] = (($sql_data[$this->forums_table]) ? $sql_data[$this->forums_table] . ', ' : '') . 'forum_posts_approved = forum_posts_approved + 1';

		if ($data['post_postcount'])
		{
			$sql_data[$this->users_table] = (($sql_data[$this->users_table]) ? $sql_data[$this->users_table] . ', ' : '') . 'user_posts = user_posts + 1';
		}

		$this->config->increment('num_posts', 1, false);
	}

	/**
	* Remove post from topic and forum statistics
	*
	* @param $data			array	Contains information from the topics table about given topic
	* @param $sql_data		array	Populated with the SQL changes, may be empty at call time (by reference)
	* @return null
	*/
	public function remove_post_from_statistic($data, &$sql_data)
	{
		if ($data['post_visibility'] == ITEM_APPROVED)
		{
			$sql_data[$this->topics_table] = ((!empty($sql_data[$this->topics_table])) ? $sql_data[$this->topics_table] . ', ' : '') . 'topic_posts_approved = topic_posts_approved - 1';
			$sql_data[$this->forums_table] = ((!empty($sql_data[$this->forums_table])) ? $sql_data[$this->forums_table] . ', ' : '') . 'forum_posts_approved = forum_posts_approved - 1';

			if ($data['post_postcount'])
			{
				$sql_data[$this->users_table] = ((!empty($sql_data[$this->users_table])) ? $sql_data[$this->users_table] . ', ' : '') . 'user_posts = user_posts - 1';
			}

			$this->config->increment('num_posts', -1, false);
		}
		else if ($data['post_visibility'] == ITEM_UNAPPROVED || $data['post_visibility'] == ITEM_REAPPROVE)
		{
			$sql_data[FORUMS_TABLE] = (($sql_data[FORUMS_TABLE]) ? $sql_data[FORUMS_TABLE] . ', ' : '') . 'forum_posts_unapproved = forum_posts_unapproved - 1';
			$sql_data[TOPICS_TABLE] = (($sql_data[TOPICS_TABLE]) ? $sql_data[TOPICS_TABLE] . ', ' : '') . 'topic_posts_unapproved = topic_posts_unapproved - 1';
		}
		else if ($data['post_visibility'] == ITEM_DELETED)
		{
			$sql_data[FORUMS_TABLE] = (($sql_data[FORUMS_TABLE]) ? $sql_data[FORUMS_TABLE] . ', ' : '') . 'forum_posts_softdeleted = forum_posts_softdeleted - 1';
			$sql_data[TOPICS_TABLE] = (($sql_data[TOPICS_TABLE]) ? $sql_data[TOPICS_TABLE] . ', ' : '') . 'topic_posts_softdeleted = topic_posts_softdeleted - 1';
		}
	}

	/**
	* Remove topic from forum statistics
	*
	* @param $data			array	Post and topic data
	* @param $sql_data		array	Populated with the SQL changes, may be empty at call time (by reference)
	* @return null
	*/
	public function remove_topic_from_statistic($data, &$sql_data)
	{
		if ($data['topic_visibility'] == ITEM_APPROVED)
		{
			$sql_data[FORUMS_TABLE] .= 'forum_posts_approved = forum_posts_approved - 1, forum_topics_approved = forum_topics_approved - 1';

			if ($data['post_postcount'])
			{
				$sql_data[$this->users_table] = ((!empty($sql_data[$this->users_table])) ? $sql_data[$this->users_table] . ', ' : '') . 'user_posts = user_posts - 1';
			}
		}
		else if ($data['topic_visibility'] == ITEM_UNAPPROVED || $data['post_visibility'] == ITEM_REAPPROVE)
		{
			$sql_data[FORUMS_TABLE] .= 'forum_posts_unapproved = forum_posts_unapproved - 1, forum_topics_unapproved = forum_topics_unapproved - 1';
		}
		else if ($data['topic_visibility'] == ITEM_DELETED)
		{
			$sql_data[FORUMS_TABLE] .= 'forum_posts_softdeleted = forum_posts_softdeleted - 1, forum_topics_softdeleted = forum_topics_softdeleted - 1';
		}

	}
}
