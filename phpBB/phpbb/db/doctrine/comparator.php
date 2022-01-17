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

use Doctrine\DBAL\Schema\Table;

class comparator extends \Doctrine\DBAL\Schema\Comparator
{
	/**
	 * {@inerhitDoc}
	 */
	public function diffTable(Table $fromTable, Table $toTable)
	{
		$diff = parent::diffTable($fromTable, $toTable);

		if ($diff === false)
		{
			return false;
		}

		if (!is_array($diff->changedColumns))
		{
			return $diff;
		}

		// When the type of a column changes, re-create the associated indices
		foreach ($diff->changedColumns as $columnName => $changedColumn)
		{
			if (!$changedColumn->hasChanged('type'))
			{
				continue;
			}

			foreach ($toTable->getIndexes() as $index_name => $index)
			{
				if (array_key_exists($index_name, $diff->addedIndexes) || array_key_exists($index_name, $diff->changedIndexes))
				{
					continue;
				}

				$index_columns = array_map('strtolower', $index->getUnquotedColumns());
				if (!in_array($columnName, $index_columns, true))
				{
					continue;
				}

				$diff->changedIndexes[$index_name] = $index;
			}
		}

		return $diff;
	}
}
