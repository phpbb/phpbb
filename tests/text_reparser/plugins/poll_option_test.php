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
require_once __DIR__ . '/../../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../test_framework/phpbb_database_test_case.php';

class phpbb_textreparser_poll_option_test extends phpbb_database_test_case
{
	protected $db;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/poll_options.xml');
	}

	protected function get_reparser()
	{
		return new \phpbb\textreparser\plugins\poll_option($this->db);
	}

	protected function get_rows()
	{
		$sql = 'SELECT topic_id, poll_option_id, poll_option_text
			FROM ' . POLL_OPTIONS_TABLE . '
			ORDER BY topic_id, poll_option_id';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function setUp()
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

	public function test_get_max_id()
	{
		$reparser = $this->get_reparser();
		$this->assertEquals(123, $reparser->get_max_id());
	}

	public function test_dry_run()
	{
		$old_rows = $this->get_rows();
		$reparser = $this->get_reparser();
		$reparser->disable_save();
		$reparser->reparse_range(1, 1);
		$new_rows = $this->get_rows();
		$this->assertEquals($old_rows, $new_rows);
	}

	public function testReparse()
	{
		$reparser = $this->get_reparser();
		$reparser->enable_save();
		$reparser->reparse_range(2, 13);
		$expected = array(
			array(
				'topic_id'         => 1,
				'poll_option_id'   => 1,
				'poll_option_text' => 'This row should be [b]ignored[/b]',
			),
			array(
				'topic_id'         => 1,
				'poll_option_id'   => 2,
				'poll_option_text' => 'This row should be [b:abcd1234]ignored[/b:abcd1234]',
			),
			array(
				'topic_id'         => 2,
				'poll_option_id'   => 1,
				'poll_option_text' => '<r><B><s>[b]</s>Bold<e>[/b]</e></B></r>',
			),
			array(
				'topic_id'         => 2,
				'poll_option_id'   => 2,
				'poll_option_text' => '<r><E>:)</E></r>',
			),
			array(
				'topic_id'         => 2,
				'poll_option_id'   => 3,
				'poll_option_text' => '<r><URL url="http://example.org">http://example.org</URL></r>',
			),
			array(
				'topic_id'         => 11,
				'poll_option_id'   => 1,
				'poll_option_text' => '<r><B><s>[b]</s>Bold<e>[/b]</e></B> :) http://example.org</r>',
			),
			array(
				'topic_id'         => 12,
				'poll_option_id'   => 1,
				'poll_option_text' => '<r>[b]Not bold[/b] <E>:)</E> http://example.org</r>',
			),
			array(
				'topic_id'         => 13,
				'poll_option_id'   => 1,
				'poll_option_text' => '<r>[b]Not bold[/b] :) <URL url="http://example.org">http://example.org</URL></r>',
			),
			array(
				'topic_id'         => 123,
				'poll_option_id'   => 1,
				'poll_option_text' => 'This row should be [b]ignored[/b]',
			),
			array(
				'topic_id'         => 123,
				'poll_option_id'   => 2,
				'poll_option_text' => 'This row should be [b:abcd1234]ignored[/b:abcd1234]',
			),
		);
		$this->assertEquals($expected, $this->get_rows());
	}
}
