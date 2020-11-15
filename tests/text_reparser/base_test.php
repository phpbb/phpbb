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

require_once __DIR__ . '/../test_framework/phpbb_database_test_case.php';

class phpbb_textreparser_base_test extends phpbb_database_test_case
{
	protected $db;

	protected function setUp(): void
	{
		global $config;
		if (!isset($config))
		{
			$config = new \phpbb\config\config(array());
		}
		$this->get_test_case_helpers()->set_s9e_services();
		$this->db = $this->new_dbal();
		parent::setUp();
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/base.xml');
	}

	protected function get_reparser()
	{
		return new \phpbb\textreparser\plugins\post_text($this->db, POSTS_TABLE);
	}

	protected function get_rows(array $ids)
	{
		$sql = 'SELECT post_id AS id, post_text AS text
			FROM ' . POSTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('post_id', $ids) . '
			ORDER BY id';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function test_reparse_empty()
	{
		$this->get_reparser()->reparse_range(1, 1);

		$this->assertEquals(
			array(
				array(
					'id'   => 1,
					'text' => '<t></t>'
				)
			),
			$this->get_rows(array(1))
		);
	}

	public function test_reparse_case_insensitive()
	{
		$this->get_reparser()->reparse_range(2, 2);

		$this->assertEquals(
			[
				[
					'id'   => '2',
					'text' => '<r><IMG src="img.png"><s>[IMG]</s>img.png<e>[/IMG]</e></IMG></r>'
				]
			],
			$this->get_rows([2])
		);
	}
}
