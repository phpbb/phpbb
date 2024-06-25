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

require_once __DIR__ . '/../../phpBB/includes/functions_acp.php';

class phpbb_functions_acp_build_cfg_template_test extends phpbb_test_case
{
	public function build_cfg_template_text_data()
	{
		return array(
			array(
				array('text', 20, 255),
				'key_name',
				array('config_key_name'	=> '1'),
				'config_key_name',
				array(),
				[
					'tag'		=> 'input',
					'id'		=> 'key_name',
					'type'		=> 'text',
					'name'		=> 'config[config_key_name]',
					'size'		=> 20,
					'maxlength'	=> 255,
					'value'		=> '1',
				],
			),
			array(
				array('password', 20, 128),
				'key_name',
				array('config_key_name'	=> '2'),
				'config_key_name',
				array(),
				[
					'tag'		=> 'input',
					'id'		=> 'key_name',
					'type'		=> 'password',
					'name'		=> 'config[config_key_name]',
					'size'		=> 20,
					'maxlength'	=> 128,
					'value'		=> '********',
				],
			),
			array(
				array('text', 0, 255),
				'key_name',
				array('config_key_name'	=> '3'),
				'config_key_name',
				array(),
				[
					'tag'		=> 'input',
					'id'		=> 'key_name',
					'type'		=> 'text',
					'name'		=> 'config[config_key_name]',
					'maxlength'	=> 255,
					'value'		=> '3',
					'size'		=> '',
				],
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_text_data
	*/
	public function test_build_cfg_template_text($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher, $language;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$language = new phpbb_mock_lang();
		$user->lang = $language;

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_dimension_data()
	{
		return array(
			array(
				array('dimension', 5, 15),
				'number_key_name',
				array('config_key_name_width' => 10, 'config_key_name_height' => 20),
				'config_key_name',
				array(),
				[
					'tag'		=> 'dimension',
					'width' => [
						'id'		=> 'number_key_name',
						'type'		=> 'number',
						'name'		=> 'config[config_key_name_width]',
						'min'		=> 5,
						'max'		=> 15,
						'value'		=> 10,
					],
					'height' => [
						'type'		=> 'number',
						'name'		=> 'config[config_key_name_height]',
						'min'		=> 5,
						'max'		=> 15,
						'value'		=> 20,
					],
				],
			),
			array(
				array('dimension', 0, 15),
				'number_key_name',
				array('config_key_name_width' => 10, 'config_key_name_height' => 20),
				'config_key_name',
				array(),
				[
					'tag'		=> 'dimension',
					'width' => [
						'id'		=> 'number_key_name',
						'type'		=> 'number',
						'name'		=> 'config[config_key_name_width]',
						'min'		=> 0,
						'max'		=> 15,
						'value'		=> 10,
					],
					'height' => [
						'type'		=> 'number',
						'name'		=> 'config[config_key_name_height]',
						'min'		=> 0,
						'max'		=> 15,
						'value'		=> 20,
					],
				],
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_dimension_data
	*/
	public function test_build_cfg_template_dimension($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_number_data()
	{
		return array(
			array(
				array('number', 5, 15),
				'number_key_name',
				array('config_key_name' => 10),
				'config_key_name',
				array(),
				[
					'tag'		=> 'input',
					'id'		=> 'number_key_name',
					'type'		=> 'number',
					'name'		=> 'config[config_key_name]',
					'min'		=> 5,
					'max'		=> 15,
					'value'		=> 10,
				],
			),
			array(
				array('number', -1, 9999),
				'number_key_name',
				array('config_key_name' => 10),
				'config_key_name',
				array(),
				[
					'tag'		=> 'input',
					'id'		=> 'number_key_name',
					'type'		=> 'number',
					'name'		=> 'config[config_key_name]',
					'min'		=> -1,
					'max'		=> 9999,
					'value'		=> 10,
				],
			),
			array(
				array('number', 0, 9999),
				'number_key_name',
				array('config_key_name' => 10),
				'config_key_name',
				array(),
				[
					'tag'		=> 'input',
					'id'		=> 'number_key_name',
					'type'		=> 'number',
					'name'		=> 'config[config_key_name]',
					'min'		=> 0,
					'max'		=> 9999,
					'value'		=> 10,
				],
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_number_data
	*/
	public function test_build_cfg_template_number($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_textarea_data()
	{
		return array(
			array(
				array('textarea', 5, 30),
				'key_name',
				array('config_key_name' => 'phpBB'),
				'config_key_name',
				array(),
				[
					'tag'		=> 'textarea',
					'id'		=> 'key_name',
					'name'		=> 'config[config_key_name]',
					'rows'		=> 5,
					'cols'		=> 30,
					'content'	=> 'phpBB',
				]
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_textarea_data
	*/
	public function test_build_cfg_template_textarea($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_radio_data()
	{
		return [
			[
				['radio', 'enabled_disabled'],
				'key_name',
				['config_key_name'	=> '0'],
				'config_key_name',
				[],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'ENABLED',
							'checked'	=> false,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> true,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'DISABLED',
						],
					],
				],
			],
			[
				['radio'],
				'key_name',
				['config_key_name'	=> '0'],
				'config_key_name',
				['function' => 'phpbb_build_radio', 'params' => ['{CONFIG_VALUE}', '{KEY}', [1 => 'ENABLED', 0 => 'DISABLED']]],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'ENABLED',
							'checked'	=> false,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> true,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'DISABLED',
						],
					],
				],
			],
			[
				['radio', 'enabled_disabled'],
				'key_name',
				['config_key_name'	=> '1'],
				'config_key_name',
				[],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'ENABLED',
							'checked'	=> true,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> false,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'DISABLED',
						],
					],
				],
			],
			[
				['radio'],
				'key_name',
				['config_key_name'	=> '1'],
				'config_key_name',
				['function' => 'phpbb_build_radio', 'params' => ['{CONFIG_VALUE}', '{KEY}', [1 => 'ENABLED', 0 => 'DISABLED']]],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'ENABLED',
							'checked'	=> true,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> false,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'DISABLED',
						],
					],
				],
			],
			[
				['radio', 'yes_no'],
				'key_name',
				['config_key_name'	=> '0'],
				'config_key_name',
				[],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'YES',
							'checked'	=> false,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> true,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'NO',
						],
					],
				],
			],
			[
				['radio'],
				'key_name',
				['config_key_name'	=> '0'],
				'config_key_name',
				['function' => 'phpbb_build_radio', 'params' => ['{CONFIG_VALUE}', '{KEY}', [1 => 'YES', 0 => 'NO']]],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'YES',
							'checked'	=> false,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> true,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'NO',
						],
					],
				],
			],
			[
				['radio', 'yes_no'],
				'key_name',
				['config_key_name'	=> '1'],
				'config_key_name',
				[],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'YES',
							'checked'	=> true,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> false,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'NO',
						],
					],
				],
			],
			[
				['radio'],
				'key_name',
				['config_key_name'	=> '1'],
				'config_key_name',
				['function' => 'phpbb_build_radio', 'params' => ['{CONFIG_VALUE}', '{KEY}', [1 => 'YES', 0 => 'NO']]],
				[
					'tag'		=> 'radio',
					'buttons'	=> [
						[
							'id'		=> 'key_name',
							'type'		=> 'radio',
							'value'		=> 1,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'YES',
							'checked'	=> true,
						],
						[
							'type'		=> 'radio',
							'value'		=> 0,
							'checked'	=> false,
							'name'		=> 'config[config_key_name]',
							'label'		=> 'NO',
						],
					],
				],
			],
		];
	}

	/**
	* @dataProvider build_cfg_template_radio_data
	*/
	public function test_build_cfg_template_radio($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $language, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$language = new \phpbb_mock_lang();

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_append_data()
	{
		return array(
			array(
				array('textarea', 5, 30),
				'key_name',
				array('config_key_name' => 'phpBB'),
				'config_key_name',
				array('append' => 'Bertie is cool!'),
				[
					'tag'		=> 'textarea',
					'id'		=> 'key_name',
					'name'		=> 'config[config_key_name]',
					'rows'		=> 5,
					'cols'		=> 30,
					'content'	=> 'phpBB',
					'append'	=> 'Bertie is cool!',
				]
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_append_data
	*/
	public function test_build_cfg_template_append($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_select_data()
	{
		return [
			[
				['select'],
				'key_name',
				['config_key_name'	=> '0'],
				'config_key_name',
				['method' => 'select_helper'],
				[
					'tag'		=> 'select',
					'class'		=> false,
					'id'		=> 'key_name',
					'data'		=> [],
					'name'		=> 'config[config_key_name]',
					'toggleable' => false,
					'options'	=> [
						[
							'value'		=> 1,
							'label'		=> 'First_Option',
							'selected'	=> false,
						],
						[
							'value'		=> 2,
							'label'		=> 'Second_Option',
							'selected'	=> true,
						],
						[
							'value'		=> 3,
							'label'		=> 'Third_Option',
							'selected'	=> false,
						]
					],
					'group_only'	=> false,
					'size'			=> 1,
					'multiple'		=> false,
				],
			],
			[
				['select', 8],
				'key_name',
				['config_key_name'	=> '1'],
				'config_key_name',
				['method' => 'select_helper'],
				[
					'tag'		=> 'select',
					'class'		=> false,
					'id'		=> 'key_name',
					'data'		=> [],
					'name'		=> 'config[config_key_name]',
					'toggleable' => false,
					'options'	=> [
						[
							'value'		=> 1,
							'label'		=> 'First_Option',
							'selected'	=> false,
						],
						[
							'value'		=> 2,
							'label'		=> 'Second_Option',
							'selected'	=> true,
						],
						[
							'value'		=> 3,
							'label'		=> 'Third_Option',
							'selected'	=> false,
						]
					],
					'group_only'	=> false,
					'size'			=> 8,
					'multiple'		=> false,
				],
			],
		];
	}

	/**
	* @dataProvider build_cfg_template_select_data
	*/
	public function test_build_cfg_template_select($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $module, $user, $phpbb_dispatcher, $language;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$language = new phpbb_mock_lang();
		$user->lang = $language;
		$user->module = $this;
		$module = $user;

		$this->assertEquals($expected, phpbb_build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function select_helper()
	{
		return [
			'options' => build_select(
				[
					'1'	=> 'First_Option',
					'2'	=> 'Second_Option',
					'3'	=> 'Third_Option',
				],
				'2'),
		];
	}
}
