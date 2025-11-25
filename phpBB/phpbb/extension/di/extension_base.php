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

namespace phpbb\extension\di;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use phpbb\filesystem\helper as filesystem_helper;

/**
 * Container core extension
 */
class extension_base extends Extension
{
	/**
	 * Name of the extension (vendor/name)
	 *
	 * @var string
	 */
	protected $extension_name;

	/**
	 * Path to the extension.
	 *
	 * @var string
	 */
	protected $ext_path;

	/**
	 * Constructor
	 *
	 * @param string $extension_name Name of the extension (vendor/name)
	 * @param string $ext_path       Path to the extension
	 */
	public function __construct($extension_name, $ext_path)
	{
		$this->extension_name = $extension_name;
		$this->ext_path = $ext_path;
	}

	/**
	 * Loads a specific configuration.
	 *
	 * @param array            $configs   An array of configuration values
	 * @param ContainerBuilder $container A ContainerBuilder instance
	 *
	 * @throws \InvalidArgumentException When provided tag is not defined in this extension
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$this->load_services($container);
	}

	/**
	 * Loads the services.yml file.
	 *
	 * @param ContainerBuilder $container A ContainerBuilder instance
	 */
	protected function load_services(ContainerBuilder $container)
	{
		$services_directory = false;
		$services_file = false;
		$additional_services_files = array();

		if (file_exists($this->ext_path . 'config/' . $container->getParameter('core.environment') . '/container/environment.yml'))
		{
			$services_directory = 'config/' . $container->getParameter('core.environment') . '/container';
			$services_file = 'environment.yml';
		}
		else if (!is_dir($this->ext_path . 'config/' . $container->getParameter('core.environment')))
		{
			if (file_exists($this->ext_path . 'config/default/container/environment.yml'))
			{
				$services_directory = 'config/default/container';
				$services_file = 'environment.yml';
			}
			else if (!is_dir($this->ext_path . 'config/default') && file_exists($this->ext_path . '/config/services.yml'))
			{
				$services_directory = 'config';
				$services_file = 'services.yml';
			}
		}

		if ($services_directory && $services_file)
		{
			$loader = new YamlFileLoader($container, new FileLocator(filesystem_helper::realpath($this->ext_path . $services_directory)));
			$loader->load($services_file);

			// Load additional services located in service_ prefixed Yaml files if any
			$additional_services_files = $this->getServicesFilenames($services_directory, 'services_');
			foreach ($additional_services_files as $file)
			{
				$loader->load($file);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		$reflected = new \ReflectionClass($this);
		$namespace = $reflected->getNamespaceName();

		$class = $namespace . '\\di\configuration';
		if (class_exists($class))
		{
			$r = new \ReflectionClass($class);
			$container->addResource(new FileResource($r->getFileName()));

			if (!method_exists($class, '__construct'))
			{
				$configuration = new $class();

				return $configuration;
			}
		}

		return null;
	}

	/**
	 * Returns the recommended alias to use in XML.
	 *
	 * This alias is also the mandatory prefix to use when using YAML.
	 *
	 * @return string The alias
	 */
	public function getAlias(): string
	{
		return str_replace('/', '_', $this->extension_name);
	}

	/**
	 * Gets the array of the Yaml filenames.
	 *
	 * @param string $services_directory   Directory in the extension folder containing services files
	 * @param string $services_file_prefix Services files prefix to look for
	 *
	 * @return array The services filenames array
	 */
	public function getServicesFilenames($services_directory, $services_file_prefix = '')
	{
		$services_files = array();
		$finder = new \Symfony\Component\Finder\Finder();
		$finder
			->name("{$services_file_prefix}*.yml")
			->path($services_directory)
			->files()
			->in($this->ext_path);

		foreach ($finder as $file)
		{
			$services_files[] = $file->getBasename();
		}

		return $services_files;
	}
}
