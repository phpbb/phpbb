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

class main_navigation implements navigation_interface
{
	/**
	 * {@inheritdoc}
	 */
	public function get()
	{
		return array(
			'overview'	=> array(
				'label'	=> 'MENU_OVERVIEW',
				'route'	=> 'phpbb_installer_index',
				'order'	=> 0,
				array(
					'introduction'	=> array(
						'label'	=> 'MENU_INTRO',
						'route'	=> 'phpbb_installer_index',
						'order'	=> 0,
					),
					'support'	=> array(
						'label'	=> 'MENU_SUPPORT',
						'route'	=> 'phpbb_installer_support',
						'order'	=> 1,
					),
					'license'	=> array(
						'label'	=> 'MENU_LICENSE',
						'route'	=> 'phpbb_installer_license',
						'order'	=> 2,
					),
				),
			),
		);
	}
}
