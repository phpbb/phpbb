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

abstract class phpbb_textreparser_test_row_based_plugin extends phpbb_database_test_case
{
	protected $db;

	abstract protected function get_reparser();

	protected function get_rows(array $ids)
	{
		$reparser = $this->get_reparser();
		$columns = $reparser->get_columns();
		$sql = 'SELECT ' . $columns['id'] . ' AS id, ' . $columns['text'] . ' AS text
			FROM ' . $reparser->get_table_name() . '
			WHERE ' . $this->db->sql_in_set($columns['id'], $ids) . '
			ORDER BY id';
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
		$this->assertEquals(1000, $reparser->get_max_id());
	}

	public function test_dry_run()
	{
		$old_rows = $this->get_rows(array(1));
		$reparser = $this->get_reparser();
		$reparser->disable_save();
		$reparser->reparse_range(1, 1);
		$new_rows = $this->get_rows(array(1));
		$this->assertEquals($old_rows, $new_rows);
	}

	/**
	* @dataProvider get_reparse_tests
	*/
	public function test_reparse($min_id, $max_id, $expected)
	{
		$reparser = $this->get_reparser();
		$reparser->reparse_range($min_id, $max_id);

		$ids = array();
		foreach ($expected as $row)
		{
			$ids[] = $row['id'];
		}

		$this->assertEquals($expected, $this->get_rows($ids));
	}

	public function get_reparse_tests()
	{
		return array(
			array(
				2,
				5,
				array(
					array(
						'id'   => '1',
						'text' => 'This row should be [b]ignored[/b]',
					),
					array(
						'id'   => '2',
						'text' => '<t>[b]Not bold[/b] :) http://example.org</t>',
					),
					array(
						'id'   => '3',
						'text' => '<r><B><s>[b]</s>Bold<e>[/b]</e></B> :) http://example.org</r>',
					),
					array(
						'id'   => '4',
						'text' => '<r>[b]Not bold[/b] <E>:)</E> http://example.org</r>',
					),
					array(
						'id'   => '5',
						'text' => '<r>[b]Not bold[/b] :) <URL url="http://example.org">http://example.org</URL></r>',
					),
					array(
						'id'   => '1000',
						'text' => 'This row should be [b]ignored[/b]',
					),
				)
			),
			array(
				6,
				7,
				array(
					array(
						'id'   => '6',
						'text' => '<r><FLASH height="345" url="http://example.org/flash.swf" width="123"><s>[flash=123,345]</s>http://example.org/flash.swf<e>[/flash]</e></FLASH></r>',
					),
					array(
						'id'   => '7',
						'text' => '<t>[flash=123,345]http://example.org/flash.swf[/flash]</t>',
					),
				)
			),
			array(
				8,
				9,
				array(
					array(
						'id'   => '8',
						'text' => '<r><IMG src="http://example.org/img.png"><s>[img]</s>http://example.org/img.png<e>[/img]</e></IMG></r>',
					),
					array(
						'id'   => '9',
						'text' => '<t>[img]http://example.org/img.png[/img]</t>',
					),
				)
			),
		);
	}
}
