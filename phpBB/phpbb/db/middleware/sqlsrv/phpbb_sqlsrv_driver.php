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

use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * Microsoft SQL server Doctrine driver middleware.
 * Makes use of phpBB's SQL Server specific platform.
 */
class phpbb_sqlsrv_driver extends AbstractDriverMiddleware
{
	/**
	 * {@inheritDoc}
	 */
	public function createDatabasePlatformForVersion($version)
	{
		return new phpbb_sqlsrv_platform();
	}
}
