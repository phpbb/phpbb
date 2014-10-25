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

namespace phpbb\db;

use phpbb\user;

class log_wrapper_migrator_output_handler implements migrator_output_handler_interface
{
	/**
	 * User object.
	 *
	 * @var user
	 */
	protected $user;

	/**
	 * A migrator output handler
	 *
	 * @var migrator_output_handler_interface
	 */
	protected $migrator;

	/**
	 * Log file handle
	 * @var resource
	 */
	protected $file_handle = false;

	/**
	 * Constructor
	 *
	 * @param user $user	User object
	 * @param migrator_output_handler_interface $migrator Migrator output handler
	 * @param string $log_file	File to log to
	 */
	public function __construct(user $user, migrator_output_handler_interface $migrator, $log_file)
	{
		$this->user = $user;
		$this->migrator = $migrator;
		$this->file_open($log_file);
	}

	/**
	 * Open file for logging
	 *
	 * @param string $file File to open
	 */
	protected function file_open($file)
	{
		if (phpbb_is_writable(dirname($file)))
		{
			$this->file_handle = fopen($file, 'w');
		}
		else
		{
			throw new \RuntimeException('Unable to write to migrator log file');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function write($message, $verbosity)
	{
		$this->migrator->write($message, $verbosity);

		if ($this->file_handle !== false)
		{
			$translated_message = call_user_func_array(array($this->user, 'lang'), $message) . "\n";

			if ($verbosity <= migrator_output_handler_interface::VERBOSITY_NORMAL)
			{
				$translated_message = '[INFO] ' . $translated_message;
			}
			else
			{
				$translated_message = '[DEBUG] ' . $translated_message;
			}

			fwrite($this->file_handle, $translated_message);
			fflush($this->file_handle);
		}
	}
}
