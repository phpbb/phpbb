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

namespace phpbb\composer;

use Composer\IO\IOInterface;
use phpbb\composer\exception\runtime_exception;

/**
 * Class to manage packages through composer.
 */
interface manager_interface
{
	/**
	 * Installs (if necessary) a set of packages
	 *
	 * @param array $packages Packages to install.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface $io IO object used for the output
	 *
	 * @throws runtime_exception
	 */
	public function install(array $packages, IOInterface $io = null);

	/**
	 * Updates or installs a set of packages
	 *
	 * @param array $packages Packages to update.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface $io IO object used for the output
	 *
	 * @throws runtime_exception
	 */
	public function update(array $packages, IOInterface $io = null);

	/**
	 * Removes a set of packages
	 *
	 * @param array $packages Packages to remove.
	 *                        Each entry may be a name or an array associating a version constraint to a name
	 * @param IOInterface $io IO object used for the output
	 *
	 * @throws runtime_exception
	 */
	public function remove(array $packages, IOInterface $io = null);

	/**
	 * Tells whether or not a package is managed by Composer.
	 *
	 * @param string $packages Package name
	 *
	 * @return bool
	 */
	public function is_managed($packages);

	/**
	 * Returns the list of managed packages for the current type
	 *
	 * @return array The managed packages associated to their version.
	 */
	public function get_managed_packages();

	/**
	 * Returns the list of managed packages for all phpBB types
	 *
	 * @return array The managed packages associated to their version.
	 */
	public function get_all_managed_packages();

	/**
	 * Returns the list of available packages
	 *
	 * @return array The name of the available packages, associated to their definition. Ordered by name.
	 */
	public function get_available_packages();

	/**
	 * Reset the cache
	 */
	public function reset_cache();

	/**
	 * Start managing a manually installed package
	 *
	 * Remove a package installed manually and reinstall it using composer.
	 *
	 * @param string $package Package to manage
	 * @param IOInterface $io IO object used for the output
	 *
	 * @throws runtime_exception
	 */
	public function start_managing($package, $io);

	/**
	 * Checks the requirements of the manager and returns true if it can be used.
	 *
	 * @return bool
	 */
	public function check_requirements();
}
