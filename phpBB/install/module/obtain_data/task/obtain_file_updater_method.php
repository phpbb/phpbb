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

class obtain_file_updater_method extends task_base
{
	/**
	 * @var array	Supported compression methods
	 *
	 * Note: .tar is assumed to be supported, but not in the list
	 */
	protected $available_methods;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $installer_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
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

		$this->available_methods = array('.tar.gz' => 'zlib', '.tar.bz2' => 'bz2', '.zip' => 'zlib');

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
		// Check if data is sent
		if ($this->iohandler->get_input('submit_update_file', false))
		{
			$supported_methods = array('compression', 'ftp', 'direct_file');
			$method = $this->iohandler->get_input('method', 'compression');
			$update_method = (in_array($method, $supported_methods, true)) ? $method : 'compression';
			$this->installer_config->set('file_update_method', $update_method);

			$compression = $this->iohandler->get_input('compression_method', '.zip');
			$supported_methods = array_keys($this->available_methods);
			$supported_methods[] = '.tar';
			$compression = (in_array($compression, $supported_methods, true)) ? $compression : '.zip';
			$this->installer_config->set('file_update_compression', $compression);
		}
		else
		{
			$this->iohandler->add_user_form_group('UPDATE_FILE_METHOD_TITLE', array(
				'method' => array(
					'label'		=> 'UPDATE_FILE_METHOD',
					'type'		=> 'select',
					'options'	=> array(
						array(
							'value'		=> 'compression',
							'label'		=> 'UPDATE_FILE_METHOD_DOWNLOAD',
							'selected'	=> true,
						),
						array(
							'value'		=> 'ftp',
							'label'		=> 'UPDATE_FILE_METHOD_FTP',
							'selected'	=> false,
						),
						array(
							'value'		=> 'direct_file',
							'label'		=> 'UPDATE_FILE_METHOD_FILESYSTEM',
							'selected'	=> false,
						),
					),
				),
				'compression_method' => array(
					'label'		=> 'SELECT_DOWNLOAD_FORMAT',
					'type'		=> 'select',
					'options'	=> $this->get_available_compression_methods(),
				),
				'submit_update_file' => array(
					'label'	=> 'SUBMIT',
					'type'	=> 'submit',
				),
			));

			throw new user_interaction_required_exception();
		}
	}

	/**
	 * Returns form elements in an array of available compression methods
	 *
	 * @return array
	 */
	protected function get_available_compression_methods()
	{
		$methods[] = array(
			'value'		=> '.tar',
			'label'		=> '.tar',
			'selected'	=> true,
		);

		foreach ($this->available_methods as $type => $module)
		{
			if (!@extension_loaded($module))
			{
				continue;
			}

			$methods[] = array(
				'value'		=> $type,
				'label'		=> $type,
				'selected'	=> false,
			);
		}

		return $methods;
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
