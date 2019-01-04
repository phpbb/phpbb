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

namespace phpbb\viewtopic;

use phpbb\viewtopic\exception\topic_not_found_exception;

/**
 * Class for retrieving topic data.
 */
class topic_retriever
{
	/**
	 * @var string
	 */
	protected $bookmark_table;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var string
	 */
	protected $forum_table;

	/**
	 * @var string
	 */
	protected $forum_track_table;

	/**
	 * @var string
	 */
	protected $post_table;

	/**
	 * @var array
	 */
	protected $topic_data;

	/**
	 * @var string
	 */
	protected $topic_table;

	/**
	 * @var string
	 */
	protected $topic_track_table;

	/**
	 * @var string
	 */
	protected $topic_watch_table;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var integer
	 */
	protected $user_id;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config				Config object.
	 * @param \phpbb\db\driver\driver_interface	$db					Database driver.
	 * @param \phpbb\event\dispatcher_interface	$dispatcher			Event dispatcher.
	 * @param \phpbb\user 						$user				User object
	 * @param string							$bookmark_table		Name of the bookmarks table.
	 * @param string							$forum_table		Name of the forums table.
	 * @param string							$forum_track_table	Name of the forums track table.
	 * @param string							$post_table			Name of the posts table.
	 * @param string							$topic_table		Name of the topics table.
	 * @param string							$topic_track_table	Name of the topics track table.
	 * @param string							$topic_watch_table	Name of the topics watch table.
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\user $user,
		$bookmark_table,
		$forum_table,
		$forum_track_table,
		$post_table,
		$topic_table,
		$topic_track_table,
		$topic_watch_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->user = $user;

		$this->bookmark_table = $bookmark_table;
		$this->forum_table = $forum_table;
		$this->forum_track_table = $forum_track_table;
		$this->post_table = $post_table;
		$this->topic_table = $topic_table;
		$this->topic_track_table = $topic_track_table;
		$this->topic_watch_table = $topic_watch_table;

		$this->topic_data = [];
		$this->user_id = (int) $this->user->data['user_id'];
	}

	/**
	 * Returns topic data from topic ID.
	 *
	 * @param integer $topic_id Topic ID.
	 *
	 * @return array Array containing the topic properties.
	 */
	public function get_topic_by_id($topic_id)
	{
		$topic_id = (int) $topic_id;
		if ($topic_id < 1)
		{
			throw new topic_not_found_exception();
		}

		$sql_ary = $this->get_sql_array();
		$sql_ary['WHERE'] .= ' AND t.topic_id = ' . $topic_id;

		$this->query_topic_data($sql_ary);

		return $this->topic_data;
	}

	/**
	 * Returns topic data from a post's ID.
	 *
	 * @param integer $post_id Post ID.
	 *
	 * @return array Array containing the topic properties.
	 */
	public function get_topic_by_post($post_id)
	{
		$post_id = (int) $post_id;
		if ($post_id < 1)
		{
			throw new topic_not_found_exception();
		}

		$sql_ary = $this->get_sql_array();
		$sql_ary['SELECT'] .= ', p.post_visibility, p.post_time, p.post_id';
		$sql_ary['FROM'] = array_merge(
			[$this->post_table => 'p'],
			$sql_ary['FROM']
		);
		$sql_ary['WHERE'] = ' AND p.post_id = ' . $post_id . ' AND t.topic_id = p.topic_id';

		$this->query_topic_data($sql_ary);

		return $this->topic_data;
	}

	/**
	 * Returns the last loaded topic's data.
	 *
	 * @return array The topic data.
	 */
	public function get_topic_data()
	{
		return $this->topic_data;
	}

	/**
	 * Returns the common parts for building the SQL query.
	 *
	 * @return array Common parts of SQL for the SQL builder.
	 */
	protected function get_sql_array()
	{
		$sql_ary = [
			'SELECT'	=> 't.*, f.*',
			'FROM'		=> [
				$this->forum_table => 'f',
				$this->topic_table => 't'
			],
			'WHERE' 	=> 'f.forum_id = t.forum_id'
		];

		if ($this->user->data['is_registered'])
		{
			$sql_ary['SELECT']		.= ', tw.notify_status';
			$sql_ary['LEFT_JOIN']	= [
				[
					'FROM'	=> [$this->topic_watch_table => 'tw'],
					'ON'	=> 'tw.user_id = ' . $this->user_id . ' AND t.topic_id = tw.topic_id'
				]
			];

			if ($this->config['allow_bookmarks'])
			{
				$sql_ary['SELECT'] .= ', bm.topic_id as bookmarked';
				$sql_ary['LEFT_JOIN'][] = [
					'FROM'	=> [$this->bookmark_table => 'bm'],
					'ON'	=> 'bm.user_id = ' . $this->user_id . ' AND t.topic_id = bm.topic_id'
				];
			}

			if ($this->config['load_db_lastread'])
			{
				$sql_ary['SELECT'] .= ', tt.mark_time, ft.mark_time as forum_mark_time';

				$sql_ary['LEFT_JOIN'][] = [
					'FROM'	=> [$this->topic_track_table => 'tt'],
					'ON'	=> 'tt.user_id = ' . $this->user_id . ' AND t.topic_id = tt.topic_id'
				];

				$sql_ary['LEFT_JOIN'][] = [
					'FROM'	=> [$this->forum_track_table => 'ft'],
					'ON'	=> 'ft.user_id = ' . $this->user_id . ' AND t.forum_id = ft.forum_id'
				];
			}
		}

		return $sql_ary;
	}

	/**
	 * Executes the SQL query to retrieve topic data.
	 *
	 * @param array $sql_array SQL array for SQL builder.
	 */
	protected function query_topic_data($sql_array)
	{
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$this->topic_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$this->topic_data)
		{
			throw new topic_not_found_exception();
		}

		$topic_data = $this->topic_data;
		$forum_id = (int) $this->topic_data['forum_id'];

		/**
		 * Modify the forum ID to handle the correct display of viewtopic if needed
		 *
		 * @event core.viewtopic_modify_forum_id
		 * @var int		forum_id		forum ID
		 * @var array	topic_data		array of topic's data
		 * @since 3.2.5-RC1
		 */
		$vars = array(
			'forum_id',
			'topic_data',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_modify_forum_id', compact($vars)));
		$this->topic_data = $topic_data;
		$this->topic_data['forum_id'] = (int) $forum_id;

		$this->update_topic_meta_data();
	}

	/**
	 * Updates the meta data for time restricted topic types.
	 */
	protected function update_topic_meta_data()
	{
		// Check sticky/announcement/global  time limit
		if ($this->topic_data['topic_type'] != POST_NORMAL &&
			$this->topic_data['topic_time_limit'] &&
			($this->topic_data['topic_time'] + $this->topic_data['topic_time_limit']) < time())
		{
			$sql = 'UPDATE ' . $this->topic_table . '
				SET topic_type = ' . POST_NORMAL . ', topic_time_limit = 0
				WHERE topic_id = ' . (int) $this->topic_data['topic_id'];

			$this->db->sql_query($sql);

			$this->topic_data['topic_type'] = POST_NORMAL;
			$this->topic_data['topic_time_limit'] = 0;
		}
	}
}
