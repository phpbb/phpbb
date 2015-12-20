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

class obtain_update_files extends task_base
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
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param iohandler_interface	$iohandler
	 * @param string				$phpbb_root_path
	 * @param string				$php_ext
	 */
	public function __construct(config $config, iohandler_interface $iohandler, $phpbb_root_path, $php_ext)
	{
		$this->installer_config	= $config;
		$this->iohandler		= $iohandler;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

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
		// Load update info file
		// The file should be checked in the requirements, so we assume that it exists
		$update_info_file = $this->phpbb_root_path . 'install/update/index.' . $this->php_ext;
		include($update_info_file);
		$info = (empty($update_info) || !is_array($update_info)) ? false : $update_info;

		// If the file is invalid, abort mission
		if (!$info)
		{
			$this->iohandler->add_error_message('WRONG_INFO_FILE_FORMAT');
			throw new user_interaction_required_exception();
		}

		// Replace .php with $this->php_ext if needed
		if ($this->php_ext !== 'php')
		{
			$custom_extension = '.' . $this->php_ext;
			$info['files'] = preg_replace('#\.php$#i', $custom_extension, $info['files']);
		}

		// Save update info
		$this->installer_config->set('update_info_unprocessed', $info);
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
