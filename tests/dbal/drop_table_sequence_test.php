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

class phpbb_dbal_drop_table_sequence_test extends phpbb_test_case
{
	public static function identifier_cases()
	{
		return array(array('phpbb_fixture', 'phpbb_fixture_seq', true), array('PHPBB_FIXTURE', 'PHPBB_FIXTURE_SEQ', false));
	}

	/** @dataProvider identifier_cases */
	public function test_drop_table_removes_only_its_identity_sequence($table_name, $sequence_name, $autoincrement)
	{
		$schema = new \Doctrine\DBAL\Schema\Schema();
		$table = $schema->createTable($table_name);
		$table->addColumn('id', 'integer', array('autoincrement' => $autoincrement));
		$table->setPrimaryKey(array('id'));
		$schema->createSequence($sequence_name);
		$schema->createSequence('unrelated_seq');
		$connection = $this->createMock(\Doctrine\DBAL\Connection::class);
		$connection->method('getDatabasePlatform')->willReturn(new \Doctrine\DBAL\Platforms\OraclePlatform());
		$tools = new \phpbb\db\tools\doctrine($connection);
		$method = new \ReflectionMethod($tools, 'schema_drop_table');
		$method->setAccessible(true);
		$method->invoke($tools, $schema, $table_name, true);
		$this->assertFalse($schema->hasTable($table_name));
		$this->assertFalse($schema->hasSequence($sequence_name));
		$this->assertTrue($schema->hasSequence('unrelated_seq'));
	}
}
