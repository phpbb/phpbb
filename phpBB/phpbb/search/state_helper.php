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
use phpbb\search\exception\action_in_progress_exception;
use phpbb\search\exception\no_action_in_progress_exception;
use phpbb\search\exception\search_exception;

class state_helper
{
	protected const STATE_SEARCH_TYPE = 0;
	protected const STATE_ACTION = 1;
	protected const STATE_POST_COUNTER = 2;

	/** @var config */
	protected $config;

	/** @var search_backend_factory */
	protected $search_backend_factory;

	/**
	 * Constructor.
	 *
	 * @param config                 $config
	 * @param search_backend_factory $search_backend_factory
	 */
	public function __construct(config $config, search_backend_factory $search_backend_factory)
	{
		$this->config = $config;
		$this->search_backend_factory = $search_backend_factory;
	}

	/**
	 * Returns if there is an action in progress
	 *
	 * @return bool
	 */
	public function is_action_in_progress(): bool
	{
		return !empty($this->config['search_indexing_state']);
	}

	/**
	 * @return string
	 *
	 * @throws no_action_in_progress_exception If there is no action in progress
	 */
	public function type(): string
	{
		$state = $this->load_state();

		return $state[self::STATE_SEARCH_TYPE];
	}

	/**
	 * @return string
	 *
	 * @throws no_action_in_progress_exception If there is no action in progress
	 */
	public function action(): string
	{
		$state = $this->load_state();

		return $state[self::STATE_ACTION];
	}

	/**
	 * @return int
	 *
	 * @throws no_action_in_progress_exception If there is no action in progress
	 */
	public function counter(): int
	{
		$state = $this->load_state();

		return $state[self::STATE_POST_COUNTER];
	}

	/**
	 * @param string $search_type
	 * @param string $action
	 *
	 * @throws action_in_progress_exception  If there is an action in progress
	 * @throws no_search_backend_found_exception If search backend don't exist
	 */
	public function init(string $search_type, string $action): void
	{
		// Is not possible to start a new process when there is one already running
		if ($this->is_action_in_progress())
		{
			throw new action_in_progress_exception();
		}

		// Make sure the search type exist (if not, the next line launch an exception)
		$this->search_backend_factory->get($search_type);

		// Make sure the action is correct (just in case)
		if (!in_array($action, ['create', 'delete']))
		{
			throw new search_exception('Invalid action');
		}

		$state = [
			self::STATE_SEARCH_TYPE => $search_type,
			self::STATE_ACTION => $action,
			self::STATE_POST_COUNTER => 0
		];

		$this->save_state($state);
	}

	/**
	 * @param int $counter
	 *
	 * @throws no_action_in_progress_exception If there is no action in progress
	 */
	public function update_counter(int $counter): void
	{
		$state = $this->load_state();

		$state[self::STATE_POST_COUNTER] = $counter;

		$this->save_state($state);
	}

	/**
	 * Clear the state
	 */
	public function clear_state(): void
	{
		$this->save_state([]);
	}

	/**
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

		return explode(',', $this->config['search_indexing_state']);
	}

	/**
	 * @param array $state
	 */
	private function save_state(array $state = []): void
	{
		ksort($state);

		$this->config->set('search_indexing_state', implode(',', $state), true);
	}
}
