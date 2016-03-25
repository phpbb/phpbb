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

abstract class type_string_common extends type_base
{
	protected $validation_options = array(
		'CHARS_ANY'			=> '.*',
		'NUMBERS_ONLY'		=> '[0-9]+',
		'ALPHA_ONLY'		=> '[a-zA-Z0-9]+',
		'ALPHA_UNDERSCORE'	=> '[\w]+',
		'ALPHA_DOTS'        => '[a-zA-Z0-9.]+',
		'ALPHA_SPACERS'		=> '[\w\x20+\-\[\]]+',
		'ALPHA_PUNCTUATION' => '[a-zA-Z][\w\.,\-]+',
		'LETTER_NUM_ONLY'			=> '[\p{Lu}\p{Ll}0-9]+',
		'LETTER_NUM_UNDERSCORE'		=> '[\p{Lu}\p{Ll}0-9_]+',
		'LETTER_NUM_DOTS'			=> '[\p{Lu}\p{Ll}0-9.]+',
		'LETTER_NUM_SPACERS'		=> '[\p{Lu}\p{Ll}0-9\x20_+\-\[\]]+',
		'LETTER_NUM_PUNCTUATION'	=> '[\p{Lu}\p{Ll}][\p{Lu}\p{Ll}0-9.,\-_]+',
	);

	/**
	* Return possible validation options
	*/
	public function validate_options($field_data)
	{
		$validate_options = '';
		foreach ($this->validation_options as $lang => $value)
		{
			$selected = ($field_data['field_validation'] == $value) ? ' selected="selected"' : '';
			$validate_options .= '<option value="' . $value . '"' . $selected . '>' . $this->user->lang[$lang] . '</option>';
		}

		return $validate_options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_field_value($field_data)
	{
		return $field_data['lang_default_value'];
	}

	/**
	* Validate entered profile field data
	*
	* @param string	$field_type			Field type (string or text)
	* @param mixed	$field_value		Field value to validate
	* @param array	$field_data			Array with requirements of the field
	* @return mixed		String with key of the error language string, false otherwise
	*/
	public function validate_string_profile_field($field_type, &$field_value, $field_data)
	{
		if (trim($field_value) === '' && !$field_data['field_required'])
		{
			return false;
		}
		else if (trim($field_value) === '' && $field_data['field_required'])
		{
			return $this->user->lang('FIELD_REQUIRED', $this->get_field_name($field_data['lang_name']));
		}

		if ($field_data['field_minlen'] && utf8_strlen($field_value) < $field_data['field_minlen'])
		{
			return $this->user->lang('FIELD_TOO_SHORT', (int) $field_data['field_minlen'], $this->get_field_name($field_data['lang_name']));
		}
		else if ($field_data['field_maxlen'] && utf8_strlen(html_entity_decode($field_value)) > $field_data['field_maxlen'])
		{
			return $this->user->lang('FIELD_TOO_LONG', (int) $field_data['field_maxlen'], $this->get_field_name($field_data['lang_name']));
		}

		if (!empty($field_data['field_validation']) && $field_data['field_validation'] != '.*')
		{
			$field_validate = ($field_type != 'text') ? $field_value : bbcode_nl2br($field_value);
			if (!preg_match('#^' . str_replace('\\\\', '\\', $field_data['field_validation']) . '$#iu', $field_validate))
			{
				$validation = array_search($field_data['field_validation'], $this->validation_options);
				if ($validation)
				{
					return $this->user->lang('FIELD_INVALID_CHARS_' . $validation, $this->get_field_name($field_data['lang_name']));
				}
				return $this->user->lang('FIELD_INVALID_CHARS_INVALID', $this->get_field_name($field_data['lang_name']));
			}
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value($field_value, $field_data)
	{
		if (($field_value === null || $field_value === '') && !$field_data['field_show_novalue'])
		{
			return null;
		}

		$field_value = make_clickable($field_value);
		$field_value = censor_text($field_value);
		$field_value = bbcode_nl2br($field_value);
		return $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value_raw($field_value, $field_data)
	{
		if (($field_value === null || $field_value === '') && !$field_data['field_show_novalue'])
		{
			return null;
		}

		return $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_contact_value($field_value, $field_data)
	{
		return $this->get_profile_value_raw($field_value, $field_data);
	}

	/**
	* {@inheritDoc}
	*/
	public function prepare_options_form(&$exclude_options, &$visibility_options)
	{
		$exclude_options[1][] = 'lang_default_value';

		return $this->request->variable('lang_options', '', true);
	}
}
