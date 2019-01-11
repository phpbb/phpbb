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

namespace phpbb\forum;

use phpbb\forum\exception\forum_not_found_exception;

class forum_retriever
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\reader_tracking\reader_tracker */
	protected $read_tracker;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $forums_table;

	/** @var string */
	protected $forum_access_table;

	/** @var string */
	protected $forum_track_table;

	/** @var string */
	protected $forum_watch_table;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\reader_tracking\reader_tracker $read_tracker,
		\phpbb\user $user,
		string $forums_table,
		string $forum_access_table,
		string $forums_track_table,
		string $forums_watch_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->read_tracker = $read_tracker;
		$this->user = $user;

		$this->forums_table = $forums_table;
		$this->forum_access_table = $forum_access_table;
		$this->forum_track_table = $forums_track_table;
		$this->forum_watch_table = $forums_watch_table;
	}

	/**
	 * Queries and returns the meta data of the specified forum.
	 *
	 * @param int $forum_id The ID of the forum to query.
	 *
	 * @return array The forum metadata.
	 */
	public function get_forum_metadata($forum_id)
	{
		$forum_id = (int) $forum_id;
		$sql_from = $this->forums_table . ' f';
		$last_read_select = '';

		// Grab appropriate forum data
		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			$sql_from .= ' LEFT JOIN ' . $this->forum_track_table . ' ft ON 
				(ft.user_id = ' . (int) $this->user->data['user_id'] . ' AND ft.forum_id = f.forum_id)';
			$last_read_select .= ', ft.mark_time';
		}

		if ($this->user->data['is_registered'])
		{
			$sql_from .= ' LEFT JOIN ' . $this->forum_watch_table . ' fw ON 
				(fw.forum_id = f.forum_id AND fw.user_id = ' . (int) $this->user->data['user_id'] . ')';
			$last_read_select .= ', fw.notify_status';
		}

		$sql = 'SELECT f.* ' . $last_read_select . ' FROM ' . $sql_from . ' WHERE f.forum_id = ' . $forum_id;
		$result = $this->db->sql_query($sql);
		$forum_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$forum_data)
		{
			throw new forum_not_found_exception('NO_FORUM');
		}

		return $forum_data;
	}

	/**
	 * Returns an array of parent forums of the current forum.
	 *
	 * @param array $forum_data Array of forum metadata.
	 *
	 * @return array Parent forums.
	 */
	public function get_forum_parents(array $forum_data)
	{
		if ($forum_data['parent_id'] <= 0)
		{
			return [];
		}

		if (!empty($forum_data['forum_parents']))
		{
			return unserialize($forum_data['forum_parents']);
		}

		$forum_parents = [];

		$sql = 'SELECT forum_id, forum_name, forum_type
			FROM ' . $this->forums_table . ' 
			WHERE left_id < ' . (int) $forum_data['left_id'] . '
				AND right_id > ' . (int) $forum_data['right_id'] . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$forum_parents[$row['forum_id']] = [
				$row['forum_name'],
				(int) $row['forum_type']
			];
		}
		$this->db->sql_freeresult($result);

		$sql = 'UPDATE ' . $this->forums_table . '
			SET forum_parents = \'' . $this->db->sql_escape(serialize($forum_parents)) . '\'
			WHERE parent_id = ' . (int) $forum_data['parent_id'];
		$this->db->sql_query($sql);

		return $forum_parents;
	}

	/**
	 * Increments the forum link visited count.
	 *
	 * @param array $forum_data Array of forum metadata.
	 */
	public function update_link_count(array $forum_data)
	{
		$sql = 'UPDATE ' . $this->forums_table . '
			SET forum_posts_approved = forum_posts_approved + 1
			WHERE forum_id = ' . (int) $forum_data['forum_id'];
		$this->db->sql_query($sql);
	}

	/**
	 * Returns the subforums of a specified forum.
	 *
	 * If $forum_data['forum_id'] is 0 then it returns all root level categories and forums.
	 *
	 * @param array $forum_data Forum data array.
	 *
	 * @return array Array of subforums.
	 */
	public function get_subforums(array $forum_data)
	{
		$forum_id = (int) $forum_data['forum_id'];
		$show_active = (isset($forum_data['forum_flags']) && ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS));

		$sql_ary = [
			'SELECT'	=> 'f.*',
			'FROM'		=> [
				$this->forums_table	=> 'f'
			],
			'LEFT_JOIN'	=> [],
			'ORDER_BY'	=> 'f.left_id',
		];

		if ($forum_id !== 0)
		{
			$sql_ary['WHERE'] = 'left_id > ' . $forum_data['left_id'] . ' AND left_id < ' . $forum_data['right_id'];
			$show_active = ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS);
		}

		if ($this->config['load_db_lastread'] && $this->user->data['is_registered'])
		{
			$sql_ary['LEFT_JOIN'][] = [
				'FROM' => [
					$this->forum_track_table => 'ft'
				],
				'ON' => 'ft.user_id = ' . (int) $this->user->data['user_id'] . ' AND ft.forum_id = f.forum_id'
			];
			$sql_ary['SELECT'] .= ', ft.mark_time';
		}

		if ($show_active)
		{
			$sql_ary['LEFT_JOIN'][] = [
				'FROM'	=> [
					$this->forum_access_table => 'fa'
				],
				'ON'	=> "fa.forum_id = f.forum_id AND fa.session_id = '" . $this->db->sql_escape($this->user->session_id) . "'"
			];

			$sql_ary['SELECT'] .= ', fa.user_id';
		}

		/**
		 * Event to modify the SQL query before the forum data is queried
		 *
		 * @event core.display_forums_modify_sql
		 * @var	array	sql_ary		The SQL array to get the data of the forums
		 * @since 3.1.0-a1
		 */
		$vars = array('sql_ary');
		extract($this->dispatcher->trigger_event('core.display_forums_modify_sql', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		$forums = [];
		$branch_root_id = $forum_id;
		while ($row = $this->db->sql_fetchrow($result))
		{
			/**
			 * Event to modify the data set of a forum
			 *
			 * This event is triggered once per forum
			 *
			 * @event core.display_forums_modify_row
			 * @var	int		branch_root_id	Last top-level forum
			 * @var	array	row				The data of the forum
			 * @since 3.1.0-a1
			 */
			$vars = array('branch_root_id', 'row');
			extract($this->dispatcher->trigger_event('core.display_forums_modify_row', compact($vars)));

			if (($row['parent_id'] == $forum_id || $row['parent_id'] == $branch_root_id) &&
				($row['forum_type'] == FORUM_CAT && $row['parent_id'] == $forum_id))
			{
				$branch_root_id = (int) $row['forum_id'];
			}

			$row['branch_root_id'] = $branch_root_id;
			$forums[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $forums;
	}

	/**
	 * Builds and returns the subforums in a hierarchical structure.
	 *
	 * @param array $forum_data	The parent forum's data.
	 * @param array $forums		The query result to build the structure from.
	 *
	 * @return array 	An array in the following structure: [$forum_rows, $subforums, $valid_categories,
	 * 					$forum_moderator_ids]. $forum_rows contains the main subforums; $subforums contains the data of
	 * 					the subforums of the forums contained in $forum_rows; $valid_categories is a boolean array which
	 * 					maps the non-empty categories to their forum IDs; $forum_moderator_ids array of root level forum
	 * 					IDs.
	 */
	public function get_subforum_hierarchy(array $forum_data, array $forums)
	{
		$forum_rows = [];
		$subforums = [];
		$valid_categories = [];
		$forum_moderator_ids = [];
		$forum_tracking_info = [];
		$parent_id = 0;

		$tracking_info = $this->read_tracker->get_tracked_topics();

		foreach ($forums as $row)
		{
			$forum_id = (int) $row['forum_id'];
			$branch_root_id = (int) $row['branch_root_id'];

			if (array_key_exists($row['parent_id'], $forum_rows))
			{
				$valid_categories[$row['parent_id']] = true;
			}

			$forum_tracking_info[$forum_id] = $this->read_tracker->get_last_read_time($row, $tracking_info);

			if ($row['parent_id'] == $forum_data['forum_id'] || $row['parent_id'] == $branch_root_id)
			{
				if ($row['forum_type'] != FORUM_CAT)
				{
					$forum_moderator_ids[] = (int) $forum_id;
				}

				// Direct child of current branch
				$parent_id = $forum_id;
				$forum_rows[$forum_id] = $row;

				$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
				$forum_rows[$parent_id]['forum_password_last_post'] = $row['forum_password'];
				$forum_rows[$parent_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
				$forum_rows[$parent_id]['may_display_last_post'] = $row['may_display_last_post'];
			}
			else if ($row['forum_type'] != FORUM_CAT)
			{
				$subforums[$parent_id][$forum_id]['display'] = ($row['display_on_index']) ? true : false;
				$subforums[$parent_id][$forum_id]['name'] = $row['forum_name'];
				$subforums[$parent_id][$forum_id]['orig_forum_last_post_time'] = $row['forum_last_post_time'];
				$subforums[$parent_id][$forum_id]['children'] = [];
				$subforums[$parent_id][$forum_id]['type'] = $row['forum_type'];

				if (isset($subforums[$parent_id][$row['parent_id']]) && !$row['display_on_index'])
				{
					$subforums[$parent_id][$row['parent_id']]['children'][] = $forum_id;
				}

				if (!$forum_rows[$parent_id]['forum_id_unapproved_topics'] && $row['forum_id_unapproved_topics'])
				{
					$forum_rows[$parent_id]['forum_id_unapproved_topics'] = $forum_id;
				}

				if (!$forum_rows[$parent_id]['forum_id_unapproved_posts'] && $row['forum_id_unapproved_posts'])
				{
					$forum_rows[$parent_id]['forum_id_unapproved_posts'] = $forum_id;
				}

				$forum_rows[$parent_id]['forum_topics'] += $row['forum_topics'];

				// Do not list redirects in LINK Forums as Posts.
				if ($row['forum_type'] != FORUM_LINK)
				{
					$forum_rows[$parent_id]['forum_posts'] += $row['forum_posts'];
				}

				if ($row['forum_last_post_time'] > $forum_rows[$parent_id]['forum_last_post_time'])
				{
					$forum_rows[$parent_id]['forum_last_post_id'] = $row['forum_last_post_id'];
					$forum_rows[$parent_id]['forum_last_post_subject'] = $row['forum_last_post_subject'];
					$forum_rows[$parent_id]['forum_last_post_time'] = $row['forum_last_post_time'];
					$forum_rows[$parent_id]['forum_last_poster_id'] = $row['forum_last_poster_id'];
					$forum_rows[$parent_id]['forum_last_poster_name'] = $row['forum_last_poster_name'];
					$forum_rows[$parent_id]['forum_last_poster_colour'] = $row['forum_last_poster_colour'];
					$forum_rows[$parent_id]['forum_id_last_post'] = $forum_id;
					$forum_rows[$parent_id]['forum_password_last_post'] = $row['forum_password'];
					$forum_rows[$parent_id]['may_display_last_post'] = $row['may_display_last_post'];
				}
			}

			/**
			 * Event to modify the forum rows data set
			 *
			 * This event is triggered once per forum
			 *
			 * @event core.display_forums_modify_forum_rows
			 * @var	array	forum_rows		Data array of all forums we display
			 * @var	array	subforums		Data array of all subforums we display
			 * @var	int		branch_root_id	Current top-level forum
			 * @var	int		parent_id		Current parent forum
			 * @var	array	row				The data of the forum
			 * @since 3.1.0-a1
			 */
			$vars = array('forum_rows', 'subforums', 'branch_root_id', 'parent_id', 'row');
			extract($this->dispatcher->trigger_event('core.display_forums_modify_forum_rows', compact($vars)));
		}

		return [
			$forum_rows,
			$subforums,
			$valid_categories,
			$forum_moderator_ids,
			$forum_tracking_info
		];
	}

	/**
	 * Returns an array with forum data to display active topics.
	 *
	 * @param array $forum_data	Forum metadata.
	 * @param array $forum_rows	Forum rows.
	 *
	 * @return array Array with forum data to display active topics.
	 */
	public function get_active_topic_array(array $forum_data, array $forum_rows)
	{
		$show_active = (array_key_exists('forum_flags', $forum_data) && ($forum_data['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS));

		if (!$show_active)
		{
			return [];
		}

		$active_forum_array = [
			'forum_topics'	=> 0,
			'forum_posts'	=> 0
		];

		foreach ($forum_rows as $row)
		{
			$forum_id = (int) $row['forum_id'];

			if ($row['forum_type'] == FORUM_POST && $row['user_may_read_forum'] && ($row['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS))
			{
				$active_forum_array['forum_id'][]		= $forum_id;
				$active_forum_array['enable_icons'][]	= $row['enable_icons'];
				$active_forum_array['forum_topics']		+= $row['forum_topics'];
				$active_forum_array['forum_posts']		+= $row['forum_posts'];

				// If this is a passworded forum we do not show active topics from it if the user is not authorised to view it...
				if ($row['forum_password'] && $row['user_id'] != $this->user->data['user_id'])
				{
					$active_forum_array['exclude_forum_id'][] = $forum_id;
				}
			}
		}

		return $active_forum_array;
	}
}
