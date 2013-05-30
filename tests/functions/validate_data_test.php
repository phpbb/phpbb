<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_user.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/../mock/cache.php';
require_once dirname(__FILE__) . '/../mock/user.php';

class phpbb_functions_validate_data_test extends phpbb_database_test_case
{
	protected $db;
	protected $cache;
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/validate_data.xml');
	}

	protected function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->cache = new phpbb_mock_cache;
		$this->user = new phpbb_mock_user;
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
			$test = validate_data(array($data), array($validate_check[$key]));
			if ($test != $expected[$key])
			{
				var_dump($key, $data, $test, $expected[$key]);
			}
			$this->assertEquals($expected[$key], $test);
		}
	}

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

	public function test_validate_lang_iso()
	{
		global $db;

		$db = $this->db;

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

	public function validate_username_data()
	{
		return array(
			array('USERNAME_CHARS_ANY', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array(),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array(),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN')
			)),
			array('USERNAME_ALPHA_ONLY', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array('INVALID_CHARS'),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('INVALID_CHARS')
			)),
			array('USERNAME_ALPHA_SPACERS', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN')
			)),
			array('USERNAME_LETTER_NUM', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array('INVALID_CHARS'),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('INVALID_CHARS')
			)),
			array('USERNAME_LETTER_NUM_SPACERS', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array('INVALID_CHARS'),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array(),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN')
			)),
			array('USERNAME_ASCII', array(
				'foobar_allow'		=> array(),
				'foobar_ascii'		=> array(),
				'foobar_any'		=> array(),
				'foobar_alpha'		=> array(),
				'foobar_alpha_spacers'	=> array(),
				'foobar_letter_num'	=> array(),
				'foobar_letter_num_sp'	=> array('INVALID_CHARS'),
				'foobar_quot'		=> array('INVALID_CHARS'),
				'barfoo_disallow'	=> array('USERNAME_DISALLOWED'),
				'admin_taken'		=> array('USERNAME_TAKEN'),
				'group_taken'		=> array('USERNAME_TAKEN')
			)),
		);
	}

	/**
	* @dataProvider validate_username_data
	*/
	public function test_validate_username($allow_name_chars, $expected)
	{
		global $cache, $config, $db;

		$db = $this->db;
		$cache = $this->cache;
		$cache->put('_disallowed_usernames', array('barfoo'));

		$config['allow_name_chars'] = $allow_name_chars;

		$this->validate_data_check(array(
			'foobar_allow'		=> 'foobar',
			'foobar_ascii'		=> 'foobar',
			'foobar_any'		=> 'f*~*^=oo_bar1',
			'foobar_alpha'		=> 'fo0Bar',
			'foobar_alpha_spacers'	=> 'Fo0-[B]_a+ R',
			'foobar_letter_num'	=> 'fo0Bar0',
			'foobar_letter_num_sp'	=> 'Fö0-[B]_a+ R',
			'foobar_quot'		=> '"foobar"',
			'barfoo_disallow'	=> 'barfoo',
			'admin_taken'		=> 'admin',
			'group_taken'		=> 'foobar_group',
		),
		array(
			'foobar_allow'		=> array('username', 'foobar'),
			'foobar_ascii'		=> array('username'),
			'foobar_any'		=> array('username'),
			'foobar_alpha'		=> array('username'),
			'foobar_alpha_spacers'	=> array('username'),
			'foobar_letter_num'	=> array('username'),
			'foobar_letter_num_sp'	=> array('username'),
			'foobar_quot'		=> array('username'),
			'barfoo_disallow'	=> array('username'),
			'admin_taken'		=> array('username'),
			'group_taken'		=> array('username'),
		),
		$expected);
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

		$this->validate_data_check(array(
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
		),
		$expected);
	}

	public function test_validate_email()
	{
		global $config, $db, $user;

		$config['email_check_mx'] = true;
		$db = $this->db;
		$user = $this->user;
		$user->optionset('banned_users', array('banned@example.com'));

		$this->validate_data_check(array(
			'empty'			=> '',
			'allowed'		=> 'foobar@example.com',
			'invalid'		=> 'fööbar@example.com',
			'valid_complex'		=> "'%$~test@example.com",
			'taken'			=> 'admin@example.com',
			'banned'		=> 'banned@example.com',
			'no_mx'			=> 'test@wwrrrhhghgghgh.ttv',
		),
		array(
			'empty'			=> array('email'),
			'allowed'		=> array('email', 'foobar@example.com'),
			'invalid'		=> array('email'),
			'valid_complex'		=> array('email'),
			'taken'			=> array('email'),
			'banned'		=> array('email'),
			'no_mx'			=> array('email'),
		),
		array(
			'empty'			=> array(),
			'allowed'		=> array(),
			'invalid'		=> array('EMAIL_INVALID'),
			'valid_complex'		=> array(),
			'taken'			=> array('EMAIL_TAKEN'),
			'banned'		=> array('EMAIL_BANNED'),
			'no_mx'			=> array('DOMAIN_NO_MX_RECORD'),
		));
	}

	public function test_validate_jabber()
	{
		$this->validate_data_check(array(
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
		),
		array(
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
		));
	}
}
