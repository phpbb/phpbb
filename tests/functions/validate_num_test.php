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
require_once dirname(__FILE__) . '/validate_data_helper.php';

class phpbb_functions_validate_num_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function test_validate_num()
	{
		$this->helper->assert_valid_data(array(
			'empty' => array(
				array(),
				'',
				array('num'),
			),
			'zero' => array(
				array(),
				'0',
				array('num'),
			),
			'five_minmax_correct' => array(
				array(),
				'5',
				array('num', false, 2, 6),
			),
			'five_minmax_short' => array(
				array('TOO_SMALL'),
				'5',
				array('num', false, 7, 10),
			),
			'five_minmax_long' => array(
				array('TOO_LARGE'),
				'5',
				array('num', false, 2, 3),
			),
			'string' => array(
				array(),
				'foobar',
				array('num'),
			),
		));
	}
}
