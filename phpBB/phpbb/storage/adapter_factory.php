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
use phpbb\storage\exception\storage_exception;

class adapter_factory
{
	/**
	 * @var config
	 */
	protected $config;

	/**
	 * @var service_collection
	 */
	protected $adapters;

	/**
	 * @var service_collection
	 */
	protected $providers;

	/**
	 * Constructor
	 *
	 * @param config				$config
	 * @param service_collection	$adapters
	 * @param service_collection	$providers
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
	 * @return mixed
	 */
	public function get(string $storage_name): mixed
	{
		$provider_class = $this->config['storage\\' . $storage_name . '\\provider'];
		$provider = $this->providers->get_by_class($provider_class);

		$options = [];
		foreach (array_keys($provider->get_options()) as $definition)
		{
			/** @psalm-suppress InvalidArrayOffset */
			$options[$definition] = $this->config['storage\\' . $storage_name . '\\config\\' . $definition];
		}

		return $this->get_with_options($storage_name, $options);
	}

	/**
	 * Obtains a configured adapters for a given storage with custom options
	 *
	 * @param string	$storage_name
	 * @param array		$options
	 *
	 * @return mixed
	 */
	public function get_with_options(string $storage_name, array $options): mixed
	{
		$provider_class = $this->config['storage\\' . $storage_name . '\\provider'];
		$provider = $this->providers->get_by_class($provider_class);

		if (!$provider->is_available())
		{
			throw new storage_exception('STORAGE_ADAPTER_NOT_AVAILABLE');
		}

		$adapter = $this->adapters->get_by_class($provider->get_adapter_class());
		$adapter->configure($options);

		return $adapter;
	}
}
