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

use phpbb\install\helper\install_helper;

class update_navigation implements navigation_interface
{
	/**
	 * @var install_helper
	 */
	private $install_helper;

	/**
	 * Constructor
	 *
	 * @param install_helper	$install_helper
	 */
	public function __construct(install_helper $install_helper)
	{
		$this->install_helper = $install_helper;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get()
	{
		if (!$this->install_helper->is_phpbb_installed())
		{
			return array();
		}

		return array(
			'update' => array(
				'label'		=> 'UPDATE',
				'route'		=> 'phpbb_installer_update',
				'order'		=> 1,
				array(
					'introduction'	=> array(
						'label'	=> 'INTRODUCTION_TITLE',
						'stage'	=> true,
						'order'	=> 0,
					),
					'requirements'	=> array(
						'label'	=> 'STAGE_REQUIREMENTS',
						'stage'	=> true,
						'order'	=> 1,
					),
					'obtain_data'	=> array(
						'label'	=> 'STAGE_OBTAIN_DATA',
						'stage'	=> true,
						'order'	=> 2,
					),
					'update_files'	=> array(
						'label'	=> 'STAGE_UPDATE_FILES',
						'stage'	=> true,
						'order'	=> 3,
					),
					'update_database'	=> array(
						'label'	=> 'STAGE_UPDATE_DATABASE',
						'stage'	=> true,
						'order'	=> 4,
					),
				),
			),
		);
	}
}
