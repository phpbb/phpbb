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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
* Load the service configurations from all extensions into the container.
*/
class phpbb_di_processor_ext implements phpbb_di_processor_interface
{
	private $extension_manager;

	/**
	* Constructor.
	*
	* @param string $extension_manager The extension manager
	*/
	public function __construct($extension_manager)
	{
		$this->extension_manager = $extension_manager;
	}

	/**
	* @inheritdoc
	*/
	public function process(ContainerBuilder $container)
	{
		$enabled_exts = $this->extension_manager->all_enabled();
		foreach ($enabled_exts as $name => $path)
		{
			if (file_exists($path . '/config/services.yml'))
			{
				$loader = new YamlFileLoader($container, new FileLocator($path . '/config'));
				$loader->load('services.yml');
			}
		}
	}
}
