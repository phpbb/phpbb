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

namespace phpbb\db\middleware\sqlsrv;

use Doctrine\DBAL\Platforms\SQLServerPlatform;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\TableDiff;

/**
 * SQLServer specific schema restrictions for BC.
 */
class platform extends SQLServerPlatform
{
	/**
	 * {@inheritDoc}
	 *
	 * Renames the default constraints to use the classic phpBB's names
	 */
	protected function getDefaultConstraintDeclarationSQL(array $column): string
	{
		$sql = parent::getDefaultConstraintDeclarationSQL($column);

		return str_replace(
			[
				$this->generate_doctrine_identifier_name($column['name']),
				$this->generate_doctrine_identifier_name($column['name']),
			], [
				$column['name'] . '_1',
				$column['name'] . '_1',
			],
			$sql);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Renames the default constraints to use the classic phpBB's names
	 */
	public function getAlterTableSQL(TableDiff $diff): array
	{
		$sql = parent::getAlterTableSQL($diff);

		$doctrine_names = [];
		$phpbb_names = [];
		$table_name = $diff->getOldTable()->getName();

		// OLD Table name
		$doctrine_names[] = $this->generate_doctrine_identifier_name($table_name);
		$phpbb_names[] = $table_name;

		foreach ($diff->getAddedColumns() as $column)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($column->getQuotedName($this));
			$phpbb_names[] = $column->getQuotedName($this) . '_1';
		}

		foreach ($diff->getDroppedColumns() as $column)
		{
			$doctrine_names[] = $this->generate_doctrine_identifier_name($column->getQuotedName($this));
			$phpbb_names[] = $column->getQuotedName($this) . '_1';
		}

		foreach ($diff->getChangedColumns() as $column)
		{
			$new_column = $column->getNewColumn();
			$old_column = $column->getOldColumn();

			$doctrine_names[] = $this->generate_doctrine_identifier_name($new_column->getQuotedName($this));
			$phpbb_names[] = $new_column->getQuotedName($this) . '_1';

			if ($old_column->getQuotedName($this) !== $new_column->getQuotedName($this))
			{
				$doctrine_names[] = $this->generate_doctrine_identifier_name($old_column->getQuotedName($this));
				$phpbb_names[] = $old_column->getQuotedName($this) . '_1';
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
}
