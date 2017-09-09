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

namespace phpbb\storage;

use phpbb\config\config;
use phpbb\di\service_collection;
use phpbb\storage\exception\exception;

class adapter_factory
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\di\service_collection
	 */
	protected $adapters;

	/**
	 * @var \phpbb\di\service_collection
	 */
	protected $providers;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config				$config
	 * @param \phpbb\di\service_collection		$adapters
	 * @param \phpbb\di\service_collection		$providers
	 */
	public function __construct(config $config, service_collection $adapters, service_collection $providers)
	{
		$this->config = $config;
		$this->adapters = $adapters;
		$this->providers = $providers;
	}

	/**
	 * Obtains a configured adapters for a given storage
	 *
	 * @param string	$storage_name
	 *
	 * @return \phpbb\storage\adapter\adapter_interface
	 */
	public function get($storage_name)
	{
		$provider_class = $this->config['storage\\' . $storage_name . '\\provider'];
		$provider = $this->providers->get_by_class($provider_class);

		if (!$provider->is_available())
		{
			throw new exception('STORAGE_ADAPTER_NOT_AVAILABLE');
		}

		$adapter = $this->adapters->get_by_class($provider->get_adapter_class());
		$adapter->configure($this->build_options($storage_name, $provider->get_options()));

		return $adapter;
	}

	/**
	 * Obtains configuration for a given storage
	 *
	 * @param string	$storage_name
	 * @param array		$definitions
	 *
	 * @return array	Returns storage configuration values
	 */
	public function build_options($storage_name, array $definitions)
	{
		$options = [];

		foreach (array_keys($definitions) as $definition)
		{
			$options[$definition] = $this->config['storage\\' . $storage_name . '\\config\\' . $definition];
		}

		return $options;
	}
}
