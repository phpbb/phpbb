<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\controller;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
* Controller interface
* @package phpBB3
*/
class provider
{
	/**
	* YAML file(s) containing route information
	* @var array
	*/
	protected $routing_files;

	/**
	* Construct method
	*
	* @param array() $routing_files Array of strings containing paths
	*							to YAML files holding route information
	*/
	public function __construct($routing_files = array())
	{
		$this->routing_files = $routing_files;
	}

	/**
	* Locate paths containing routing files
	* This sets an internal property but does not return the paths.
	*
	* @return The current instance of this object for method chaining
	*/
	public function import_paths_from_finder(\phpbb\extension\finder $finder)
	{
		// We hardcode the path to the core config directory
		// because the finder cannot find it
		$this->routing_files = array_merge(array('config/routing.yml'), array_keys($finder
			->directory('config')
			->prefix('routing')
			->suffix('.yml')
			->find()
		));

		return $this;
	}

	/**
	* Get a list of controllers and return it
	*
	* @param string $base_path Base path to prepend to file paths
	* @return array Array of controllers and their route information
	*/
	public function find($base_path = '')
	{
		$routes = new RouteCollection;
		foreach ($this->routing_files as $file_path)
		{
			$loader = new YamlFileLoader(new FileLocator(dirname($base_path . $file_path)));
			$routes->addCollection($loader->load(basename($file_path)));
		}

		return $routes;
	}
}
