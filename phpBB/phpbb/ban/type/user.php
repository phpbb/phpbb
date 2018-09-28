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
	public function get_type()
	{
		return 'user';
	}

	public function get_user_column()
	{
		return 'user_id';
	}

	public function prepare_for_storage(array $items)
	{
		if (!$this->get_excluded())
		{
			// TODO throw exception
		}

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
			'SELECT'	=> 'user_id',
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
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ban_items[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		return $ban_items;
	}
}
