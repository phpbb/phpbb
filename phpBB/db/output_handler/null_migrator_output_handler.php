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

namespace phpbb\db\output_handler;

class null_migrator_output_handler implements migrator_output_handler_interface
{
	/**
	 * {@inheritdoc}
	 */
	public function write($message, $verbosity)
	{
	}
}
