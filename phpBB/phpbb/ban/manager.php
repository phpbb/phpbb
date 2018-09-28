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

/**
 * Class for managing bans
 */
class manager
{
	/**
	 * Database object
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * Event dispatcher object
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * Log object
	 * @var \phpbb\log\log_interface
	 */
	protected $log;

	/**
	 * Ban type map
	 * @var array
	 */
	protected $type_map;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	public function __construct($ban_types, \phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\log\log_interface $log, \phpbb\user $user)
	{
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->log = $log;
		$this->user = $user;

		$this->fill_type_map($ban_types);
	}

	/**
	 * Fill ban type map
	 *
	 * @param \phpbb\di\service_collection
	 */
	protected function fill_type_map($types)
	{
		$this->type_map = array();

		foreach ($types as $type)
		{
			$this->type_map[$type->get_type()] = $type;
		}
	}

	public function ban($mode, $ban_list, $ban_length, $ban_length_other, $ban_exclude, $ban_reason, $ban_reason_display = '')
	{
		if (!is_array($ban_list))
		{
			$ban_list = array($ban_list);
		}

		if (!isset($this->type_map[$mode]) || empty($ban_list))
		{
			// @TODO throw some exception
			return false;
		}

		/** @var \phpbb\ban\type\type_interface $ban_type */
		$ban_type = $this->type_map[$mode];

		$current_time = time();

		if ($ban_length)
		{
			if ($ban_length != -1)
			{
				$ban_end = max($current_time, $current_time + ($ban_length * 60));
			}
			else
			{
				$ban_other = explode('-', $ban_length_other);

				if (sizeof($ban_other) == 3 && ((int) $ban_other[0] < 9999) && (strlen($ban_other[0]) == 4) && (strlen($ban_other[1]) == 2) && (strlen($ban_other[2]) == 2))
				{
					$ban_end = max($current_time, $this->user->create_datetime()
							->setDate((int) $ban_other[0], (int) $ban_other[1], (int) $ban_other[2])
							->setTime(0, 0, 0)
							->getTimestamp() + $this->user->timezone->getOffset(new DateTime('UTC')));
				}
				else
				{
					// @TODO throw some exception
					return false;
				}
			}
		}
		else
		{
			$ban_end = 0;
		}

		$log_data = $ban_type->add_ban($ban_list, $ban_end, $ban_exclude, $ban_reason, $ban_reason_display);

		if ($log_data === false)
		{
			// @TODO Remove or throw awesome exception
			return false;
		}

		// Update log
		$log_entry = ($ban_exclude) ? 'LOG_BAN_EXCLUDE_' : 'LOG_BAN_';

		foreach ($log_data as $log_mode => $data)
		{
			if ($log_mode == 'user' && isset($data['reportee_ids']))
			{
				$reportee_ids = $data['reportee_ids'];
				unset($data['reportee_ids']);

				foreach ($reportee_ids as $reportee_id)
				{
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $log_entry . strtoupper($ban_type->get_type()), false, array_merge(array(
							'reportee_id' => $reportee_id,
						),
						$data
					));
				}
			}
			else
			{
				$this->log->add($log_mode, $this->user->data['user_id'], $this->user->ip, $log_entry . strtoupper($ban_type->get_type()), false, $data);
			}
		}

		return true;
	}

	public function check($mode = 'all', $data = false)
	{
		$ban_result = false;

		if ($mode == 'all')
		{
			// Okay.. if we should check all then we require a full user data array as second parameter
			if (!is_array($data))
			{
				$data = $this->user->data;
			}

			/** @var \phpbb\ban\type\type_interface $type */
			foreach ($this->type_map as $type)
			{
				if ($ban_result === false || $type->exclude_possible())
				{
					$ban_result = $type->check_ban($data[$type->get_user_column()]);
				}

				if ($ban_result == 'exclude')
				{
					return false;
				}
			}
		}
		else
		{
			if (!isset($this->type_map[$mode]))
			{
				// @TODO: maybe throw exception...
				return false;
			}

			/** @var \phpbb\ban\type\type_interface $type */
			$type = $this->type_map[$mode];

			if (empty($data))
			{
				$data = $this->user->data[$type->get_user_column()];
			}

			$ban_result = $type->check_ban($data);

			if ($ban_result == 'exclude')
			{
				return false;
			}
		}

		return $ban_result;
	}

	public function unban($mode, $ban_ids)
	{
		// @TODO: tidy
		if (!is_array($ban_ids))
		{
			$ban_ids = array($ban_ids);
		}

		if (!isset($this->type_map[$mode]) || empty($ban_ids))
		{
			// @TODO throw some exception
			return false;
		}

		/** @var \phpbb\ban\type\type_interface $ban_type */
		$ban_type = $this->type_map[$mode];

		$log_data = $ban_type->remove_ban($ban_ids);

		if ($log_data === false)
		{
			// @TODO Remove or throw awesome exception
			return false;
		}

		foreach ($log_data as $log_mode => $data)
		{
			if ($log_mode == 'user' && isset($data['reportee_ids']))
			{
				$reportee_ids = $data['reportee_ids'];
				unset($data['reportee_ids']);

				foreach ($reportee_ids as $reportee_id)
				{
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_UNBAN_' . strtoupper($ban_type->get_type()), false, array_merge(array(
							'reportee_id' => $reportee_id,
						),
						$data
					));
				}
			}
			else
			{
				$this->log->add($log_mode, $this->user->data['user_id'], $this->user->ip, 'LOG_UNBAN_' . strtoupper($ban_type->get_type()), false, $data);
			}
		}

		return true;
	}

	public function tidy()
	{
		/** @var \phpbb\ban\type\type_interface $type */
		foreach ($this->type_map as $type)
		{
			$type->tidy();
		}
	}
}
