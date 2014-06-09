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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

class phpbb_generate_string_list_test extends phpbb_test_case
{
	public $user;

	public function setUp()
	{
		parent::setUp();

		$this->user = new \phpbb\user();
		$this->user->data = array('user_lang' => 'en');
		$this->user->add_lang('common');
	}

	public function generate_string_list_data()
	{
		return array(
			array(
				array(),
				'',
			),
			array(
				array('A'),
				'A',	
			),
			array(
				array(2 => 'A', 3 => 'B'),
				'A and B',
			),
			array(
				array('A' => 'A', 'B' => 'B', 'C' => 'C'),
				'A, B, and C',
			),
			array(
				array('A', 'B', 'C', 'D'),
				'A, B, C, and D',
			)
		);
	}

	/**
	* @dataProvider generate_string_list_data
	*/
	public function test_generate_string_list($items, $expected_result)
	{
		$result = phpbb_generate_string_list($items, $this->user);
		$this->assertEquals($expected_result, $result);
	}
}
