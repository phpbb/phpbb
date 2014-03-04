<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

class type_url extends type_string
{
	/**
	* {@inheritDoc}
	*/
	public function get_name_short()
	{
		return 'url';
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
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 40,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 200,
			'field_validation'	=> '',
			'field_novalue'		=> '',
			'field_default_value'	=> '',
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		$field_value = trim($field_value);

		if ($field_value === '' && !$field_data['field_required'])
		{
			return false;
		}

		if (!preg_match('#^' . get_preg_expression('url') . '$#i', $field_value))
		{
			return $this->user->lang('FIELD_INVALID_URL', $this->get_field_name($field_data['lang_name']));
		}

		return false;
	}
}
