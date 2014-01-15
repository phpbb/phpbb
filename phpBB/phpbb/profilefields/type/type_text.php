<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

class type_text extends type_string_common implements type_interface
{
	/**
	*
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
	public function get_options($default_lang_id, $field_data)
	{
		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="rows" size="5" value="' . $field_data['rows'] . '" /> ' . $this->user->lang['ROWS'] . '</dd><dd><input type="number" min="0" max="99999" name="columns" size="5" value="' . $field_data['columns'] . '" /> ' . $this->user->lang['COLUMNS'] . ' <input type="hidden" name="field_length" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_minlen" size="10" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_maxlen" size="10" value="' . $field_data['field_maxlen'] . '" />'),
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
			'field_length'		=> '5|80',
			'field_minlen'		=> 0,
			'field_maxlen'		=> 1000,
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
		return $this->validate_string_profile_field('text', $field_value, $field_data);
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$field_length = explode('|', $profile_row['field_length']);
		$profile_row['field_rows'] = $field_length[0];
		$profile_row['field_cols'] = $field_length[1];
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];
		$default_value = $profile_row['lang_default_value'];

		$profile_row['field_value'] = ($this->request->is_set($field_ident)) ? $this->request->variable($field_ident, $default_value, true) : ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $default_value : $this->user->profile_fields[$field_ident]);

		$this->template->assign_block_vars('text', array_change_key_case($profile_row, CASE_UPPER));
	}
}
