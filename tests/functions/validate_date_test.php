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

class phpbb_functions_validate_date_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function test_validate_date()
	{
		$this->helper->assert_valid_data(array(
			'empty' => array(
				array('INVALID'),
				'',
				array('date'),
			),
			'empty_opt' => array(
				array(),
				'',
				array('date', true),
			),
			'double_single' => array(
				array(),
				'17-06-1990',
				array('date'),
			),
			'single_single' => array(
				array(),
				'05-05-2009',
				array('date'),
			),
			'double_double' => array(
				array(),
				'17-12-1990',
				array('date'),
			),
			'month_high' => array(
				array('INVALID'),
				'17-17-1990',
				array('date'),
			),
			'month_low' => array(
				array('INVALID'),
				'01-00-1990',
				array('date'),
			),
			'day_high' => array(
				array('INVALID'),
				'64-01-1990',
				array('date'),
			),
			'day_low' => array(
				array('INVALID'),
				'00-12-1990',
				array('date'),
			),
			// Currently fails
			/*
			'zero_year' => array(
				array(),
				'01-01-0000',
				array('date'),
			),
			*/
		));
	}
}
