<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
		$this->helper->assert_validate_data(array(
			'empty_opt'		=> array(),
			'empty'			=> array(),
			'foo'			=> array(),
			'foo_minmax_correct'	=> array(),
			'foo_minmax_short'	=> array('TOO_SHORT'),
			'foo_minmax_long'	=> array('TOO_LONG'),
			'empty_short'		=> array('TOO_SHORT'),
			'empty_length_opt'	=> array(),
		),
		array(
			'empty_opt'		=> '',
			'empty'			=> '',
			'foo'			=> 'foobar',
			'foo_minmax_correct'	=> 'foobar',
			'foo_minmax_short'	=> 'foobar',
			'foo_minmax_long'	=> 'foobar',
			'empty_short'		=> '',
			'empty_length_opt'	=> '',
		),
		array(
			'empty_opt'		=> array('string', true),
			'empty'			=> array('string'),
			'foo'			=> array('string'),
			'foo_minmax_correct'	=> array('string', false, 2, 6),
			'foo_minmax_short'	=> array('string', false, 7, 9),
			'foo_minmax_long'	=> array('string', false, 2, 5),
			'empty_short'		=> array('string', false, 1, 6),
			'empty_length_opt'	=> array('string', true, 1, 6),
		));
	}
}
