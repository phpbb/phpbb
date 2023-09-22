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

	/** @var service_collection */
	protected $provider_collection;

	/** @var service_collection */
	protected $adapter_collection;

	/** @var adapter_factory */
	protected $adapter_factory;

	/** @var state_helper */
	protected $state_helper;

	public function __construct(config $config, service_collection $provider_collection, service_collection $adapter_collection, adapter_factory $adapter_factory, state_helper $state_helper)
	{
		$this->config = $config;
		$this->provider_collection = $provider_collection;
		$this->adapter_collection = $adapter_collection;
		$this->adapter_factory = $adapter_factory;
		$this->state_helper = $state_helper;
	}

	/**
	 * Get adapter definitions from a provider
	 *
	 * @param string $provider Provider class
	 * @return array Adapter definitions
	 */
	public function get_provider_options(string $provider) : array
	{
		return $this->provider_collection->get_by_class($provider)->get_options();
	}

	/**
	 * Get the current provider from config
	 *
	 * @param string $storage_name Storage name
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
	public function get_new_adapter(string $storage_name)
	{
		static $adapters = [];

		if (!isset($adapters[$storage_name]))
		{
			$provider = $this->state_helper->new_provider($storage_name);
			$provider_class = $this->provider_collection->get_by_class($provider);

			$adapter = $this->adapter_collection->get_by_class($provider_class->get_adapter_class());
			$definitions = $this->get_provider_options($provider);

			$options = [];
			foreach (array_keys($definitions) as $definition)
			{
				$options[$definition] = $this->state_helper->new_definition_value($storage_name, $definition);
			}

			$adapter->configure($options);

			$adapters[$storage_name] = $adapter;
		}

		return $adapters[$storage_name];
	}

	public function delete_storage_options(string $storage_name): void
	{
		$provider = $this->get_current_provider($storage_name);
		$options = $this->get_provider_options($provider);

		foreach (array_keys($options) as $definition)
		{
			$this->config->delete('storage\\' . $storage_name . '\\config\\' . $definition);
		}
	}

	public function set_storage_provider(string $storage_name, string $provider): void
	{
		$this->config->set('storage\\' . $storage_name . '\\provider', $provider);
	}

	public function set_storage_definition(string $storage_name, string $definition, string $value): void
	{
		$this->config->set('storage\\' . $storage_name . '\\config\\' . $definition, $value);
	}

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
