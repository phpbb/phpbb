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

class type_int extends type_base
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
		return 'int';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="field_length" size="5" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_NUMBER'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_minlen" size="5" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_NUMBER'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_maxlen" size="5" value="' . $field_data['field_maxlen'] . '" />'),
			3 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'],		'FIELD' => '<input type="number" name="field_default_value" value="' . $field_data['field_default_value'] . '" />'),
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 5,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 100,
			'field_validation'	=> '',
			'field_novalue'		=> 0,
			'field_default_value'	=> 0,
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_field_value($field_data)
	{
		if ($field_data['field_default_value'] === '')
		{
			// We cannot insert an empty string into an integer column.
			return null;
		}

		return $field_data['field_default_value'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];
		if ($this->request->is_set($var_name) && $this->request->variable($var_name, '') === '')
		{
			return null;
		}
		else
		{
			return $this->request->variable($var_name, (int) $profile_row['field_default_value']);
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		if (trim($field_value) === '' && !$field_data['field_required'])
		{
			return false;
		}

		$field_value = (int) $field_value;

		if ($field_value < $field_data['field_minlen'])
		{
			return $this->user->lang('FIELD_TOO_SMALL', (int) $field_data['field_minlen'], $this->get_field_name($field_data['lang_name']));
		}
		else if ($field_value > $field_data['field_maxlen'])
		{
			return $this->user->lang('FIELD_TOO_LARGE', (int) $field_data['field_maxlen'], $this->get_field_name($field_data['lang_name']));
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value($field_value, $field_data)
	{
		if (($field_value === '' || $field_value === null) && !$field_data['field_show_novalue'])
		{
			return null;
		}
		return (int) $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value_raw($field_value, $field_data)
	{
		if (($field_value === '' || $field_value === null) && !$field_data['field_show_novalue'])
		{
			return null;
		}
		return (int) $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];
		$default_value = $profile_row['field_default_value'];

		if ($this->request->is_set($field_ident))
		{
			$value = ($this->request->variable($field_ident, '') === '') ? null : $this->request->variable($field_ident, $default_value);
		}
		else
		{
			if ($preview_options === false && array_key_exists($field_ident, $this->user->profile_fields) && is_null($this->user->profile_fields[$field_ident]))
			{
				$value = null;
			}
			else if (!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false)
			{
				$value = $default_value;
			}
			else
			{
				$value = $this->user->profile_fields[$field_ident];
			}
		}

		$profile_row['field_value'] = (is_null($value) || $value === '') ? '' : (int) $value;

		$this->template->assign_block_vars('int', array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_field_ident($field_data)
	{
		return 'pf_' . $field_data['field_ident'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'BINT';
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

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_excluded_options($key, $action, $current_value, &$field_data, $step)
	{
		if ($step == 2 && $key == 'field_default_value')
		{
			// Permit an empty string
			if ($action == 'create' && $this->request->variable('field_default_value', '') === '')
			{
				return '';
			}
		}

		return parent::get_excluded_options($key, $action, $current_value, $field_data, $step);
	}
}
