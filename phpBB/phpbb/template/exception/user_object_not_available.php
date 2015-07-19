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

namespace phpbb\template\exception;

/**
 * This exception is thrown when the user object was not set but it is required by the called method
 */
class user_object_not_available extends \phpbb\exception\runtime_exception
{

}
