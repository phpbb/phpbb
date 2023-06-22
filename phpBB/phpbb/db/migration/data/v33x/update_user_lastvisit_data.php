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

namespace phpbb\db\migration\data\v33x;

class update_user_lastvisit_data extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v33x\v3310',
		];
	}

	public function update_data()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'sessions' => [
					'session_last_visit',
				],
			],
			['custom', [[$this, 'update_user_lastvisit_fields']]],
		];
	}

	public function update_user_lastvisit_fields()
	{
		/**
		 * Logic is borrowed from function session_gc()
		 *
		 * Get most recent sessions for registered users
		 * Inner SELECT gets most recent sessions for unique session_user_id
		 * Outer SELECT gets data for them
		 */
		$sql_select = 'SELECT s1.session_user_id, s1.session_time AS recent_time
			FROM ' . SESSIONS_TABLE . ' AS s1
			INNER JOIN (
				SELECT session_user_id, MAX(session_time) AS recent_time
				FROM ' . SESSIONS_TABLE . '
				WHERE session_user_id <> ' . ANONYMOUS . '
				GROUP BY session_user_id
			) AS s2
			ON s1.session_user_id = s2.session_user_id
				AND s1.session_time = s2.recent_time';

		switch ($this->db->get_sql_layer())
		{
			case 'sqlite3':
				if (phpbb_version_compare($this->db->sql_server_info(true), '3.8.3', '>='))
				{
					// For SQLite versions 3.8.3+ which support Common Table Expressions (CTE)
					$sql = "WITH s3 (session_user_id, session_time) AS ($sql_select)
						UPDATE " . USERS_TABLE . '
						SET (user_lastvisit) = (SELECT session_time FROM s3 WHERE session_user_id = user_id)
						WHERE EXISTS (SELECT session_user_id FROM s3 WHERE session_user_id = user_id)';
					$this->db->sql_query($sql);

					break;
				}

			// No break, for SQLite versions prior to 3.8.3 and Oracle
			case 'oracle':
				$result = $this->db->sql_query($sql_select);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$sql = 'UPDATE ' . USERS_TABLE . '
						SET user_lastvisit = ' . (int) $row['recent_time'] . '
						WHERE user_id = ' . (int) $row['session_user_id'];
					$this->db->sql_query($sql);
				}
				$this->db->sql_freeresult($result);
			break;

			case 'mysqli':
				$sql = 'UPDATE ' . USERS_TABLE . " u,
					($sql_select) s3
					SET u.user_lastvisit = s3.recent_time
					WHERE u.user_id = s3.session_user_id";
				$this->db->sql_query($sql);
			break;

			default:
				$sql = 'UPDATE ' . USERS_TABLE . "
					SET user_lastvisit = s3.recent_time
					FROM ($sql_select) s3
					WHERE user_id = s3.session_user_id";
				$this->db->sql_query($sql);
			break;
		}
	}
}
