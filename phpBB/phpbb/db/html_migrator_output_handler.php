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

class html_migrator_output_handler extends migrator_output_handler
{
	/**
	 * User object.
	 *
	 * @var user
	 */
	private $user;

	/**
	 * Constructor
	 *
	 * @param user $user	User object
	 */
	public function __construct(user $user)
	{
		$this->user = $user;
	}

	/**
	 * Write output using the configured closure.
	 *
	 * @param string|array $message The message to write or an array containing the language key and all of its parameters.
	 * @param int $verbosity The verbosity of the message.
	 */
	public function write($message, $verbosity)
	{
		if ($verbosity <= migrator_output_handler::VERBOSITY_NORMAL)
		{
			$final_message = call_user_func_array(array($this->user, 'lang'), $message);
			echo $final_message . "<br />\n";
		}
	}
}
