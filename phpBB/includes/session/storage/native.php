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
	protected $time_now;

	public function __construct()
	{
		global $db;
		$this->db = $db;
		$this->time_now = time();
	}

	protected function query($sql)
	{
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $data;
	}

	protected function update_query($sql)
	{
		return $this->db->sql_query($sql);
	}

	public function set_time_now($time_now)
	{
		$this->time_now = $time_now;
	}

	public function create($session_data)
	{
		$sql = 'INSERT INTO ' . SESSIONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $session_data);
		$this->update_query($sql);
	}

	public function update($session_id, $session_data)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $session_data) . "
			WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		$this->update_query($sql);
	}

	public function get($session_id)
	{
		$sql = 'SELECT u.*, s.*
				FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . " u
				WHERE s.session_id = '" . $this->db->sql_escape($session_id) . "'
					AND u.user_id = s.session_user_id";
		return $this->query($sql);
	}

	public function get_with_user_id($user_id)
	{
		$sql = 'SELECT u.*, s.*
					FROM ' . USERS_TABLE . ' u
					LEFT JOIN ' . SESSIONS_TABLE . ' s ON (s.session_user_id = u.user_id)
					WHERE u.user_id = ' . (int) $user_id;
		return $this->query($sql);
	}

	public function get_user_info($user_id, $normal_found_only=false)
	{
		$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $user_id;
		if ($normal_found_only)
		{
			$sql .= 'AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
		}
		return $this->query($sql);
	}

	public function get_user_info_with_key($user, $session_key)
	{
		$sql = 'SELECT u.*
					FROM ' . USERS_TABLE . ' u, ' . SESSIONS_KEYS_TABLE . ' k
					WHERE u.user_id = ' . (int) $user . '
						AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ")
						AND k.user_id = u.user_id
						AND k.key_id = '" . $this->db->sql_escape(md5($session_key)) . "'";
		return $this->query($sql);
	}

	public function get_newest($user_id)
	{
		$sql = 'SELECT *
			FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . (int) $user_id . '
			ORDER BY session_time DESC';
		return $this->query($sql);
	}

	public function delete_by_user_id($user_id)
	{
		$this->update_query('DELETE FROM ' . SESSIONS_TABLE . ' WHERE session_user_id = ' . (int) $user_id);
	}

	public function delete($session_id, $user_id = false)
	{
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
				WHERE session_id = \'' . $this->db->sql_escape($session_id) . '\' ';

		if ($user_id !== false)
		{
			$sql .= 'AND session_user_id = ' . (int) $user_id;
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
		return $this->query($sql);
	}

	public function num_sessions($user_id, $min_time)
	{
		$sql = 'SELECT COUNT(session_id) AS sessions
			FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . (int) $user_id . '
				AND session_time >= ' . (int) $min_time;
		return $this->query($sql);
	}

	public function unset_admin($session_id)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
			SET session_admin = 0
			WHERE session_id = \'' . $this->db->sql_escape($session_id) . '\'';
		$this->update_query($sql);
	}

	public function set_viewonline($user_id, $viewonline)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
			SET session_viewonline = ' . (int) $viewonline . '
			WHERE session_user_id = ' . $user_id;
		$this->update_query($sql);
	}

	public function update_session($session_data, $session_id)
	{
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
		 		SET ' . $this->db->sql_build_array('UPDATE', $session_data) . "
				WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		return $this->update_query($sql);
	}
}
