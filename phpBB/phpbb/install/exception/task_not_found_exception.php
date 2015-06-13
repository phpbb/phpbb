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

namespace phpbb\install\exception;

class task_not_found_exception extends installer_exception
{
	/**
	 * @var string
	 */
	private $task_service_name;

	/**
	 * Constructor
	 *
	 * @param string	$task_service_name	The name of the missing installer module
	 */
	public function __construct($task_service_name)
	{
		$this->task_service_name = $task_service_name;
	}

	/**
	 * Returns the missing installer task's service name
	 *
	 * @return string
	 */
	public function get_task_service_name()
	{
		return $this->task_service_name;
	}
}
