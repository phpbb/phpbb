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

use Doctrine\DBAL\Driver\Result as DriverResult;

class result implements DriverResult
{
	/**
	 * @var DriverResult
	 */
	private $wrapped;

	/**
	 * @param DriverResult $wrapped
	 */
	public function __construct(DriverResult $wrapped)
	{
		$this->wrapped = $wrapped;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchNumeric()
	{
		return $this->wrapped->fetchNumeric();
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchAssociative()
	{
		return array_change_key_case($this->wrapped->fetchAssociative(), CASE_LOWER);
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchOne()
	{
		return $this->wrapped->fetchOne();
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchAllNumeric(): array
	{
		return $this->wrapped->fetchAllNumeric();
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchAllAssociative(): array
	{
		$rows = [];
		foreach ($this->wrapped->fetchAllAssociative() as $row)
		{
			$rows[] = array_change_key_case($row, CASE_LOWER);
		}
		return $rows;
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetchFirstColumn(): array
	{
		return $this->wrapped->fetchFirstColumn();
	}

	/**
	 * {@inheritDoc}
	 */
	public function rowCount(): int
	{
		return $this->wrapped->rowCount();
	}

	/**
	 * {@inheritDoc}
	 */
	public function columnCount(): int
	{
		return $this->wrapped->columnCount();
	}

	/**
	 * {@inheritDoc}
	 */
	public function free(): void
	{
		$this->wrapped->free();
	}
}
