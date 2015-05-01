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
require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../test_framework/phpbb_database_test_case.php';

class phpbb_textreparser_posttext_test extends phpbb_database_test_case
{
	public function setUp()
	{
		global $config;
		if (!isset($config))
		{
			$config = new \phpbb\config\config(array());
		}
		$this->get_test_case_helpers()->set_s9e_services();
		parent::setUp();
	}

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/posts.xml');
	}

	/**
	* @dataProvider getReparseTests
	*/
	public function testReparse($min_id, $max_id, $expected)
	{
		$db = $this->new_dbal();
		$reparser = new \phpbb\textreparser\posttext($db);
		$reparser->reparse_range($min_id, $max_id);
		$sql = 'SELECT post_id, post_text
			FROM ' . POSTS_TABLE . "
			WHERE post_id BETWEEN $min_id AND $max_id";
		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		$this->assertEquals($expected, $rows);
	}

	public function getReparseTests()
	{
		return array(
			array(
				1,
				1,
				array(
					array(
						'post_id'   => 1,
						'post_text' => '<t>Plain text</t>'
					)
				)
			),
		);
	}
}
