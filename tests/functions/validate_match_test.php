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

class phpbb_functions_validate_match_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
	}

	public function test_validate_match()
	{
		$this->helper->assert_validate_data(array(
			'empty_opt'		=> array(),
			'empty_empty_match'	=> array(),
			'foobar'		=> array(),
			'foobar_fail'		=> array('WRONG_DATA'),
		),
		array(
			'empty_opt'		=> '',
			'empty_empty_match'	=> '',
			'foobar'		=> 'foobar',
			'foobar_fail'		=> 'foobar123',
		),
		array(
			'empty_opt'		=> array('match', true, '/[a-z]$/'),
			'empty_empty_match'	=> array('match'),
			'foobar'		=> array('match', false, '/[a-z]$/'),
			'foobar_fail'		=> array('match', false, '/[a-z]$/'),
		));
	}
}
