<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\data\v310;

class topic_sort_username_clean extends \phpbb\db\migration\migration
{
	protected $user_array;

	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'topics', 'topic_first_poster_name_clean');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'topics'	=> array(
					'topic_first_poster_name_clean' => array('VCHAR_UNI:255', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'topics'	=> array(
					'topic_first_poster_name_clean',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_topics'))),
		);
	}

	public function build_user_array()
	{
		if (!$this->user_array)
		{
			$this->user_array = array();
			// First be get the username_clean field for all users to save some db load.
			$sql = 'SELECT user_id, username_clean
				FROM ' . $this->table_prefix . 'users
				ORDER BY user_id ASC';

			$result = $this->db->sql_query($sql);
			while($row = $this->db->sql_fetchrow($result))
			{
				$this->user_array[$row['user_id']] = $row['username_clean'];
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	 * Warning : the function could take a long time (over 30 seconds)
	 */
	public function update_topics($start)
	{
		$start = (int) $start;
		$limit = 500;
		$converted = 0;

		$this->build_user_array();

		$sql = 'SELECT topic_id, topic_poster, topic_first_poster_name
			FROM ' . $this->table_prefix . 'topics
			ORDER BY topic_id ASC';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while($row = $this->db->sql_fetchrow($result))
		{
			if($row['topic_poster'] == ANONYMOUS)
			{
				if($row['topic_first_poster_name'] != '')
				{
					$username = utf8_clean_string($row['topic_first_poster_name']);
				}
				else
				{
					// It is possible to use $user->lang['GUEST'] ?
					$username = utf8_clean_string('Guest');
				}
			}
			else
			{
				if(isset($this->user_array[$row['topic_poster']]))
				{
					$username = $this->user_array[$row['topic_poster']];
				}
				else if($row['topic_first_poster_name'] != '')
				{
					$username = utf8_clean_string($row['topic_first_poster_name']);
				}
				else
				{
					// It is possible to use $user->lang['GUEST'] ?
					$username = utf8_clean_string('Guest');
				}
			}

			$converted++;

			$sql = 'UPDATE ' . $this->table_prefix . 'topics SET topic_first_poster_name_clean = "' . $this->db->sql_escape($username) . '" WHERE topic_id = ' . $row['topic_id'];
			$this->db->sql_query($sql);
		}
		$this->db->sql_freeresult($result);

		if ($converted == $limit) {
			// There are still more to convert
			return $start + $limit;
		}
	}
}
