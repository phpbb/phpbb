<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

abstract class type_string_common
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
			return 'FIELD_REQUIRED';
		}

		if ($field_data['field_minlen'] && utf8_strlen($field_value) < $field_data['field_minlen'])
		{
			return 'FIELD_TOO_SHORT';
		}
		else if ($field_data['field_maxlen'] && utf8_strlen($field_value) > $field_data['field_maxlen'])
		{
			return 'FIELD_TOO_LONG';
		}

		if (!empty($field_data['field_validation']) && $field_data['field_validation'] != '.*')
		{
			$field_validate = ($field_type != 'text') ? $field_value : bbcode_nl2br($field_value);
			if (!preg_match('#^' . str_replace('\\\\', '\\', $field_data['field_validation']) . '$#i', $field_validate))
			{
				return 'FIELD_INVALID_CHARS';
			}
		}

		return false;
	}
}
