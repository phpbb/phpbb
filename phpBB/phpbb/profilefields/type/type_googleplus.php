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

class type_googleplus extends type_string
{
	/**
	* {@inheritDoc}
	*/
	public function get_name()
	{
		return $this->user->lang('FIELD_GOOGLEPLUS');
	}

	/**
	* {@inheritDoc}
	*/
	public function get_service_name()
	{
		return 'profilefields.type.googleplus';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'			=> 20,
			'field_minlen'			=> 3,
			'field_maxlen'			=> 255,
			'field_validation'		=> '(?:(?!\.{2,})([^<>=+]))+',
			'field_novalue'			=> '',
			'field_default_value'	=> '',
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_contact_value($field_value, $field_data)
	{
		if (!$field_value && !$field_data['field_show_novalue'])
		{
			return null;
		}

		if (!is_numeric($field_value))
		{
			$field_value = '+' . $field_value;
		}

		return $field_value;
	}
}
