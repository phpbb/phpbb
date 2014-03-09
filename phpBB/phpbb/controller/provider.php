<?php
/**
*
* @package controller
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\controller;

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
	* Collection of the routes in phpBB and all found extensions
	* @var RouteCollection
	*/
	protected $routes;

	/**
	* Construct method
	*
	* @param array() $routing_files Array of strings containing paths
	*							to YAML files holding route information
	*/
	public function __construct(\phpbb\extension\finder $finder = null, $routing_files = array())
	{
		$this->routing_files = $routing_files;

		if ($finder)
		{
			// We hardcode the path to the core config directory
			// because the finder cannot find it
			$this->routing_files = array_merge($this->routing_files, array('config/routing.yml'), array_keys($finder
					->directory('config')
					->suffix('routing.yml')
					->find()
			));
		}
	}

	/**
	* Find a list of controllers and return it
	*
	* @param string $base_path Base path to prepend to file paths
	* @return null
	*/
	public function find($base_path = '')
	{
		$this->routes = new RouteCollection;
		foreach ($this->routing_files as $file_path)
		{
			$loader = new YamlFileLoader(new FileLocator($base_path));
			$this->routes->addCollection($loader->load($file_path));
		}

		return $this;
	}

	/**
	* Get the list of routes
	*
	* @return RouteCollection Get the route collection
	*/
	public function get_routes()
	{
		return $this->routes;
	}
}
