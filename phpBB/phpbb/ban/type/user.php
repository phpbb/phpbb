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
use phpbb\exception\runtime_exception;

class user extends base
{
	/** @var array */
	private $banned_users;

	/**
	 * {@inheritDoc}
	 */
	public function get_type()
	{
		return 'user';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_user_column()
	{
		return 'user_id';
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_ban(array $data)
	{
		$this->logout_affected_users($data['items']);
		return $this->banned_users;
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_unban(array $data)
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
	public function prepare_for_storage(array $items)
	{
		if (!$this->get_excluded())
		{
			throw new runtime_exception(); // TODO
		}

		$sql_usernames = [];
		$sql_or_like = [];
		foreach ($items as $item) // TODO: Prevent banning Anonymous
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
			$ban_items[] = (int) $row['user_id'];
			$this->banned_users[(int) $row['user_id']] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		if (empty($ban_items))
		{
			throw new no_valid_users_exception(); // TODO
		}

		return $ban_items;
	}
}
