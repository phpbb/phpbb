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
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

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

	/**
	 * {@inheritDoc}
	 */
	public function getCreateIndexSQL(Index $index, $table): string
	{
		if ($table instanceof Table)
		{
			$table_name = $table->getName();
		}
		else
		{
			$table_name = $table;
		}

		$index_name = $index->getName();
		if (strpos($index->getName(), $table_name) !== 0)
		{
			$index_name = $table_name . '_' . $index->getName();
		}

		$index = new Index(
			$this->check_index_name_length($table_name, $index_name),
			$index->getColumns(),
			$index->isUnique(),
			$index->isPrimary(),
			$index->getFlags(),
			$index->getOptions()
		);

		return parent::getCreateIndexSQL($index, $table);
	}

	/**
	 * Check whether the index name is too long
	 *
	 * @param string	$table_name
	 * @param string	$index_name
	 * @param bool		$throw_error
	 * @return string	The index name, shortened if too long
	 */
	protected function check_index_name_length(string $table_name, string $index_name, bool $throw_error = true): string
	{
		$max_index_name_length = $this->getMaxIdentifierLength();
		if (strlen($index_name) > $max_index_name_length)
		{
			// Try removing the table prefix if it's at the beginning
			$table_prefix = substr(CONFIG_TABLE, 0, -6); // strlen(config)
			if (strpos($index_name, $table_prefix) === 0)
			{
				$index_name = substr($index_name, strlen($table_prefix));
				return $this->check_index_name_length($table_name, $index_name, $throw_error);
			}

			// Try removing the remaining suffix part of table name then
			$table_suffix = substr($table_name, strlen($table_prefix));
			if (strpos($index_name, $table_suffix) === 0)
			{
				// Remove the suffix and underscore separator between table_name and index_name
				$index_name = substr($index_name, strlen($table_suffix) + 1);
				return $this->check_index_name_length($table_name, $index_name, $throw_error);
			}

			if ($throw_error)
			{
				throw new \InvalidArgumentException(
					"Index name '$index_name' on table '$table_name' is too long. The maximum is $max_index_name_length characters."
				);
			}
		}

		return $index_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIdentitySequenceName($tableName, $columnName): string
	{
		return $tableName . '_SEQ';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCreateAutoincrementSql($name, $table, $start = 1)
	{
		$sql = parent::getCreateAutoincrementSql($name, $table, $start);

		return str_replace(
			$this->get_doctrine_autoincrement_identifier_name($this->doctrine_normalize_identifier($table)),
			'T_'.$table,
			$sql
		);
	}

	/**
	 * @see OraclePlatform::normalizeIdentifier()
	 */
	private function doctrine_normalize_identifier($name): Identifier
	{
		$identifier = new Identifier($name);

		return $identifier->isQuoted() ? $identifier : new Identifier(strtoupper($name));
	}

	/**
	 * @see OraclePlatform::getAutoincrementIdentifierName()
	 */
	private function get_doctrine_autoincrement_identifier_name(Identifier $table): string
	{
		$identifierName = $this->add_doctrine_suffix($table->getName(), '_AI_PK');

		return $table->isQuoted()
			? $this->quoteSingleIdentifier($identifierName)
			: $identifierName;
	}

	/**
	 * @see OraclePlatform::addSuffix()
	 */
	private function add_doctrine_suffix(string $identifier, string $suffix): string
	{
		$maxPossibleLengthWithoutSuffix = $this->getMaxIdentifierLength() - strlen($suffix);
		if (strlen($identifier) > $maxPossibleLengthWithoutSuffix)
		{
			$identifier = substr($identifier, 0, $maxPossibleLengthWithoutSuffix);
		}

		return $identifier . $suffix;
	}
}
