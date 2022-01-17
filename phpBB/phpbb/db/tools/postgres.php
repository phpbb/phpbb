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

namespace phpbb\db\tools;

/**
 * Database Tools for handling cross-db actions such as altering columns, etc.
 * Currently not supported is returning SQL for creating tables.
 *
 * @deprecated 4.0.0-a1
 */
class postgres extends doctrine
{
}
