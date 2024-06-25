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
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Sequence;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\Type;

/**
 * PostgreSQL specific schema restrictions for BC.
 *
 * Doctrine is using SERIAL which auto creates the sequences with
 * a name different from the one our driver is using. So in order
 * to stay compatible with the existing DB we have to change its
 * naming and not ours.
 */
class postgresql_platform extends PostgreSQL94Platform
{
	/**
	 * {@inheritdoc}
	 */
	public function getIdentitySequenceName($tableName, $columnName): string
	{
		return $tableName . '_seq';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIntegerTypeDeclarationSQL(array $column): string
	{
		return 'INT';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBigIntTypeDeclarationSQL(array $column): string
	{
		return 'BIGINT';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getSmallIntTypeDeclarationSQL(array $column): string
	{
		return 'SMALLINT';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefaultValueDeclarationSQL($column): string
	{
		if ($this->isSerialColumn($column))
		{
			return ' DEFAULT {{placeholder_sequence}}';
		}

		return AbstractPlatform::getDefaultValueDeclarationSQL($column);
	}

	/**
	 * {@inheritDoc}
	 */
	public function supportsIdentityColumns(): bool
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _getCreateTableSQL($name, array $columns, array $options = []): array
	{
		$sql = [];
		$post_sql = [];
		foreach ($columns as $column_name => $column)
		{
			if (!empty($column['autoincrement']))
			{
				$sequence = new Sequence($this->getIdentitySequenceName($name, $column_name));
				$sql[] = $this->getCreateSequenceSQL($sequence);
				$post_sql[] = 'ALTER SEQUENCE '.$sequence->getName().' OWNED BY '.$name.'.'.$column_name;
			}
		}
		$sql = array_merge($sql, parent::_getCreateTableSQL($name, $columns, $options), $post_sql);

		foreach ($sql as $i => $query)
		{
			$sql[$i] = str_replace('{{placeholder_sequence}}', "nextval('{$name}_seq')", $query);
		}

		return $sql;
	}

	/**
	 * Return if column is a "serial" column, i.e. type supporting auto-increment
	 *
	 * @param array $column Column data
	 * @return bool
	 */
	private function isSerialColumn(array $column): bool
	{
		return isset($column['type'], $column['autoincrement'])
			&& $column['autoincrement'] === true
			&& $this->isNumericType($column['type']);
	}

	/**
	 * Return if supplied type is of numeric type
	 *
	 * @param Type $type
	 * @return bool
	 */
	private function isNumericType(Type $type): bool
	{
		return $type instanceof IntegerType || $type instanceof BigIntType || $type instanceof SmallIntType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getListSequencesSQL($database): string
	{
		return "SELECT sequence_name AS relname,
				sequence_schema AS schemaname,
				1 AS min_value,
				1 AS increment_by
			FROM information_schema.sequences
			WHERE sequence_schema NOT LIKE 'pg\_%'
				AND sequence_schema <> 'information_schema'";
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDropIndexSQL($index, $table = null): string
	{
		// If we have a primary or a unique index, we need to drop the constraint
		// instead of the index itself or postgreSQL will reject the query.
		if ($index instanceof Index)
		{
			if ($index->isPrimary())
			{
				if ($table instanceof Table)
				{
					$table = $table->getQuotedName($this);
				}
				else if (!is_string($table))
				{
					throw new \InvalidArgumentException(
						__METHOD__ . '() expects $table parameter to be string or ' . Table::class . '.'
					);
				}

				return 'ALTER TABLE '.$table.' DROP CONSTRAINT '.$index->getQuotedName($this);
			}
		}
		else if (! is_string($index))
		{
			throw new \InvalidArgumentException(
				__METHOD__ . '() expects $index parameter to be string or ' . Index::class . '.'
			);
		}

		return parent::getDropIndexSQL($index, $table);
	}
}
