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

class phpbb_functions_validate_data_simple_test extends phpbb_test_case
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

	public function validate_password_data()
	{
		return array(
			array('PASS_TYPE_ANY', array(
				'empty'			=> array(),
				'foobar_any'		=> array(),
				'foobar_mixed'		=> array(),
				'foobar_alpha'		=> array(),
				'foobar_symbol'		=> array(),
			)),
			array('PASS_TYPE_CASE', array(
				'empty'			=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_mixed'		=> array(),
				'foobar_alpha'		=> array(),
				'foobar_symbol'		=> array(),
			)),
			array('PASS_TYPE_ALPHA', array(
				'empty'			=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_mixed'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_symbol'		=> array(),
			)),
			array('PASS_TYPE_SYMBOL', array(
				'empty'			=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_mixed'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array('INVALID_CHARS'),
				'foobar_symbol'		=> array(),
			)),
		);
	}

	/**
	* @dataProvider validate_password_data
	*/
	public function test_validate_password($pass_complexity, $expected)
	{
		global $config;

		// Set complexity to mixed case letters, numbers and symbols
		$config['pass_complex'] = $pass_complexity;

		$this->helper->assert_validate_data($expected, array(
			'empty'			=> '',
			'foobar_any'		=> 'foobar',
			'foobar_mixed'		=> 'FooBar',
			'foobar_alpha'		=> 'F00bar',
			'foobar_symbol'		=> 'fooBar123*',
		),
		array(
			'empty'			=> array('password'),
			'foobar_any'		=> array('password'),
			'foobar_mixed'		=> array('password'),
			'foobar_alpha'		=> array('password'),
			'foobar_symbol'		=> array('password'),
		));
	}

	public function test_validate_jabber()
	{
		$this->helper->assert_validate_data(array(
			'empty'			=> array(),
			'no_seperator'		=> array('WRONG_DATA'),
			'no_user'		=> array('WRONG_DATA'),
			'no_realm'		=> array('WRONG_DATA'),
			'dot_realm'		=> array('WRONG_DATA'),
			'-realm'		=> array('WRONG_DATA'),
			'realm-'		=> array('WRONG_DATA'),
			'correct'		=> array(),
			'prohibited'		=> array('WRONG_DATA'),
			'prohibited_char'	=> array('WRONG_DATA'),
		),
		array(
			'empty'			=> '',
			'no_seperator'		=> 'testjabber.ccc',
			'no_user'		=> '@jabber.ccc',
			'no_realm'		=> 'user@',
			'dot_realm'		=> 'user@.....',
			'-realm'		=> 'user@-jabber.ccc',
			'realm-'		=> 'user@jabber.ccc-',
			'correct'		=> 'user@jabber.09A-z.org',
			'prohibited'		=> 'u@ser@jabber.ccc.org',
			'prohibited_char'	=> 'u<s>er@jabber.ccc.org',
		),
		array(
			'empty'			=> array('jabber'),
			'no_seperator'		=> array('jabber'),
			'no_user'		=> array('jabber'),
			'no_realm'		=> array('jabber'),
			'dot_realm'		=> array('jabber'),
			'-realm'		=> array('jabber'),
			'realm-'		=> array('jabber'),
			'correct'		=> array('jabber'),
			'prohibited'		=> array('jabber'),
			'prohibited_char'	=> array('jabber'),
		));
	}
}
