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

namespace phpbb\db\middleware\sqlsrv;

use Doctrine\DBAL\Driver;

/**
 * Microsoft SQL server Doctrine middleware.
 * Makes use of phpBB's SQL Server specific platform.
 */
class phpbb_sqlsrv_middleware implements Driver\Middleware
{
	public function wrap(Driver $driver): Driver
	{
		return new phpbb_sqlsrv_driver($driver);
	}
}
