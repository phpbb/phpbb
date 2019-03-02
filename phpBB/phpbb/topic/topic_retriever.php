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

namespace phpbb\topic;

class topic_retriever
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $forum_track_table;

	/** @var string */
	protected $topic_table;

	/** @var string */
	protected $topic_posted_table;

	/** @var string */
	protected $topic_tracking_table;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\content_visibility $content_visibility,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\user $user,
		string $forum_track_table,
		string $topic_table,
		string $topic_posted_table,
		string $topic_tracking_table)
	{
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->user = $user;

		$this->forum_track_table = $forum_track_table;
		$this->topic_table = $topic_table;
		$this->topic_posted_table = $topic_posted_table;
		$this->topic_tracking_table = $topic_tracking_table;
	}

	public function get_topic_list_query($should_display_active_topics)
	{
		$sql_array = [
			'SELECT'	=> 't.*',
			'FROM'		=> [
				$this->topic_table => 't'
			],
			'LEFT_JOIN'	=> []
		];

		/**
		 * Event to modify the SQL query before the topic data is retrieved
		 *
		 * It may also be used to override the above assigned template vars
		 *
		 * @event core.viewforum_get_topic_data
		 * @var	array	forum_data			Array with forum data
		 * @var	array	sql_array			The SQL array to get the data of all topics
		 * @var	int		forum_id			The forum_id whose topics are being listed
		 * @var	int		topics_count		The total number of topics for display
		 * @var	int		sort_days			The oldest topic displayable in elapsed days
		 * @var	string	sort_key			The sorting by. It is one of the first character of (in low case):
		 *									Author, Post time, Replies, Subject, Views
		 * @var	string	sort_dir			Either "a" for ascending or "d" for descending
		 * @since 3.1.0-a1
		 * @changed 3.1.0-RC4 Added forum_data var
		 * @changed 3.1.4-RC1 Added forum_id, topics_count, sort_days, sort_key and sort_dir vars
		 * @changed 3.1.9-RC1 Fix types of properties
		 */
		$vars = array(
			'forum_data',
			'sql_array',
			'forum_id',
			'topics_count',
			'sort_days',
			'sort_key',
			'sort_dir',
		);
		extract($this->dispatcher->trigger_event('core.viewforum_get_topic_data', compact($vars)));

		if ($this->user->data['is_registered'])
		{
			if ($this->config['load_db_track'])
			{
				$sql_array['LEFT_JOIN'][] = [
					'FROM' => [$this->topic_posted_table => 'tp'],
					'ON' => 'tp.topic_id = t.topic_id AND tp.user_id = ' . $this->user->data['user_id']
				];

				$sql_array['SELECT'] .= ', tp.topic_posted';
			}

			if ($this->config['load_db_lastread'])
			{
				$sql_array['LEFT_JOIN'][] = [
					'FROM' => [$this->topic_tracking_table => 'tt'],
					'ON' => 'tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id']
				];

				$sql_array['SELECT'] .= ', tt.mark_time';

				if ($should_display_active_topics)
				{
					$sql_array['LEFT_JOIN'][] = array(
						'FROM' => array($this->forum_track_table => 'ft'),
						'ON' => 'ft.forum_id = t.forum_id AND ft.user_id = ' . $this->user->data['user_id']
					);

					$sql_array['SELECT'] .= ', ft.mark_time AS forum_mark_time';
				}
			}
		}

		return $sql_array;
	}

	/**
	 * Returns the topic count which have newer posts than the date specified in a specific forum.
	 *
	 * @param int $day_limit	Time limit in days.
	 * @param int $forum_id		ID of the forum.
	 *
	 * @return int Number of topics in the specified forum with posts newer than the time limit.
	 */
	public function get_topic_count_with_date_limit($day_limit, $forum_id)
	{
		$day_limit = (int) $day_limit;
		$forum_id = (int) $forum_id;

		$min_post_time = time() - ($day_limit * 86400);

		$sql_array = [
			'SELECT'	=> 'COUNT(t.topic_id) AS num_topics',
			'FROM'		=> [
				$this->topic_table => 't',
			],
			'WHERE'		=> 't.forum_id = ' . $forum_id . '
			AND (t.topic_last_post_time >= ' . $min_post_time . '
				OR t.topic_type = ' . POST_ANNOUNCE . '
				OR t.topic_type = ' . POST_GLOBAL . ')
			AND ' . $this->content_visibility->get_visibility_sql('topic', $forum_id, 't.'),
		];

		/**
		 * Modify the sort data SQL query for getting additional fields if needed
		 *
		 * @event core.viewforum_modify_sort_data_sql
		 * @var int		forum_id		The forum_id whose topics are being listed
		 * @var int		start			Variable containing start for pagination
		 * @var int		sort_days		The oldest topic displayable in elapsed days
		 * @var string	sort_key		The sorting by. It is one of the first character of (in low case):
		 *								Author, Post time, Replies, Subject, Views
		 * @var string	sort_dir		Either "a" for ascending or "d" for descending
		 * @var array	sql_array		The SQL array to get the data of all topics
		 * @since 3.1.9-RC1
		 */
		$vars = array(
			'forum_id',
			'start',
			'sort_days',
			'sort_key',
			'sort_dir',
			'sql_array',
		);
		extract($this->dispatcher->trigger_event('core.viewforum_modify_sort_data_sql', compact($vars)));

		$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_array));
		$topics_count = (int) $this->db->sql_fetchfield('num_topics');
		$this->db->sql_freeresult($result);

		return $topics_count;
	}
}
