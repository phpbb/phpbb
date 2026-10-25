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

namespace phpbb\tests\composer;

use phpbb\composer\io\io_interface;
use phpbb\composer\installer;

/**
 * Exposes protected installer methods for unit tests.
 */
class test_installer extends installer
{
	/**
	 * Returns generated Composer repository configuration.
	 *
	 * @return array Composer repository configuration
	 */
	public function get_repositories(): array
	{
		return $this->get_composer_repositories();
	}

	/**
	 * Filters unavailable Composer repositories.
	 *
	 * @param array        $repository_configs Composer repository configuration
	 * @param array        $repositories        Instantiated Composer repositories
	 * @param array        $package_names       Package names involved in the operation
	 * @param io_interface $io                  Composer output
	 *
	 * @return array Available repository configuration
	 */
	public function filter_repositories(array $repository_configs, array $repositories, array $package_names, io_interface $io): array
	{
		return $this->filter_unavailable_repositories($repository_configs, $repositories, $package_names, $io);
	}
}
