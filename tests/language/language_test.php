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

class phpbb_language_test extends phpbb_test_case
{
	/** @var \phpbb\language\language */
	protected $lang;

	public function setUp()
	{
		global $phpbb_root_path, $phpEx;

		// Set up language service
		$this->lang = new \phpbb\language\language(
			new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)
		);

		// Set up language data for testing
		$reflection_class = new ReflectionClass('\phpbb\language\language');

		// Set default language files loaded flag to true
		$loaded_flag = $reflection_class->getProperty('common_language_files_loaded');
		$loaded_flag->setAccessible(true);
		$loaded_flag->setValue($this->lang, true);

		// Set up test language data
		$lang_array = $reflection_class->getProperty('lang');
		$lang_array->setAccessible(true);
		$lang_array->setValue($this->lang, $this->get_test_data_set());
	}

	public function test_lang()
	{
		// No param
		$this->assertEquals($this->lang->lang('FOO'), 'BAR');
		$this->assertEquals($this->lang->lang('EMPTY'), '');
		$this->assertEquals($this->lang->lang('ZERO'), '0');

		// Invalid index
		$this->assertEquals($this->lang->lang('VOID'), 'VOID');

		// Unnecessary param
		$this->assertEquals($this->lang->lang('FOO', 2), 'BAR');
		$this->assertEquals($this->lang->lang('FOO', 2, 3), 'BAR');
		$this->assertEquals($this->lang->lang('FOO', 2, 3, 'BARZ'), 'BAR');

		// String
		$this->assertEquals($this->lang->lang('STR', 24, 'x', 42), '24 x, 42 topics');
		$this->assertEquals($this->lang->lang('STR2', 64), '64 foos');

		// Array
		$this->assertEquals($this->lang->lang('ARRY', 0), 'No posts');
		$this->assertEquals($this->lang->lang('ARRY', 1), '1 post');
		$this->assertEquals($this->lang->lang('ARRY', 2), '2 posts');
		$this->assertEquals($this->lang->lang('ARRY', 123), '123 posts');

		// Empty array returns the language key
		$this->assertEquals($this->lang->lang('ARRY_EMPTY', 123), 'ARRY_EMPTY');

		// No 0 key defined
		$this->assertEquals($this->lang->lang('ARRY_NO_ZERO', 0), '0 posts');
		$this->assertEquals($this->lang->lang('ARRY_NO_ZERO', 1), '1 post');
		$this->assertEquals($this->lang->lang('ARRY_NO_ZERO', 2), '2 posts');

		// Array with missing keys
		$this->assertEquals($this->lang->lang('ARRY_MISSING', 2), '2 post');

		// Floats as array key
		$this->assertEquals($this->lang->lang('ARRY_FLOAT', 1.3), '1 post');
		$this->assertEquals($this->lang->lang('ARRY_FLOAT', 2.0), '2.0 posts');
		$this->assertEquals($this->lang->lang('ARRY_FLOAT', 2.51), '2.5 posts');

		// Use sub key, if first paramenter is an array
		$this->assertEquals($this->lang->lang(array('dateformat', 'AGO'), 2), '2 seconds');

		// ticket PHPBB3-9949 - use first int to determinate the plural-form to use
		$this->assertEquals($this->lang->lang('ARRY', 1, 2), '1 post');
		$this->assertEquals($this->lang->lang('ARRY', 1, 's', 2), '1 post');
	}

	public function test_lang_plural_rules()
	{
		$this->assertEquals($this->lang->lang('PLURAL_ARRY', 0), '0 is 0');
		$this->assertEquals($this->lang->lang('PLURAL_ARRY', 1), '1 is 1');
		$this->assertEquals($this->lang->lang('PLURAL_ARRY', 103), '103 ends with 01-10');
		$this->assertEquals($this->lang->lang('PLURAL_ARRY', 15), '15 ends with 11-19');
		$this->assertEquals($this->lang->lang('PLURAL_ARRY', 300), '300 is part of the last rule');
	}

	public function test_lang_bc()
	{
		$user = new \phpbb\user($this->lang, '\phpbb\datetime');

		// Test lang array access
		$this->assertEquals($user->lang['FOO'], 'BAR');

		// No param
		$this->assertEquals($user->lang('FOO'), 'BAR');
		$this->assertEquals($user->lang('EMPTY'), '');
		$this->assertEquals($user->lang('ZERO'), '0');

		// Invalid index
		$this->assertEquals($user->lang('VOID'), 'VOID');

		// Unnecessary param
		$this->assertEquals($user->lang('FOO', 2), 'BAR');
		$this->assertEquals($user->lang('FOO', 2, 3), 'BAR');
		$this->assertEquals($user->lang('FOO', 2, 3, 'BARZ'), 'BAR');

		// String
		$this->assertEquals($user->lang('STR', 24, 'x', 42), '24 x, 42 topics');
		$this->assertEquals($user->lang('STR2', 64), '64 foos');

		// Array
		$this->assertEquals($user->lang('ARRY', 0), 'No posts');
		$this->assertEquals($user->lang('ARRY', 1), '1 post');
		$this->assertEquals($user->lang('ARRY', 2), '2 posts');
		$this->assertEquals($user->lang('ARRY', 123), '123 posts');

		// Empty array returns the language key
		$this->assertEquals($user->lang('ARRY_EMPTY', 123), 'ARRY_EMPTY');

		// No 0 key defined
		$this->assertEquals($user->lang('ARRY_NO_ZERO', 0), '0 posts');
		$this->assertEquals($user->lang('ARRY_NO_ZERO', 1), '1 post');
		$this->assertEquals($user->lang('ARRY_NO_ZERO', 2), '2 posts');

		// Array with missing keys
		$this->assertEquals($user->lang('ARRY_MISSING', 2), '2 post');

		// Floats as array key
		$this->assertEquals($user->lang('ARRY_FLOAT', 1.3), '1 post');
		$this->assertEquals($user->lang('ARRY_FLOAT', 2.0), '2.0 posts');
		$this->assertEquals($user->lang('ARRY_FLOAT', 2.51), '2.5 posts');

		// Use sub key, if first paramenter is an array
		$this->assertEquals($user->lang(array('dateformat', 'AGO'), 2), '2 seconds');

		// ticket PHPBB3-9949 - use first int to determinate the plural-form to use
		$this->assertEquals($user->lang('ARRY', 1, 2), '1 post');
		$this->assertEquals($user->lang('ARRY', 1, 's', 2), '1 post');
	}

	public function test_lang_plural_rules_bc()
	{
		$user = new \phpbb\user($this->lang, '\phpbb\datetime');

		// ticket PHPBB3-10345 - different plural rules, not just 0/1/2+
		$this->assertEquals($user->lang('PLURAL_ARRY', 0), '0 is 0');
		$this->assertEquals($user->lang('PLURAL_ARRY', 1), '1 is 1');
		$this->assertEquals($user->lang('PLURAL_ARRY', 103), '103 ends with 01-10');
		$this->assertEquals($user->lang('PLURAL_ARRY', 15), '15 ends with 11-19');
		$this->assertEquals($user->lang('PLURAL_ARRY', 300), '300 is part of the last rule');
	}

	protected function get_test_data_set()
	{
		return array(
			'FOO'		=> 'BAR',
			'BARZ'		=> 'PENG',
			'EMPTY'		=> '',
			'ZERO'		=> '0',
			'STR'		=> '%d %s, %d topics',
			'STR2'		=> '%d foos',
			'ARRY'		=> array(
				0		=> 'No posts',		// 0
				1		=> '1 post',		// 1
				2		=> '%d posts',		// 2+
			),
			'ARRY_NO_ZERO'	=> array(
				1		=> '1 post',		// 1
				2		=> '%d posts',		// 0, 2+
			),
			'ARRY_MISSING'	=> array(
				1		=> '%d post',		// 1
				//Missing second plural
			),
			'ARRY_FLOAT'	=> array(
				1		=> '1 post',		// 1.x
				2		=> '%1$.1f posts',	// 0.x, 2+.x
			),
			'ARRY_EMPTY'	=> array(
			),
			'dateformat'	=> array(
				'AGO'	=> array(
					1	=> '%d second',
					2	=> '%d seconds',
				),
			),
			'PLURAL_RULE' => 13,
			'PLURAL_ARRY' => array(
				0		=> '%d is 0',						// 0
				1		=> '%d is 1',						// 1
				2		=> '%d ends with 01-10',			// ending with 01-10
				3		=> '%d ends with 11-19',			// ending with 11-19
				4		=> '%d is part of the last rule',	// everything else
			),
		);
	}
}
