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

namespace phpbb\ban;

use phpbb\ban\exception\ban_insert_failed_exception;
use phpbb\ban\exception\invalid_length_exception;
use phpbb\ban\exception\type_not_found_exception;
use phpbb\ban\type\type_interface;

class manager
{
	const CACHE_KEY_INFO = '_ban_info';
	const CACHE_KEY_USERS = '_banned_users';
	const CACHE_TTL = 3600;

	/** @var string */
	protected $bans_table;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\di\service_collection */
	protected $types;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $users_table;

	/**
	 * Creates a service which manages all bans. Developers can
	 * create their own ban types which will be handled in this.
	 *
	 * @param \phpbb\di\service_collection		$types					A service collection containing all ban types
	 * @param \phpbb\cache\service				$cache					A cache object
	 * @param \phpbb\db\driver\driver_interface	$db						A phpBB DBAL object
	 * @param \phpbb\user						$user					User object
	 * @param string							$bans_table				The bans table
	 * @param string							$users_table			The users table
	 */
	public function __construct($types, \phpbb\cache\service $cache, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $bans_table, $users_table = '')
	{
		$this->bans_table = $bans_table;
		$this->cache = $cache;
		$this->db = $db;
		$this->types = $types;
		$this->user = $user;
		$this->users_table = $users_table;
	}

