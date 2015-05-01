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

class phpbb_textreparser_post_text_test extends phpbb_database_test_case
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
		$reparser = new \phpbb\textreparser\plugins\post_text($db);
		$reparser->reparse_range($min_id, $max_id);

		$post_ids = array();
		foreach ($expected as $row)
		{
			$post_ids[] = $row['post_id'];
		}
		$sql = 'SELECT post_id, post_text
			FROM ' . POSTS_TABLE . '
			WHERE ' . $db->sql_in_set('post_id', $post_ids);
		$result = $db->sql_query($sql);
		$rows = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		$this->assertEquals($expected, $rows);
	}

	public function getReparseTests()
	{
		return array(
			array(
				2,
				5,
				array(
					array(
						'post_id'   => 1,
						'post_text' => 'This post should be [b]ignored[/b]',
					),
					array(
						'post_id'   => 2,
						'post_text' => '<t>[b]Not bold[/b] :) http://example.org</t>',
					),
					array(
						'post_id'   => 3,
						'post_text' => '<r><B><s>[b]</s>Bold<e>[/b]</e></B> :) http://example.org</r>',
					),
					array(
						'post_id'   => 4,
						'post_text' => '<r>[b]Not bold[/b] <E>:)</E> http://example.org</r>',
					),
					array(
						'post_id'   => 5,
						'post_text' => '<r>[b]Not bold[/b] :) <URL url="http://example.org">http://example.org</URL></r>',
					),
					array(
						'post_id'   => 1000,
						'post_text' => 'This post should be [b]ignored[/b]',
					),
				)
			),
		);
	}
}
