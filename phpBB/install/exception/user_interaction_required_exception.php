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

/**
 * This exception should be thrown when user interaction is inevitable
 *
 * Note: Please note that the output should already be setup for the user
 * 		 when you use throw this exception
 */
class user_interaction_required_exception extends installer_exception
{

}
