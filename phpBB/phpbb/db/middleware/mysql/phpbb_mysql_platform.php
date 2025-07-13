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

namespace phpbb\db\middleware\mysql;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\TableDiff;

/**
 * MySQL specific schema handling.
 *
 * While adding auto_increment column to MySQL, it must be indexed.
 * If it's indexed as primary key, it should be declared as NOT NULL
 * because MySQL primary key columns cannot be NULL.
 */
class phpbb_mysql_platform extends AbstractMySQLPlatform
{
	/**
	 * {@inheritDoc}
	 */
	public function getAlterTableSQL(TableDiff $diff)
	{
		$sql = parent::getAlterTableSQL($diff);
		$table = $diff->getOldTable();
		$columns = $diff->getAddedColumns();

		foreach ($columns as $column)
		{
			$column_name = $column->getName();
			if (!empty($column->getAutoincrement()) && $table)
			{
				foreach ($sql as $i => $query)
				{
					if (stripos($query, "add $column_name"))
					{
						if (!$table->getPrimaryKey())
						{
							$sql[$i] = str_replace(' DEFAULT NULL', '', $sql[$i]);
							$sql[$i] .= ' PRIMARY KEY';
						}
						else
						{
							$sql[$i] .= ", ADD KEY ($column_name)";
						}
					}
				}
			}
		}

		return $sql;
	}
}
