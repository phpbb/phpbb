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

class phpbb_dbal_doctrine_comparator_test extends phpbb_test_case
{
	public static function platforms(): array
	{
		return [
			'MySQL'			=> [new \Doctrine\DBAL\Platforms\MySQLPlatform()],
			'PostgreSQL'	=> [new \Doctrine\DBAL\Platforms\PostgreSQLPlatform()],
			'SQLite'		=> [new \Doctrine\DBAL\Platforms\SQLitePlatform()],
		];
	}

	/**
	 * An index dropped and added again with the same name but other columns is a
	 * "modified" index to Doctrine; the rebuilt diff must still change it.
	 *
	 * @dataProvider platforms
	 */
	public function test_modified_index_is_kept(\Doctrine\DBAL\Platforms\AbstractPlatform $platform): void
	{
		$old = new \Doctrine\DBAL\Schema\Table('articles');
		foreach (['article_id', 'word_id', 'title_match', 'descr_match'] as $column)
		{
			$old->addColumn($column, 'integer');
		}
		$old->addUniqueIndex(['article_id', 'word_id', 'title_match'], 'article_match');

		$new = clone $old;
		$new->dropIndex('article_match');
		$new->addUniqueIndex(['article_id', 'word_id', 'title_match', 'descr_match'], 'article_match');

		$comparator = new \phpbb\db\doctrine\comparator($platform);
		$queries = $platform->getAlterTableSQL($comparator->compareTables($old, $new));

		$this->assertNotEmpty($queries);
		$this->assertStringContainsString('descr_match', implode(' ', $queries));
	}

	/**
	 * A migration that drops an index by its unprefixed key name drops the index
	 * phpBB generated for it, even when an unprefixed index of that name exists too.
	 */
	public function test_legacy_key_name_drops_the_generated_index(): void
	{
		$schema = new \Doctrine\DBAL\Schema\Schema();
		$table = $schema->createTable('phpbb_kb_src_wrdmtch');
		foreach (['article_id', 'word_id', 'title_match', 'descr_match'] as $column)
		{
			$table->addColumn($column, 'integer');
		}
		$table->addUniqueIndex(['article_id', 'word_id', 'title_match'], 'kbsrcwrdmt_un_mtch');
		$table->addUniqueIndex(['article_id', 'word_id', 'title_match', 'descr_match'], 'un_mtch');

		$reflection = new \ReflectionClass(\phpbb\db\tools\doctrine::class);
		$tools = $reflection->newInstanceWithoutConstructor();
		$tools->set_table_prefix('phpbb_');
		$reflection->getMethod('schema_index_drop')->invoke($tools, $schema, $table->getName(), 'un_mtch', true);

		$this->assertFalse($table->hasIndex('kbsrcwrdmt_un_mtch'));
		$this->assertTrue($table->hasIndex('un_mtch'));
	}
}
