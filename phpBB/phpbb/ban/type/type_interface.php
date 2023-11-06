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
	 * Returns the type identifier for this ban type
	 *
	 * @return string
	 */
	public function get_type(): string;

	/**
	 * Returns the column in the users table which contains
	 * the values that should be looked for when checking a ban.
	 * If it returns null, the check method will be called when
	 * checking for bans.
	 *
	 * @return string|null
	 */
	public function get_user_column(): ?string;

	/**
	 * Sets a user object to the ban type to have it excluded
	 * from banning.
	 *
	 * @param \phpbb\user	$user	An user object
	 *
	 * @return void
	 */
	public function set_user(\phpbb\user $user): void;

	/**
	 * Gives the possibility to do some clean up after banning.
	 * The return value of this method will be passed through
	 * to the caller.
	 *
	 * @param array $data An array containing information about
	 *                    the bans, like the reason or the start
	 *                    and end of the ban
	 *
	 * @return array List of banned users
	 */
	public function after_ban(array $data): array;

	/**
	 * Gives the possibility to do some clean up after unbanning.
	 * The return value of this method will be passed through
	 * to the caller.
	 *
	 * @param array $data An array containing information about
	 *                    the unbans, e.g. the unbanned items.
	 *
	 * @return array List of unbanned users
	 */
	public function after_unban(array $data): array;

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
	 * In case get_user_column() returns no string, this method will be called
	 * when a list of banned users is retrieved.
	 * Returns a list of banned users.
	 * The result is cached and is not used for ban checking, so the accuracy
	 * of the results is not as important as when *really* checking in check()
	 *
	 * @return array An array of banned users, where the user ids are the keys
	 *               and the value is the end of the ban (or 0 if permanent)
	 */
	public function get_banned_users(): array;

	/**
	 * Get ban options mapping ban ID to an option to display to admins
	 *
	 * @return array
	 */
	public function get_ban_options(): array;

	/**
	 * Prepares the given ban items before saving them in the database
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	public function prepare_for_storage(array $items): array;

	/**
	 * Does some cleanup work for the banning mode.
	 * Is called before banning and unbanning and as cron job.
	 *
	 * @return void
	 */
	public function tidy(): void;
}
