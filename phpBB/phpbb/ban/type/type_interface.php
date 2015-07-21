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
 * The interface ban types have to implement.
 */
interface type_interface
{
	/**
	 * Returns the ban type
	 *
	 * @return string	Ban type
	 */
	public function get_type();

	/**
	 * Returns the database table for the ban type.
	 *
	 * @return string 	Database table.
	 */
	public function get_table();

	/**
	 * Returns the column in the user table this ban type needs to be checked against.
	 *
	 * @return string	User column.
	 */
	public function get_user_column();

	/**
	 * Add a ban or ban exclusion to the banlist.
	 *
	 * @param array			$ban_list				List of entities to ban
	 * @param int			$ban_end				Unix timestamp of the ban end
	 * @param bool			$ban_exclude			Exclude these from banning?
	 * @param string		$ban_reason				String describing the reason for the ban
	 * @param string		$ban_reason_display		String which is displayed to the user
	 *
	 * @return array|bool	False if no bans, otherwise an array with data for logging
	 */
	public function add_ban(array $ban_list, $ban_end, $ban_exclude, $ban_reason, $ban_reason_display);

	/**
	 * Check for ban
	 *
	 * @param string	$ban	The entity to check for.
	 *
	 * @return bool	True if banned.
	 */
	public function check_ban($ban);

	/**
	 * Removes a ban or ban exlusion from the banlist
	 *
	 * @param array			$ban_ids	List of entities to unban
	 *
	 * @return array|bool	False if no unbans, otherwise an array with data for logging
	 */
	public function remove_ban(array $ban_ids);

	/**
	 * Tidy the banlist of outdated bans which are not valid anymore
	 *
	 * @return null
	 */
	public function tidy();
}
