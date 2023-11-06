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

use phpbb\db\driver\driver_interface;

abstract class base implements type_interface
{
	/** @var driver_interface */
	protected $db;

	/** @var array */
	protected $excluded;

	/** @var string */
	protected $bans_table;

	/** @var string */
	protected $sessions_keys_table;

	/** @var string */
	protected $sessions_table;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $users_table;

	/**
	 * Creates a ban type.
	 *
	 * @param driver_interface	$db						A phpBB DBAL object
	 * @param string			$bans_table				The bans table
	 * @param string			$users_table			The users table
	 * @param string			$sessions_table			The sessions table
	 * @param string			$sessions_keys_table	The sessions keys table
	 */
	public function __construct(driver_interface $db, string $bans_table, string $users_table, string $sessions_table, string $sessions_keys_table)
	{
		$this->db = $db;
		$this->bans_table = $bans_table;
		$this->users_table = $users_table;
		$this->sessions_table = $sessions_table;
		$this->sessions_keys_table = $sessions_keys_table;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_user(\phpbb\user $user): void
	{
		$this->user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_ban(array $data): array
	{
		return $this->logout_affected_users($data['items']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_unban(array $data): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function check(array $ban_rows, array $user_data)
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function tidy(): void
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_banned_users(): array
	{
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_ban_options(): array
	{
		$sql = 'SELECT *
			FROM ' . $this->bans_table . '
			WHERE (ban_end >= ' . time() . "
					OR ban_end = 0)
				AND ban_mode = '{$this->db->sql_escape($this->get_type())}'
			ORDER BY ban_item";
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * Queries users that are excluded from banning (like founders)
	 * from the database and saves them in $this->excluded array.
	 * Returns true on success and false on failure
	 *
	 * @return bool
	 */
	protected function get_excluded(): bool
	{
		$user_column = $this->get_user_column();
		if (empty($user_column))
		{
			return false;
		}

		$this->excluded = [];

		if (!empty($this->user))
		{
			$this->excluded[$this->user->id()] = $this->user->data[$user_column];
		}

		$sql = "SELECT user_id, {$this->db->sql_escape($user_column)}
			FROM {$this->users_table}
			WHERE user_type = " . USER_FOUNDER;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->excluded[(int) $row['user_id']] = $row[$user_column];
		}
		$this->db->sql_freeresult($result);

		return true;
	}

	/**
	 * Logs out all affected users in the given array. The values
	 * have to match the values of the column returned by get_user_column().
	 * Returns all banned users.
	 *
	 * @param array $ban_items
	 *
	 * @return array Logged out users
	 */
	protected function logout_affected_users(array $ban_items): array
	{
		$user_column = $this->get_user_column();

		if (empty($user_column))
		{
			return [];
		}

		if ($user_column !== 'user_id')
		{
			$ban_items_sql = [];
			$ban_like_items = [];
			foreach ($ban_items as $ban_item)
			{
				if (stripos($ban_item, '*') === false)
				{
					$ban_items_sql[] = $ban_item;
				}
				else
				{
					$ban_like_items[] = [$user_column, 'LIKE', str_replace('*', $this->db->get_any_char(), $ban_item)];
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
								[$user_column, 'IN', $ban_items_sql]
							], $ban_like_items),
						],
						['user_id', 'NOT_IN', array_map('intval', array_keys($this->excluded))],
					],
				],
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);

			$user_ids = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_ids[] = (int) $row['user_id'];
			}
			$this->db->sql_freeresult($result);
		}
		else
		{
			$user_ids = array_map('intval', $ban_items);
		}

		if (!empty($user_ids) && !empty($this->sessions_table))
		{
			$sql = 'DELETE FROM ' . $this->sessions_table . '
				WHERE ' . $this->db->sql_in_set('session_user_id', $user_ids);
			$this->db->sql_query($sql);
		}
		if (!empty($user_ids) && !empty($this->sessions_keys_table))
		{
			$sql = 'DELETE FROM ' . $this->sessions_keys_table . '
				WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
			$this->db->sql_query($sql);
		}

		return $user_ids;
	}
}
