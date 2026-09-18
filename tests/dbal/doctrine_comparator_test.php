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

use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Table;
use phpbb\db\doctrine\comparator;

class phpbb_dbal_doctrine_comparator_test extends phpbb_test_case
{
	public function test_compare_tables_recreates_an_index_for_a_type_change()
	{
		$old_table = new Table('test_table');
		$old_table->addColumn('value', 'integer');
		$old_table->addIndex(['value'], 'value_index');

		$new_table = new Table('test_table');
		$new_table->addColumn('value', 'string');
		$new_table->addIndex(['value'], 'value_index');

		$comparator = new comparator(new SqlitePlatform());
		$diff = $comparator->compareTables($old_table, $new_table);

		$this->assertCount(1, $diff->getModifiedColumns());
		$this->assertCount(1, $diff->getAddedIndexes());
		$this->assertCount(1, $diff->getDroppedIndexes());
		$this->assertSame($old_table, $diff->getOldTable());
	}
}
