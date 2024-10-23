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

namespace phpbb\captcha\plugins;

/**
 * Confirmation types for CAPTCHA plugins
 */
enum confirm_type: int {
	case UNDEFINED = 0;
	case REGISTRATION = 1;
	case LOGIN = 2;
	case POST = 3;
	case REPORT = 4;
}
