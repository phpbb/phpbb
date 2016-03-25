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

namespace phpbb\profilefields\type;

class type_string extends type_string_common
{
	/**
	* Request object
	* @var \phpbb\request\request
	*/
	protected $request;

	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Construct
	*
	* @param	\phpbb\request\request		$request	Request object
	* @param	\phpbb\template\template	$template	Template object
	* @param	\phpbb\user					$user		User object
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name_short()
	{
		return 'string';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" name="field_length" size="5" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" name="field_minlen" size="5" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" name="field_maxlen" size="5" value="' . $field_data['field_maxlen'] . '" />'),
			3 => array('TITLE' => $this->user->lang['FIELD_VALIDATION'],	'FIELD' => '<select name="field_validation">' . $this->validate_options($field_data) . '</select>'),
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 10,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 20,
			'field_validation'	=> '.*',
			'field_novalue'		=> '',
			'field_default_value'	=> '',
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];
		return $this->request->variable($var_name, (string) $profile_row['field_default_value'], true);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		return $this->validate_string_profile_field('string', $field_value, $field_data);
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];
		$default_value = $profile_row['lang_default_value'];
		$profile_row['field_value'] = ($this->request->is_set($field_ident)) ? $this->request->variable($field_ident, $default_value, true) : ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $default_value : $this->user->profile_fields[$field_ident]);

		$this->template->assign_block_vars($this->get_name_short(), array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'VCHAR';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_language_options($field_data)
	{
		$options = array(
			'lang_name' => 'string',
		);

		if ($field_data['lang_explain'])
		{
			$options['lang_explain'] = 'text';
		}

		if (strlen($field_data['lang_default_value']))
		{
			$options['lang_default_value'] = 'string';
		}

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function display_options(&$template_vars, &$field_data)
	{
		$template_vars = array_merge($template_vars, array(
			'S_STRING'					=> true,
			'L_DEFAULT_VALUE_EXPLAIN'	=> $this->user->lang['STRING_DEFAULT_VALUE_EXPLAIN'],
			'LANG_DEFAULT_VALUE'		=> $field_data['lang_default_value'],
		));
	}
}
