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

namespace phpbb\db\doctrine\oci8;

use Doctrine\DBAL\Connection as DoctrineConnection;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Driver as DoctrineDriver;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8Driver;

class driver implements DoctrineDriver
{
	/**
	 * @var DoctrineDriver
	 */
	private $wrapped;

	public function __construct()
	{
		$this->wrapped = new OCI8Driver();
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect(array $params)
	{
		return new connection($this->wrapped->connect($params));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDatabasePlatform()
	{
		return $this->wrapped->getDatabasePlatform();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSchemaManager(DoctrineConnection $conn, AbstractPlatform $platform)
	{
		return new schema_manager($conn, $platform);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getExceptionConverter(): ExceptionConverter
	{
		return $this->wrapped->getExceptionConverter();
	}
}
