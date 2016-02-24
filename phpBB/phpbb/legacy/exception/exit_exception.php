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

namespace phpbb\legacy\exception;

/**
 * Class exit_without_response_exception
 *
 * Special exception to exit from anywhere and generate the response from the current buffer
 */
class exit_exception extends \Exception
{
}
