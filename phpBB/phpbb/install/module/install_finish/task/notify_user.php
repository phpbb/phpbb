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

use phpbb\config\db;

/**
 * Logs installation and sends an email to the admin
 */
class notify_user extends \phpbb\install\task_base
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
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\config\db
	 */
	protected $config;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\log\log_interface
	 */
	protected $log;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

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
	 * @param \phpbb\install\helper\container_factory				$container
	 * @param \phpbb\install\helper\config							$install_config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler
	 * @param string												$phpbb_root_path
	 * @param string												$php_ext
	 */
	public function __construct(\phpbb\install\helper\container_factory $container, \phpbb\install\helper\config $install_config, \phpbb\install\helper\iohandler\iohandler_interface $iohandler, $phpbb_root_path, $php_ext)
	{
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;

		$this->auth				= $container->get('auth');
		$this->language			= $container->get('language');
		$this->log				= $container->get('log');
		$this->user				= $container->get('user');
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;

		// We need to reload config for cases when it doesn't have all values
		/** @var \phpbb\cache\driver\driver_interface $cache */
		$cache = $container->get('cache.driver');
		$cache->destroy('config');

		$this->config = new db(
			$container->get('dbal.conn'),
			$cache,
			$container->get_parameter('tables.config')
		);

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		$this->user->session_begin();
		$this->user->setup('common');

		if ($this->config['email_enable'])
		{
			include ($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);

			// functions_messenger.php uses config to determine language paths
			// Remove when able
			global $config;
			$config = $this->config;

			$messenger = new \messenger(false);
			$messenger->template('installed', $this->install_config->get('user_language', 'en'));
			$messenger->to($this->config['board_email'], $this->install_config->get('admin_name'));
			$messenger->anti_abuse_headers($this->config, $this->user);
			$messenger->assign_vars(array(
					'USERNAME'		=> htmlspecialchars_decode($this->install_config->get('admin_name')),
					'PASSWORD'		=> htmlspecialchars_decode($this->install_config->get('admin_passwd')))
			);
			$messenger->send(NOTIFY_EMAIL);
		}

		// Login admin
		// Ugly but works
		$this->auth->login(
			$this->install_config->get('admin_name'),
			$this->install_config->get('admin_passwd'),
			false,
			true,
			true
		);

		$this->iohandler->set_cookie($this->config['cookie_name'] . '_sid', $this->user->session_id);
		$this->iohandler->set_cookie($this->config['cookie_name'] . '_u', $this->user->cookie_data['u']);
		$this->iohandler->set_cookie($this->config['cookie_name'] . '_k', $this->user->cookie_data['k']);

		// Create log
		$this->log->add(
			'admin',
			$this->user->data['user_id'],
			$this->user->ip,
			'LOG_INSTALL_INSTALLED',
			false,
			array($this->config['version'])
		);

		// Remove install_lock
		@unlink($this->phpbb_root_path . 'cache/install_lock');
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
		return 'TASK_NOTIFY_USER';
	}
}
