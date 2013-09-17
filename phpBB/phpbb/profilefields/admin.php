<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields;

/**
* Custom Profile Fields ACP
* @package phpBB3
*/
class admin extends profilefields
{
	var $vars = array();

	/**
	* Return possible validation options
	*/
	function validate_options()
	{
		global $user;

		$validate_ary = array('CHARS_ANY' => '.*', 'NUMBERS_ONLY' => '[0-9]+', 'ALPHA_ONLY' => '[\w]+', 'ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');

		$validate_options = '';
		foreach ($validate_ary as $lang => $value)
		{
			$selected = ($this->vars['field_validation'] == $value) ? ' selected="selected"' : '';
			$validate_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		return $validate_options;
	}

	/**
	* Get string options for second step in ACP
	*/
	function get_string_options()
	{
		global $user;

		$options = array(
			0 => array('TITLE' => $user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" name="field_length" size="5" value="' . $this->vars['field_length'] . '" />'),
			1 => array('TITLE' => $user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" name="field_minlen" size="5" value="' . $this->vars['field_minlen'] . '" />'),
			2 => array('TITLE' => $user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" size="5" value="' . $this->vars['field_maxlen'] . '" />'),
			3 => array('TITLE' => $user->lang['FIELD_VALIDATION'],	'FIELD' => '<select name="field_validation">' . $this->validate_options() . '</select>')
		);

		return $options;
	}

	/**
	* Get text options for second step in ACP
	*/
	function get_text_options()
	{
		global $user;

		$options = array(
			0 => array('TITLE' => $user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="rows" size="5" value="' . $this->vars['rows'] . '" /> ' . $user->lang['ROWS'] . '</dd><dd><input type="number" min="0" max="99999" name="columns" size="5" value="' . $this->vars['columns'] . '" /> ' . $user->lang['COLUMNS'] . ' <input type="hidden" name="field_length" value="' . $this->vars['field_length'] . '" />'),
			1 => array('TITLE' => $user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_minlen" size="10" value="' . $this->vars['field_minlen'] . '" />'),
			2 => array('TITLE' => $user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_maxlen" size="10" value="' . $this->vars['field_maxlen'] . '" />'),
			3 => array('TITLE' => $user->lang['FIELD_VALIDATION'],	'FIELD' => '<select name="field_validation">' . $this->validate_options() . '</select>')
		);

		return $options;
	}

	/**
	* Get int options for second step in ACP
	*/
	function get_int_options()
	{
		global $user;

		$options = array(
			0 => array('TITLE' => $user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="field_length" size="5" value="' . $this->vars['field_length'] . '" />'),
			1 => array('TITLE' => $user->lang['MIN_FIELD_NUMBER'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_minlen" size="5" value="' . $this->vars['field_minlen'] . '" />'),
			2 => array('TITLE' => $user->lang['MAX_FIELD_NUMBER'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_maxlen" size="5" value="' . $this->vars['field_maxlen'] . '" />'),
			3 => array('TITLE' => $user->lang['DEFAULT_VALUE'],		'FIELD' => '<input type="post" name="field_default_value" value="' . $this->vars['field_default_value'] . '" />')
		);

		return $options;
	}

	/**
	* Get bool options for second step in ACP
	*/
	function get_bool_options()
	{
		global $user, $config, $lang_defs;

		$default_lang_id = $lang_defs['iso'][$config['default_lang']];

		$profile_row = array(
			'var_name'				=> 'field_default_value',
			'field_id'				=> 1,
			'lang_name'				=> $this->vars['lang_name'],
			'lang_explain'			=> $this->vars['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $this->vars['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> FIELD_BOOL,
			'field_length'			=> $this->vars['field_length'],
			'lang_options'			=> $this->vars['lang_options']
		);

		$options = array(
			0 => array('TITLE' => $user->lang['FIELD_TYPE'], 'EXPLAIN' => $user->lang['BOOL_TYPE_EXPLAIN'], 'FIELD' => '<label><input type="radio" class="radio" name="field_length" value="1"' . (($this->vars['field_length'] == 1) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $user->lang['RADIO_BUTTONS'] . '</label><label><input type="radio" class="radio" name="field_length" value="2"' . (($this->vars['field_length'] == 2) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $user->lang['CHECKBOX'] . '</label>'),
			1 => array('TITLE' => $user->lang['DEFAULT_VALUE'], 'FIELD' => $this->process_field_row('preview', $profile_row))
		);

		return $options;
	}

	/**
	* Get dropdown options for second step in ACP
	*/
	function get_dropdown_options()
	{
		global $user, $config, $lang_defs;

		$default_lang_id = $lang_defs['iso'][$config['default_lang']];

		$profile_row[0] = array(
			'var_name'				=> 'field_default_value',
			'field_id'				=> 1,
			'lang_name'				=> $this->vars['lang_name'],
			'lang_explain'			=> $this->vars['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $this->vars['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> FIELD_DROPDOWN,
			'lang_options'			=> $this->vars['lang_options']
		);

		$profile_row[1] = $profile_row[0];
		$profile_row[1]['var_name'] = 'field_novalue';
		$profile_row[1]['field_ident'] = 'field_novalue';
		$profile_row[1]['field_default_value']	= $this->vars['field_novalue'];

		$options = array(
			0 => array('TITLE' => $user->lang['DEFAULT_VALUE'], 'FIELD' => $this->process_field_row('preview', $profile_row[0])),
			1 => array('TITLE' => $user->lang['NO_VALUE_OPTION'], 'EXPLAIN' => $user->lang['NO_VALUE_OPTION_EXPLAIN'], 'FIELD' => $this->process_field_row('preview', $profile_row[1]))
		);

		return $options;
	}

	/**
	* Get date options for second step in ACP
	*/
	function get_date_options()
	{
		global $user, $config, $lang_defs;

		$default_lang_id = $lang_defs['iso'][$config['default_lang']];

		$profile_row = array(
			'var_name'				=> 'field_default_value',
			'lang_name'				=> $this->vars['lang_name'],
			'lang_explain'			=> $this->vars['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $this->vars['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> FIELD_DATE,
			'field_length'			=> $this->vars['field_length']
		);

		$always_now = request_var('always_now', -1);
		if ($always_now == -1)
		{
			$s_checked = ($this->vars['field_default_value'] == 'now') ? true : false;
		}
		else
		{
			$s_checked = ($always_now) ? true : false;
		}

		$options = array(
			0 => array('TITLE' => $user->lang['DEFAULT_VALUE'],	'FIELD' => $this->process_field_row('preview', $profile_row)),
			1 => array('TITLE' => $user->lang['ALWAYS_TODAY'],	'FIELD' => '<label><input type="radio" class="radio" name="always_now" value="1"' . (($s_checked) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $user->lang['YES'] . '</label><label><input type="radio" class="radio" name="always_now" value="0"' . ((!$s_checked) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $user->lang['NO'] . '</label>'),
		);

		return $options;
	}
}
