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

use phpbb\mimetype\null_guesser;

/**
 * Interface implemented by all ban types
 */
interface type_interface
{
	/**
	 * Returns the language key that's used for the ban log entry.
	 * False, if there is none (and thus no logs are created)
	 *
	 * @return string|bool
	 */
	public function get_ban_log_string();

	/**
	 * Returns the language key that's used for the unban log entry.
	 * False, if thee is none (and thus no logs are created)
	 *
	 * @return string|bool
	 */
	public function get_unban_log_string();

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
	 * Gives the possibility to do some clean up after banning.
	 * Returns true if affected users should be logged out and
	 * false otherwise
	 *
	 * @param array $data An array containing information about
	 *                    the bans, like the reason or the start
	 *                    and end of the ban
	 *
	 * @return bool
	 */
	public function after_ban(array $data);

	/**
	 * Gives the possiblity to do some clean up after unbanning.
	 * The return value of this method will be ignored and thus
	 * should be null
	 *
	 * @param array $data An array containing information about
	 *                    the unbans, e.g. the unbanned items.
	 *
	 * @return null
	 */
	public function after_unban(array $data);

	/**
	 * In the case that get_user_column() returns null, this method
	 * is called when checking the ban status.
	 * Please note, that this method is basically called on every page,
	 * so the check should perform rather fast.
	 *
	 * Returns an array with information about the ban, like the end or
	 * the reason. False if the user is not banned.
	 *
	 * @param array $ban_rows	An array containing the ban rows retrieved
	 *                        	from the database for this specific mode.
	 *                        	They contain the item, reason and end of the ban.
	 * @param array $user_data	The user data
	 *
	 * @return array|bool
	 */
	public function check(array $ban_rows, array $user_data);

	/**
	 * Prepares the given ban items before saving them in the database
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function prepare_for_storage(array $items);

	/**
	 * Does some cleanup work for the banning mode.
	 * Is called before banning and unbanning and as cron job.
	 *
	 * @return null
	 */
	public function tidy();
}
