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

class install_navigation implements navigation_interface
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
		if ($this->install_helper->is_phpbb_installed())
		{
			return array();
		}

		return array(
			'install'	=> array(
				'label'	=> 'INSTALL',
				'route'	=> 'phpbb_installer_install',
				'order'	=> 1,
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
					'obtain_admin_data'	=> array(
						'label'	=> 'Administrator configuration',
						'stage'	=> true,
                        'is_link'	=> true,
                        'link'	=> '/install/app.php/install',
                        'class'	=> 'my_send',
                        'task_index'	=> '1',
						'order'	=> 2,
					),
                    'obtain_database_data'	=> array(
                        'label'	=> 'Database configuration',
                        'stage'	=> true,
                        'is_link'	=> true,
                        'link'	=> '/install/app.php/install',
                        'class'	=> 'my_send',
                        'task_index'	=> '2',
                        'order'	=> 3,
                    ),
                    'obtain_server_data'	=> array(
                        'label'	=> 'Server configuration',
                        'stage'	=> true,
                        'is_link'	=> true,
                        'link'	=> '/install/app.php/install',
                        'class'	=> 'my_send',
                        'task_index'	=> '3',
                        'order'	=> 4,
                    ),
                    'obtain_email_data'	=> array(
                        'label'	=> 'E-mail configuration',
                        'stage'	=> true,
                        'is_link'	=> true,
                        'link'	=> '/install/app.php/install',
                        'class'	=> 'my_send',
                        'task_index'	=> '4',
                        'order'	=> 5,
                    ),
                    'obtain_board_data'	=> array(
                        'label'	=> 'Bulletin board configuration',
                        'stage'	=> true,
                        'is_link'	=> true,
                        'link'	=> '/install/app.php/install',
                        'class'	=> 'my_send',
                        'task_index'	=> '5',
                        'order'	=> 6,
                    ),
					'install'	=> array(
						'label'	=> 'STAGE_INSTALL',
						'stage'	=> true,
						'order'	=> 7,
					),
				),
			),
		);
	}
}
