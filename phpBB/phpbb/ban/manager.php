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
use phpbb\ban\exception\no_items_specified_exception;
use phpbb\ban\exception\type_not_found_exception;

class manager
{
	const CACHE_KEY_INFO = '_ban_info';
	const CACHE_KEY_USERS = '_banned_users';
	const CACHE_TTL = 3600;

	protected $ban_table;

	protected $cache;

	protected $db;

	protected $log;

	protected $sessions_keys_table;

	protected $sessions_table;

	protected $types;

	protected $user;

	protected $users_table;

	public function __construct($types, \phpbb\cache\service $cache, \phpbb\db\driver\driver_interface $db, \phpbb\log\log_interface $log, \phpbb\user $user, $ban_table, $users_table = '', $sessions_table = '', $sessions_keys_table = '')
	{
		$this->ban_table = $ban_table;
		$this->cache = $cache;
		$this->db = $db;
		$this->log = $log;
		$this->sessions_keys_table = $sessions_keys_table;
		$this->sessions_table = $sessions_table;
		$this->types = $types;
		$this->user = $user;
		$this->users_table = $users_table;
	}

	public function ban($mode, array $items, \DateTimeInterface $start, \DateTimeInterface $end, $reason, $display_reason = '')
	{
		if ($start > $end && $end->getTimestamp() !== 0)
		{
			throw new invalid_length_exception(); // TODO
		}

		/** @var \phpbb\ban\type\type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception(); // TODO
		}
		$this->tidy();

		$ban_items = $ban_mode->prepare_for_storage($items);

		// Prevent duplicate bans
		$sql = 'DELETE FROM ' . $this->ban_table . "
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
			throw new no_items_specified_exception(); // TODO
		}

		$result = $this->db->sql_multi_insert($this->ban_table, $insert_array);
		if ($result === false)
		{
			throw new ban_insert_failed_exception(); // TODO
		}

		if ($ban_mode->get_ban_log_string() !== false)
		{
			$ban_items_log = implode(', ', $ban_items);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $ban_mode->get_ban_log_string(), false, [$reason, $ban_items_log]);
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $ban_mode->get_ban_log_string(), false, [
				'forum_id'	=> 0,
				'topic_id'	=> 0,
				$reason,
				$ban_items_log,
			]);
		}

		$ban_data = [
			'items'				=> $ban_items,
			'start'				=> $start,
			'end'				=> $end,
			'reason'			=> $reason,
			'display_reason'	=> $display_reason,
		];

		if ($ban_mode->after_ban($ban_data))
		{
			$user_column = $ban_mode->get_user_column();
			if (!empty($user_column) && !empty($this->users_table))
			{
				if ($user_column !== 'user_id')
				{
					$ban_items_sql = [];
					$ban_or_like = '';
					foreach ($ban_items as $ban_item)
					{
						if (stripos($ban_item, '*') === false)
						{
							$ban_items_sql[] = $ban_item;
						}
						else
						{
							$ban_or_like .= ' OR ' . $user_column . ' ' . $this->db->sql_like_expression(str_replace('*', $this->db->get_any_char(), $ban_item));
						}
					}

					$sql = 'SELECT user_id
						FROM ' . $this->users_table . '
						WHERE ' . $this->db->sql_in_set('u.' . $user_column, $ban_items_sql) . $ban_or_like;
					$result = $this->db->sql_query($sql);

					$user_ids = [];
					while ($row = $this->db->sql_fetchrow($result))
					{
						$user_ids[] = (int)$row['user_id'];
					}
					$this->db->sql_freeresult($result);
				}
				else
				{
					$user_ids = $ban_items;
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
			}
		}

		$this->cache->destroy(self::CACHE_KEY_INFO);
		$this->cache->destroy(self::CACHE_KEY_USERS);
	}

	public function unban($mode, array $items, $logging = true)
	{
		/** @var \phpbb\ban\type\type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception(); // TODO
		}
		$this->tidy();

		$sql_ids = array_map('intval', $items);
		$sql = 'SELECT ban_item
			FROM ' . $this->ban_table . '
			WHERE ' . $this->db->sql_in_set('ban_id', $sql_ids); // TODO (what if empty?)
		$result = $this->db->sql_query($sql);

		$unbanned_items = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$unbanned_items[] = $row['ban_item'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'DELETE FROM ' . $this->ban_table . '
			WHERE ' . $this->db->sql_in_set('ban_id', $sql_ids);
		$this->db->sql_query($sql);

		if ($logging && $ban_mode->get_unban_log_string() !== false)
		{
			$unban_items_log = implode(', ', $unbanned_items);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $ban_mode->get_unban_log_string(), false, [$unban_items_log]);
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $ban_mode->get_unban_log_string(), false, [
				'forum_id'	=> 0,
				'topic_id'	=> 0,
				$unban_items_log,
			]);
		}

		$unban_data = [
			'items'		=> $unbanned_items,
			'logging'	=> $logging,
		];
		$ban_mode->after_unban($unban_data);

		$this->cache->destroy(self::CACHE_KEY_INFO);
		$this->cache->destroy(self::CACHE_KEY_USERS);
	}

	public function check(array $user_data = [])
	{
		if (empty($user_data))
		{
			$user_data = $this->user->data;
		}

		$ban_info = $this->get_info_cache();

		foreach ($ban_info as $mode => $ban_rows)
		{
			/** @var \phpbb\ban\type\type_interface $ban_mode */
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
					return $ban_result;
				}
			}
			else
			{
				$user_column = $ban_mode->get_user_column();

				foreach ($ban_rows as $ban_row)
				{
					if ($ban_row['end'] > 0 && $ban_row['end'] < time())
					{
						if (stripos($ban_row['item'], '*') === false)
						{
							if ($ban_row['item'] == $user_data[$user_column])
							{
								return $ban_row;
							}
						}
						else
						{
							$regex = str_replace('\*', '.*?', preg_quote($ban_row['item'], '#'));
							if (preg_match($regex, $user_data[$user_column]))
							{
								return $ban_row;
							}
						}
					}
				}
			}
		}

		return false;
	}

	public function get_bans($mode)
	{
		/** @var \phpbb\ban\type\type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception(); // TODO
		}
		$this->tidy();

		$sql = 'SELECT ban_id, ban_item, ban_start, ban_end, ban_reason, ban_reason_display
			FROM ' . $this->ban_table . "
			WHERE ban_mode = '" . $this->db->sql_escape($mode) . "'
				AND (ban_end <= 0 OR ban_end >= " . (int) time() . ')';
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rowset;
	}

	public function get_banned_users()
	{
		$banned_users = $this->cache->get(self::CACHE_KEY_USERS);
		if ($banned_users === false)
		{
			$manual_modes = [];
			$where_array = [];

			/** @var \phpbb\ban\type\type_interface $ban_mode */
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
						['b.ban_mode', '=', $ban_mode->get_type()],
					],
				];
			}

			$sql_array = [
				'SELECT'	=> 'u.user_id, b.ban_end',
				'FROM'		=> [
					$this->ban_table	=> 'b',
					$this->users_table	=> 'u',
				],
				'WHERE'		=> ['OR',
					$where_array,
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

			/** @var \phpbb\ban\type\type_interface $manual_mode */
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

	public function tidy()
	{
		// Delete stale bans
		$sql = 'DELETE FROM ' . $this->ban_table . '
			WHERE ban_end > 0 AND ban_end < ' . (int) time();
		$this->db->sql_query($sql);

		/** @var \phpbb\ban\type\type_interface $type */
		foreach ($this->types as $type)
		{
			$type->tidy();
		}
	}

	protected function find_type($mode)
	{
		/** @var \phpbb\ban\type\type_interface $type */
		foreach ($this->types as $type)
		{
			if ($type->get_type() === $mode)
			{
				return $type;
			}
		}

		return false;
	}

	protected function get_info_cache()
	{
		$ban_info = $this->cache->get(self::CACHE_KEY_INFO);
		if ($ban_info === false)
		{
			$sql = 'SELECT ban_mode, ban_item, ban_end, ban_reason_display
				FROM ' . $this->ban_table . '
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
