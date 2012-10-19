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
	* @param array() $routing_paths Array of strings containing paths
	*							to YAML files holding route information
	*/
	public function __construct($routing_paths = array())
	{
		$this->set_paths($routing_paths);
	}

	/**
	* Locate paths containing routing files
	* This sets an internal property but does not return the paths.
	*
	* @return The current instance of this object for method chaining
	*/
	public function get_paths(phpbb_extension_finder $finder)
	{
		// We hardcode the path to the core config directory
		// because the finder cannot find it
		$this->set_paths(array_merge(array('config'), array_map('dirname', array_keys($finder
			->directory('config')
			->prefix('routing')
			->suffix('yml')
			->find()
		))));

		return $this;
	}

	/**
	* Set the $routing_paths property with a given list of paths
	*
	* @return The current instance of this object for method chaining
	*/
	public function set_paths(array $paths)
	{
		$this->routing_paths = $paths;

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
		foreach ($this->routing_paths as $path)
		{
			$loader = new YamlFileLoader(new FileLocator($base_path . $path));
			$routes->addCollection($loader->load('routing.yml'));
		}

		return $routes;
	}
}
