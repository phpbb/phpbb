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
		$this->helper->assert_validate_data(array(
			'empty'			=> array(),
			'zero'			=> array(),
			'five_minmax_correct'	=> array(),
			'five_minmax_short'	=> array('TOO_SMALL'),
			'five_minmax_long'	=> array('TOO_LARGE'),
			'string'		=> array(),
		),
		array(
			'empty'			=> '',
			'zero'			=> 0,
			'five_minmax_correct'	=> 5,
			'five_minmax_short'	=> 5,
			'five_minmax_long'	=> 5,
			'string'		=> 'foobar',
		),
		array(
			'empty'			=> array('num'),
			'zero'			=> array('num'),
			'five_minmax_correct'	=> array('num', false, 2, 6),
			'five_minmax_short'	=> array('num', false, 7, 10),
			'five_minmax_long'	=> array('num', false, 2, 3),
			'string'		=> array('num'),
		));
	}
}
