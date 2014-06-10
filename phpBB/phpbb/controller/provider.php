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

namespace phpbb\controller;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

/**
* Controller interface
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
	* phpBB root path
	* @var string
	*/
	protected $phpbb_root_path;

	/**
	* PHP extension
	* @var string
	*/
	protected $php_ext;

	/**
	* Construct method
	*
	* @param string $phpbb_root_path phpBB root path
	* @param string $php_ext PHP extension
	* @param array $routing_files Array of strings containing paths
	*							to YAML files holding route information
	*/
	public function __construct($phpbb_root_path, $php_ext, $routing_files = array())
	{
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->routing_files = $routing_files;
	}

	/**
	* Find the list of routing files
	*
	* @param \phpbb\finder $finder
	* @return null
	*/
	public function find_routing_files(\phpbb\finder $finder)
	{
		// We hardcode the path to the core config directory
		// because the finder cannot find it
		$this->routing_files = array_merge($this->routing_files, array('config/routing.yml'), array_keys($finder
				->directory('/config')
				->suffix('routing.yml')
				->find()
		));
	}

	/**
	* Find a list of controllers
	*
	* @param string $base_path Base path to prepend to file paths
	* @return provider
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

	/**
	* Create and return a new UrlGenerator object
	*
	* @param \phpbb\finder					$finder		A finder object
	* @param \Symfony\Component\Routing\RequestContext	$context	Symfony RequestContext object
	* @return \Symfony\Component\Routing\Generator\UrlGenerator
	*/
	public function get_url_generator(\phpbb\finder $finder, RequestContext $context)
	{
		if ($this->routes == null || empty($this->routing_files))
		{
			$this->find_routing_files($finder);
			$this->find($this->phpbb_root_path);
		}

		if (!defined('DEBUG'))
		{
			if (!$this->is_url_generator_dumped())
			{
				$this->create_dumped_url_generator();
			}

			return $this->load_dumped_url_generator($context);
		}
		else
		{
			return $this->create_url_generator($context);
		}
	}

	/**
	* Create a non-cached UrlGenerator
	*
	* @param RequestContext $context Symfony RequestContext object
	* @return UrlGenerator
	*/
	protected function create_url_generator(RequestContext $context)
	{
		return new UrlGenerator($this->get_routes(), $context);
	}

	/**
	* Create a new UrlGenerator class and dump it into the cache file
	*/
	protected function create_dumped_url_generator()
	{
		$dumper = new PhpGeneratorDumper($this->get_routes());
		$cached_url_generator_dumped = $dumper->dump(array('class'  => 'phpbb_url_generator'));

		file_put_contents($this->phpbb_root_path . 'cache/url_generator.' . $this->php_ext, $cached_url_generator_dumped);
	}

	/**
	* Determine whether we have our dumped URL Generator
	*
	* The class is automatically dumped to the cache directory
	*
	* @return bool
	*/
	protected function is_url_generator_dumped()
	{
		return file_exists($this->phpbb_root_path . 'cache/url_generator.' . $this->php_ext);
	}

	/**
	* Load the cached phpbb_url_generator class
	*
	* @param RequestContext $context Symfony RequestContext object
	* @return \phpbb_url_generator
	*/
	protected function load_dumped_url_generator(RequestContext $context)
	{
		if (!class_exists('phpbb_url_generator'))
		{
			require($this->phpbb_root_path . 'cache/url_generator.' . $this->php_ext);
		}

		return new \phpbb_url_generator($context);
	}
}
