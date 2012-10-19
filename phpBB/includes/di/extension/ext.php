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
* Container ext extension
*/
class phpbb_di_extension_ext extends Extension
{
	protected $paths = array();

	public function __construct($extensions_config_file)
	{
		if (file_exists($extensions_config_file))
		{
			$contents = file_get_contents($extensions_config_file);
			$enabled_extensions = json_decode($contents);
			foreach ($enabled_extensions->extensions as $ext)
			{
				$this->paths[] = './ext/' . $ext;
			}
		}
	}

	/**
	* Loads a specific configuration.
	*
	* @param array            $config    An array of configuration values
	* @param ContainerBuilder $container A ContainerBuilder instance
	*
	* @throws InvalidArgumentException When provided tag is not defined in this extension
	*
	* @api
	*/
	public function load(array $config, ContainerBuilder $container)
	{
		foreach ($this->paths as $path)
		{
			if (file_exists($path . '/config/services.yml'))
			{
				$loader = new YamlFileLoader($container, new FileLocator($path . '/config'));
				$loader->load('services.yml');
			}
		}
	}

	/**
	* Returns the recommended alias to use in XML.
	*
	* This alias is also the mandatory prefix to use when using YAML.
	*
	* @return string The alias
	*
	* @api
	*/
	public function getAlias()
	{
		return 'ext';
	}
}
