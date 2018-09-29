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

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $users_table;

	/**
	 * Creates a ban type.
	 *
	 * @param \phpbb\db\driver\driver_interface	$db				A phpBB DBAL object
	 * @param \phpbb\user						$user			An user object
	 * @param string							$users_table	The users table
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $users_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->users_table = $users_table;
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_ban(array $data)
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function after_unban(array $data)
	{
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
	public function tidy()
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_banned_users()
	{
		return [];
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

		$this->excluded = [
			(int)$this->user->data['user_id']	=> $this->user->data[$user_column],
		];

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
