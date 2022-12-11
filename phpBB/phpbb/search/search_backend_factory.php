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

namespace phpbb\search;

use phpbb\config\config;
use phpbb\di\exception\service_not_found_exception;
use phpbb\di\service_collection;
use phpbb\search\backend\search_backend_interface;
use phpbb\search\exception\no_search_backend_found_exception;

class search_backend_factory
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var service_collection
	 */
	protected $search_backends;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param service_collection $search_backends
	 */
	public function __construct(config $config, service_collection $search_backends)
	{
		$this->config = $config;
		$this->search_backends = $search_backends;
	}

	/**
	 * Obtains a specified search backend
	 *
	 * @param string $class
	 *
	 * @throws no_search_backend_found_exception
	 *
	 * @return search_backend_interface
	 */
	public function get(string $class): search_backend_interface
	{
		try
		{
			$search = $this->search_backends->get_by_class($class);
		}
		catch (service_not_found_exception $e)
		{
			throw new no_search_backend_found_exception('SEARCH_BACKEND_NOT_FOUND', [], $e);
		}

		return $search;
	}

	/**
	 * Obtains active search backend
	 *
	 * @throws no_search_backend_found_exception
	 *
	 * @return search_backend_interface
	 */
	public function get_active(): search_backend_interface
	{
		return $this->get($this->config['search_type']);
	}
}
