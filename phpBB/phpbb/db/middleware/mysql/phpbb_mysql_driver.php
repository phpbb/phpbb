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

namespace phpbb\db\middleware\mysql;

use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;

/**
 * MySQL Doctrine driver middleware.
 * Makes use of phpBB's MySQL specific platform.
 */
class phpbb_mysql_driver extends AbstractDriverMiddleware
{
	/**
	 * {@inheritDoc}
	 */
	public function createDatabasePlatformForVersion($version)
	{
		return new phpbb_mysql_platform();
	}
}
