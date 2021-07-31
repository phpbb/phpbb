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

namespace phpbb\db\doctrine;

use Doctrine\DBAL\Platforms\OraclePlatform;

/**
 * Oracle specific schema restrictions for BC.
 */
class oracle_platform extends OraclePlatform
{
	/**
	 * {@inheritDoc}
	 */
	public function getVarcharTypeDeclarationSQL(array $column): string
	{
		if (array_key_exists('length', $column) && is_int($column['length']))
		{
			$column['length'] *= 3;
		}

		return parent::getVarcharTypeDeclarationSQL($column);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAsciiStringTypeDeclarationSQL(array $column): string
	{
		return parent::getVarcharTypeDeclarationSQL($column);
	}
}
