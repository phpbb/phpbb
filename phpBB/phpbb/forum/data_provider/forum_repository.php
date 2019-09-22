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

namespace phpbb\forum\data_provider;

/**
 * Forum repository class.
 */
class forum_repository
{
	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	private $db;

	/**
	 * @var string
	 */
	private $forums_table;

	/**
	 * @var string
	 */
	private $forums_track_table;

	/**
	 * @var string
	 */
	private $forums_watch_table;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		$forums_table,
		$forums_track_table,
		$forums_watch_table)
	{
		$this->db = $db;

		$this->forums_table = $forums_table;
		$this->forums_track_table = $forums_track_table;
		$this->forums_watch_table = $forums_watch_table;
	}

	/**
	 * Retrieve forum data by forum ID.
	 *
	 * @param int	$forum_id		Forum ID.
	 * @param bool	$read_tracking	Whether or not to query read tracking information.
	 * @param bool	$watch_tracking	Whether or not to query forum watching information.
	 * @param int	$user_id		The current user's ID.
	 *
	 * @return array Forum data.
	 */
	public function get_forum_by_id($forum_id, $read_tracking = false, $watch_tracking = false, $user_id = 0)
	{
		$sql_array = [
			'SELECT'	=> 'f.*',
			'FROM'		=> [
				$this->forums_table	=> 'f',
			],
			'WHERE'	=> 'f.forum_id = ' . (int) $forum_id,
		];

		if ($read_tracking)
		{
			$sql_array = $this->join_forum_tracking($sql_array, $user_id);
		}

		if ($watch_tracking)
		{
			$sql_array = $this->join_forum_watching($sql_array, $user_id);
		}

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$forum_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $forum_data;
	}

	/**
	 * Add forum tracking information to the query.
	 *
	 * @param array	$sql_array	The query array.
	 * @param int	$user_id	User ID.
	 *
	 * @return array SQL query array.
	 */
	private function join_forum_tracking($sql_array, $user_id)
	{
		$sql_array['SELECT'] .= ', ft.mark_time';
		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [$this->forums_track_table => 'ft'],
			'ON'	=> 'ft.user_id = ' . (int) $user_id . ' AND ft.forum_id = f.forum_id',
		];

		return $sql_array;
	}

	/**
	 * Add forum watching information to the query.
	 *
	 * @param array	$sql_array	The query array.
	 * @param int	$user_id	User ID.
	 *
	 * @return array SQL query array.
	 */
	private function join_forum_watching($sql_array, $user_id)
	{
		$sql_array['SELECT'] .= ', fw.notify_status';
		$sql_array['LEFT_JOIN'][] = [
			'FROM'	=> [$this->forums_watch_table => 'fw'],
			'ON'	=> 'fw.forum_id = f.forum_id AND fw.user_id = ' . (int) $user_id,
		];

		return $sql_array;
	}
}
