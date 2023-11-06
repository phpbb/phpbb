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

use phpbb\ban\exception\invalid_length_exception;
use phpbb\ban\exception\type_not_found_exception;
use phpbb\ban\type\type_interface;
use phpbb\cache\driver\driver_interface as cache_driver;
use phpbb\db\driver\driver_interface;
use phpbb\di\service_collection;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\user;

class manager
{
	const CACHE_KEY_INFO = '_ban_info';
	const CACHE_KEY_USERS = '_banned_users';
	const CACHE_TTL = 3600;

	/** @var string */
	protected $bans_table;

	/** @var cache_driver */
	protected $cache;

	/** @var driver_interface */
	protected $db;

	/** @var service_collection */
	protected $types;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var user */
	protected $user;

	/** @var string */
	protected $users_table;

	/**
	 * Creates a service which manages all bans. Developers can
	 * create their own ban types which will be handled in this.
	 *
	 * @param service_collection	$types					A service collection containing all ban types
	 * @param cache_driver			$cache					A cache object
	 * @param driver_interface		$db						A phpBB DBAL object
	 * @param language				$language				Language object
	 * @param log_interface			$log					Log object
	 * @param user					$user					User object
	 * @param string				$bans_table				The bans table
	 * @param string				$users_table			The users table
	 */
	public function __construct(service_collection $types, cache_driver $cache, driver_interface $db, language $language,
								log_interface $log, user $user, string $bans_table, string $users_table = '')
	{
		$this->bans_table = $bans_table;
		$this->cache = $cache;
		$this->db = $db;
		$this->types = $types;
		$this->language = $language;
		$this->log = $log;
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
			throw new invalid_length_exception('LENGTH_BAN_INVALID');
		}

		/** @var type_interface $ban_mode */
		$ban_mode = $this->find_type($mode);
		if ($ban_mode === false)
		{
			throw new type_not_found_exception();
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
				'ban_userid'			=> $mode === 'user' ? $ban_item : 0,
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

		// Add to admin log, moderator log and user notes
		$ban_list_log = implode(', ', $items);

		$log_operation = 'LOG_BAN_' . strtoupper($mode);
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [$reason, $ban_list_log]);
		$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [
			'forum_id' => 0,
			'topic_id' => 0,
			$reason,
			$ban_list_log
		]);

		if ($banlist_ary = $ban_mode->after_ban($ban_data))
		{
			foreach ($banlist_ary as $user_id)
			{
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [
					'reportee_id' => $user_id,
					$reason,
					$ban_list_log
				]);
			}
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
			throw new type_not_found_exception();
		}
		$this->tidy();

		$sql_ids = array_map('intval', $items);

		if (count($sql_ids))
		{
			$sql = 'SELECT ban_item
				FROM ' . $this->bans_table . '
				WHERE ' . $this->db->sql_in_set('ban_id', $sql_ids);
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
				'items' => $unbanned_items,
			];
			$unbanned_users = $ban_mode->after_unban($unban_data);

			// Add to moderator log, admin log and user notes
			$log_operation = 'LOG_UNBAN_' . strtoupper($mode);
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [$unbanned_users]);
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $log_operation, false, [
				'forum_id' => 0,
				'topic_id' => 0,
				$unbanned_users
			]);
			if (count($unbanned_users))
			{
				foreach ($unbanned_users as $user_id)
				{
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $log_operation, false, array(
						'reportee_id' => $user_id,
						$unbanned_users
					));
				}
			}
		}

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
		/** @var type_interface $ban_type */
		$ban_type = $this->find_type($mode);
		if ($ban_type === false)
		{
			throw new type_not_found_exception();
		}
		$this->tidy();

		return $ban_type->get_ban_options();
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

				$where_column = $user_column == 'user_id' ? 'b.ban_userid' : 'b.ban_item';

				$where_array[] = ['AND',
					[
						[$where_column, '=', 'u.' . $user_column],
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

		return $banned_users;
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
					throw new invalid_length_exception('LENGTH_BAN_INVALID');
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
			WHERE ban_end > 0
				AND ban_end < ' . (int) time();
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
				FROM ' . $this->bans_table;
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

	/**
	 * Get ban info message
	 *
	 * @param array $ban_row Ban data row from database
	 * @param string $ban_triggered_by Ban triggered by; allowed 'user', 'ip', 'email
	 * @param string $contact_link Contact link URL
	 *
	 * @return string Ban message
	 */
	public function get_ban_message(array $ban_row, string $ban_triggered_by, string $contact_link): string
	{
		if ($ban_row['end'] > 0)
		{
			$till_date = $this->user->format_date($ban_row['end']);
			$ban_type = 'BOARD_BAN_TIME';
		}
		else
		{
			$till_date = '';
			$ban_type = 'BOARD_BAN_PERM';
		}

		$message = $this->language->lang($ban_type, $till_date, '<a href="' . $contact_link . '">', '</a>');
		$message .= !empty($ban_row['reason']) ? '<br><br>' . $this->language->lang('BOARD_BAN_REASON', $ban_row['reason']) : '';
		$message .= '<br><br><em>' . $this->language->lang('BAN_TRIGGERED_BY_' . strtoupper($ban_triggered_by)) . '</em>';

		return $message;
	}
}
