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
	protected $cp_manager;

	protected $options = [
		'defaults'		=> 'setDefaults',
		'requirements'	=> 'setRequirements',
		'options'		=> 'setOptions',
		'host'			=> 'setHost',
		'schemes'		=> 'setSchemes',
		'methods'		=> 'setMethods',
		'condition'		=> 'setCondition',
	];

	public function __construct(\phpbb\cp\manager $cp_manager)
	{
		$this->cp_manager = $cp_manager;
	}

	public function load($resource, $type = null)
	{
		$collection = new RouteCollection();

		foreach ($this->cp_manager->get_collections() as $cp => $services)
		{
			/** @var \phpbb\cp\item $service */
			foreach ($services as $name => $service)
			{
				$data = $service->get_route();

				if (!is_array($data) || empty($data['path']))
				{
					continue;
				}

				$collection->add($name, $this->create_route($cp, $data));

				if ($service->get_page() !== '')
				{
					$collection->add($name . $this->cp_manager->get_route_pagination(), $this->create_route($cp, $data, $service->get_page()));
				}
			}
		}

		return $collection;
	}

	public function supports($resource, $type = null)
	{
		return $type === 'phpbb_cp_route';
	}

	protected function create_route($cp, array $data, $page = '')
	{
		$path = $this->cp_manager->get_route_prefix($cp) . $data['path'];

		if ($page !== '')
		{
			$path .= '/{' . $page . '}';
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
