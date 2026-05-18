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
use phpbb\user;

/**
* Tidy guest (anonymous) sessions cron task.
*
* Runs independently of tidy_sessions on a tighter schedule.
*/
class tidy_guest_sessions extends base
{
	protected $config;
	protected $user;

	/**
	* Constructor.
	*
	* @param config	$config	The config
	* @param user			$user	The user
	*/
	public function __construct(config $config, user $user)
	{
		$this->config = $config;
		$this->user = $user;
	}

	/**
	* {@inheritdoc}
	*/
	public function run()
	{
		$this->user->session_guest_gc();
	}

	/**
	 * {@inheritdoc}
	 */
	public function should_run(): bool
	{
		return ((int) $this->config['session_guest_last_gc'] + (int) $this->config['session_guest_gc']) <= time();
	}
}

