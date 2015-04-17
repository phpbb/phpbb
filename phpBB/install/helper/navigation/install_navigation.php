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

namespace phpbb\install\helper\navigation;

class install_navigation implements navigation_interface
{
	public function get()
	{
		return array(
			'install'	=> array(
				'label'	=> 'INSTALL',
				'route'	=> 'phpbb_installer_install',
				'order'	=> 0,
			),
		);
	}
}
