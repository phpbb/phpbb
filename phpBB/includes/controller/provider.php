<?php
/**
*
* @package controller
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

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
* Controller interface
* @package phpBB3
*/
class phpbb_controller_provider
{
	/**
	* YAML file(s) containing route information
	* @var array
	*/
	protected $routing_paths;

	/**
	* Construct method
	*
	* @param mixed $routing_paths String or array of strings containing paths
	*							to YAML files holding route information
	*/
	public function __construct($routing_paths)
	{
		if (!is_array($routing_paths))
		{
			$routing_paths = array($routing_paths);
		}

		$this->routing_paths = $routing_paths;		
	}

	/**
	* Get a list of controllers and return it
	*
	* @param string $base_path Base path to prepend to file paths
	* @return array|bool Array of controllers and handles or false if the 
	* 					routing file does not exist
	*/
	public function find($base_path = '')
	{
		if ($base_path)
		{
			foreach ($this->routing_paths as $key => $path)
			{
				$this->routing_paths[$key] = './' . $base_path . $path;
			}
		}
		$loader = new YamlFileLoader(new FileLocator($this->routing_paths));
		return $loader->load('routing.yml');
	}
}