	/**
	 * Creates ban entries for the given $items. Returns true if successful
	 * and false if no entries were added to the database
	 *
	 * @param string $mode			A string which identifies a ban type
	 * @param array					$items			An array of items which should be banned
	 * @param \DateTimeInterface	$start			A DateTimeInterface object which is the start of the ban
	 * @param \DateTimeInterface	$end			A DateTimeInterface object which is the end of the ban (or 0 if permanent)
	 * @param string $reason			An (internal) reason for the ban
	 * @param string $display_reason	An optional reason which should be displayed to the banned
	 *
	 * @return bool
	 */
	public function ban(string $mode, array $items, \DateTimeInterface $start, \DateTimeInterface $end, string $reason, string $display_reason = ''): bool
	{
		if ($start > $end && $end->getTimestamp() !== 0)
		{
			throw new invalid_length_exception(); // TODO
		}

		/** @var type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception(); // TODO
		}

		if (!empty($this->user))
		{
			$ban_mode->set_user($this->user);
		}
		$this->tidy();

		$ban_items = $ban_mode->prepare_for_storage($items);

		// Prevent duplicate bans
		$sql = 'DELETE FROM ' . $this->bans_table . "
			WHERE ban_mode = '" . $this->db->sql_escape($mode) . "'
			AND " . $this->db->sql_in_set('ban_item', $ban_items, false, true);
		$this->db->sql_query($sql);

		$insert_array = [];
		foreach ($ban_items as $ban_item)
		{
			$insert_array[] = [
				'ban_mode'				=> $mode,
				'ban_item'				=> $ban_item,
				'ban_start'				=> $start->getTimestamp(),
				'ban_end'				=> $end->getTimestamp(),
				'ban_reason'			=> $reason,
				'ban_reason_display'	=> $display_reason,
			];
		}

		if (empty($insert_array))
		{
			return false;
		}

		$this->db->sql_multi_insert($this->bans_table, $insert_array);

		$ban_data = [
			'items'				=> $ban_items,
			'start'				=> $start,
			'end'				=> $end,
			'reason'			=> $reason,
			'display_reason'	=> $display_reason,
		];

		if ($ban_mode->after_ban($ban_data))
		{
			// @todo: Add logging
		}

		$this->cache->destroy(self::CACHE_KEY_INFO);
		$this->cache->destroy(self::CACHE_KEY_USERS);

		return true;
	}

	/**
	 * Removes ban entries from the database with the given IDs
	 *
	 * @param string	$mode		The ban type in which the ban IDs were created
	 * @param array		$items		An array of ban IDs which should be removed
	 */
	public function unban(string $mode, array $items)
	{
		/** @var type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception(); // TODO
		}
		$this->tidy();

		$sql_ids = array_map('intval', $items);
		$sql = 'SELECT ban_item
			FROM ' . $this->bans_table . '
			WHERE ' . $this->db->sql_in_set('ban_id', $sql_ids); // TODO (what if empty?)
		$result = $this->db->sql_query($sql);

		$unbanned_items = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$unbanned_items[] = $row['ban_item'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . $this->bans_table . '
			WHERE ' . $this->db->sql_in_set('ban_id', $sql_ids);
		$this->db->sql_query($sql);

		$unban_data = [
			'items'		=> $unbanned_items,
		];
		$unbanned_users = $ban_mode->after_unban($unban_data);

		$this->cache->destroy(self::CACHE_KEY_INFO);
		$this->cache->destroy(self::CACHE_KEY_USERS);
	}

	/**
	 * Checks for the given user data whether the user is banned.
	 * Returns false if nothing was found and an array containing
	 * 'mode', 'end', 'reason' and 'item' otherwise.
	 *
	 * @param array	$user_data	The array containing the user data
	 *
	 * @return array|bool
	 */
	public function check(array $user_data = [])
	{
		if (empty($user_data))
		{
			$user_data = $this->user->data;
		}

		$ban_info = $this->get_info_cache();

		foreach ($ban_info as $mode => $ban_rows)
		{
			/** @var type_interface $ban_mode */
			$ban_mode = $this->find_type($mode);
			if ($ban_mode === false)
			{
				continue;
			}

			if ($ban_mode->get_user_column() === null)
			{
				$ban_result = $ban_mode->check($ban_rows, $user_data);
				if ($ban_result !== false)
				{
					return $ban_result + ['mode' => $mode];
				}
			}
			else
			{
				$user_column = $ban_mode->get_user_column();
				if (!isset($user_data[$user_column]))
				{
					continue;
				}

				foreach ($ban_rows as $ban_row)
				{
					if (!$ban_row['end'] || $ban_row['end'] > time())
					{
						if (stripos($ban_row['item'], '*') === false)
						{
							if ($ban_row['item'] == $user_data[$user_column])
							{
								return $ban_row + ['mode' => $mode];
							}
						}
						else
						{
							$regex = '#^' . str_replace('\*', '.*?', preg_quote($ban_row['item'], '#')) . '$#i';
							if (preg_match($regex, $user_data[$user_column]))
							{
								return $ban_row + ['mode' => $mode];
							}
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Returns all bans for a given ban type. False, if none were found
	 *
	 * @param string	$mode	The ban type for which the entries should be retrieved
	 *
	 * @return array|bool
	 */
	public function get_bans(string $mode)
	{
		/** @var type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception(); // TODO
		}
		$this->tidy();

		$sql = 'SELECT ban_id, ban_item, ban_start, ban_end, ban_reason, ban_reason_display
			FROM ' . $this->bans_table . "
			WHERE ban_mode = '" . $this->db->sql_escape($mode) . "'
				AND (ban_end = 0 OR ban_end >= " . time() . ')';
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	/**
	 * Returns an array of banned users with 'id' => 'end' values.
	 * The result is cached for performance reasons and is not as
	 * accurate as the check() method. (Wildcards aren't considered e.g.)
	 *
	 * @return array
	 */
	public function get_banned_users(): array
	{
		$banned_users = $this->cache->get(self::CACHE_KEY_USERS);
		if ($banned_users === false)
		{
			$manual_modes = [];
			$where_array = [];

			/** @var type_interface $ban_mode */
			foreach ($this->types as $ban_mode)
			{
				$user_column = $ban_mode->get_user_column();
				if (empty($user_column))
				{
					$manual_modes[] = $ban_mode;
					continue;
				}
				$where_array[] = ['AND',
					[
						['b.ban_item', '=', 'u.' . $user_column],
						['b.ban_mode', '=', "'{$ban_mode->get_type()}'"],
					],
				];
			}

			$sql_array = [
				'SELECT'	=> 'u.user_id, b.ban_end',
				'FROM'		=> [
					$this->bans_table	=> 'b',
					$this->users_table	=> 'u',
				],
				'WHERE'		=> ['AND',
					[
						['OR',
							$where_array,
						],
						['u.user_type', '<>', USER_FOUNDER],
					],
				],
			];
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);

			$banned_users = [];
			while ($row = $this->db->sql_fetchrow($result))
			{
				$user_id = (int) $row['user_id'];
				$end = (int) $row['ban_end'];
				if (!isset($banned_users[$user_id]) || ($banned_users[$user_id] > 0 && $banned_users[$user_id] < $end))
				{
					$banned_users[$user_id] = $end;
				}
			}
			$this->db->sql_freeresult($result);

			/** @var type_interface $manual_mode */
			foreach ($manual_modes as $manual_mode)
			{
				$mode_banned_users = $manual_mode->get_banned_users();
				foreach ($mode_banned_users as $user_id => $end)
				{
					$user_id = (int) $user_id;
					$end = (int) $end;
					if (!isset($banned_users[$user_id]) || ($banned_users[$user_id] > 0 && $banned_users[$user_id] < $end))
					{
						$banned_users[$user_id] = $end;
					}
				}
			}

			$this->cache->put(self::CACHE_KEY_USERS, $banned_users, self::CACHE_TTL);
		}

		return array_filter($banned_users, function ($end) {
			return $end <= 0 || $end > time();
		});
	}

	/**
	 * Get ban end
	 *
	 * @param \DateTimeInterface $ban_start Ban start time
	 * @param int $length Ban length in minutes
	 * @param string $end_date Ban end date as YYYY-MM-DD string
	 * @return \DateTimeInterface Ban end as DateTimeInterface instance
	 */
	public function get_ban_end(\DateTimeInterface $ban_start, int $length, string $end_date): \DateTimeInterface
	{
		$current_time = $ban_start->getTimestamp();
		$end_time = 0;

		if ($length)
		{
			if ($length != -1 || !$end_date)
			{
				$end_time = max($current_time, $current_time + ($length) * 60);
			}
			else
			{
				if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date))
				{
					$end_time = max(
						$current_time,
						\DateTime::createFromFormat('Y-m-d', $end_date, $this->user->timezone)->getTimestamp()
					);
				}
				else
				{
					throw new invalid_length_exception();
				}
			}
		}

		$ban_end = new \DateTime();
		$ban_end->setTimestamp($end_time);

		return $ban_end;
	}

	/**
	 * Cleans up the database of e.g. stale bans
	 */
	public function tidy()
	{
		// Delete stale bans
		$sql = 'DELETE FROM ' . $this->bans_table . '
			WHERE ban_end > 0 AND ban_end < ' . (int) time();
		$this->db->sql_query($sql);

		/** @var type_interface $type */
		foreach ($this->types as $type)
		{
			$type->tidy();
		}
	}

	/**
	 * Finds the ban type for the given mode string.
	 * Returns false if none was found
	 *
	 * @param string $mode	The mode string
	 *
	 * @return bool|type\type_interface
	 */
	protected function find_type(string $mode)
	{
		/** @var type_interface $type */
		foreach ($this->types as $type)
		{
			if ($type->get_type() === $mode)
			{
				return $type;
			}
		}

		return false;
	}

	/**
	 * Returns the ban_info from the cache.
	 * If they're not in the cache, bans are retrieved from the database
	 * and then put into the cache.
	 * The array contains an array for each mode with respectively
	 * three values for 'item', 'end' and 'reason' only.
	 *
	 * @return array
	 */
	protected function get_info_cache(): array
	{
		$ban_info = $this->cache->get(self::CACHE_KEY_INFO);
		if ($ban_info === false)
		{
			$sql = 'SELECT ban_mode, ban_item, ban_end, ban_reason_display
				FROM ' . $this->bans_table . '
				WHERE 1';
			$result = $this->db->sql_query($sql);

			$ban_info = [];

			while ($row = $this->db->sql_fetchrow($result))
			{
				if (!isset($ban_info[$row['ban_mode']]))
				{
					$ban_info[$row['ban_mode']] = [];
				}

				$ban_info[$row['ban_mode']][] = [
					'item'		=> $row['ban_item'],
					'end'		=> $row['ban_end'],
					'reason'	=> $row['ban_reason_display'],
				];
			}
			$this->db->sql_freeresult($result);

			$this->cache->put(self::CACHE_KEY_INFO, $ban_info, self::CACHE_TTL);
		}

		return $ban_info;
	}
}
