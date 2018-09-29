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
	/** @var array */
	private $banned_users;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var string */
	private $log_string = 'LOG_BAN_USER';

	/**
	 * {@inheritDoc}
	 */
	public function get_log_string()
	{
		// Have to handle logging differently here
		return false;
	}

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
	public function after_ban($data)
	{
		$usernames_log = implode(', ', $this->banned_users);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $this->log_string, false, [$data['reason'], $usernames_log]);
		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $this->log_string, false, [
			'forum_id'	=> 0,
			'topic_id'	=> 0,
			$data['reason'],
			$usernames_log,
		]);

		foreach ($this->banned_users as $user_id => $username)
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $this->log_string, false, [
				'reportee_id'	=> $user_id,
				$data['reason'],
				$usernames_log,
			]);
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
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

		return $ban_items;
	}
}
