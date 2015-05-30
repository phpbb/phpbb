<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license       GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\routing;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use phpbb\extension\manager;

/**
 * Integration of all pieces of the routing system for easier use.
 */
class router implements RouterInterface
{
	/**
	 * Extension manager
	 *
	 * @var manager
	 */
	protected $extension_manager;

	/**
	 * phpBB root path
	 *
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * PHP file extensions
	 *
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Name of the current environment
	 *
	 * @var string
	 */
	protected $environment;

	/**
	 * YAML file(s) containing route information
	 *
	 * @var array
	 */
	protected $routing_files;

	/**
	 * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface|null
	 */
	protected $matcher;

	/**
	 * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface|null
	 */
	protected $generator;

	/**
	 * @var RequestContext
	 */
	protected $context;

	/**
	 * @var RouteCollection|null
	 */
	protected $route_collection;

	/**
	 * @var \phpbb\filesystem\filesystem_interface
	 */
	protected $filesystem;

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Construct method
	 *
	 * @param ContainerInterface	$container			DI container
	 * @param \phpbb\filesystem\filesystem_interface $filesystem	Filesystem helper
	 * @param string	$phpbb_root_path	phpBB root path
	 * @param string	$php_ext			PHP file extension
	 * @param string	$environment		Name of the current environment
	 * @param manager	$extension_manager	Extension manager
	 * @param array		$routing_files		Array of strings containing paths to YAML files holding route information
	 */
	public function __construct(ContainerInterface $container, \phpbb\filesystem\filesystem_interface $filesystem, $phpbb_root_path, $php_ext, $environment, manager $extension_manager = null, $routing_files = array())
	{
		$this->container			= $container;
		$this->filesystem			= $filesystem;
		$this->extension_manager	= $extension_manager;
		$this->routing_files		= $routing_files;
		$this->phpbb_root_path		= $phpbb_root_path;
		$this->php_ext				= $php_ext;
		$this->environment			= $environment;
		$this->context				= new RequestContext();
	}

	/**
	 * Find the list of routing files
	 *
	 * @param array $paths Array of paths where to look for routing files (they must be relative to the phpBB root path).
	 * @return router
	 */
	public function find_routing_files(array $paths)
	{
		$this->routing_files = array('config/' . $this->environment . '/routing/environment.yml');
		foreach ($paths as $path)
		{
			if (file_exists($this->phpbb_root_path . $path . 'config/' . $this->environment . '/routing/environment.yml'))
			{
				$this->routing_files[] = $path . 'config/' . $this->environment . '/routing/environment.yml';
			}
			else if (!is_dir($this->phpbb_root_path . $path . 'config/' . $this->environment))
			{
				if (file_exists($this->phpbb_root_path . $path . 'config/default/routing/environment.yml'))
				{
					$this->routing_files[] = $path . 'config/default/routing/environment.yml';
				}
				else if (!is_dir($this->phpbb_root_path . $path . 'config/default/routing') && file_exists($this->phpbb_root_path . $path . 'config/routing.yml'))
				{
					$this->routing_files[] = $path . 'config/routing.yml';
				}
			}
		}

		return $this;
	}

	/**
	 * Find a list of controllers
	 *
	 * @param string $base_path Base path to prepend to file paths
	 * @return router
	 */
	public function find($base_path = '')
	{
		if ($this->route_collection === null || $this->route_collection->count() === 0)
		{
			$this->route_collection = new RouteCollection;
			foreach ($this->routing_files as $file_path)
			{
				$loader = new YamlFileLoader(new FileLocator($this->filesystem->realpath($base_path)));
				$this->route_collection->addCollection($loader->load($file_path));
			}
		}

		$this->resolveParameters($this->route_collection);

		return $this;
	}

	/**
	 * Replaces placeholders with service container parameter values in:
	 * - the route defaults,
	 * - the route requirements,
	 * - the route path,
	 * - the route host,
	 * - the route schemes,
	 * - the route methods.
	 *
	 * @param RouteCollection $collection
	 */
	private function resolveParameters(RouteCollection $collection)
	{
		foreach ($collection as $route)
		{
			foreach ($route->getDefaults() as $name => $value)
			{
				$route->setDefault($name, $this->resolve($value));
			}

			$requirements = $route->getRequirements();
			unset($requirements['_scheme']);
			unset($requirements['_method']);

			foreach ($requirements as $name => $value)
			{
				$route->setRequirement($name, $this->resolve($value));
			}

			$route->setPath($this->resolve($route->getPath()));
			$route->setHost($this->resolve($route->getHost()));

			$schemes = array();
			foreach ($route->getSchemes() as $scheme)
			{
				$schemes = array_merge($schemes, explode('|', $this->resolve($scheme)));
			}

			$route->setSchemes($schemes);
			$methods = array();
			foreach ($route->getMethods() as $method)
			{
				$methods = array_merge($methods, explode('|', $this->resolve($method)));
			}

			$route->setMethods($methods);
			$route->setCondition($this->resolve($route->getCondition()));
		}
	}

