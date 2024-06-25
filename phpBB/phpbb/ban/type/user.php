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

use phpbb\ban\exception\no_valid_users_exception;

class user extends base
{
	/** @var array */
	private $banned_users;

	/**
	 * {@inheritDoc}
	 */
	public function get_type(): string
	{
		return 'user';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_user_column(): string
	{
		return 'user_id';
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_ban(array $data): array
	{
		$this->logout_affected_users($data['items']);
		return $this->banned_users;
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_unban(array $data): array
	{
		$user_ids = array_map('intval', $data['items']);

		$sql = 'SELECT user_id, username
			FROM ' . $this->users_table . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$result = $this->db->sql_query($sql);

		$unbanned_users = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$unbanned_users[(int) $row['user_id']] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		return $unbanned_users;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ban_options(): array
	{
		$ban_options = [];

		$sql = 'SELECT b.*, u.user_id, u.username, u.username_clean
			FROM ' . $this->bans_table . ' b, ' . $this->users_table . ' u
			WHERE (b.ban_end >= ' . time() . "
					OR b.ban_end = 0)
				AND b.ban_userid = u.user_id
				AND b.ban_mode = '{$this->db->sql_escape($this->get_type())}'
			ORDER BY u.username_clean ASC";
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['label'] = $row['username'];
			$ban_options[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $ban_options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_for_storage(array $items): array
	{
		// Fill excluded user list
		$this->get_excluded();

		// Prevent banning of anonymous
		$this->excluded[ANONYMOUS] = ANONYMOUS;

		$sql_usernames = [];
		$sql_or_like = [];
		foreach ($items as $item)
		{
			$cleaned_username = utf8_clean_string($item);
			if (stripos($cleaned_username, '*') === false)
			{
				$sql_usernames[] = $cleaned_username;
			}
			else
			{
				$sql_or_like[] = ['username_clean', 'LIKE', str_replace('*', $this->db->get_any_char(), $cleaned_username)];
			}
		}

		$sql_array = [
			'SELECT'	=> 'user_id, username',
			'FROM'		=> [
				$this->users_table	=> '',
			],
			'WHERE'		=> ['AND',
				[
					['OR',
						array_merge([
							['username_clean', 'IN', $sql_usernames]
						], $sql_or_like),
					],
					['user_id', 'NOT_IN', array_map('intval', $this->excluded)],
				],
			],
		];
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$ban_items = [];
		$this->banned_users = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ban_items[] = (string) $row['user_id'];
			$this->banned_users[(int) $row['user_id']] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		if (empty($ban_items))
		{
			throw new no_valid_users_exception('NO_USER_SPECIFIED');
		}

		return $ban_items;
	}
}
