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

require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_profilefield_type_url_test extends phpbb_test_case
{
	protected $cp;
	protected $field_options;

	/**
	* Sets up basic test objects
	*
	* @access protected
	*/
	protected function setUp(): void
	{
		global $config, $request, $user, $cache;

		$config = new \phpbb\config\config([]);
		$cache = new phpbb_mock_cache;
		$user = $this->createMock('\phpbb\user');
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$request = $this->createMock('\phpbb\request\request');
		$template = $this->createMock('\phpbb\template\template');

		$this->cp = new \phpbb\profilefields\type\type_url(
			$request,
			$template,
			$user
		);

		$this->field_options = array(
			'field_type'     => '\phpbb\profilefields\type\type_url',
			'field_name' 	 => 'field',
			'field_id'	 	 => 1,
			'lang_id'	 	 => 1,
			'lang_name'      => 'field',
			'field_required' => false,
			'field_show_novalue'	=> null,
			'field_novalue'    	=> null,
			'field_minlen'		=> null,
			'field_maxlen'		=> null,
		);
	}

	public function validate_profile_field_data()
	{
		return array(
			array(
				'',
				array('field_required' => true),
				'FIELD_INVALID_URL-field',
				'Field should reject empty field that is required',
			),
			array(
				'invalidURL',
				array(),
				'FIELD_INVALID_URL-field',
				'Field should reject invalid input',
			),
			array(
				'http://onetwothree.example.io',
				array(),
				false,
				'Field should accept valid URL',
			),
			array(
				'http://example.com/index.html?param1=test&param2=awesome',
				array(),
				false,
				'Field should accept valid URL',
			),
			array(
				'http://example.com/index.html/test/path?document=get',
				array(),
				false,
				'Field should accept valid URL',
			),
			array(
				'http://example.com/index.html/test/path?document[]=DocType%20test&document[]=AnotherDoc',
				array(),
				'FIELD_INVALID_URL-field',
				'Field should reject invalid URL having multi value parameters',
			),
			// Not allowed schemes
			array(
				'ftp://example.com/',
				array(),
				'FIELD_INVALID_URL-field',
				'Field should reject invalid URL having multi value parameters',
			),
			array(
				'javascript://alert.com',
				array(),
				'FIELD_INVALID_URL-field',
				'Field should reject invalid URL having multi value parameters',
			),

			// IDN url type profilefields
			array(
				'http://www.täst.de',
				array(),
				false,
				'Field should accept valid IDN',
			),
			array(
				'http://täst.de/index.html?param1=test&param2=awesome',
				array(),
				false,
				'Field should accept valid IDN URL with params',
			),
			array(
				'http://домен.рф/index.html/тест/path?document=get',
				array(),
				false,
				'Field should accept valid IDN URL',
			),
			array(
				'http://домен.рф/index.html/тест/path?document[]=DocType%20test&document[]=AnotherDoc',
				array(),
				'FIELD_INVALID_URL-field',
				'Field should reject invalid IDN URL having multi value parameters',
			),
		);
	}

	/**
	* @dataProvider validate_profile_field_data
	*/
	public function test_validate_profile_field($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->validate_profile_field($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function profile_value_raw_data()
	{
		return array(
			array(
				'http://example.com',
				array('field_show_novalue' => true),
				'http://example.com',
				'Field should return the correct raw value',
			),
			array(
				'http://example.com',
				array('field_show_novalue' => false),
				'http://example.com',
				'Field should return correct raw value',
			),

			// IDN tests
			array(
				'http://täst.de',
				array('field_show_novalue' => true),
				'http://täst.de',
				'Field should return the correct raw value',
			),
			array(
				'http://домен.рф',
				array('field_show_novalue' => false),
				'http://домен.рф',
				'Field should return correct raw value',
			),
		);
	}

	public function profile_value_data()
	{
		return array(
			array(
				'http://foobar.com',
				array('field_show_novalue' => true),
				'<!-- l --><a class="postlink-local" href="http://foobar.com">foobar.com</a><!-- l -->',
				'Field should output the given value',
			),
			array(
				'http://foobar.com',
				array('field_show_novalue' => false),
				'<!-- l --><a class="postlink-local" href="http://foobar.com">foobar.com</a><!-- l -->',
				'Field should output the given value',
			),
			array(
				'test',
				array('field_show_novalue' => true),
				null,
				'Field should output nothing for empty value',
			),
			array(
				'test',
				array('field_show_novalue' => false),
				null,
				'Field should simply output null for empty value',
			),
			array(
				'javascript://foobar.com',
				array('field_show_novalue' => true),
				null,
				'Field should output nothing for empty value',
			),
		);
	}


	/**
	 * @dataProvider profile_value_data
	 */
	public function test_get_profile_value($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->get_profile_value($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	/**
	* @dataProvider profile_value_raw_data
	*/
	public function test_get_profile_value_raw($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->get_profile_value_raw($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}
}
