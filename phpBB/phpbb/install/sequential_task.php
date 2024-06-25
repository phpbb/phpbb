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

namespace phpbb\install;

use phpbb\install\exception\resource_limit_reached_exception;
use phpbb\install\helper\config;

/**
 * Trait to execute tasks in steps with timeout management.
 */
trait sequential_task
{
	/**
	 * Callback function to execute a unit of work.
	 *
	 * @param mixed $key	The array key.
	 * @param mixed $value	The array value.
	 */
	abstract protected function execute_step($key, $value) : void;

	/**
	 * Execute the tasks with timeout management.
	 *
	 * @param config		$config			Installer config.
	 * @param array			$data			Array of elements to iterate over.
	 * @param string|null	$counter_name	The name of the counter or null.
	 *
	 * @throws resource_limit_reached_exception When resources are exhausted.
	 */
	protected function execute(config $config, array $data, ?string $counter_name = null) : void
	{
		if ($counter_name === null)
		{
			$counter_name = 'step_counter_' . get_class($this);
		}

		$counter = $config->get($counter_name, 0);
		$total = count($data);
		$data = array_slice($data, $counter);
		foreach ($data as $key => $value)
		{
			if ($config->get_time_remaining() <= 0 || $config->get_memory_remaining() <= 0)
			{
				break;
			}

			$this->execute_step($key, $value);
			++$counter;
		}

		$config->set($counter_name, $counter);

		if ($counter < $total)
		{
			throw new resource_limit_reached_exception();
		}
	}
}
