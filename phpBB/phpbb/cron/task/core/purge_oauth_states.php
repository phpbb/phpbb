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
use phpbb\db\driver\driver_interface;

/**
* Purge oauth_states cron task.
*/
class purge_oauth_states extends base
{
	/** @var config */
	protected $config;

	/** @var driver_interface */
	protected $db;

	/** @var string */
	protected $oauth_states_table;

	/**
	* Constructor
	*
	* @param config $config The config
	* @param driver_interface $db The database connection
	* @param string $oauth_states_table The oauth_states table name
	*/
	public function __construct(config $config, driver_interface $db, string $oauth_states_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->oauth_states_table = $oauth_states_table;
	}

	/**
	* {@inheritdoc}
	*/
	public function run()
	{
		$sql = 'DELETE FROM ' . $this->oauth_states_table . '
			WHERE state_time < ' . (time() - (int) $this->config['session_gc']);
		$this->db->sql_query($sql);

		$this->config->set('oauth_states_last_gc', time(), true);
	}

	/**
	* {@inheritdoc}
	*/
	public function is_runnable(): bool
	{
		return (bool) $this->config['session_gc'];
	}

	/**
	* {@inheritdoc}
	*/
	public function should_run(): bool
	{
		return isset($this->config['oauth_states_last_gc']) && $this->config['oauth_states_last_gc'] < time() - $this->config['session_gc'];
	}
}
