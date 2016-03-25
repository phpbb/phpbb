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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_string_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function test_validate_string()
	{
		$this->helper->assert_valid_data(array(
			'empty_opt' => array(
				array(),
				'',
				array('string', true),
			),
			'empty' => array(
				array(),
				'',
				array('string'),
			),
			'foo' => array(
				array(),
				'foobar',
				array('string'),
			),
			'foo_minmax_correct' => array(
				array(),
				'foobar',
				array('string', false, 2, 6),
			),
			'foo_minmax_short' => array(
				array('TOO_SHORT'),
				'foobar',
				array('string', false, 7, 9),
			),
			'foo_minmax_long' => array(
				array('TOO_LONG'),
				'foobar',
				array('string', false, 2, 5),
			),
			'empty_short' => array(
				array('TOO_SHORT'),
				'',
				array('string', false, 1, 6),
			),
			'empty_length_opt' => array(
				array(),
				'',
				array('string', true, 1, 6),
			),
		));
	}
}
