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

namespace phpbb\di;

use phpbb\filesystem\filesystem;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

class container_builder
{
	/**
	 * @var string The environment to use.
	 */
	protected $environment;

	/**
	 * @var string phpBB Root Path
	 */
	protected $phpbb_root_path;

	/**
	 * @var string php file extension
	 */
	protected $php_ext;

	/**
	 * The container under construction
	 *
	 * @var ContainerBuilder
	 */
	protected $container;

	/**
	 * Indicates whether extensions should be used (default to true).
	 *
	 * @var bool
	 */
	protected $use_extensions = true;

	/**
	 * Defines a custom path to find the configuration of the container (default to $this->phpbb_root_path . 'config')
	 *
	 * @var string
	 */
	protected $config_path = null;

	/**
	 * Indicates whether the container should be dumped to the filesystem (default to true).
	 *
	 * If DEBUG_CONTAINER is set this option is ignored and a new container is build.
	 *
	 * @var bool
	 */
	protected $use_cache = true;

	/**
	 * Indicates if the container should be compiled automatically (default to true).
	 *
	 * @var bool
	 */
	protected $compile_container = true;

	/**
	 * Custom parameters to inject into the container.
	 *
	 * Default to:
	 * 	array(
	 * 		'core.root_path', $this->phpbb_root_path,
	 * 		'core.php_ext', $this->php_ext,
	 * );
	 *
	 * @var array
	 */
	protected $custom_parameters = null;

	/**
	 * @var \phpbb\config_php_file
	 */
	protected $config_php_file;

	/**
	 * @var string
	 */
	protected $cache_dir;

	/**
	 * @var array
	 */
	private $container_extensions;

	/**
	 * Constructor
	 *
	 * @param string $phpbb_root_path Path to the phpbb includes directory.
	 * @param string $php_ext php file extension
	 */
	function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Build and return a new Container respecting the current configuration
	 *
	 * @return \phpbb_cache_container|ContainerBuilder
	 */
	public function get_container()
	{
		$container_filename = $this->get_container_filename();
		$config_cache = new ConfigCache($container_filename, defined('DEBUG'));
		if ($this->use_cache && $config_cache->isFresh())
		{
			require($config_cache->getPath());
			$this->container = new \phpbb_cache_container();
		}
		else
		{
			$this->container_extensions = array(new extension\core($this->get_config_path()));

			if ($this->use_extensions)
			{
				$this->load_extensions();
			}

			// Inject the config
			if ($this->config_php_file)
			{
				$this->container_extensions[] = new extension\config($this->config_php_file);
			}

			$this->container = $this->create_container($this->container_extensions);

			// Easy collections through tags
			$this->container->addCompilerPass(new pass\collection_pass());

			// Event listeners "phpBB style"
			$this->container->addCompilerPass(new RegisterListenersPass('dispatcher', 'event.listener_listener', 'event.listener'));

			// Event listeners "Symfony style"
			$this->container->addCompilerPass(new RegisterListenersPass('dispatcher'));

			$filesystem = new filesystem();
			$loader     = new YamlFileLoader($this->container, new FileLocator($filesystem->realpath($this->get_config_path())));
			$loader->load($this->container->getParameter('core.environment') . '/config.yml');

			$this->inject_custom_parameters();

			if ($this->compile_container)
			{
				$this->container->compile();

				if ($this->use_cache)
				{
					$this->dump_container($config_cache);
				}
			}
		}

		if ($this->compile_container && $this->config_php_file)
		{
			$this->container->set('config.php', $this->config_php_file);
		}

		return $this->container;
	}

	/**
	 * Enable the extensions.
	 *
	 * @param string $environment The environment to use
	 * @return $this
	 */
	public function with_environment($environment)
	{
		$this->environment = $environment;

		return $this;
	}

	/**
	 * Enable the extensions.
	 *
	 * @return $this
	 */
	public function with_extensions()
	{
		$this->use_extensions = true;

		return $this;
	}

	/**
	 * Disable the extensions.
	 *
	 * @return $this
	 */
	public function without_extensions()
	{
		$this->use_extensions = false;

		return $this;
	}

	/**
	 * Enable the caching of the container.
	 *
	 * If DEBUG_CONTAINER is set this option is ignored and a new container is build.
	 *
	 * @return $this
	 */
	public function with_cache()
	{
		$this->use_cache = true;

		return $this;
	}

	/**
	 * Disable the caching of the container.
	 *
	 * @return $this
	 */
	public function without_cache()
	{
		$this->use_cache = false;

		return $this;
	}

	/**
	 * Set the cache directory.
	 *
	 * @param string $cache_dir The cache directory.
	 * @return $this
	 */
	public function with_cache_dir($cache_dir)
	{
		$this->cache_dir = $cache_dir;

		return $this;
	}

	/**
	 * Enable the compilation of the container.
	 *
	 * @return $this
	 */
	public function with_compiled_container()
	{
		$this->compile_container = true;

		return $this;
	}

	/**
	 * Disable the compilation of the container.
	 *
	 * @return $this
	 */
	public function without_compiled_container()
	{
		$this->compile_container = false;

		return $this;
	}

	/**
	 * Set a custom path to find the configuration of the container.
	 *
	 * @param string $config_path
	 * @return $this
	 */
	public function with_config_path($config_path)
	{
		$this->config_path = $config_path;

		return $this;
	}

