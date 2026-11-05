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

class phpbb_migrator_remove_duplicate_ai_crawler_bots_test extends phpbb_database_test_case
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\migration\data\v33x\remove_duplicate_ai_crawler_bots */
	protected $migration;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/remove_duplicate_ai_crawler_bots.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$factory = new \phpbb\db\tools\factory();

		$this->migration = new \phpbb\db\migration\data\v33x\remove_duplicate_ai_crawler_bots(
			new \phpbb\config\config([]),
			$this->db,
			$factory->get($this->db),
			$phpbb_root_path,
			$phpEx,
			'phpbb_'
		);
	}

	public function test_remove_duplicate_bots()
	{
		$this->migration->remove_duplicate_bots();

		// The AI crawlers whose agent string the administrator's older bots
		// match, case insensitively or by wildcard, are gone; the other AI
		// crawlers and the administrator's own newer duplicate are kept
		$this->assertSame([1, 2, 4, 6, 7, 8], $this->get_ids('phpbb_bots', 'bot_id'));
		$this->assertSame([10, 11, 13, 15, 16, 17], $this->get_ids('phpbb_users', 'user_id'));
		$this->assertSame([10, 11, 13, 15, 16, 17], $this->get_ids('phpbb_user_group', 'user_id'));
	}

	public function test_nothing_to_remove_without_group()
	{
		$sql = 'DELETE FROM phpbb_groups
			WHERE group_id = 8';
		$this->db->sql_query($sql);

		$this->migration->remove_duplicate_bots();

		$this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $this->get_ids('phpbb_bots', 'bot_id'));
		$this->assertSame([10, 11, 12, 13, 14, 15, 16, 17], $this->get_ids('phpbb_users', 'user_id'));
	}

	protected function get_ids($table, $column)
	{
		$sql = "SELECT $column
			FROM $table
			ORDER BY $column ASC";
		$result = $this->db->sql_query($sql);
		$ids = array_map('intval', array_column($this->db->sql_fetchrowset($result), $column));
		$this->db->sql_freeresult($result);

		return $ids;
	}
}
