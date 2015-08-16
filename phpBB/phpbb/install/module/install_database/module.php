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

namespace phpbb\install\module\install_database;

/**
 * Installer module for database installation
 */
class module extends \phpbb\install\module_base
{
	/**
	 * {@inheritdoc}
	 */
	public function get_navigation_stage_path()
	{
		return array('install', 0, 'install');
	}
}
