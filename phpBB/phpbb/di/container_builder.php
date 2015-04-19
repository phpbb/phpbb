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

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

class container_builder
{
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
	* @var array the installed extensions
	*/
	protected $installed_exts = null;

	/**
	* Indicates whether the php config file should be injected into the container (default to true).
	*
	* @var bool
	*/
	protected $inject_config = true;

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
	* Indicates whether the phpBB compile pass should be used (default to true).
	*
	* @var bool
	*/
	protected $use_custom_pass = true;

	/**
	* Indicates whether the kernel compile pass should be used (default to true).
	*
	* @var bool
	*/
	protected $use_kernel_pass = true;

	/**
	* Indicates whether the container should be dumped to the filesystem (default to true).
	*
	* If DEBUG_CONTAINER is set this option is ignored and a new container is build.
	*
	* @var bool
	*/
	protected $dump_container = true;

	/**
	* Indicates if the container should be compiled automatically (default to true).
	*
	* @var bool
	*/
	protected $compile_container = true;

	/**
	* Custom parameters to inject into the container.
	*
	* Default to true:
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
	* Constructor
	*
	* @param \phpbb\config_php_file $config_php_file
	* @param string $phpbb_root_path Path to the phpbb includes directory.
	* @param string $php_ext php file extension
	*/
	function __construct(\phpbb\config_php_file $config_php_file, $phpbb_root_path, $php_ext)
	{
		$this->config_php_file = $config_php_file;
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
		if ($this->dump_container && $config_cache->isFresh())
		{
			require($container_filename);
			$this->container = new \phpbb_cache_container();
		}
		else
		{
			$container_extensions = array(new \phpbb\di\extension\core($this->get_config_path()));

			if ($this->use_extensions)
			{
				$installed_exts = $this->get_installed_extensions();
				foreach ($installed_exts as $ext_name => $path)
				{
					$extension_class = '\\' . str_replace('/', '\\', $ext_name) . '\\di\\extension';

					if (!class_exists($extension_class))
					{
						$extension_class = '\phpbb\extension\di\extension_base';
					}

					$container_extensions[] = new $extension_class($ext_name, $path);
				}
			}

			if ($this->inject_config)
			{
				$container_extensions[] = new \phpbb\di\extension\config($this->config_php_file);
			}

			$this->container = $this->create_container($container_extensions);

			if ($this->use_custom_pass)
			{
				// Symfony Kernel Listeners
				$this->container->addCompilerPass(new \phpbb\di\pass\collection_pass());
				$this->container->addCompilerPass(new RegisterListenersPass('dispatcher', 'event.listener_listener', 'event.listener'));

				if ($this->use_kernel_pass)
				{
					$this->container->addCompilerPass(new RegisterListenersPass('dispatcher'));
				}
			}

			$filesystem = new \phpbb\filesystem\filesystem();
			$loader = new YamlFileLoader($this->container, new FileLocator($filesystem->realpath($this->get_config_path())));
			$loader->load($this->container->getParameter('core.environment') . '/config.yml');

			$this->inject_custom_parameters();

			if ($this->compile_container)
			{
				$this->container->compile();
			}

			if ($this->dump_container)
			{
				$this->dump_container($config_cache);
			}
		}

		$this->container->set('config.php', $this->config_php_file);

		if ($this->compile_container)
		{
			$this->inject_dbal();
		}

		return $this->container;
	}

	/**
	* Set if the extensions should be used.
	*
	* @param bool $use_extensions
	*/
	public function set_use_extensions($use_extensions)
	{
		$this->use_extensions = $use_extensions;
	}

	/**
	* Set if the phpBB compile pass have to be used.
	*
	* @param bool $use_custom_pass
	*/
	public function set_use_custom_pass($use_custom_pass)
	{
		$this->use_custom_pass = $use_custom_pass;
	}

	/**
	* Set if the kernel compile pass have to be used.
	*
	* @param bool $use_kernel_pass
	*/
	public function set_use_kernel_pass($use_kernel_pass)
	{
		$this->use_kernel_pass = $use_kernel_pass;
	}

