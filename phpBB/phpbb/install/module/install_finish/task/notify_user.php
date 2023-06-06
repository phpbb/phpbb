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
use phpbb\install\helper\config;
use phpbb\install\helper\iohandler\iohandler_interface;
use phpbb\auth\auth;
use phpbb\log\log_interface;
use phpbb\user;
use phpbb\install\helper\container_factory;
use phpbb\di\service_collection;

/**
 * Logs installation and sends an email to the admin
 */
class notify_user extends \phpbb\install\task_base
{
	/** @var config */
	protected $install_config;

	/** @var iohandler_interface */
	protected $iohandler;

	/** @var auth */
	protected $auth;

	/** @var db */
	protected $config;

	/** @var log_interface */
	protected $log;

	/** @var user */
	protected $user;

	/** @var service_collection */
	protected $messenger;

	/**
	 * Constructor
	 *
	 * @param container_factory		$container
	 * @param config				$install_config
	 * @param iohandler_interface	$iohandler
	 */
	public function __construct(container_factory $container, config $install_config, iohandler_interface $iohandler)
	{
		$this->install_config	= $install_config;
		$this->iohandler		= $iohandler;

		$this->auth				= $container->get('auth');
		$this->log				= $container->get('log');
		$this->user				= $container->get('user');
		$this->messenger		= $container->get('messenger.method_collection');

		// We need to reload config for cases when it doesn't have all values
		/** @var \phpbb\cache\driver\driver_interface $cache */
		$cache = $container->get('cache.driver');
		$cache->destroy('config');

		$this->config = new db(
			$container->get('dbal.conn'),
			$cache,
			$container->get_parameter('tables.config')
		);

		global $config;
		$config = $this->config;

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
			$email = $this->messenger->offsetGet('messenger.method.email');
			$email->set_use_queue(false);
			$email->template('installed', $this->install_config->get('user_language', 'en'));
			$email->to($this->config['board_email'], $this->install_config->get('admin_name'));
			$email->anti_abuse_headers($this->config, $this->user);
			$email->assign_vars([
				'USERNAME' => html_entity_decode($this->install_config->get('admin_name'), ENT_COMPAT),
				'PASSWORD' => html_entity_decode($this->install_config->get('admin_passwd'), ENT_COMPAT),
			]);
			$email->send();
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
			[$this->config['version']]
		);

		// Remove install_lock
		@unlink($this->phpbb_root_path . 'cache/install_lock');
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_step_count()
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
