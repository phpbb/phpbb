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

/**
 * Interface implemented by all ban types
 */
interface type_interface
{
	/**
	 * Returns the language key that's used for the log entry.
	 * False, if there is none (and thus no logs are created)
	 *
	 * @return string|bool
	 */
	public function get_log_string();

	/**
	 * Returns the type identifier for this ban type
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Returns the column in the users table which contains
	 * the values that should be looked for when checking a ban.
	 * If it returns null, the check method will be called when
	 * checking for bans.
	 *
	 * @return string|null
	 */
	public function get_user_column();

	/**
	 * Gives the possibility to do some clean up after banning
	 * Returns true if affected users should be logged out and
	 * false otherwise
	 *
	 * @param array $data An array containing information about
	 *                    the bans, like the reason or the start
	 *                    and end of the ban
	 *
	 * @return bool
	 */
	public function after_ban($data);

	public function after_unban(); // ???

	/**
	 * In the case that get_user_column() returns null, this method
	 * is called when checking the ban status.
	 * Please note, that this method is basically called on every page,
	 * so the check should perform rather fast.
	 *
	 * Returns true if the person is banned and false otherwise.
	 *
	 * @param array $data The user data array
	 *
	 * @return bool
	 */
	public function check(array $data);

	/**
	 * Prepares the given ban items before saving them in the database
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function prepare_for_storage(array $items);

	public function tidy(); // ???
}
