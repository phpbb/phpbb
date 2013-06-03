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

class phpbb_functions_validate_password_test extends phpbb_test_case
{
	protected $helper;

	protected function setUp()
	{
		parent::setUp();

		$this->helper = new phpbb_functions_validate_data_helper($this);
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
}
