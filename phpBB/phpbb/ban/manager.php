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

class manager
{
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
		if (!isset($this->types[$mode]))
		{
			throw new type_not_found_exception(); // TODO
		}
		if ($start > $end && $end->getTimestamp() !== 0)
		{
			throw new invalid_length_exception(); // TODO
		}

		/** @var \phpbb\ban\type\type_interface $ban_mode */
		$ban_mode = $this->types[$mode];
		$ban_items = $ban_mode->prepare_for_storage($items);

		// Prevent duplicate bans
		$sql = 'DELETE FROM ' . $this->ban_table . "
			WHERE ban_mode = '" . $this->db->sql_escape($mode) . "'
			AND " . $this->db->sql_in_set('ban_item', $ban_items); // TODO (what if empty?)
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
			return; // TODO
		}

		$result = $this->db->sql_multi_insert($this->ban_table, $insert_array);
		if ($result === false)
		{
			// Something went wrong
			// TODO throw exception
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

		$this->cache->destroy('sql', $this->ban_table);
	}

	public function unban($mode, array $items)
	{
		if (!isset($this->types[$mode]))
		{
			throw new type_not_found_exception(); // TODO
		}
		/** @var \phpbb\ban\type\type_interface $ban_mode */
		$ban_mode = $this->types[$mode];

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

		if ($ban_mode->get_unban_log_string() !== false)
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
		];
		$ban_mode->after_unban($unban_data);

		$this->cache->destroy('sql', $this->ban_table);
	}

	public function check(array $user_data = [])
	{
	}

	public function tidy()
	{
		// TODO: Delete stale bans
	}
}
