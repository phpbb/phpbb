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

abstract class base implements type_interface
{
	const CACHE_TTL = 3600;

	/**
	 * Cache service
	 * @var \phpbb\cache\service
	 */
	protected $cache;

	/**
	 * Database object
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * Helper object
	 * @var helper
	 */
	protected $helper;

	/**
	 * Log object
	 * @var \phpbb\log\log_interface
	 */
	protected $log;

	/**
	 * User object
	 * @var \phpbb\user
	 */
	protected $user;

	public function __construct(\phpbb\cache\service $cache, \phpbb\db\driver\driver_interface $db, helper $helper, \phpbb\log\log_interface $log, \phpbb\user $user)
	{
		$this->cache = $cache;
		$this->db = $db;
		$this->helper = $helper;
		$this->log = $log;
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_ban(array $ban_list, $ban_end, $ban_exclude, $ban_reason, $ban_reason_display)
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_ban($ban)
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove_ban(array $ban_ids)
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function tidy()
	{
		return null;
	}
}
