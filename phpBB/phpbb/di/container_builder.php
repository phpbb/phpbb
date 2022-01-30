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
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Finder\Finder;
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
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $dbal_connection = null;

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
	protected $custom_parameters = [];

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

	/** @var \Exception */
	private $build_exception;

	/**
	 * @var array
	 */
	private $env_parameters = [];

	/**
	 * Constructor
	 *
	 * @param string $phpbb_root_path Path to the phpbb includes directory.
	 * @param string $php_ext php file extension
	 */
	public function __construct($phpbb_root_path, $php_ext)
	{
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->env_parameters	= $this->get_env_parameters();

		if (isset($this->env_parameters['core.cache_dir']))
		{
			$this->with_cache_dir($this->env_parameters['core.cache_dir']);
		}
	}

	/**
	 * Build and return a new Container respecting the current configuration
	 *
	 * @return \phpbb_cache_container|ContainerBuilder
	 */
	public function get_container()
	{
		try
		{
			$build_container = true;

			if ($this->use_cache)
			{
				if ($this->use_extensions)
				{
					$autoload_cache = new ConfigCache($this->get_autoload_filename(), defined('DEBUG'));

					if (!$autoload_cache->isFresh())
					{
						// autoload cache should be refreshed
						$this->load_extensions();
					}

					require($this->get_autoload_filename());
				}

				$container_filename = $this->get_container_filename();
				$config_cache = new ConfigCache($container_filename, defined('DEBUG'));

				if ($config_cache->isFresh())
				{
					require($config_cache->getPath());
					$this->container = new \phpbb_cache_container();
					$build_container = false;
				}
			}

			if ($build_container)
			{
				$this->container_extensions = [
					new extension\core($this->get_config_path()),
				];

				if ($this->use_extensions)
				{
					$this->load_extensions();
				}

				// Add tables extension after all extensions
				$this->container_extensions[] = new extension\tables();

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

				if ($this->use_extensions)
				{
					$this->register_ext_compiler_pass();
				}

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

			if ($this->config_php_file)
			{
				$this->container->set('config.php', $this->config_php_file);
			}

			$this->inject_dbal_driver();

			return $this->container;
		}
		catch (\Exception $e)
		{
			// Don't try to recover if we are in the development environment
			if ($this->get_environment() === 'development')
			{
				throw $e;
			}

			if ($this->build_exception === null)
			{
				$this->build_exception = $e;

				return $this
					->without_extensions()
					->without_cache()
					->with_custom_parameters(array_merge($this->custom_parameters, [
						'container_exception' => $e,
					]))
					->get_container();
			}
			else
			{
				// Rethrow the original exception if it's still failing
				throw $this->build_exception;
			}
		}
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
				->with_config_path($this->get_config_path())
				->with_environment('production')
				->without_compiled_container()
				->get_container()
			;

			$ext_container->register('cache.driver', '\\phpbb\\cache\\driver\\dummy');
			$ext_container->compile();

			$extensions = $ext_container->get('ext.manager')->all_enabled();

			// Load each extension found
			$autoloaders = '<?php
/**
 * Loads all extensions custom auto-loaders.
 *
 * This file has been auto-generated
 * by phpBB while loading the extensions.
 */

';
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
					$autoloaders .= "require('{$filename}');\n";
				}
			}

			$configCache = new ConfigCache($this->get_autoload_filename(), false);
			$configCache->write($autoloaders);

			require($this->get_autoload_filename());
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
			$dumper = new PhpDumper($this->container);
			$proxy_dumper = new ProxyDumper();
			$dumper->setProxyDumper($proxy_dumper);

			$cached_container_dump = $dumper->dump(array(
				'class'      => 'phpbb_cache_container',
				'base_class' => 'Symfony\\Component\\DependencyInjection\\Container',
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
		$container->setProxyInstantiator(new proxy_instantiator($this->get_cache_dir()));

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
		foreach ($this->custom_parameters as $key => $value)
		{
			$this->container->setParameter($key, $value);
		}
	}

	/**
	 * Inject the dbal connection driver into container
	 */
	protected function inject_dbal_driver()
	{
		if (empty($this->config_php_file))
		{
			return;
		}

		$config_data = $this->config_php_file->get_all();
		if (!empty($config_data))
		{
			if ($this->dbal_connection === null)
			{
				$dbal_driver_class = $this->config_php_file->convert_30_dbms_to_31($this->config_php_file->get('dbms'));
				/** @var \phpbb\db\driver\driver_interface $dbal_connection */
				$this->dbal_connection = new $dbal_driver_class();
				$this->dbal_connection->sql_connect(
					$this->config_php_file->get('dbhost'),
					$this->config_php_file->get('dbuser'),
					$this->config_php_file->get('dbpasswd'),
					$this->config_php_file->get('dbname'),
					$this->config_php_file->get('dbport'),
					false,
					defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK
				);
			}
			$this->container->set('dbal.conn.driver', $this->dbal_connection);
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
			[
				'core.root_path'     => $this->phpbb_root_path,
				'core.php_ext'       => $this->php_ext,
				'core.environment'   => $this->get_environment(),
				'core.debug'         => defined('DEBUG') ? DEBUG : false,
				'core.cache_dir'     => $this->get_cache_dir(),
			],
			$this->env_parameters
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
		$container_params = [
			'phpbb_root_path' => $this->phpbb_root_path,
			'use_extensions' => $this->use_extensions,
			'config_path' => $this->config_path,
		];

		return $this->get_cache_dir() . 'container_' . md5(implode(',', $container_params)) . '.' . $this->php_ext;
	}

	/**
	 * Get the filename under which the dumped extensions autoloader will be stored.
	 *
	 * @return string Path for dumped extensions autoloader
	 */
	protected function get_autoload_filename()
	{
		$container_params = [
			'phpbb_root_path' => $this->phpbb_root_path,
			'use_extensions' => $this->use_extensions,
			'config_path' => $this->config_path,
		];

		return $this->get_cache_dir() . 'autoload_' . md5(implode(',', $container_params)) . '.' . $this->php_ext;
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

	private function register_ext_compiler_pass()
	{
		$finder = new Finder();
		$finder
			->name('*_pass.php')
			->path('di/pass')
			->files()
			->ignoreDotFiles(true)
			->ignoreUnreadableDirs(true)
			->ignoreVCS(true)
			->followLinks()
			->in($this->phpbb_root_path . 'ext')
		;

		/** @var \SplFileInfo $pass */
		foreach ($finder as $pass)
		{
			$filename = $pass->getPathname();
			$filename = substr($filename, 0, -strlen('.' . $pass->getExtension()));
			$filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
			$className = preg_replace('#^.*ext/#', '', $filename);
			$className = '\\' . str_replace('/', '\\', $className);

			if (class_exists($className) && in_array('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface', class_implements($className), true))
			{
				$this->container->addCompilerPass(new $className());
			}
		}
	}
}
