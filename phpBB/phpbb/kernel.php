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

namespace phpbb;

use phpbb\debug\debug;
use phpbb\di\container_builder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

class kernel implements kernel_interface, TerminableInterface
{
	/** @var config_php_file */
	protected $config_file;

	/** @var ContainerInterface */
	protected $container;

	/** @var string */
	protected $root_dir;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $environment;

	/** @var string */
	protected $debug;

	/** @var string */
	protected $booted = false;

	/** @var string */
	protected $start_time;

	/** @var class_loader[] */
	protected $autoloaders = [];

	/**
	 * Constructor.
	 *
	 * @param config_php_file	$config			The config file object
	 * @param string			$root_dir		The phpBB root directory
	 * @param string			$php_ext		The phpBB files' extension
	 * @param string			$environment	The environment
	 * @param bool				$debug			Whether to enable debugging or not
	 */
	public function __construct(config_php_file $config, $root_dir, $php_ext, $environment, $debug)
	{
		$this->config_file = $config;
		$this->root_dir = $root_dir;
		$this->php_ext = $php_ext;
		$this->environment = $environment;
		$this->debug = (bool) $debug;

		if ($this->debug)
		{
			$this->start_time = microtime(true);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
		if ($this->booted === true)
		{
			return;
		}

		if ($this->debug)
		{
			debug::enable();
		}
		else
		{
			require_once($this->root_dir . 'includes/functions.' . $this->php_ext);

			set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'msg_handler');
		}

		// init container
		$this->initializeContainer();

		$this->initializePostContainer();

		$this->booted = true;

		/**
		 * Main event which is triggered on every page
		 *
		 * You can use this event to load function files and initiate objects
		 *
		 * NOTE:	At this point the global session ($user) and permissions ($auth)
		 *		do NOT exist yet. If you need to use the user object
		 *		(f.e. to include language files) or need to check permissions,
		 *		please use the core.user_setup event instead!
		 *
		 * @event core.common
		 * @since 3.1.0-a1
		 */
		$this->container->get('dispatcher')->dispatch('core.common');
	}

	/**
	 * {@inheritdoc}
	 */
	public function terminate(Request $request, Response $response)
	{
		if ($this->booted === false) {
			return;
		}

		if ($this->get_http_kernel() instanceof TerminableInterface)
		{
			$this->get_http_kernel()->terminate($request, $response);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown()
	{
		if ($this->booted === false)
		{
			return;
		}

		$this->booted = false;
		$this->container = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
	{
		if ($this->booted === false)
		{
			$this->boot();
		}

		return $this->get_http_kernel()->handle($request, $type, $catch);
	}

	/**
	 * Gets a HTTP kernel from the container.
	 *
	 * @return HttpKernel
	 */
	protected function get_http_kernel()
	{
		return $this->container->get('http_kernel');
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_environment()
	{
		return $this->environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_debug()
	{
		return $this->debug;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_root_dir()
	{
		return $this->root_dir;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_php_ext()
	{
		return $this->php_ext;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_container()
	{
		return $this->container;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_start_time()
	{
		return $this->debug ? $this->start_time : -INF;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_cache_dir()
	{
		return $this->root_dir . '/cache/' . $this->environment . '/';
	}

	/**
	 * @param class_loader[] $autoloaders
	 */
	public function set_autoloaders(array $autoloaders)
	{
		$this->autoloaders = $autoloaders;
	}

	/**
	 * Initializes the service container.
	 *
	 * The cached version of the service container is used when fresh, otherwise the
	 * container is built.
	 */
	protected function initializeContainer()
	{
		try
		{
			$container_builder = new container_builder($this->root_dir, $this->php_ext);
			$this->container = $container_builder
				->with_cache_dir($this->get_cache_dir())
				->with_environment($this->environment)
				->with_config($this->config_file)
				->get_container()
			;

			global $phpbb_container;
			$phpbb_container = $this->container;
		}
		catch (InvalidArgumentException $e)
		{
			if (!$this->debug)
			{
				trigger_error(
					'The requested environment ' . $this->environment . ' is not available.',
					E_USER_ERROR
				);
			}
			else
			{
				throw $e;
			}
		}
	}

	/**
	 * Initialize stuff after loading the container
	 */
	protected function initializePostContainer()
	{
		foreach ($this->autoloaders as $autoloader)
		{
			$autoloader->set_cache($this->container->get('cache.driver'));
		}

		// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
		global $phpbb_adm_relative_path, $phpbb_admin_path;
		$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
		$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $this->root_dir . $phpbb_adm_relative_path;

		$this->loadFunctions();

		require($this->root_dir . 'includes/compatibility_globals.' . $this->php_ext);

		$this->load_hook_handler();
	}

	/**
	 * Loads the functions files
	 */
	protected function loadFunctions()
	{
		require_once($this->root_dir . 'includes/functions.' . $this->php_ext);
		require_once($this->root_dir . 'includes/functions_content.' . $this->php_ext);
		require_once($this->root_dir . 'includes/functions_compatibility.' . $this->php_ext);
		require_once($this->root_dir . 'includes/constants.' . $this->php_ext);
		require_once($this->root_dir . 'includes/utf/utf_tools.' . $this->php_ext);
	}

	/**
	 * Register phpBB's legacy hook handler
	 *
	 * @deprecated To be removed in 4.0
	 */
	protected function load_hook_handler()
	{
		global $phpbb_hook;

		require($this->root_dir . 'includes/hooks/index.' . $this->php_ext);
		$phpbb_hook = new \phpbb_hook(array('exit_handler', 'phpbb_user_session_handler', 'append_sid', ['template', 'display']));

		/* @var $phpbb_hook_finder \phpbb\hook\finder */
		$phpbb_hook_finder = $this->container->get('hook_finder');

		foreach ($phpbb_hook_finder->find() as $hook)
		{
			@include($this->root_dir . 'includes/hooks/' . $hook . '.' . $this->php_ext);
		}
	}
}
