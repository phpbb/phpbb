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

class test_installer extends installer
{
	public function get_repositories(): array
	{
		return $this->get_composer_repositories();
	}

	public function filter_repositories(array $repository_configs, array $repositories, array $package_names, io_interface $io): array
	{
		return $this->filter_unavailable_repositories($repository_configs, $repositories, $package_names, $io);
	}
}
