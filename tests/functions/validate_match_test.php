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
		$this->helper->assert_valid_data(array(
			'empty_opt' => array(
				array(),
				'',
				array('match', true, '/[a-z]$/'),
			),
			'empty_empty_match' => array(
				array(),
				'',
				array('match'),
			),
			'foobar' => array(
				array(),
				'foobar',
				array('match', false, '/[a-z]$/'),
			),
			'foobar_fail' => array(
				array('WRONG_DATA'),
				'foobar123',
				array('match', false, '/[a-z]$/'),
			),
		));
	}
}
