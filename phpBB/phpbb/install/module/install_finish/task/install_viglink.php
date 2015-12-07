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

namespace phpbb\install\module\install_finish\task;

/**
 * Installs viglink extension with default config
 */
class install_viglink extends \phpbb\install\task_base
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler;

	/**
	 * @var \phpbb\config\db
	 */
	protected $config;

	/**
	 * @var \phpbb\log\log_interface
	 */
	protected $log;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/** @var \phpbb\extension\manager */
	protected $extension_manager;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\container_factory				$container
	 * @param \phpbb\install\helper\config							$install_config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler
	 */
	public function __construct(\phpbb\install\helper\container_factory $container, \phpbb\install\helper\config $install_config, \phpbb\install\helper\iohandler\iohandler_interface $iohandler)
	{
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;

		$this->log				= $container->get('log');
		$this->user				= $container->get('user');
		$this->extension_manager = $container->get('ext.manager');
		$this->config			= $container->get('config');

		// Make sure asset version exists in config. Otherwise we might try to
		// insert the assets_version setting into the database and cause a
		// duplicate entry error.
		if (!isset($this->config['assets_version']))
		{
			$this->config['assets_version'] = 0;
		}

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->user->session_begin();
		$this->user->setup(array('common', 'acp/common', 'cli'));
		$name = 'phpbb/viglink';

		// Should be available by default but make sure it is
		if ($this->extension_manager->is_available($name))
		{
			$this->extension_manager->enable($name);
			$this->extension_manager->load_extensions();

			if (!$this->extension_manager->is_enabled($name))
			{
				// Create log
				$this->log->add('admin', ANONYMOUS, '', 'LOG_EXT_ENABLE', time(), array($name));
			}
		}
		else
		{
			$this->iohandler->add_log_message('CLI_EXTENSION_ENABLE_FAILURE', array('phpbb/viglink'));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return 'TASK_INSTALL_VIGLINK';
	}
}
