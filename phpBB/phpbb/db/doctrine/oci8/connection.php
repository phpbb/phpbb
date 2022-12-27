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

use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Driver\Result as DriverResult;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\ParameterType;

class connection implements DriverConnection
{
	/**
	 * @var DriverConnection
	 */
	private $wrapped;

	/**
	 * @param DriverConnection $wrapped
	 */
	public function __construct(DriverConnection $wrapped)
	{
		$this->wrapped = $wrapped;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare(string $sql): DriverStatement
	{
		return new statement($this->wrapped->prepare($sql));
	}

	/**
	 * {@inheritDoc}
	 */
	public function query(string $sql): DriverResult
	{
		return new result($this->wrapped->query($sql));
	}

	/**
	 * {@inheritDoc}
	 */
	public function quote($value, $type = ParameterType::STRING)
	{
		return $this->wrapped->quote($value, $type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function exec(string $sql): int
	{
		return $this->wrapped->exec($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function lastInsertId($name = null)
	{
		return $this->wrapped->lastInsertId($name);
	}

	/**
	 * {@inheritDoc}
	 */
	public function beginTransaction(): bool
	{
		return $this->wrapped->beginTransaction();
	}

	/**
	 * {@inheritDoc}
	 */
	public function commit(): bool
	{
		return $this->wrapped->commit();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rollBack(): bool
	{
		return $this->wrapped->rollBack();
	}
}
