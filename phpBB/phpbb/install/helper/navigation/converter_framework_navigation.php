<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */
namespace phpbb\install\helper\navigation;

use phpbb\install\helper\install_helper;

class converter_framework_navigation implements navigation_interface
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
			'converter'	=> array(
				'label'	=> 'CF_MENU',
				'route'	=> 'phpbb_converter_index',
				'order'	=> 4,
				array(
					'home'	=> array(
						'label'	=> 'CF_HOME',
						'stage'	=> true,
						'route'	=> 'phpbb_converter_index',
						'order'	=> 0,
					),
					'list'	=> array(
						'label'	=> 'CF_LIST',
						'stage'	=> true,
						'route'	=> 'phpbb_converter_convert',
						'order'	=> 1,
					),
					'progress'	=> array(
						'label'	=> 'CF_CONVERT',
						'stage'	=> true,
						'route'	=> 'phpbb_converter_start',
						'order'	=> 2,
					),
					'finished'	=> array(
						'label'	=> 'CF_FINISHED',
						'stage'	=> true,
						'order'	=> 3,
					),
				),
			),
		);
	}
}