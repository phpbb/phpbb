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

namespace phpbb\cron\task\core;

use phpbb\config\config;
use phpbb\cron\task\base;
use phpbb\di\exception\di_exception;
use phpbb\search\backend\search_backend_interface;
use phpbb\search\search_backend_factory;

/**
* Tidy search cron task.
*
* Will only run when the currently selected search backend supports tidying.
*/
class tidy_search extends base
{
	/**
	* Config object
	* @var config
	*/
	protected $config;

	/**
	* Search backend factory
	* @var search_backend_factory
	*/
	protected $search_backend_factory;

	/**
	 * Reference to active search backend to avoid calling the factory multiple times
	 * @var search_backend_interface
	 */
	protected $active_search;

	/**
	 * Constructor.
	 *
	 * @param config $config The config object
	 * @param search_backend_factory $search_backend_factory
	 */
	public function __construct(config $config, search_backend_factory $search_backend_factory)
	{
		$this->config = $config;
		$this->search_backend_factory = $search_backend_factory;
	}

	/**
	* Runs this cron task.
	*
	* @return void
	*/
	public function run()
	{
		if ($this->active_search === null)
		{
			$this->active_search = $this->search_backend_factory->get_active();
		}

		$this->active_search->tidy();
	}

	/**
	* Returns whether this cron task can run, given current board configuration.
	*
	* Search cron task is runnable in all normal use. It may not be
	* runnable if the search backend implementation selected in board
	* configuration does not exist.
	*
	* @return bool
	*/
	public function is_runnable()
	{
		try
		{
			if ($this->active_search === null)
			{
				$this->active_search = $this->search_backend_factory->get_active();
			}
		}
		catch (di_exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	* Returns whether this cron task should run now, because enough time
	* has passed since it was last run.
	*
	* The interval between search tidying is specified in board
	* configuration.
	*
	* @return bool
	*/
	public function should_run()
	{
		return $this->config['search_last_gc'] < time() - $this->config['search_gc'];
	}
}
