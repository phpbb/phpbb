<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

abstract class type_string_common extends type_base
{
	/**
	* Return possible validation options
	*/
	function validate_options($field_data)
	{
		$validate_ary = array('CHARS_ANY' => '.*', 'NUMBERS_ONLY' => '[0-9]+', 'ALPHA_ONLY' => '[\w]+', 'ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');

		$validate_options = '';
		foreach ($validate_ary as $lang => $value)
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
			return $this->user->lang('FIELD_REQUIRED', $field_data['lang_name']);
		}

		if ($field_data['field_minlen'] && utf8_strlen($field_value) < $field_data['field_minlen'])
		{
			return $this->user->lang('FIELD_TOO_SHORT', (int) $row['field_minlen'], $row['lang_name']);
		}
		else if ($field_data['field_maxlen'] && utf8_strlen($field_value) > $field_data['field_maxlen'])
		{
			return $this->user->lang('FIELD_TOO_LONG', (int) $row['field_maxlen'], $row['lang_name']);
		}

		if (!empty($field_data['field_validation']) && $field_data['field_validation'] != '.*')
		{
			$field_validate = ($field_type != 'text') ? $field_value : bbcode_nl2br($field_value);
			if (!preg_match('#^' . str_replace('\\\\', '\\', $field_data['field_validation']) . '$#i', $field_validate))
			{
				switch ($row['field_validation'])
				{
					case '[0-9]+':
						return $this->user->lang('FIELD_INVALID_CHARS_NUMBERS_ONLY', $row['lang_name']);

					case '[\w]+':
						return $this->user->lang('FIELD_INVALID_CHARS_ALPHA_ONLY', $row['lang_name']);

					case '[\w_\+\. \-\[\]]+':
						return $this->user->lang('FIELD_INVALID_CHARS_SPACERS_ONLY', $row['lang_name']);
				}
			}
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value($field_value, $field_data)
	{
		if (!$field_value && !$field_data['field_show_novalue'])
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
	public function prepare_options_form(&$exclude_options, &$visibility_options)
	{
		$exclude_options[1][] = 'lang_default_value';

		return $this->request->variable('lang_options', '', true);
	}
}
