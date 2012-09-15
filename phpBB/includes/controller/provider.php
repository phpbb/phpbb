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
	protected $routing_files;

	/**
	* Construct method
	*
	* @param mixed $routing_file String or array of strings containing paths
	*							to YAML files holding route information
	*/
	public function __construct($routing_file)
	{
		if (!is_array($routing_file))
		{
			$routing_file = array($routing_file);
		}

		$this->routing_files = $routing_file;		
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
		$routes = array();

		foreach ($this->routing_files as $routing_file)
		{
			$routing_file = "{$base_path}{$routing_file}";
			if (!file_exists($routing_file))
			{
				continue;
			}

			$routes = array_merge($routes, Yaml::parse($routing_file));
		}

		return $routes;
	}
}
