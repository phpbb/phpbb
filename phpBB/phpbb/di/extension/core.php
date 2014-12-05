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

namespace phpbb\di\extension;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
* Container core extension
*/
class core extends Extension
{
	/**
	 * Config path
	 * @var string
	 */
	protected $config_path;

	/**
	 * Constructor
	 *
	 * @param string $config_path Config path
	 */
	public function __construct($config_path)
	{
		$this->config_path = $config_path;
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
		$loader = new YamlFileLoader($container, new FileLocator(phpbb_realpath($this->config_path)));
		$loader->load($container->getParameter('core.environment') . '/container/environment.yml');

		$config = $this->getConfiguration($configs, $container);
		$config = $this->processConfiguration($config, $configs);

		if ($config['require_dev_dependencies'])
		{
			if (!class_exists('Goutte\Client', true))
			{
				trigger_error(
					'Composer development dependencies have not been set up for the ' . $container->getParameter('core.environment') . ' environment yet, run ' .
					"'php ../composer.phar install --dev' from the phpBB directory to do so.",
					E_USER_ERROR
				);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		$r = new \ReflectionClass('\phpbb\di\extension\container_configuration');
		$container->addResource(new FileResource($r->getFileName()));

		return new container_configuration();
	}

	/**
	 * Returns the recommended alias to use in XML.
	 *
	 * This alias is also the mandatory prefix to use when using YAML.
	 *
	 * @return string The alias
	 */
	public function getAlias()
	{
		return 'core';
	}
}
