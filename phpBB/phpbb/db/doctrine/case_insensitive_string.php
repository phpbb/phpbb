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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Case-insensitive string type (only supported by Postgres).
 */
class case_insensitive_string extends Type
{
	public const CASE_INSENSITIVE_STRING = 'string_ci';

	/**
	 * {@inheritdoc}
	 */
	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		if ($platform instanceof postgresql_platform)
		{
			return 'varchar_ci';
		}

		// This relies on our own oracle_platform implementation, and the fact that
		// we used 3 times larger capacity for strings on oracle for unicode strings
		// as on other platforms. This is not the case with varchar_ci, which uses
		// the same length as other platforms.
		if ($platform instanceof oracle_platform)
		{
			return $platform->getAsciiStringTypeDeclarationSQL($column);
		}

		return $platform->getVarcharTypeDeclarationSQL($column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return self::CASE_INSENSITIVE_STRING;
	}
}
