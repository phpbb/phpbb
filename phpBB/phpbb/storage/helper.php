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

class helper
{
	/** @var config */
	protected $config;

	/** @var adapter_factory */
	protected $adapter_factory;

	/** @var state_helper */
	protected $state_helper;

	/** @var service_collection */
	protected $provider_collection;

	/** @var service_collection */
	protected $adapter_collection;

	/**
	 * Constructor
	 *
	 * @param config $config
	 * @param adapter_factory $adapter_factory
	 * @param state_helper $state_helper
	 * @param service_collection $provider_collection
	 * @param service_collection $adapter_collection
 */
	public function __construct(config $config, adapter_factory $adapter_factory, state_helper $state_helper, service_collection $provider_collection, service_collection $adapter_collection)
	{
		$this->config = $config;
		$this->adapter_factory = $adapter_factory;
		$this->state_helper = $state_helper;
		$this->provider_collection = $provider_collection;
		$this->adapter_collection = $adapter_collection;
	}

	/**
	 * Get adapter definitions from a provider
	 *
	 * @param string $provider_class Provider class
	 *
	 * @return array Adapter definitions
	 */
	public function get_provider_options(string $provider_class) : array
	{
		return $this->provider_collection->get_by_class($provider_class)->get_options();
	}

	/**
	 * Get the current provider from config
	 *
	 * @param string $storage_name Storage name
	 *
	 * @return string The current provider
	 */
	public function get_current_provider(string $storage_name) : string
	{
		return (string) $this->config['storage\\' . $storage_name . '\\provider'];
	}

	/**
	 * Get the current value of the definition of a storage from config
	 *
	 * @param string $storage_name Storage name
	 * @param string $definition Definition
	 *
	 * @return string Definition value
	 */
	public function get_current_definition(string $storage_name, string $definition) : string
	{
		return (string) $this->config['storage\\' . $storage_name . '\\config\\' . $definition];
	}

	/**
	 * Get current storage adapter
	 *
	 * @param string $storage_name Storage adapter name
	 *
	 * @return object Storage adapter instance
	 */
	public function get_current_adapter(string $storage_name): object
	{
		static $adapters = [];

		if (!isset($adapters[$storage_name]))
		{
			$adapters[$storage_name] = $this->adapter_factory->get($storage_name);
		}

		return $adapters[$storage_name];
	}

	/**
	 * Get new storage adapter
	 *
	 * @param string $storage_name
	 *
	 * @return mixed Storage adapter instance
	 */
	public function get_new_adapter(string $storage_name): mixed
	{
		static $adapters = [];

		if (!isset($adapters[$storage_name]))
		{
			$provider_class = $this->state_helper->new_provider($storage_name);
			$definitions = array_keys($this->get_provider_options($provider_class));

			$options = [];
			foreach ($definitions as $definition)
			{
				$options[$definition] = $this->state_helper->new_definition_value($storage_name, $definition);
			}

			$adapters[$storage_name] = $this->adapter_factory->get_with_options($storage_name, $options);
		}

		return $adapters[$storage_name];
	}

	/**
	 * Delete configuration options for a given storage
	 *
	 * @param string $storage_name
	 *
	 * @return void
	 */
	public function delete_storage_options(string $storage_name): void
	{
		$provider = $this->get_current_provider($storage_name);
		$options = $this->get_provider_options($provider);

		foreach (array_keys($options) as $definition)
		{
			$this->config->delete('storage\\' . $storage_name . '\\config\\' . $definition);
		}
	}

	/**
	 * Set a provider in configuration for a given storage
	 *
	 * @param string $storage_name
	 * @param string $provider
	 *
	 * @return void
	 */
	public function set_storage_provider(string $storage_name, string $provider): void
	{
		$this->config->set('storage\\' . $storage_name . '\\provider', $provider);
	}

	/**
	 * Set storage options in configuration for a given storage
	 *
	 * @param string $storage_name
	 * @param string $definition
	 * @param string $value
	 *
	 * @return void
	 */
	public function set_storage_definition(string $storage_name, string $definition, string $value): void
	{
		$this->config->set('storage\\' . $storage_name . '\\config\\' . $definition, $value);
	}

	/**
	 * Copy a file from the current adapter to the new adapter
	 *
	 * @param $storage_name
	 * @param $file
	 *
	 * @return void
	 */
	public function copy_file_to_new_adapter($storage_name, $file): void
	{
		$current_adapter = $this->get_current_adapter($storage_name);
		$new_adapter = $this->get_new_adapter($storage_name);

		$stream = $current_adapter->read_stream($file);
		$new_adapter->write_stream($file, $stream);

		if (is_resource($stream))
		{
			fclose($stream);
		}
	}


	/**
	 * Updates a storage with the info provided in the form (that is stored in the state at this point)
	 *
	 * @param string $storage_name Storage name
	 */
	public function update_storage_config(string $storage_name) : void
	{
		// Remove old storage config
		$this->delete_storage_options($storage_name);

		// Update provider
		$new_provider = $this->state_helper->new_provider($storage_name);
		$this->set_storage_provider($storage_name, $new_provider);

		// Set new storage config
		$new_options = $this->get_provider_options($new_provider);

		foreach (array_keys($new_options) as $definition)
		{
			$new_definition_value = $this->state_helper->new_definition_value($storage_name, $definition);
			$this->set_storage_definition($storage_name, $definition, $new_definition_value);
		}
	}

}
