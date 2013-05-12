<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions_acp.php';

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
				'<input id="key_name" type="text" size="20" maxlength="255" name="config[config_key_name]" value="1" />',
			),
			array(
				array('password', 20, 128),
				'key_name',
				array('config_key_name'	=> '2'),
				'config_key_name',
				array(),
				'<input id="key_name" type="password" size="20" maxlength="128" name="config[config_key_name]" value="2" autocomplete="off" />',
			),
			array(
				array('text', 0, 255),
				'key_name',
				array('config_key_name'	=> '3'),
				'config_key_name',
				array(),
				'<input id="key_name" type="text" maxlength="255" name="config[config_key_name]" value="3" />',
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_text_data
	*/
	public function test_build_cfg_template_text($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
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
				'<input id="number_key_name" type="number" size="2" maxlength="2" min="5" max="15" name="config[config_key_name_width]" value="10" /> x <input type="number" size="2" maxlength="2" min="5" max="15" name="config[config_key_name_height]" value="20" />',
			),
			array(
				array('dimension', 0, 15),
				'number_key_name',
				array('config_key_name_width' => 10, 'config_key_name_height' => 20),
				'config_key_name',
				array(),
				'<input id="number_key_name" type="number" size="2" maxlength="2" min="0" max="15" name="config[config_key_name_width]" value="10" /> x <input type="number" size="2" maxlength="2" min="0" max="15" name="config[config_key_name_height]" value="20" />',
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

		$this->assertEquals($expected, build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
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
				'<input id="number_key_name" type="number" maxlength="2" min="5" max="15" name="config[config_key_name]" value="10" />',
			),
			array(
				array('number', -1, 9999),
				'number_key_name',
				array('config_key_name' => 10),
				'config_key_name',
				array(),
				'<input id="number_key_name" type="number" maxlength="4" min="-1" max="9999" name="config[config_key_name]" value="10" />',
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

		$this->assertEquals($expected, build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
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
				'<textarea id="key_name" name="config[config_key_name]" rows="5" cols="30">phpBB</textarea>',
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

		$this->assertEquals($expected, build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}

	public function build_cfg_template_radio_data()
	{
		return array(
			array(
				array('radio', 'enabled_disabled'),
				'key_name',
				array('config_key_name'	=> '0'),
				'config_key_name',
				array(),
				'<label><input type="radio" id="key_name" name="config[config_key_name]" value="1" class="radio" /> ENABLED</label><label><input type="radio" name="config[config_key_name]" value="0" checked="checked" class="radio" /> DISABLED</label>',
			),
			array(
				array('radio', 'enabled_disabled'),
				'key_name',
				array('config_key_name'	=> '1'),
				'config_key_name',
				array(),
				'<label><input type="radio" id="key_name" name="config[config_key_name]" value="1" checked="checked" class="radio" /> ENABLED</label><label><input type="radio" name="config[config_key_name]" value="0" class="radio" /> DISABLED</label>',
			),
			array(
				array('radio', 'yes_no'),
				'key_name',
				array('config_key_name'	=> '0'),
				'config_key_name',
				array(),
				'<label><input type="radio" id="key_name" name="config[config_key_name]" value="1" class="radio" /> YES</label><label><input type="radio" name="config[config_key_name]" value="0" checked="checked" class="radio" /> NO</label>',
			),
			array(
				array('radio', 'yes_no'),
				'key_name',
				array('config_key_name'	=> '1'),
				'config_key_name',
				array(),
				'<label><input type="radio" id="key_name" name="config[config_key_name]" value="1" checked="checked" class="radio" /> YES</label><label><input type="radio" name="config[config_key_name]" value="0" class="radio" /> NO</label>',
			),
		);
	}

	/**
	* @dataProvider build_cfg_template_radio_data
	*/
	public function test_build_cfg_template_radio($tpl_type, $key, $new, $config_key, $vars, $expected)
	{
		global $user, $phpbb_dispatcher;

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$user = new phpbb_mock_user();
		$user->lang = new phpbb_mock_lang();

		$this->assertEquals($expected, build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
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
				'<textarea id="key_name" name="config[config_key_name]" rows="5" cols="30">phpBB</textarea>Bertie is cool!',
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

		$this->assertEquals($expected, build_cfg_template($tpl_type, $key, $new, $config_key, $vars));
	}
}
