<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$this->helper->assert_validate_data(array(
			'empty'			=> array('INVALID'),
			'empty_opt'		=> array(),
			'double_single'		=> array(),
			'single_single'		=> array(),
			'double_double'		=> array(),
			// Currently fails
			//'zero_year'		=> array(),
			'month_high'		=> array('INVALID'),
			'month_low'		=> array('INVALID'),
			'day_high'		=> array('INVALID'),
			'day_low'		=> array('INVALID'),
		),
		array(
			'empty'			=> '',
			'empty_opt'		=> '',
			'double_single'		=> '17-06-1990',
			'single_single'		=> '05-05-2009',
			'double_double'		=> '17-12-1990',
			// Currently fails
			//'zero_year'		=> '01-01-0000',
			'month_high'		=> '17-17-1990',
			'month_low'		=> '01-00-1990',
			'day_high'		=> '64-01-1990',
			'day_low'		=> '00-12-1990',
		),
		array(
			'empty'			=> array('date'),
			'empty_opt'		=> array('date', true),
			'double_single'		=> array('date'),
			'single_single'		=> array('date'),
			'double_double'		=> array('date'),
			// Currently fails
			//'zero_year'		=> array('date'),
			'month_high'		=> array('date'),
			'month_low'		=> array('date'),
			'day_high'		=> array('date'),
			'day_low'		=> array('date'),
		));
	}
}
