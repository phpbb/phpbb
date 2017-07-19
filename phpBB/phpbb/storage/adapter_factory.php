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
use Symfony\Component\DependencyInjection\ContainerInterface;

class adapter_factory
{
	protected $config;
	protected $container;
	protected $adapters;
	protected $providers;

	public function __construct(config $config, ContainerInterface $container, service_collection $adapters, service_collection $providers)
	{
		$this->config = $config;
		$this->container = $container;
		$this->adapters = $adapters;
		$this->providers = $providers;
	}

	public function get($storage_name)
	{
		$provider_class = $this->config['storage\\' . $storage_name . '\\adapter'];
		$provider = $this->providers->get_by_class($provider_class);

		$adapter = $this->adapters->get_by_class($provider->get_class());
		$adapter->configure($this->build_options($storage_name, $provider->get_options()));

		return $adapter;
	}

	public function build_options($storage_name, array $definitions)
	{
		$options = [];

		foreach ($definitions as $def)
		{
			$options[$def] = $this->config['storage\\' . $storage_name . '\\config\\' . $def];
		}

		return $options;
	}
}
