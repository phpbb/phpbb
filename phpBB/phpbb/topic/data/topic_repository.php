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

namespace phpbb\topic\data;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\content_visibility;
use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\event\dispatcher_interface as event_dispatcher_interface;
use phpbb\topic\enumeration\topic_types;
use phpbb\user;

/**
 * Class for querying topic related data from the database.
 */
class topic_repository
{
	/**
	 * @var auth
	 */
	private $auth;

	/**
	 * @var config
	 */
	private $config;

	/**
	 * @var content_visibility
	 */
	private $content_visibility;

	/**
	 * @var db_driver_interface
	 */
	private $db;

	/**
	 * @var event_dispatcher_interface
	 */
	private $dispatcher;

	/**
	 * @var user
	 */
	private $user;

	/**
	 * @var string
	 */
	private $forums_table;

	/**
	 * @var string
	 */
	private $forums_tracked_table;

	/**
	 * @var string
	 */
	private $topics_table;

	/**
	 * @var string
	 */
	private $topics_posted_table;

	/**
	 * @var string
	 */
	private $topics_track_table;

	/**
	 * Constructor.
	 *
	 * @param auth							$auth					Auth service.
	 * @param config						$config					Configuration object.
	 * @param content_visibility			$content_visibility		Content visibility helper.
	 * @param db_driver_interface			$db						Database driver.
	 * @param event_dispatcher_interface	$dispatcher				Evenet dispatcher.
	 * @param user							$user					User object.
	 * @param string						$forums_table			Forums table's name.
	 * @param string						$forums_tracked_table	Forums tracking database table's name.
	 * @param string						$topics_table			Topics database table's name.
	 * @param string						$topics_posted_table	Topics posted database table's name.
	 * @param string						$topics_track_table		Topics tracking database table's name.
	 */
	public function __construct(
		auth $auth,
		config $config,
		content_visibility $content_visibility,
		db_driver_interface $db,
		event_dispatcher_interface $dispatcher,
		user $user,
		string $forums_table,
		string $forums_tracked_table,
		string $topics_table,
		string $topics_posted_table,
		string $topics_track_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->user = $user;

		$this->forums_table = $forums_table;
		$this->forums_tracked_table = $forums_tracked_table;
		$this->topics_table = $topics_table;
		$this->topics_posted_table = $topics_posted_table;
		$this->topics_track_table = $topics_track_table;
	}

	/**
	 * Builds a SQL query array to query topic information for the topic list.
	 *
	 * @param int	$forum_id			The ID of the forum.
	 * @param bool	$s_display_active	@todo
	 * @param array	$active_forum_ary	@todo
	 *
	 * @return array An array of [sql_array, sql_approved] to be used to build a query.
	 */
	public function build_base_query(int $forum_id, bool $s_display_active, array $active_forum_ary)
	{
		$sql_array = [
			'SELECT' => 't.*',
			'FROM' => [
				$this->topics_table => 't'
			],
			'LEFT_JOIN' => [],
		];

		/**
		 * Event to modify the SQL query before the topic data is retrieved
		 *
		 * It may also be used to override the above assigned template vars
		 *
		 * @event core.viewforum_get_topic_data
		 * @var array	sql_array		The SQL array to get the data of all topics.
		 * @var int		forum_id		The forum_id whose topics are being listed.
		 *
		 * @since 3.1.0-a1
		 * @changed 3.1.0-RC4 Added forum_data var.
		 * @changed 3.1.4-RC1 Added forum_id, topics_count, sort_days, sort_key and sort_dir vars.
		 * @changed 3.1.9-RC1 Fix types of properties.
		 * @changed 3.4.0-a1 Removed forum_data, topics_count, sort_days, sort_key and sort_dir vars.
		 */
		$vars = [
			'sql_array',
			'forum_id',
		];
		extract($this->dispatcher->trigger_event('core.viewforum_get_topic_data', compact($vars)));

		$sql_approved = ' AND ' . $this->content_visibility->get_visibility_sql('topic', $forum_id, 't.');

		if ($this->user->data['is_registered'])
		{
			if ($this->config['load_db_track'])
			{
				$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->topics_posted_table => 'tp'], 'ON' => 'tp.topic_id = t.topic_id AND tp.user_id = ' . $this->user->data['user_id']];
				$sql_array['SELECT'] .= ', tp.topic_posted';
			}

