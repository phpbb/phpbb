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
 * Class to handle viewonline related tasks
 */
class viewonline_helper
{
	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * @param \phpbb\filesystem\filesystem_interface $filesystem	phpBB's filesystem service
	 * @param \phpbb\db\driver\driver_interface $db
	 */
	public function __construct(\phpbb\filesystem\filesystem_interface $filesystem, \phpbb\db\driver\driver_interface $db)
	{
		$this->filesystem = $filesystem;
		$this->db = $db;
	}

	/**
	 * Get forum IDs for topics
	 *
	 * Retrieve forum IDs and add the data into the session data array
	 * Array structure matches sql_fethrowset() result array
	 *
	 * @param array $session_data_rowset Users' session data array
	 * @return void
	 */
	public function get_forum_ids(array &$session_data_rowset): void
	{
		$topic_ids = $match = [];
		foreach ($session_data_rowset as $number => $row)
		{
			if ($row['session_forum_id'] == 0 && preg_match('#t=([0-9]+)#', $row['session_page'], $match))
			{
				$topic_ids[$number] = (int) $match[1];
			}
		}

		if (count($topic_ids = array_unique($topic_ids)))
		{
			$sql_ary = [
				'SELECT'	=> 't.topic_id, t.forum_id',
				'FROM'		=> [
					TOPICS_TABLE => 't',
				],
				'WHERE'		=> $this->db->sql_in_set('t.topic_id', $topic_ids),
				'ORDER_BY'	=> 't.topic_id',
			];
			$result = $this->db->sql_query($this->db->sql_build_query('SELECT', $sql_ary));
			$forum_ids_rowset = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);

			foreach ($forum_ids_rowset as $forum_ids_row)
			{
				$session_data_row_number = array_search((int) $forum_ids_row['topic_id'], $topic_ids);
				$session_data_rowset[$session_data_row_number]['session_forum_id'] = (int) $forum_ids_row['forum_id'];
			}
		}
	}

	/**
	 * Get user page
	 *
	 * @param string $session_page User's session page
	 * @return array Match array filled by preg_match()
	 */
	public function get_user_page($session_page)
	{
		$session_page = $this->filesystem->clean_path($session_page);
		if (strpos($session_page, './') === 0)
		{
			$session_page = substr($session_page, 2);
		}

		preg_match('#^((\.\./)*([a-z0-9/_-]+))#i', $session_page, $on_page);
		if (empty($on_page))
		{
			$on_page[1] = '';
		}

		return $on_page;
	}
}
