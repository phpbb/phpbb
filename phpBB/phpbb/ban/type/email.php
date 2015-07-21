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

class email extends base
{
	/**
	 * {@inheritdoc}
	 */
	public function get_type()
	{
		return 'email';
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
		return 'user_email';
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_ban(array $ban_list, $ban_end, $ban_exclude, $ban_reason, $ban_reason_display)
	{
		$founder = $banned_emails = array();

		// Exclude the founders and the current user
		if (!$ban_exclude)
		{
			$founder = $this->helper->get_founder('user_email');
		}

		foreach ($ban_list as $ban_item)
		{
			$ban_item = trim($ban_item);

			if (strlen($ban_item) > 100 || stripos($ban_item, '@') === false)
			{
				continue;
			}

			if ($ban_item == $this->user->data['user_email'])
			{
				// @TODO: Throw exception when trying to ban yourself
				continue;
			}

			if (empty($founder) || !in_array($ban_item, $founder))
			{
				$banned_emails[] = $ban_item;
			}
		}

		if (empty($ban_list) || empty($banned_emails))
		{
			return false;
		}

		$this->helper->remove_duplicate_bans($banned_emails, BANLIST_TABLE, 'ban_email', 'string', $ban_exclude);

		$sql_ary = array();

		foreach ($banned_emails as $ban_entry)
		{
			$sql_ary[] = array(
				'ban_email'			=> $ban_entry,
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
			$db = $this->db;
			$banned_emails_sql = array_map(function ($email) use ($db)
			{
				return str_replace('*', $db->get_any_char(), $email);
			}, $banned_emails);

			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_email', $banned_emails_sql);
			$result = $this->db->sql_query($sql);

			$user_id_ary = array();

			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_id_ary[] = $row['user_id'];
			}
			$this->db->sql_freeresult($result);

			$sql = 'DELETE FROM ' . SESSIONS_TABLE . '
				WHERE ' . $this->db->sql_in_set('session_user_id', $user_id_ary);
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_id_ary);
			$this->db->sql_query($sql);
		}

		$ban_list_log = implode(', ', $banned_emails);

		return array(
			'admin' => array($ban_reason, $ban_list_log),
			'mod'	=> array('forum_id' => 0, 'topic_id' => 0, $ban_reason, $ban_list_log),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_ban($ban)
	{
		// Because nobody wants a cache with >1000 files or something like that
		$sql = 'SELECT ban_email, ban_exclude, ban_give_reason, ban_end
			FROM ' . BANLIST_TABLE . "
			WHERE ban_email <> ''";
		$result = $this->db->sql_query($sql, self::CACHE_TTL);

		$banned = $exclude = false;
		$ban_row = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['ban_end'] && $row['ban_end'] < time())
			{
				continue;
			}

			if ($row['ban_email'] == $ban)
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
			'ban_triggered_by'	=> 'email',
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

		$sql = 'SELECT ban_email
			FROM ' . BANLIST_TABLE . '
			WHERE ' . $this->db->sql_in_set('ban_id', $ban_ids);
		$result = $this->db->sql_query($sql);

		$l_unban_list = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$l_unban_list .= (($l_unban_list != '') ? ', ' : '') . $row['ban_email'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . BANLIST_TABLE . '
			WHERE ' . $this->db->sql_in_set('ban_id', $ban_ids);
		$this->db->sql_query($sql);

		return array(
			'admin' => array($l_unban_list),
			'mod'	=> array('forum_id' => 0, 'topic_id' => 0, $l_unban_list),
		);
	}
}
