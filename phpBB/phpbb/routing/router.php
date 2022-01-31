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

use phpbb\routing\resources_locator\resources_locator_interface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Integration of all pieces of the routing system for easier use.
 */
class router implements RouterInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var resources_locator_interface
	 */
	protected $resources_locator;

	/**
	 * @var LoaderInterface
	 */
	protected $loader;

	/**
	 * PHP file extensions
	 *
	 * @var string
	 */
	protected $php_ext;

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
	 * @var RouteCollection
	 */
	protected $route_collection;

	/**
	 * @var string
	 */
	protected $cache_dir;

	/**
	 * Construct method
	 *
	 * @param ContainerInterface			$container			DI container
	 * @param resources_locator_interface	$resources_locator	Resources locator
	 * @param LoaderInterface				$loader				Resources loader
	 * @param string						$php_ext			PHP file extension
	 * @param string						$cache_dir			phpBB cache directory
	 */
	public function __construct(ContainerInterface $container, resources_locator_interface $resources_locator, LoaderInterface $loader, $php_ext, $cache_dir)
	{
		$this->container			= $container;
		$this->resources_locator	= $resources_locator;
		$this->loader				= $loader;
		$this->php_ext				= $php_ext;
		$this->context				= new RequestContext();
		$this->cache_dir			= $cache_dir;
	}

	/**
	 * Get the list of routes
	 *
	 * @return RouteCollection Get the route collection
	 */
	public function get_routes()
	{
		if ($this->route_collection === null /*|| $this->route_collection->count() === 0*/)
		{
			$this->route_collection = new RouteCollection;
			foreach ($this->resources_locator->locate_resources() as $resource)
			{
				if (is_array($resource))
				{
					$this->route_collection->addCollection($this->loader->load($resource[0], $resource[1]));
				}
				else
				{
					$this->route_collection->addCollection($this->loader->load($resource));
				}
			}

			$this->resolveParameters($this->route_collection);
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
			$cache = new ConfigCache("{$this->cache_dir}url_matcher.{$this->php_ext}", defined('DEBUG'));
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
			$cache = new ConfigCache("{$this->cache_dir}url_generator.{$this->php_ext}", defined('DEBUG'));
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
	protected function resolveParameters(RouteCollection $collection)
	{
		/** @var \Symfony\Component\Routing\Route $route */
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
}
