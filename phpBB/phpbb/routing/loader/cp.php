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

namespace phpbb\routing\loader;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class cp extends Loader
{
	/** @var \phpbb\cp\manager */
	protected $cp_manager;

	/** @var array Route options */
	protected $options = [
		'defaults'		=> 'setDefaults',
		'requirements'	=> 'setRequirements',
		'options'		=> 'setOptions',
		'host'			=> 'setHost',
		'schemes'		=> 'setSchemes',
		'methods'		=> 'setMethods',
		'condition'		=> 'setCondition',
	];

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cp\manager		$cp_manager		Control panel manager object
	 */
	public function __construct(\phpbb\cp\manager $cp_manager)
	{
		$this->cp_manager = $cp_manager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($resource, $type = null)
	{
		$collection = new RouteCollection();

		foreach ($this->cp_manager->get_collections() as $cp => $items)
		{
			/** @var \phpbb\cp\menu\item $item */
			foreach ($items as $name => $item)
			{
				$data = $item->get_route();

				// If the route data is not an array, it's just a regular link
				if (!is_array($data) || empty($data['path']))
				{
					continue;
				}

				// Add the Route to the collection
				$collection->add($name, $this->create_route($cp, $data));

				// If the 'page' variable is defined, we also create a pagination route.
				if ($item->get_page() !== '')
				{
					$collection->add($name . $this->cp_manager->get_route_pagination(), $this->create_route($cp, $data, $item->get_page()));
				}
			}
		}

		return $collection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function supports($resource, $type = null)
	{
		return $type === 'phpbb_cp_route';
	}

	/**
	 * Create a route for a control panel menu item.
	 *
	 * @param string	$cp
	 * @param array		$data
	 * @param string	$page
	 * @return Route
	 */
	protected function create_route($cp, array $data, $page = '')
	{
		$path = $this->cp_manager->get_route_prefix($cp) . $data['path'];

		// If page is defined, we are creating a pagination route.
		if ($page !== '')
		{
			$path .= '/page-{' . $page . '}';
		}

		$route = new Route($path);

		foreach ($this->options as $option => $function)
		{
			if (isset($data[$option]))
			{
				if ($option === 'defaults' && $page !== '')
				{
					unset($data[$option][$page]);
				}

				$route->$function($data[$option]);
			}
		}

		return $route;
	}
}