	/**
	* Set if the php config file should be injecting into the container.
	*
	* @param bool $inject_config
	*/
	public function set_inject_config($inject_config)
	{
		$this->inject_config = $inject_config;
	}

	/**
	* Set if a dump container should be used.
	*
	* If DEBUG_CONTAINER is set this option is ignored and a new container is build.
	*
	* @var bool $dump_container
	*/
	public function set_dump_container($dump_container)
	{
		$this->dump_container = $dump_container;
	}

	/**
	* Set if the container should be compiled automatically (default to true).
	*
	* @var bool $dump_container
	*/
	public function set_compile_container($compile_container)
	{
		$this->compile_container = $compile_container;
	}

	/**
	* Set a custom path to find the configuration of the container
	*
	* @param string $config_path
	*/
	public function set_config_path($config_path)
	{
		$this->config_path = $config_path;
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
	* Set custom parameters to inject into the container.
	*
	* @param array $custom_parameters
	*/
	public function set_custom_parameters($custom_parameters)
	{
		$this->custom_parameters = $custom_parameters;
	}

	/**
	 * Set the path to the cache directory.
	 *
	 * @param string $cache_dir Path to the cache directory
	 */
	public function set_cache_dir($cache_dir)
	{
		$this->cache_dir = $cache_dir;
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
	* Dump the container to the disk.
	*
	* @param ConfigCache $cache The config cache
	*/
	protected function dump_container($cache)
	{
		$dumper = new PhpDumper($this->container);
		$cached_container_dump = $dumper->dump(array(
			'class'         => 'phpbb_cache_container',
			'base_class'    => 'Symfony\\Component\\DependencyInjection\\ContainerBuilder',
		));

		$cache->write($cached_container_dump, $this->container->getResources());
	}

	/**
	* Inject the connection into the container if one was opened.
	*/
	protected function inject_dbal()
	{
		if ($this->dbal_connection !== null)
		{
			$this->container->get('dbal.conn')->set_driver($this->dbal_connection);
		}
	}

	/**
	* Get DB connection.
	*
	* @return \phpbb\db\driver\driver_interface
	*/
	protected function get_dbal_connection()
	{
		if ($this->dbal_connection === null)
		{
			$dbal_driver_class = $this->config_php_file->convert_30_dbms_to_31($this->config_php_file->get('dbms'));
			$this->dbal_connection = new $dbal_driver_class();
			$this->dbal_connection->sql_connect(
				$this->config_php_file->get('dbhost'),
				$this->config_php_file->get('dbuser'),
				$this->config_php_file->get('dbpasswd'),
				$this->config_php_file->get('dbname'),
				$this->config_php_file->get('dbport'),
				defined('PHPBB_DB_NEW_LINK') && PHPBB_DB_NEW_LINK
			);
		}

		return $this->dbal_connection;
	}

	/**
	* Get enabled extensions.
	*
	* @return array enabled extensions
	*/
	protected function get_installed_extensions()
	{
		$db = $this->get_dbal_connection();
		$extension_table = $this->config_php_file->get('table_prefix') . 'ext';

		$sql = 'SELECT *
			FROM ' . $extension_table . '
			WHERE ext_active = 1';

		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);

		$exts = array();
		foreach ($rows as $row)
		{
			$exts[$row['ext_name']] = $this->phpbb_root_path . 'ext/' . $row['ext_name'] . '/';
		}

		return $exts;
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
			//$container->loadFromExtension($extension->getAlias());
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
		$filename = str_replace(array('/', '.'), array('slash', 'dot'), $this->phpbb_root_path);
		return $this->get_cache_dir() . 'container_' . $filename . '.' . $this->php_ext;
	}

	/**
	 * Return the name of the current environment.
	 *
	 * @return string
	 */
	protected function get_environment()
	{
		return PHPBB_ENVIRONMENT;
	}
}