	/**
	 * Set custom parameters to inject into the container.
	 *
	 * @param array $custom_parameters
	 * @return $this
	 */
	public function with_custom_parameters($custom_parameters)
	{
		$this->custom_parameters = $custom_parameters;

		return $this;
	}

	/**
	 * Set custom parameters to inject into the container.
	 *
	 * @param \phpbb\config_php_file $config_php_file
	 * @return $this
	 */
	public function with_config(\phpbb\config_php_file $config_php_file)
	{
		$this->config_php_file = $config_php_file;

		return $this;
	}

	/**
	 * Returns the path to the container configuration (default: root_path/config)
	 *
	 * @return string
	 */
	protected function get_config_path()
	{
		return $this->config_path ?: $this->phpbb_root_path . 'config';
	}

	/**
	 * Returns the path to the cache directory (default: root_path/cache/environment).
	 *
	 * @return string Path to the cache directory.
	 */
	protected function get_cache_dir()
	{
		return $this->cache_dir ?: $this->phpbb_root_path . 'cache/' . $this->get_environment() . '/';
	}

	/**
	 * Load the enabled extensions.
	 */
	protected function load_extensions()
	{
		if ($this->config_php_file !== null)
		{
			// Build an intermediate container to load the ext list from the database
			$container_builder = new container_builder($this->phpbb_root_path, $this->php_ext);
			$ext_container = $container_builder
				->without_cache()
				->without_extensions()
				->with_config($this->config_php_file)
				->with_environment('production')
				->without_compiled_container()
				->get_container()
			;

			$ext_container->register('cache.driver', '\\phpbb\\cache\\driver\\dummy');
			$ext_container->compile();

			$extensions = $ext_container->get('ext.manager')->all_enabled();

			// Load each extension found
			foreach ($extensions as $ext_name => $path)
			{
				$extension_class = '\\' . str_replace('/', '\\', $ext_name) . '\\di\\extension';

				if (!class_exists($extension_class))
				{
					$extension_class = '\\phpbb\\extension\\di\\extension_base';
				}

				$this->container_extensions[] = new $extension_class($ext_name, $path);

				// Load extension autoloader
				$filename = $path . 'vendor/autoload.php';
				if (file_exists($filename))
				{
					require $filename;
				}
			}
		}
		else
		{
			// To load the extensions we need the database credentials.
			// Automatically disable the extensions if we don't have them.
			$this->use_extensions = false;
		}
	}

	/**
	 * Dump the container to the disk.
	 *
	 * @param ConfigCache $cache The config cache
	 */
	protected function dump_container($cache)
	{
		try
		{
			$dumper                = new PhpDumper($this->container);
			$cached_container_dump = $dumper->dump(array(
				'class'      => 'phpbb_cache_container',
				'base_class' => 'Symfony\\Component\\DependencyInjection\\ContainerBuilder',
			));

			$cache->write($cached_container_dump, $this->container->getResources());
		}
		catch (IOException $e)
		{
			// Don't fail if the cache isn't writeable
		}
	}

	/**
	 * Create the ContainerBuilder object
	 *
	 * @param array $extensions Array of Container extension objects
	 * @return ContainerBuilder object
	 */
	protected function create_container(array $extensions)
	{
		$container = new ContainerBuilder(new ParameterBag($this->get_core_parameters()));

		$extensions_alias = array();

		foreach ($extensions as $extension)
		{
			$container->registerExtension($extension);
			$extensions_alias[] = $extension->getAlias();
		}

		$container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions_alias));

		return $container;
	}

	/**
	 * Inject the customs parameters into the container
	 */
	protected function inject_custom_parameters()
	{
		if ($this->custom_parameters !== null)
		{
			foreach ($this->custom_parameters as $key => $value)
			{
				$this->container->setParameter($key, $value);
			}
		}
	}

	/**
	 * Returns the core parameters.
	 *
	 * @return array An array of core parameters
	 */
	protected function get_core_parameters()
	{
		return array_merge(
			array(
				'core.root_path'     => $this->phpbb_root_path,
				'core.php_ext'       => $this->php_ext,
				'core.environment'   => $this->get_environment(),
				'core.debug'         => DEBUG,
			),
			$this->get_env_parameters()
		);
	}

	/**
	 * Gets the environment parameters.
	 *
	 * Only the parameters starting with "PHPBB__" are considered.
	 *
	 * @return array An array of parameters
	 */
	protected function get_env_parameters()
	{
		$parameters = array();
		foreach ($_SERVER as $key => $value)
		{
			if (0 === strpos($key, 'PHPBB__'))
			{
				$parameters[strtolower(str_replace('__', '.', substr($key, 9)))] = $value;
			}
		}

		return $parameters;
	}

	/**
	 * Get the filename under which the dumped container will be stored.
	 *
	 * @return string Path for dumped container
	 */
	protected function get_container_filename()
	{
		return $this->get_cache_dir() . 'container_' . md5($this->phpbb_root_path) . '.' . $this->php_ext;
	}

	/**
	 * Return the name of the current environment.
	 *
	 * @return string
	 */
	protected function get_environment()
	{
		return $this->environment ?: PHPBB_ENVIRONMENT;
	}
}
