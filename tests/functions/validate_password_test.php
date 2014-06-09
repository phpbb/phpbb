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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
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

		$this->helper->assert_valid_data(array(
			'empty'			=> array(
				$expected['empty'],
				'',
				array('password'),
			),
			'foobar_any'		=> array(
				$expected['foobar_any'],
				'foobar',
				array('password'),
			),
			'foobar_mixed'		=> array(
				$expected['foobar_mixed'],
				'FooBar',
				array('password'),
			),
			'foobar_alpha'		=> array(
				$expected['foobar_alpha'],
				'F00bar',
				array('password'),
			),
			'foobar_symbol'		=> array(
				$expected['foobar_symbol'],
				'fooBar123*',
				array('password'),
			),
		));
	}
}
