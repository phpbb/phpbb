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
use phpbb\config\db_text;
use phpbb\di\service_collection;
use phpbb\request\request;
use phpbb\storage\exception\action_in_progress_exception;
use phpbb\storage\exception\no_action_in_progress_exception;

class state_helper
{
	/** @var config */
	protected $config;

	/** @var db_text $config_text */
	protected $config_text;

	/** @var service_collection */
	protected $provider_collection;

	/**
	 * @param config $config
	 * @param db_text $config_text
	 * @param service_collection $provider_collection
	 */
	public function __construct(config $config, db_text $config_text, service_collection $provider_collection)
	{
		$this->config = $config;
		$this->config_text = $config_text;
		$this->provider_collection = $provider_collection;
	}

	/**
	 * Returns if there is an action in progress
	 *
	 * @return bool
	 */
	public function is_action_in_progress(): bool
	{
		return !empty(json_decode($this->config_text->get('storage_update_state'), true));
	}

	/**
	 * Get new provider for the specified storage
	 *
	 * @param string $storage_name
	 *
	 * @return string
	 */
	public function new_provider(string $storage_name): string
	{
		$state = $this->load_state();

		return $state['storages'][$storage_name]['provider'];
	}

	/**
	 * Get new definition value for the specified storage
	 *
	 * @param string $storage_name
	 * @param string $definition
	 *
	 * @return string
	 */
	public function new_definition_value(string $storage_name, string $definition): string
	{
		$state = $this->load_state();

		return $state['storages'][$storage_name]['config'][$definition];
	}

	/**
	 * Get the update type
	 *
	 * @return update_type
	 */
	public function update_type(): update_type
	{
		$state = $this->load_state();

		return update_type::from($state['update_type']);
	}

	/**
	 * Get the current storage index
	 *
	 * @return int
	 */
	public function storage_index(): int
	{
		$state = $this->load_state();

		return $state['storage_index'];
	}

	/**
	 * Update the storage index
	 *
	 * @param int $storage_index
	 *
	 * @return void
	 */
	public function set_storage_index(int $storage_index): void
	{
		$state = $this->load_state();

		$state['storage_index'] = $storage_index;

		$this->save_state($state);
	}

	/**
	 * Get the current remove storage index
	 *
	 * @return int
	 */
	public function remove_storage_index(): int
	{
		$state = $this->load_state();

		return $state['remove_storage_index'];
	}

	/**
	 * Update the remove storage index
	 *
	 * @param int $storage_index
	 *
	 * @return void
	 */
	public function set_remove_storage_index(int $storage_index): void
	{
		$state = $this->load_state();

		$state['remove_storage_index'] = $storage_index;

		$this->save_state($state);
	}

	/**
	 * Get the file index
	 *
	 * @return int
	 */
	public function file_index(): int
	{
		$state = $this->load_state();

		return $state['file_index'];
	}

	/**
	 * Set the file index
	 *
	 * @param int $file_index
	 * @return void
	 */
	public function set_file_index(int $file_index): void
	{
		$state = $this->load_state();

		$state['file_index'] = $file_index;

		$this->save_state($state);
	}

	/**
	 * Get the storage names to be updated
	 *
	 * @return array
	 */
	public function storages(): array
	{
		$state = $this->load_state();

		return array_keys($state['storages']);
	}

	/**
	 * Start a indexing or delete process.
	 *
	 * @param update_type $update_type
	 * @param array $modified_storages
	 * @param request $request
	 *
	 * @throws action_in_progress_exception  If there is an action in progress
	 * @throws \JsonException
	 */
	public function init(update_type $update_type, array $modified_storages, request $request): void
	{
		// Is not possible to start a new process when there is one already running
		if ($this->is_action_in_progress())
		{
			throw new action_in_progress_exception();
		}

		$state = [
			// Save the value of the checkbox, to remove all files from the
			// old storage once they have been successfully moved
			'update_type' => $update_type->value,
			'storages' => [],
			'storage_index' => 0,
			'file_index' => 0,
			'remove_storage_index' => 0,
		];

		// Save in the state the selected storages and their new configuration
		foreach ($modified_storages as $storage_name)
		{
			$state['storages'][$storage_name] = [];

			$state['storages'][$storage_name]['provider'] = $request->variable([$storage_name, 'provider'], '');

			$options = $this->provider_collection->get_by_class($request->variable([$storage_name, 'provider'], ''))->get_options();

			foreach (array_keys($options) as $definition)
			{
				/** @psalm-suppress InvalidArrayOffset */
				$state['storages'][$storage_name]['config'][$definition] = $request->variable([$storage_name, $definition], '');
			}
		}

		$this->save_state($state);
	}

	/**
	 * Clear the state
	 *
	 * @throws \JsonException
	 */
	public function clear_state(): void
	{
		$this->save_state();
	}

	/**
	 * Load the state from the database
	 *
	 * @return array
	 *
	 * @throws no_action_in_progress_exception If there is no action in progress
	 */
	private function load_state(): array
	{
		// Is not possible to execute an action over state if is empty
		if (!$this->is_action_in_progress())
		{
			throw new no_action_in_progress_exception();
		}

		return json_decode($this->config_text->get('storage_update_state'), true) ?? [];
	}

	/**
	 * Save the specified state in the database
	 *
	 * @param array $state
	 *
	 * @throws \JsonException
	 */
	private function save_state(array $state = []): void
	{
		$this->config_text->set('storage_update_state', json_encode($state, JSON_THROW_ON_ERROR));
	}
}
