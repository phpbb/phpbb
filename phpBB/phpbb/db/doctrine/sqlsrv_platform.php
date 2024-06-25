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

use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\TableDiff;

/**
 * SQLServer specific schema restrictions for BC.
 */
class sqlsrv_platform extends SQLServerPlatform
{
	/**
	 * {@inheritDoc}
	 *
	 * Renames the default constraints to use the classic phpBB's names
	 */
	public function getDefaultConstraintDeclarationSQL($table, array $column)
	{
		$sql = parent::getDefaultConstraintDeclarationSQL($table, $column);

		return str_replace(
			[
				$this->generate_doctrine_identifier_name($table),
				$this->generate_doctrine_identifier_name($column['name']),
			], [
				$table,
				$column['name'] . '_1',
			],
			$sql);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Renames the default constraints to use the classic phpBB's names
	 */
	public function getAlterTableSQL(TableDiff $diff)
	{
		$sql = [];

		// When dropping a column, if it has a default we need to drop the default constraint first
		foreach ($diff->removedColumns as $column)
		{
			if (!$column->getAutoincrement())
			{
				$sql[] = $this->getDropConstraintSQL($this->generate_doctrine_default_constraint_name($diff->name, $column->getQuotedName($this)), $diff->name);
			}
		}

		// When dropping a primary key, the constraint needs to be dropped
		foreach ($diff->removedIndexes as $key => $index)
		{
			if ($index->isPrimary())
			{
				unset($diff->removedIndexes[$key]);
				$sql[] = $this->getDropConstraintSQL($index->getQuotedName($this), $diff->name);
			}
		}

		$sql = array_merge($sql, parent::getAlterTableSQL($diff));

		$doctrine_names = [];
		$phpbb_names = [];

		// OLD Table name
		$doctrine_names[] = $this->generate_doctrine_identifier_name($diff->name);
		$phpbb_names[] = $diff->name;

		// NEW Table name if relevant
		if ($diff->getNewName() !== false)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($diff->getNewName()->getName());
			$phpbb_names[] = $diff->getNewName()->getName();
		}

		foreach ($diff->addedColumns as $column)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($column->getQuotedName($this));
			$phpbb_names[] = $column->getQuotedName($this) . '_1';
		}

		foreach ($diff->removedColumns as $column)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($column->getQuotedName($this));
			$phpbb_names[] = $column->getQuotedName($this) . '_1';
		}

		foreach ($diff->renamedColumns as $column)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($column->getQuotedName($this));
			$phpbb_names[] = $column->getQuotedName($this) . '_1';
		}

		foreach ($diff->changedColumns as $column)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($column->column->getQuotedName($this));
			$phpbb_names[] = $column->column->getQuotedName($this) . '_1';

			if ($column->oldColumnName != $column->column->getQuotedName($this))
			{
				$doctrine_names[] = $this->generate_doctrine_identifier_name($column->oldColumnName);
				$phpbb_names[] = $column->oldColumnName . '_1';
			}
		}

		return str_replace($doctrine_names, $phpbb_names, $sql);
	}

	/**
	 * Returns a hash value for a given identifier.
	 *
	 * @param string $identifier Identifier to generate a hash value for.
	 *
	 * @return string
	 */
	private function generate_doctrine_identifier_name(string $identifier): string
	{
		// Always generate name for unquoted identifiers to ensure consistency.
		$identifier = new Identifier($identifier);

		return strtoupper(dechex(crc32($identifier->getName())));
	}

	/**
	 * Returns a unique default constraint name for a table and column.
	 *
	 * @param string $table  Name of the table to generate the unique default constraint name for.
	 * @param string $column Name of the column in the table to generate the unique default constraint name for.
	 *
	 * @return string
	 */
	private function generate_doctrine_default_constraint_name(string $table, string $column): string
	{
		return 'DF_' . $this->generate_doctrine_identifier_name($table) . '_' . $this->generate_doctrine_identifier_name($column);
	}
}
