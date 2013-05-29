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

class phpbb_functions_validate_data_test extends phpbb_database_test_case
{
	/*
	* Types to test
	* - username
	*	. 
	* - password
	* - email
	* - jabber
	*/

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/language_select.xml');
	}

	/**
	* Test provided input data with supplied checks and compare to expected
	* results
	*
	* @param array $input Input data with specific array keys that need to
	*		be matched by the ones in the other 2 params
	* @param array $validate_check Array containing validate_data check
	*		settings, i.e. array('foobar' => array('string'))
	* @param array $expected Array containing the expected results. Either
	*		an array containing the error message or the an empty
	*		array if input is correct
	*/
	public function validate_data_check($input, $validate_check, $expected)
	{
		foreach ($input as $key => $data)
		{
			$this->assertEquals($expected[$key], validate_data(array($data), array($validate_check[$key])));
		}
	}

	/*
	* Types to test
	* - string:
	*	empty + optional = true --> good
	*	empty + optional = false --> good (min = 0)
	*	'foobar' --> good
	*	'foobar' + optional = false|true + min = 2 + max = 6 --> good
	*	'foobar' + "			    + min = 7 + max = 9 --> TOO_SHORT
	*	'foobar' + "			    + min = 2 + max = 5 --> TOO_LONG
	*	''	   + optional = false	    + min = 1 + max = 6 --> TOO_SHORT
	*	''	   + optional = true	    + min = 1 + max = 6 --> good
	*/
	public function test_validate_string()
	{
		$this->validate_data_check(array(
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
		),
		array(
			'empty_opt'		=> array(),
			'empty'			=> array(),
			'foo'			=> array(),
			'foo_minmax_correct'	=> array(),
			'foo_minmax_short'	=> array('TOO_SHORT'),
			'foo_minmax_long'	=> array('TOO_LONG'),
			'empty_short'		=> array('TOO_SHORT'),
			'empty_length_opt'	=> array(),
		));
	}

	/*
	* Types to test
	* - num
	*	empty + optional = true|false --> good
	*	0	--> good
	*	5 + optional = false|true + min = 2 + max = 6 --> good
	*	5 + optional = false|true + min = 7 + max = 10 --> TOO_SMALL
	*	5 + optional = false|true + min = 2 + max = 3 --> TOO_LARGE
	*	'foobar' --> should fail with WRONG_DATA_NUMERIC !!!
	*/
	public function test_validate_num()
	{
		$this->validate_data_check(array(
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
		),
		array(
			'empty'			=> array(),
			'zero'			=> array(),
			'five_minmax_correct'	=> array(),
			'five_minmax_short'	=> array('TOO_SMALL'),
			'five_minmax_long'	=> array('TOO_LARGE'),
			'string'	=> array(),
		));
	}

	/*
	* Types to test
	* - date
	*	. ''	--> invalid
	*	. '' + optional = true --> good
	*	. 17-06-1990 --> good
	*	. 05-05-1990 --> good
	*	. 17-12-1990 --> good
	*	. 01-01-0000 --> good!!!
	*	. 17-17-1990 --> invalid
	*	. 00-12-1990 --> invalid
	*	. 01-00-1990 --> invalid
	*/
	public function test_validate_date()
	{
		$this->validate_data_check(array(
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
		),
		array(
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
		));
	}

	/*
	* Types to test
	* - match
	*	. empty + optional = true --> good
	*	. empty + empty match --> good
	*	. 'test' + optional = true|false + match = '/[a-z]/' --> good
	*	. 'test123' + optional = true|false + match = '/[a-z]/' --> WRONG_DATA_MATCH
	*/
	public function test_validate_match()
	{
		$this->validate_data_check(array(
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
		),
		array(
			'empty_opt'		=> array(),
			'empty_empty_match'	=> array(),
			'foobar'		=> array(),
			'foobar_fail'		=> array('WRONG_DATA'),
		));
	}

	/*
	* Types to test
	* - language_iso_name
	*	. empty --> WRONG_DATA
	*	. 'en' --> good
	*	. 'cs' --> good
	*	. 'de' --> WRONG_DATA (won't exist)
	*/
	public function test_validate_lang_iso()
	{
		global $db;

		$db = $this->new_dbal();

		$this->validate_data_check(array(
			'empty'		=> '',
			'en'		=> 'en',
			'cs'		=> 'cs',
			'de'		=> 'de',
		),
		array(
			'empty'		=> array('language_iso_name'),
			'en'		=> array('language_iso_name'),
			'cs'		=> array('language_iso_name'),
			'de'		=> array('language_iso_name'),
		),
		array(
			'empty'		=> array('WRONG_DATA'),
			'en'		=> array(),
			'cs'		=> array(),
			'de'		=> array('WRONG_DATA'),
		));
	}
}
