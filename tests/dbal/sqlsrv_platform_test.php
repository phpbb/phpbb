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

use Doctrine\DBAL\Schema\Table;
use phpbb\db\doctrine\comparator;
use phpbb\db\middleware\sqlsrv\platform;

class phpbb_dbal_sqlsrv_platform_test extends phpbb_test_case
{
	public function test_default_constraint_uses_phpbb_name()
	{
		$platform = new platform();

		$this->assertSame(
			" CONSTRAINT DF_test_table_value_1 DEFAULT '0' FOR value",
			$platform->getDefaultConstraintDeclarationSQL('test_table', [
				'name' => 'value',
				'default' => 0,
			])
		);
	}

	public function test_alter_table_sql_handles_modified_columns()
	{
		$old_table = new Table('test_table');
		$old_table->addColumn('value', 'integer');

		$new_table = new Table('test_table');
		$new_table->addColumn('value', 'string');

		$platform = new platform();
		$comparator = new comparator($platform);
		$diff = $comparator->compareTables($old_table, $new_table);

		$this->assertNotEmpty($platform->getAlterTableSQL($diff));
	}
}
