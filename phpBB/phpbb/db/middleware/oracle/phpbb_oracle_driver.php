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

namespace phpbb\db\middleware\oracle;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Oracle Doctrine driver middleware.
 * Makes use of phpBB's Oracle specific platform.
 */
class phpbb_oracle_driver extends AbstractDriverMiddleware
{
	/**
	 * {@inheritDoc}
	 */
	public function getSchemaManager(DoctrineConnection $conn, AbstractPlatform $platform)
	{
		return new phpbb_oracle_schema_manager($conn, $platform);
	}

	/**
	 * {@inheritDoc}
	 */
	public function createDatabasePlatformForVersion($version)
	{
		return new phpbb_oracle_platform();
	}
}
