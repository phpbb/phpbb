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

namespace phpbb\ban\type;

class user extends base
{
	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'user';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table()
	{
		return BANLIST_TABLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_user_column()
	{
		return 'user_id';
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_ban(array $ban_list, $ban_end, $ban_exclude, $ban_reason, $ban_reason_display)
	{
		$founder = $sql_usernames = $banned_users = array();

		// Exclude the founders and the current user
		if (!$ban_exclude)
		{
			$founder = $this->helper->get_founder();
		}

		foreach ($ban_list as $username)
		{
			$username = trim($username);
			if (!empty($username))
			{
				$clean_name = utf8_clean_string($username);
				if ($clean_name == $this->user->data['username_clean'])
				{
					// @TODO throw exception when trying to ban yourself
					return false;
				}
				else if (in_array($clean_name, $founder))
				{
					// @TODO throw exception when trying to ban founder
					return false;
				}
				$sql_usernames[] = $clean_name;
			}
		}
		if (empty($sql_usernames))
		{
			return false;
		}

		$non_bannable = array_merge(array_keys($founder), array(
			$this->user->data['user_id'],
			ANONYMOUS,
		));

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('username_clean', $sql_usernames) . '
				AND ' . $this->db->sql_in_set('user_id', $non_bannable, true);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$banned_users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($banned_users))
		{
			// @TODO throw some awesome and nice exception.. help.. i'm out of todos
			return false;
		}

		$this->helper->remove_duplicate_bans($banned_users, BANLIST_TABLE, 'ban_userid', 'int', $ban_exclude);

		$sql_ary = array();

		foreach ($banned_users as $ban_entry)
		{
			$sql_ary[] = array(
				'ban_userid'		=> $ban_entry,
				'ban_start'			=> (int) time(),
				'ban_end'			=> (int) $ban_end,
				'ban_exclude'		=> (int) $ban_exclude,
				'ban_reason'		=> (string) $ban_reason,
				'ban_give_reason'	=> (string) $ban_reason_display,
			);
		}

		$this->db->sql_multi_insert(BANLIST_TABLE, $sql_ary);

		// We're banning so make sure that everybody we ban will be logged out
		if (!$ban_exclude)
		{
			$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
				WHERE ' . $this->db->sql_in_set('session_user_id', $banned_users);
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', $banned_users);
			$this->db->sql_query($sql);
		}

		$ban_list_log = implode(', ', $ban_list);

		return array(
			'admin'	=> array($ban_reason, $ban_list_log),
			'mod'	=> array('forum_id' => 0, 'topic_id' => 0, $ban_reason, $ban_list_log),
			'user'	=> array('reportee_ids' => $banned_users, $ban_reason, $ban_list_log)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_ban($ban)
	{
		// Because nobody wants a cache with >1000 files or something like that
		$sql = 'SELECT ban_userid, ban_exclude, ban_give_reason, ban_end
			FROM ' . BANLIST_TABLE . '
			WHERE ban_userid <> 0';
		$result = $this->db->sql_query($sql, self::CACHE_TTL);

		$banned = $exclude = false;
		$ban_row = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['ban_end'] && $row['ban_end'] < time())
			{
				continue;
			}

			if ($row['ban_userid'] == $ban)
			{
				if ($row['ban_exclude'])
				{
					$banned = false;
					$exclude = true;
					break;
				}

				$banned = true;
				$ban_row = $row;
			}
		}
		$this->db->sql_freeresult($result);

		if ($exclude)
		{
			return 'exclude';
		}

		if (!$banned)
		{
			return false;
		}

		return array(
			'ban_triggered_by'	=> 'user',
			'ban_end'			=> $ban_row['ban_end'],
			'ban_reason'		=> $ban_row['ban_give_reason'],
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove_ban(array $ban_ids)
	{
		$ban_ids = array_map('intval', $ban_ids);

		if (empty($ban_ids))
		{
			return false;
		}

		$sql = 'SELECT u.username, u.user_id
			FROM ' . USERS_TABLE . ' u, ' . BANLIST_TABLE . ' b
			WHERE ' . $this->db->sql_in_set('b.ban_id', $ban_ids) . '
				AND u.user_id = b.ban_userid';
		$result = $this->db->sql_query($sql);

		$l_unban_list = '';
		$unbanned_users = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$l_unban_list = (($l_unban_list != '') ? ', ' : '') . $row['username'];
			$unbanned_users[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . BANLIST_TABLE . '
			WHERE ' . $this->db->sql_in_set('ban_id', $ban_ids);
		$this->db->sql_query($sql);

		return array(
			'admin'	=> array($l_unban_list),
			'mod'	=> array('forum_id' => 0, 'topic_id' => 0, $l_unban_list),
			'user'	=> array('reportee_ids' => $unbanned_users, $l_unban_list),
		);
	}
}
