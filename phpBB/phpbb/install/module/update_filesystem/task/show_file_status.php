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
use phpbb\install\exception\user_interaction_required_exception;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\file_updater\factory;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\task_base;

class show_file_status extends task_base
{
	/**
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * @var filesystem
	 */
	protected $filesystem;

	/**
	 * @var config
	 */
	protected $installer_config;

	/**
	 * @var iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\install\helper\file_updater\compression_file_updater
	 */
	protected $file_updater;

	/**
	 * Constructor
	 *
	 * @param container_factory		$container
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 * @param filesystem			$filesystem
	 * @param factory				$file_updater_factory
	 */
	public function __construct(container_factory $container, config $config, iohandler_interface $iohandler, filesystem $filesystem, factory $file_updater_factory)
	{
		$this->installer_config	= $config;
		$this->iohandler		= $iohandler;
		$this->filesystem		= $filesystem;

		$this->cache = $container->get('cache.driver');

		// Initialize compression file updater
		$this->file_updater = $file_updater_factory->get('compression');

		parent::__construct(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
	{
		return $this->installer_config->get('do_update_files', false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if (!$this->iohandler->get_input('submit_continue_file_update', false))
		{
			// Handle merge conflicts
			$merge_conflicts = $this->installer_config->get('merge_conflict_list', array());

			// Create archive for merge conflicts
			if (!empty($merge_conflicts))
			{
				$compression_method = $this->installer_config->get('file_update_compression', '');
				$conflict_archive = $this->file_updater->init($compression_method);
				$this->installer_config->set('update_file_conflict_archive', $conflict_archive);

				foreach ($merge_conflicts as $filename)
				{
					$this->file_updater->create_new_file(
						$filename,
						base64_decode($this->cache->get('_file_' . md5($filename))),
						true
					);
				}

				// Render download box
				$this->iohandler->add_download_link(
					'phpbb_installer_update_conflict_download',
					'DOWNLOAD_CONFLICTS',
					'DOWNLOAD_CONFLICTS_EXPLAIN'
				);

				$this->file_updater->close();
			}

			// Render update file statuses
			$file_update_info = $this->installer_config->get('update_files', array());
			$file_status = array(
				'deleted'		=> (!isset($file_update_info['delete'])) ? array() : $file_update_info['delete'],
				'new'			=> (!isset($file_update_info['new'])) ? array() : $file_update_info['new'],
				'conflict'		=> $this->installer_config->get('merge_conflict_list', array()),
				'modified'		=> (!isset($file_update_info['update_with_diff'])) ? array() : $file_update_info['update_with_diff'],
				'not_modified'	=> (!isset($file_update_info['update_without_diff'])) ? array() : $file_update_info['update_without_diff'],
			);

			$this->iohandler->render_update_file_status($file_status);

			// Add form to continue update
			$this->iohandler->add_user_form_group('UPDATE_CONTINUE_FILE_UPDATE', array(
				'submit_continue_file_update'	=> array(
					'label'	=> 'UPDATE_CONTINUE_FILE_UPDATE',
					'type'	=> 'submit',
				),
			));

			// Show results to the user
			throw new user_interaction_required_exception();
		}
		else
		{
			$conflict_archive_path = $this->installer_config->get('update_file_conflict_archive', null);

			// Remove archive
			if ($conflict_archive_path !== null && $this->filesystem->exists($conflict_archive_path))
			{
				$this->filesystem->remove($conflict_archive_path);
			}

			$this->installer_config->set('update_file_conflict_archive', null);
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
