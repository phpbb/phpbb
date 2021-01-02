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

namespace phpbb\install\module\obtain_data\task;

use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\task_base;

class obtain_update_settings extends task_base
{
	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * Constructor
	 *
	 * @param config				$installer_config
	 * @param iohandler_interface	$iohandler
	 */
	public function __construct(config $installer_config, iohandler_interface $iohandler)
	{
		$this->installer_config	= $installer_config;
		$this->iohandler		= $iohandler;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Check if data is sent
		if ($this->iohandler->get_input('submit_update', false))
		{
			$update_files = $this->iohandler->get_input('update_type', 'all') === 'all';

			if ($this->installer_config->get('disable_filesystem_update', false) && $update_files)
			{
				$this->iohandler->add_error_message('UPDATE_FILES_NOT_FOUND');

				throw new user_interaction_required_exception();
			}

			$this->installer_config->set('do_update_files', $update_files);
		}
		else
		{
			if ($this->installer_config->get('disable_filesystem_update', false))
			{
				$options[] = array(
					'value'		=> 'db_only',
					'label'		=> 'UPDATE_TYPE_DB_ONLY',
					'selected'	=> true,
				);
			}
			else
			{
				$options = array(
					array(
						'value'		=> 'all',
						'label'		=> 'UPDATE_TYPE_ALL',
						'selected'	=> true,
					),
					array(
						'value'		=> 'db_only',
						'label'		=> 'UPDATE_TYPE_DB_ONLY',
						'selected'	=> false,
					),
				);
			}

			$this->iohandler->add_user_form_group('UPDATE_TYPE', array(
				'update_type' => array(
					'label'		=> 'UPDATE_TYPE',
					'type'		=> 'radio',
					'options'	=> $options,
				),
				'submit_update' => array(
					'label'	=> 'SUBMIT',
					'type'	=> 'submit',
				),
			));

			throw new user_interaction_required_exception();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}
