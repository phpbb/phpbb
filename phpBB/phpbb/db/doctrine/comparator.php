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
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Schema\Table;

class comparator extends \Doctrine\DBAL\Schema\Comparator
{
	/**
	 * @var AbstractPlatform
	 */
	private $platform;

	public function __construct(AbstractPlatform $platform)
	{
		parent::__construct($platform);

		$this->platform = $platform;
	}

	/**
	 * {@inerhitDoc}
	 */
	public function compareTables(Table $oldTable, Table $newTable): TableDiff
	{
		$diff = parent::compareTables($oldTable, $newTable);
		$added_indexes = $diff->getAddedIndexes();
		$dropped_indexes = $diff->getDroppedIndexes();
		$added_index_names = array_map([$this, 'get_index_name'], $added_indexes);
		$dropped_index_names = array_map([$this, 'get_index_name'], $dropped_indexes);

		// When the type of a column changes, re-create the associated indices
		foreach ($diff->getChangedColumns() as $column_name => $changed_column)
		{
			if (!$changed_column->hasTypeChanged())
			{
				continue;
			}

			foreach ($newTable->getIndexes() as $index_name => $index)
			{
				if (in_array($this->get_index_name($index), $added_index_names, true)
					|| in_array($this->get_index_name($index), $dropped_index_names, true))
				{
					continue;
				}

				$index_columns = array_map('strtolower', $index->getUnquotedColumns());
				if (!in_array($column_name, $index_columns, true))
				{
					continue;
				}

				$old_index = $oldTable->getIndex($index_name);
				$dropped_indexes[] = $old_index;
				$added_indexes[] = $index;
				$dropped_index_names[] = $this->get_index_name($old_index);
				$added_index_names[] = $this->get_index_name($index);
			}
		}

		return new TableDiff(
			$diff->getOldTable(),
			addedColumns: $diff->getAddedColumns(),
			changedColumns: $diff->getChangedColumns(),
			droppedColumns: $diff->getDroppedColumns(),
			addedIndexes: $added_indexes,
			droppedIndexes: $dropped_indexes,
			renamedIndexes: $diff->getRenamedIndexes(),
			addedForeignKeys: $diff->getAddedForeignKeys(),
			droppedForeignKeys: $diff->getDroppedForeignKeys(),
		);
	}

	private function get_index_name(Index $index): string
	{
		return strtolower($index->getQuotedName($this->platform));
	}
}