			if ($this->config['load_db_lastread'])
			{
				$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->topics_track_table => 'tt'], 'ON' => 'tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id']];
				$sql_array['SELECT'] .= ', tt.mark_time';

				if ($s_display_active && count($active_forum_ary))
				{
					$sql_array['LEFT_JOIN'][] = ['FROM' => [$this->forums_tracked_table => 'ft'], 'ON' => 'ft.forum_id = t.forum_id AND ft.user_id = ' . $this->user->data['user_id']];
					$sql_array['SELECT'] .= ', ft.mark_time AS forum_mark_time';
				}
			}
		}
		return [$sql_array, $sql_approved];
	}

	/**
	 * Get announcement topics.
	 *
	 * @param int	$forum_id	The forum ID.
	 * @param array	$sql_array	The SQL query parameters in an array.
	 *
	 * @return array Array of announcement topic data, topic IDs and global announcement forum IDs.
	 */
	public function get_announcement_topics(int $forum_id, array $sql_array)
	{
		// Get global announcement forums
		$announcement_forum_ids = $this->auth->acl_getf('f_read', true);
		$announcement_forum_ids = array_unique(array_keys($announcement_forum_ids));

		// Build the query.
		$sql_announce_array['LEFT_JOIN'] = $sql_array['LEFT_JOIN'];
		$sql_announce_array['LEFT_JOIN'][] = ['FROM' => [$this->forums_table => 'f'], 'ON' => 'f.forum_id = t.forum_id'];
		$sql_announce_array['SELECT'] = $sql_array['SELECT'] . ', f.forum_name';

		// Obtain announcements ... removed sort ordering, sort by time in all cases
		$sql_ary = [
			'SELECT'	=> $sql_announce_array['SELECT'],
			'FROM'		=> $sql_array['FROM'],
			'LEFT_JOIN'	=> $sql_announce_array['LEFT_JOIN'],
			'WHERE'		=> '(t.forum_id = ' . $forum_id . '
				AND t.topic_type = ' . topic_types::POST_ANNOUNCE . ') OR
				(' . $this->db->sql_in_set('t.forum_id', $announcement_forum_ids, false, true) . '
				AND t.topic_type = ' . topic_types::POST_GLOBAL . ')',
			'ORDER_BY'	=> 't.topic_time DESC',
		];

		/**
		 * Event to modify the SQL query before the announcement topic ids data is retrieved
		 *
		 * @event core.viewforum_get_announcement_topic_ids_data
		 * @var array	g_forum_ary			Global announcement forums array
		 * @var array	sql_anounce_array	SQL announcement array
		 * @var array	sql_ary				SQL query array to get the announcement topic ids data
		 * @var int		forum_id			The forum ID
		 *
		 * @since 3.1.10-RC1
		 * @changed 3.4.0-a1 Removed forum_data.
		 */
		$sql_anounce_array = $sql_announce_array;
		$g_forum_ary = $announcement_forum_ids;
		$vars = [
			'g_forum_ary',
			'sql_anounce_array',
			'sql_ary',
			'forum_id',
		];
		extract($this->dispatcher->trigger_event('core.viewforum_get_announcement_topic_ids_data', compact($vars)));
		unset($vars, $g_forum_ary, $sql_anounce_array);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		$rowset = [];
		$announcement_list = [];
		$global_announce_forums = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->content_visibility->is_visible('topic', $row['forum_id'], $row))
			{
				// Do not display announcements that are waiting for approval or soft deleted.
				continue;
			}

			$topic_id = (int) $row['topic_id'];
			$current_forum_id = (int) $row['forum_id'];

			$rowset[$topic_id] = $row;
			$announcement_list[] = $topic_id;

			if ($forum_id !== $current_forum_id)
			{
				$global_announce_forums[] = $current_forum_id;
			}
		}
		$this->db->sql_freeresult($result);
		return [$rowset, $announcement_list, $global_announce_forums];
	}
}
