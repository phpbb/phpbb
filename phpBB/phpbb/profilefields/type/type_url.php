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
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="field_length" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_minlen" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_maxlen" value="' . $field_data['field_maxlen'] . '" />'),
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

		if (!preg_match('#^' . get_preg_expression('url') . '$#iu', $field_value))
		{
			return $this->user->lang('FIELD_INVALID_URL', $this->get_field_name($field_data['lang_name']));
		}

		return false;
	}
}
