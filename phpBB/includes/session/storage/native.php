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
			$sql .= ' AND user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ')';
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

	public function delete_session_user_id($user_id)
	{
		$this->update_query('
			DELETE FROM ' . SESSIONS_TABLE .'
			WHERE session_user_id = ' . (int) $user_id
		);
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

	public function update_last_visit($time, $user, $page='')
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_lastvisit = ' . (int) $time;
		if(!empty($page))
		{
			$sql .= ", user_lastpage = '" . $this->db->sql_escape($page) . "'";
		}
		$sql .=	' WHERE user_id = ' . (int) $user;
		$this->update_query($sql);
	}

	public function update_form_salt($salt, $user)
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_form_salt = \'' . $this->db->sql_escape($salt) . '\'
					WHERE user_id = ' . (int) $user;
		$this->update_query($sql);
	}

	public function remove_session_key($user, $key)
	{
		$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
					WHERE user_id = ' . (int) $user . "
						AND key_id = '" . $this->db->sql_escape(md5($key)) . "'";
		$this->update_query($sql);

	}

	public function update_session_key($user, $key, $data)
	{
		$sql = 'UPDATE ' . SESSIONS_KEYS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE user_id = ' . (int) $user . "
					AND key_id = '" . $this->db->sql_escape(md5($key)) . "'";
		$this->update_query($sql);
	}

	public function insert_session_key($data)
	{
		$this->update_query(
			'INSERT INTO ' . SESSIONS_KEYS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $data)
		);
	}

	public function cleanup_guest_sessions($session_length)
	{
		$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
			WHERE session_user_id = ' . ANONYMOUS . '
				AND session_time < ' . (int) ($this->time_now - $session_length);
		$this->update_query($sql);
	}

	public function cleanup_attempt_table($ip_login_limit_time)
	{
		$sql = 'DELETE FROM ' . LOGIN_ATTEMPT_TABLE . '
				WHERE attempt_time < ' . (time() - (int) $ip_login_limit_time);
		$this->update_query($sql);
	}

	public function banlist($user_email, $user_ips, $user_id, $cache_ttl)
	{
		$where_sql = array();
		$sql = 'SELECT ban_ip, ban_userid, ban_email, ban_exclude, ban_give_reason, ban_end
			FROM ' . BANLIST_TABLE . '
			WHERE ';

		// Determine which entries to check, only return those
		if ($user_email === false)
		{
			$where_sql[] = "ban_email = ''";
		}

		if ($user_ips === false)
		{
			$where_sql[] = "(ban_ip = '' OR ban_exclude = 1)";
		}

		if ($user_id === false)
		{
			$where_sql[] = '(ban_userid = 0 OR ban_exclude = 1)';
		}
		else
		{
			$cache_ttl = ($user_id == ANONYMOUS) ? 3600 : 0;
			$_sql = '(ban_userid = ' . $user_id;

			if ($user_email !== false)
			{
				$_sql .= " OR ban_email <> ''";
			}

			if ($user_ips !== false)
			{
				$_sql .= " OR ban_ip <> ''";
			}

			$_sql .= ')';

			$where_sql[] = $_sql;
		}
		$sql .= (sizeof($where_sql)) ? implode(' AND ', $where_sql) : '';
		return $this->db->sql_query($sql, $cache_ttl);
	}

}
