<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
* Container core extension
*/
class phpbb_di_extension_core extends Extension
{
	/**
	* phpBB Root path
	* @var string
	*/
	protected $root_path;

	/**
	* Constructor
	*
	* @param string $root_path Root path
	*/
	public function __construct($root_path)
	{
		$this->root_path = $root_path;
	}

	/**
	* Loads a specific configuration.
	*
	* @param array            $config    An array of configuration values
	* @param ContainerBuilder $container A ContainerBuilder instance
	*
	* @throws InvalidArgumentException When provided tag is not defined in this extension
	*/
	public function load(array $config, ContainerBuilder $container)
	{
		// If we are in install, try to use the updated version, when available
		if (defined('IN_INSTALL') && file_exists($this->root_path . 'install/update/new/config/services.yml'))
		{
			$loader = new YamlFileLoader($container, new FileLocator(phpbb_realpath($this->root_path . 'install/update/new/config')));
			$loader->load('services.yml');
		}
		else if (file_exists($this->root_path . 'config/services.yml'))
		{
			$loader = new YamlFileLoader($container, new FileLocator(phpbb_realpath($this->root_path . 'config')));
			$loader->load('services.yml');
		}
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
