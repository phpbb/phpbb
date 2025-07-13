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

namespace phpbb\db\middleware\postgresql;

use Doctrine\DBAL\Driver;

/**
 * PostgreSQL Doctrine middleware.
 * Makes use of phpBB's PostgreSQL specific platform.
 */
class phpbb_postgresql_middleware implements Driver\Middleware
{
	public function wrap(Driver $driver): Driver
	{
		return new phpbb_postgresql_driver($driver);
	}
}
