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
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var array */
	protected $excluded;

	/** @var string */
	protected $users_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, $users_table)
	{
		$this->db = $db;
		$this->users_table = $users_table;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_user_column()
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_ban()
	{
		return true;
	}

	public function after_unban()
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function check(array $data)
	{
		return false;
	}

	public function tidy()
	{
	}

	/**
	 * Queries users that are excluded from banning (like founders)
	 * from the database and saves them in $this->excluded array.
	 * Returns true on success and false on failure
	 *
	 * @return bool
	 */
	protected function get_excluded()
	{
		$user_column = $this->get_user_column();
		if (empty($user_column))
		{
			return false;
		}

		$this->excluded = [];

		$sql = "SELECT user_id, {$user_column}
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
}
