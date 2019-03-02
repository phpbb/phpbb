<?php


namespace phpbb\forum;

/**
 * Helper object for forum data related data transformations.
 */
class data_transformer
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\reader_tracking\reader_tracker */
	protected $read_tracker;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\reader_tracking\reader_tracker $read_tracker,
		\phpbb\user $user)
	{
		$this->auth = $auth;
		$this->dispatcher = $dispatcher;
		$this->read_tracker = $read_tracker;
		$this->user = $user;
	}

	/**
	 * Builds and returns the subforums in a hierarchical structure.
	 *
	 * @param array $forum_data	The parent forum's data.
	 * @param array $forums		The query result to build the structure from.
	 *
	 * @return array 	An array in the following structure: [$forum_rows, $subforums, $valid_categories].
	 * 					$forum_rows contains the main subforums;
	 * 					$subforums contains the data of the subforums of the forums contained in $forum_rows;
	 * 					$valid_categories is a boolean array which maps the non-empty categories to their forum IDs.
	 */
	public function get_subforum_hierarchy(array $forum_data, array $forums)
	{
		$forum_rows = [];
		$subforums = [];
		$valid_categories = [];
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

			if ($row['forum_type'] == FORUM_POST && $row['user_may_read_forum'] &&
				($row['forum_flags'] & FORUM_FLAG_ACTIVE_TOPICS) &&
				$this->auth->acl_get('f_read', $forum_id))
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

	/**
	 * Returns forum IDs from the array which are not categories.
	 *
	 * @param array $rows Forum data.
	 *
	 * @return array Array of the forum IDs.
	 */
	public function get_non_category_ids(array $rows)
	{
		$rows = array_filter($rows, function($value)
		{
			return $value != FORUM_CAT;
		});

		return array_column($rows, 'forum_id');
	}
}
