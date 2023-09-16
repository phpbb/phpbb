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

/**
 * This class requests and saves some information about the server
 */
class obtain_server_data extends \phpbb\install\task_base implements \phpbb\install\task_interface
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $io_handler;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config							$config		Installer's config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler	Installer's input-output handler
	 */
	public function __construct(\phpbb\install\helper\config $config,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler)
	{
		$this->install_config	= $config;
		$this->io_handler		= $iohandler;

		parent::__construct(true);
	}
	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$cookie_secure = $this->io_handler->is_secure();
		$server_protocol = ($this->io_handler->is_secure()) ? 'https://' : 'http://';
		$server_port = $this->io_handler->get_server_variable('SERVER_PORT', 0);

		// HTTP_HOST is having the correct browser url in most cases...
		$server_name = strtolower(html_entity_decode($this->io_handler->get_header_variable(
			'Host',
			$this->io_handler->get_server_variable('SERVER_NAME')
		), ENT_COMPAT));

		// HTTP HOST can carry a port number...
		if (strpos($server_name, ':') !== false)
		{
			$server_name = substr($server_name, 0, strpos($server_name, ':'));
		}

		$script_path = html_entity_decode($this->io_handler->get_server_variable('REQUEST_URI'), ENT_COMPAT);

		if (!$script_path)
		{
			$script_path = html_entity_decode($this->io_handler->get_server_variable('PHP_SELF'), ENT_COMPAT);
		}

		$script_path = str_replace(array('\\', '//'), '/', $script_path);
		$script_path = trim(dirname(dirname(dirname($script_path)))); // Because we are in install/app.php/route_name

		// Server data
		$cookie_secure		= $this->io_handler->get_input('cookie_secure', $cookie_secure);
		$server_protocol	= $this->io_handler->get_input('server_protocol', $server_protocol);
		$force_server_vars	= $this->io_handler->get_input('force_server_vars', 0);
		$server_name		= $this->io_handler->get_input('server_name', $server_name, true);
		$server_port		= $this->io_handler->get_input('server_port', $server_port);
		$script_path		= $this->io_handler->get_input('script_path', $script_path, true);

		// Clean up script path
		if ($script_path !== '/')
		{
			// Adjust destination path (no trailing slash)
			if (substr($script_path, -1) === '/')
			{
				$script_path = substr($script_path, 0, -1);
			}

			$script_path = str_replace(array('../', './'), '', $script_path);

			if ($script_path[0] !== '/')
			{
				$script_path = '/' . $script_path;
			}
		}

		// Check if data is sent
		if ($this->io_handler->get_input('submit_server', false))
		{
			$this->install_config->set('cookie_secure', $cookie_secure);
			$this->install_config->set('server_protocol', $server_protocol);
			$this->install_config->set('force_server_vars', $force_server_vars);
			$this->install_config->set('server_name', $server_name);
			$this->install_config->set('server_port', $server_port);
			$this->install_config->set('script_path', $script_path);
		}
		else
		{
			// Render form
			$server_form = array(
				'cookie_secure' => array(
					'label'			=> 'COOKIE_SECURE',
					'description'	=> 'COOKIE_SECURE_EXPLAIN',
					'type'			=> 'radio',
					'options'		=> array(
						array(
							'value'		=> 0,
							'label'		=> 'NO',
							'selected'	=> (!$cookie_secure),
						),
						array(
							'value'		=> 1,
							'label'		=> 'YES',
							'selected'	=> ($cookie_secure),
						),
					),
				),
				'force_server_vars' => array(
					'label'			=> 'FORCE_SERVER_VARS',
					'description'	=> 'FORCE_SERVER_VARS_EXPLAIN',
					'type'			=> 'radio',
					'options'		=> array(
						array(
							'value'		=> 0,
							'label'		=> 'NO',
							'selected'	=> true,
						),
						array(
							'value'		=> 1,
							'label'		=> 'YES',
							'selected'	=> false,
						),
					),
				),
				'server_protocol' => array(
					'label'			=> 'SERVER_PROTOCOL',
					'description'	=> 'SERVER_PROTOCOL_EXPLAIN',
					'type'			=> 'text',
					'default'		=> $server_protocol,
				),
				'server_name' => array(
					'label'			=> 'SERVER_NAME',
					'description'	=> 'SERVER_NAME_EXPLAIN',
					'type'			=> 'text',
					'default'		=> $server_name,
				),
				'server_port' => array(
					'label'			=> 'SERVER_PORT',
					'description'	=> 'SERVER_PORT_EXPLAIN',
					'type'			=> 'text',
					'default'		=> $server_port,
				),
				'script_path' => array(
					'label'			=> 'SCRIPT_PATH',
					'description'	=> 'SCRIPT_PATH_EXPLAIN',
					'type'			=> 'text',
					'default'		=> $script_path,
				),
				'submit_server' => array(
					'label'	=> 'SUBMIT',
					'type'	=> 'submit',
				)
			);

			$this->io_handler->add_user_form_group('SERVER_CONFIG', $server_form);

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
