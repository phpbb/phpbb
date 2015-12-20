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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_build_url_test extends phpbb_test_case
{
	protected function setUp()
	{
		global $user, $phpbb_dispatcher, $phpbb_container, $phpbb_root_path, $phpbb_path_helper;

		parent::setUp();

		$phpbb_container = new phpbb_mock_container_builder();
		$user = new phpbb_mock_user();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request(
				new phpbb_mock_request()
			),
			new \phpbb\filesystem\filesystem(),
			$this->getMock('\phpbb\request\request'),
			$phpbb_root_path,
			'php'
		);
		$phpbb_container->set('path_helper', $phpbb_path_helper);
	}
	public function build_url_test_data()
	{
		return array(
			array(
				'index.php',
				false,
				'phpBB/index.php?',
			),
			array(
				'index.php',
				't',
				'phpBB/index.php?',
			),
			array(
				'viewtopic.php?t=5&f=4',
				false,
				'phpBB/viewtopic.php?t=5&amp;f=4',
			),
			array(
				'viewtopic.php?f=2&style=1&t=6',
				'f',
				'phpBB/viewtopic.php?style=1&amp;t=6',
			),
			array(
				'viewtopic.php?f=2&style=1&t=6',
				array('f', 'style', 't'),
				'phpBB/viewtopic.php?',
			),
			array(
				'http://test.phpbb.com/viewtopic.php?f=2&style=1&t=6',
				array('f', 'style', 't'),
				'http://test.phpbb.com/viewtopic.php?',
			),
			array(
				'posting.php?f=2&mode=delete&p=20%22%3Cscript%3Ealert%281%29%3B%3C%2Fscript%3E',
				false,
				'phpBB/posting.php?f=2&amp;mode=delete&amp;p=20%22%3Cscript%3Ealert%281%29%3B%3C%2Fscript%3E',
			)
		);
	}

	/**
	* @dataProvider build_url_test_data
	*/
	public function test_build_url($page, $strip_vars, $expected)
	{
		global $user, $phpbb_root_path;

		$user->page['page'] = $page;
		$output = build_url($strip_vars);

		$this->assertEquals($expected, $output);
	}
}
