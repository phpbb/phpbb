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

namespace phpbb\db\migration;

/**
 * Base class interface for database migrations
 */
interface migration_interface
{
	/**
	 * Defines other migrations to be applied first
	 *
	 * @return array An array of migration class names
	 */
	static public function depends_on();

	/**
	 * Allows you to check if the migration is effectively installed (entirely optional)
	 *
	 * This is checked when a migration is installed. If true is returned, the migration will be set as
	 * installed without performing the database changes.
	 * This function is intended to help moving to migrations from a previous database updater, where some
	 * migrations may have been installed already even though they are not yet listed in the migrations table.
	 *
	 * @return bool True if this migration is installed, False if this migration is not installed (checked on install)
	 */
	public function effectively_installed();

	/**
	 * Updates the database schema by providing a set of change instructions
	 *
	 * @return array Array of schema changes (compatible with db_tools->perform_schema_changes())
	 */
	public function update_schema();

	/**
	 * Reverts the database schema by providing a set of change instructions
	 *
	 * @return array Array of schema changes (compatible with db_tools->perform_schema_changes())
	 */
	public function revert_schema();

	/**
	 * Updates data by returning a list of instructions to be executed
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data();

	/**
	 * Reverts data by returning a list of instructions to be executed
	 *
	 * @return array Array of data instructions that will be performed on revert
	 * 	NOTE: calls to tools (such as config.add) are automatically reverted when
	 * 		possible, so you should not attempt to revert those, this is mostly for
	 * 		otherwise unrevertable calls (custom functions for example)
	 */
	public function revert_data();
}
