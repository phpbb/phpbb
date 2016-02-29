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

namespace phpbb\install\module\requirements\task;

use phpbb\filesystem\filesystem;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\install\helper\update_helper;
use phpbb\install\task_base;

/**
 * Check the availability of updater files and update version
 */
class check_update extends task_base
{
	/**
	 * @var \phpbb\config\db
	 */
	protected $config;

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
	 * @var update_helper
	 */
	protected $update_helper;

	/**
	 * @var \phpbb\version_helper
	 */
	protected $version_helper;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var bool
	 */
	protected $tests_passed;

	/**
	 * Constructor
	 *
	 * @param container_factory		$container
	 * @param filesystem			$filesystem
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 * @param update_helper			$update_helper
	 * @param string				$phpbb_root_path
	 * @param string				$php_ext
	 */
	public function __construct(container_factory $container, filesystem $filesystem, config $config, iohandler_interface $iohandler, update_helper $update_helper, $phpbb_root_path, $php_ext)
	{
		$this->filesystem		= $filesystem;
		$this->installer_config	= $config;
		$this->iohandler		= $iohandler;
		$this->update_helper	= $update_helper;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->tests_passed		= true;

		$this->config			= $container->get('config');
		$this->version_helper	= $container->get('version_helper');

		parent::__construct(true);
	}

	/**
	 * Sets $this->tests_passed
	 *
	 * @param	bool	$is_passed
	 */
	protected function set_test_passed($is_passed)
	{
		// If one test failed, tests_passed should be false
		$this->tests_passed = $this->tests_passed && $is_passed;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Array of update files
		$update_files = array(
			$this->phpbb_root_path . 'install/update',
			$this->phpbb_root_path . 'install/update/index.' . $this->php_ext,
		);

		// Check for a valid update directory
		if (!$this->filesystem->exists($update_files) || !$this->filesystem->is_readable($update_files))
		{
			$this->iohandler->add_warning_message('UPDATE_FILES_NOT_FOUND');
			$this->set_test_passed(false);

			// If there are no update files, we can't check the version etc
			// However, we can let the users run migrations if they really want to...
			$this->installer_config->set('disable_filesystem_update', true);
			return true;
		}

		// Recover version numbers
		$update_info = array();
		@include($this->phpbb_root_path . 'install/update/index.' . $this->php_ext);
		$info = (empty($update_info) || !is_array($update_info)) ? false : $update_info;
		$update_version = false;

		if ($info !== false)
		{
			$update_version = (!empty($info['version']['to'])) ? trim($info['version']['to']) : false;
		}

		// Get current and latest version
		try
		{
			$latest_version = $this->version_helper->get_latest_on_current_branch(true);
		}
		catch (\RuntimeException $e)
		{
			$latest_version = $update_version;
		}

		$current_version = (!empty($this->config['version_update_from'])) ? $this->config['version_update_from'] : $this->config['version'];

		// Check if the update package
		if (!$this->update_helper->phpbb_version_compare($current_version, $update_version, '<'))
		{
			$this->iohandler->add_error_message('NO_UPDATE_FILES_UP_TO_DATE');
			$this->tests_passed = false;
		}

		// Check if the update package works with the installed version
		if (empty($info['version']['from']) || $info['version']['from'] !== $current_version)
		{
			$this->iohandler->add_error_message(array('INCOMPATIBLE_UPDATE_FILES', $current_version, $info['version']['from'], $update_version));
			$this->tests_passed = false;
		}

		// check if this is the latest update package
		if ($this->update_helper->phpbb_version_compare($update_version, $latest_version, '<'))
		{
			$this->iohandler->add_warning_message(array('OLD_UPDATE_FILES', $info['version']['from'], $update_version, $latest_version));
		}

		return $this->tests_passed;
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
