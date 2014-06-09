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

class phpbb_cron_task_core_second_dummy_task extends \phpbb\cron\task\base
{
	static public $was_run = 0;

	public function get_name()
	{
		return get_class($this);
	}

	public function run()
	{
		self::$was_run++;
	}

	public function should_run()
	{
		return true;
	}
}
