<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


class phpbb_session_storage_native
{
	protected $db;

	public function __construct()
	{
		global $db;
		$this->db = $db;
		$this->time_now = time();
	}

	public function set_time_now($time_now)
	{
		$this->time_now = $time_now;
	}

	public function create($session_data)
	{
		$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $session_data);
		$this->db->sql_query($sql);
	}

	public function update($session_id, $session_data)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $session_data) . "
			WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		$this->db->sql_query($sql);
	}

	public function get($session_id)
	{
		$sql = 'SELECT u.*, s.*
			FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
			WHERE s.session_id = '" . $this->db->sql_escape($session_id) . "'
				AND u.user_id = s.session_user_id";
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	public function get_newest($user_id)
	{
		$sql = 'SELECT s.*
			FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . (int) $user_id . '
			ORDER BY session_time DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	public function delete_by_user_id($user_id)
	{
		$this->db->sql_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . (int) $user_id);
	}

	public function delete($session_id, $user_id = false)
	{
		$sql = 'DELETE
			FROM ' . SESSIONS_TABLE . '
			WHERE session_id = \'' . $this->db->sql_escape($session_id) . '\'';

		if ($user_id !== false)
		{
			$sql .= 'AND session_user_id = ' . $user_id;
		}

		$result = $this->db->sql_query($sql);

		if (!$result)
		{
			return false;
		}

		if (!$this->db->sql_affectedrows())
		{
			return false;
		}

		return true;
	}

	public function num_active_sessions()
	{
		$sql = 'SELECT COUNT(session_id) AS sessions
			FROM ' . SESSIONS_TABLE . '
			WHERE session_time >= ' . ($this->time_now - 60);
		$result = $this->db->sql_query($sql);
		$num = (int) $this->db->sql_fetchfield($result);
		$this->db->sql_freeresult($result);

		return $num;
	}

	public function num_sessions($user_id, $min_time)
	{
		$sql = 'SELECT COUNT(session_id) AS sessions
			FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . (int) $user_id . '
				AND session_time >= ' . (int) $min_time;
		$result = $this->db->sql_query($sql);
		$num = (int) $this->db->sql_fetchfield($result);
		$this->db->sql_freeresult($result);

		return $num;
	}

	public function unset_admin($session_id)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
			SET session_admin = 0
			WHERE session_id = \'' . $this->db->sql_escape($session_id) . '\'';
		$this->db->sql_query($sql);
	}

	public function set_viewonline($user_id, $viewonline)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
			SET session_viewonline = ' . (int) $viewonline . '
			WHERE session_user_id = ' . $user_id;
		$this->db->sql_query($sql);
	}
}
