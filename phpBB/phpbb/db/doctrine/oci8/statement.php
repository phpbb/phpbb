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
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use Doctrine\DBAL\ParameterType;

class statement implements DriverStatement
{
	/**
	 * @var DriverStatement
	 */
	private $wrapped;

	/**
	 * @param DriverStatement $wrapped
	 */
	public function __construct(DriverStatement $wrapped)
	{
		$this->wrapped = $wrapped;
	}

	/**
	 * {@inheritDoc}
	 */
	public function bindValue($param, $value, $type = ParameterType::STRING): bool
	{
		return $this->wrapped->bindValue($param, $value, $type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): bool
	{
		return $this->wrapped->bindParam($param, $variable, $type, $length);
	}

	/**
	 * {@inheritDoc}
	 */
	public function execute($params = null): DriverResult
	{
		return new result($this->wrapped->execute($params));
	}
}
