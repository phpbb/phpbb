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
use phpbb\install\helper\update_helper;
use phpbb\install\task_base;

class obtain_update_ftp_data extends task_base
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
	 * @var update_helper
	 */
	protected $update_helper;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param config				$installer_config
	 * @param iohandler_interface	$iohandler
	 * @param update_helper			$update_helper
	 * @param string				$php_ext
	 */
	public function __construct(config $installer_config, iohandler_interface $iohandler, update_helper $update_helper, $php_ext)
	{
		$this->installer_config	= $installer_config;
		$this->iohandler		= $iohandler;
		$this->update_helper	= $update_helper;
		$this->php_ext			= $php_ext;

		parent::__construct(false);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check_requirements()
	{
		return ($this->installer_config->get('do_update_files', false) &&
			($this->installer_config->get('file_update_method', '') === 'ftp')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if ($this->iohandler->get_input('submit_ftp', false))
		{
			$this->update_helper->include_file('includes/functions_transfer.' . $this->php_ext);

			$method = 'ftp';
			$methods = \transfer::methods();
			if (!in_array($method, $methods, true))
			{
				$method = $methods[0];
			}

			$ftp_host = $this->iohandler->get_input('ftp_host', '', true);
			$ftp_user = $this->iohandler->get_input('ftp_user', '', true);
			$ftp_pass = html_entity_decode($this->iohandler->get_input('ftp_pass', '', true), ENT_COMPAT);
			$ftp_path = $this->iohandler->get_input('ftp_path', '', true);
			$ftp_port = $this->iohandler->get_input('ftp_port', 21);
			$ftp_time = $this->iohandler->get_input('ftp_timeout', 10);

			$this->installer_config->set('ftp_host', $ftp_host);
			$this->installer_config->set('ftp_user', $ftp_user);
			$this->installer_config->set('ftp_pass', $ftp_pass);
			$this->installer_config->set('ftp_path', $ftp_path);
			$this->installer_config->set('ftp_port', (int) $ftp_port);
			$this->installer_config->set('ftp_timeout', (int) $ftp_time);
			$this->installer_config->set('ftp_method', $method);
		}
		else
		{
			$this->iohandler->add_user_form_group('FTP_SETTINGS', array(
				'ftp_host'	=> array(
					'label'			=> 'FTP_HOST',
					'description'	=> 'FTP_HOST_EXPLAIN',
					'type'			=> 'text',
				),
				'ftp_user'	=> array(
					'label'			=> 'FTP_USERNAME',
					'description'	=> 'FTP_USERNAME_EXPLAIN',
					'type'			=> 'text',
				),
				'ftp_pass'	=> array(
					'label'			=> 'FTP_PASSWORD',
					'description'	=> 'FTP_PASSWORD_EXPLAIN',
					'type'			=> 'password',
				),
				'ftp_path'	=> array(
					'label'			=> 'FTP_ROOT_PATH',
					'description'	=> 'FTP_ROOT_PATH_EXPLAIN',
					'type'			=> 'text',
				),
				'ftp_port'	=> array(
					'label'			=> 'FTP_PORT',
					'description'	=> 'FTP_PORT_EXPLAIN',
					'type'			=> 'text',
					'default'		=> 21,
				),
				'ftp_timeout'	=> array(
					'label'			=> 'FTP_TIMEOUT',
					'description'	=> 'FTP_TIMEOUT_EXPLAIN',
					'type'			=> 'text',
					'default'		=> 10,
				),
				'submit_ftp'	=> array(
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