	/**
	 * Recursively replaces placeholders with the service container parameters.
	 *
	 * @param mixed $value The source which might contain "%placeholders%"
	 *
	 * @return mixed The source with the placeholders replaced by the container
	 *               parameters. Arrays are resolved recursively.
	 *
	 * @throws ParameterNotFoundException When a placeholder does not exist as a container parameter
	 * @throws RuntimeException           When a container value is not a string or a numeric value
	 */
	private function resolve($value)
	{
		if (is_array($value))
		{
			foreach ($value as $key => $val)
			{
				$value[$key] = $this->resolve($val);
			}

			return $value;
		}

		if (!is_string($value))
		{
			return $value;
		}

		$container = $this->container;
		$escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($container, $value)
		{
			// skip %%
			if (!isset($match[1]))
			{
				return '%%';
			}

			$resolved = $container->getParameter($match[1]);
			if (is_string($resolved) || is_numeric($resolved))
			{
				return (string) $resolved;
			}

			throw new RuntimeException(sprintf(
					'The container parameter "%s", used in the route configuration value "%s", '.
					'must be a string or numeric, but it is of type %s.',
					$match[1],
					$value,
					gettype($resolved)
				)
			);
		}, $value);

		return str_replace('%%', '%', $escapedValue);
	}

	/**
	 * Get the list of routes
	 *
	 * @return RouteCollection Get the route collection
	 */
	public function get_routes()
	{
		if ($this->route_collection == null || empty($this->routing_files))
		{
			$this->find_routing_files(
					($this->extension_manager !== null) ? $this->extension_manager->all_enabled(false) : array()
				)
				->find($this->phpbb_root_path);
		}

		return $this->route_collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRouteCollection()
	{
		return $this->get_routes();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContext(RequestContext $context)
	{
		$this->context = $context;

		if ($this->matcher !== null)
		{
			$this->get_matcher()->setContext($context);
		}
		if ($this->generator !== null)
		{
			$this->get_generator()->setContext($context);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
	{
		return $this->get_generator()->generate($name, $parameters, $referenceType);
	}

	/**
	 * {@inheritdoc}
	 */
	public function match($pathinfo)
	{
		return $this->get_matcher()->match($pathinfo);
	}

	/**
	 * Gets the UrlMatcher instance associated with this Router.
	 *
	 * @return \Symfony\Component\Routing\Matcher\UrlMatcherInterface A UrlMatcherInterface instance
	 */
	public function get_matcher()
	{
		if ($this->matcher !== null)
		{
			return $this->matcher;
		}

		$this->create_dumped_url_matcher();

		return $this->matcher;
	}
	/**
	 * Creates a new dumped URL Matcher (dump it if necessary)
	 */
	protected function create_dumped_url_matcher()
	{
		try
		{
			$cache = new ConfigCache("{$this->phpbb_root_path}cache/{$this->environment}/url_matcher.{$this->php_ext}", defined('DEBUG'));
			if (!$cache->isFresh())
			{
				$dumper = new PhpMatcherDumper($this->get_routes());

				$options = array(
					'class'      => 'phpbb_url_matcher',
					'base_class' => 'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
				);

				$cache->write($dumper->dump($options), $this->get_routes()->getResources());
			}

			require_once($cache->getPath());

			$this->matcher = new \phpbb_url_matcher($this->context);
		}
		catch (IOException $e)
		{
			$this->create_new_url_matcher();
		}
	}

	/**
	 * Creates a new URL Matcher
	 */
	protected function create_new_url_matcher()
	{
		$this->matcher = new UrlMatcher($this->get_routes(), $this->context);
	}

	/**
	 * Gets the UrlGenerator instance associated with this Router.
	 *
	 * @return \Symfony\Component\Routing\Generator\UrlGeneratorInterface A UrlGeneratorInterface instance
	 */
	public function get_generator()
	{
		if ($this->generator !== null)
		{
			return $this->generator;
		}

		$this->create_dumped_url_generator();

		return $this->generator;
	}

	/**
	 * Creates a new dumped URL Generator (dump it if necessary)
	 */
	protected function create_dumped_url_generator()
	{
		try
		{
			$cache = new ConfigCache("{$this->phpbb_root_path}cache/{$this->environment}/url_generator.{$this->php_ext}", defined('DEBUG'));
			if (!$cache->isFresh())
			{
				$dumper = new PhpGeneratorDumper($this->get_routes());

				$options = array(
					'class'      => 'phpbb_url_generator',
					'base_class' => 'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
				);

				$cache->write($dumper->dump($options), $this->get_routes()->getResources());
			}

			require_once($cache->getPath());

			$this->generator = new \phpbb_url_generator($this->context);
		}
		catch (IOException $e)
		{
			$this->create_new_url_generator();
		}
	}

	/**
	 * Creates a new URL Generator
	 */
	protected function create_new_url_generator()
	{
		$this->generator = new UrlGenerator($this->get_routes(), $this->context);
	}
}
