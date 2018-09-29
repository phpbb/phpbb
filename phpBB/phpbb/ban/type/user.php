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

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var string */
	private $ban_log_string = 'LOG_BAN_USER';

	/** @var string */
	private $unban_log_string = 'LOG_UNBAN_USER';

	/**
	 * Creates the user ban type
	 *
	 * @param \phpbb\db\driver\driver_interface	$db				A phpBB DBAL object
	 * @param \phpbb\log\log_interface			$log			A log object
	 * @param \phpbb\user						$user			An user object
	 * @param string							$users_table	The users table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\log\log_interface $log, \phpbb\user $user, $users_table)
	{
		$this->log = $log;

		parent::__construct($db, $user, $users_table);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ban_log_string()
	{
		// Have to handle logging differently here
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_unban_log_string()
	{
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
	public function after_ban(array $data)
	{
		$usernames_log = implode(', ', $this->banned_users);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $this->ban_log_string, false, [$data['reason'], $usernames_log]);
		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $this->ban_log_string, false, [
			'forum_id'	=> 0,
			'topic_id'	=> 0,
			$data['reason'],
			$usernames_log,
		]);

		foreach ($this->banned_users as $user_id => $username)
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $this->ban_log_string, false, [
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
	public function after_unban(array $data)
	{
		if (empty($data['logging']))
		{
			return;
		}

		$user_ids = array_map('intval', $data['items']);

		$sql = 'SELECT user_id, username
			FROM ' . $this->users_table . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$result = $this->db->sql_query($sql);

		$real_user_ids = [];
		$usernames = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$real_user_ids[] = $row['user_id'];
			$usernames[] = $row['username'];
		}
		$this->db->sql_freeresult($result);

		if (empty($usernames))
		{
			return;
		}

		$usernames_log = implode(', ', $usernames);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $this->unban_log_string, false, [$usernames_log]);
		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $this->unban_log_string, false, [
			'forum_id'	=> 0,
			'topic_id'	=> 0,
			$usernames_log,
		]);

		foreach ($real_user_ids as $user_id)
		{
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $this->unban_log_string, false, [
				'reportee_id'	=> $user_id,
				$usernames_log,
			]);
		}
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

		if (empty($ban_items))
		{
			throw new no_valid_users_exception(); // TODO
		}

		return $ban_items;
	}
}
