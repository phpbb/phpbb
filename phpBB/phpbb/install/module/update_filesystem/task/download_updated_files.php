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

namespace phpbb\install\module\update_filesystem\task;

use phpbb\filesystem\filesystem;
use phpbb\install\exception\jump_to_restart_point_exception;
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\task_base;

class download_updated_files extends task_base
{
	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var filesystem
	 */
	protected $filesystem;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 * @param filesystem			$filesystem
	 */
	public function __construct(config $config, iohandler_interface $iohandler, filesystem $filesystem)
	{
		$this->installer_config	= $config;
		$this->iohandler		= $iohandler;
		$this->filesystem		= $filesystem;

		parent::__construct(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
	{
		return $this->installer_config->get('do_update_files', false)
			&& $this->installer_config->get('file_update_method', '') === 'compression';
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if ($this->iohandler->get_input('database_update_submit', false))
		{
			// Remove archive
			$this->filesystem->remove(
				$this->installer_config->get('update_file_archive', null)
			);

			$this->installer_config->set('update_file_archive', null);
		}
		else if ($this->iohandler->get_input('update_recheck_files_submit', false))
		{
			$this->installer_config->set('file_updater_elem_progress', '');
			$this->installer_config->set('update_files', array());
			throw new jump_to_restart_point_exception('check_update_files');
		}
		else
		{
			$file_update_info = $this->installer_config->get('update_files', array());

			// Display download box only if the archive won't be empty
			$display_download_link = !empty($file_update_info) && !(isset($file_update_info['delete']) && count($file_update_info) == 1);
			if ($display_download_link)
			{
				// Render download box
				$this->iohandler->add_download_link(
					'phpbb_installer_update_file_download',
					'DOWNLOAD_UPDATE_METHOD',
					'DOWNLOAD_UPDATE_METHOD_EXPLAIN'
				);
			}

			// Add form to continue update
			$this->iohandler->add_user_form_group('UPDATE_CONTINUE_UPDATE_PROCESS', array(
				'update_recheck_files_submit'	=> array(
					'label'			=> 'UPDATE_RECHECK_UPDATE_FILES',
					'type'			=> 'submit',
					'is_secondary'	=> empty($file_update_info),
				),
				'database_update_submit'	=> array(
					'label'		=> 'UPDATE_CONTINUE_UPDATE_PROCESS',
					'type'		=> 'submit',
					'disabled'	=> $display_download_link,
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
