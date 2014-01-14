<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

class type_bool implements type_interface
{
	/**
	*
	*/
	public function __construct(\phpbb\profilefields\profilefields $profilefields, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->profilefields = $profilefields;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$profile_row = array(
			'var_name'				=> 'field_default_value',
			'field_id'				=> 1,
			'lang_name'				=> $field_data['lang_name'],
			'lang_explain'			=> $field_data['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $field_data['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> FIELD_BOOL,
			'field_length'			=> $field_data['field_length'],
			'lang_options'			=> $field_data['lang_options']
		);

		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_TYPE'], 'EXPLAIN' => $this->user->lang['BOOL_TYPE_EXPLAIN'], 'FIELD' => '<label><input type="radio" class="radio" name="field_length" value="1"' . (($field_data['field_length'] == 1) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['RADIO_BUTTONS'] . '</label><label><input type="radio" class="radio" name="field_length" value="2"' . (($field_data['field_length'] == 2) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['CHECKBOX'] . '</label>'),
			1 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'], 'FIELD' => $this->profilefields->process_field_row('preview', $profile_row))
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 1,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 0,
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
		return $field_data['field_default_value'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];

		// Checkbox
		if ($profile_row['field_length'] == 2)
		{
			return ($this->request->is_set($var_name)) ? 1 : 0;
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
		$field_value = (bool) $field_value;

		if (!$field_value && $field_data['field_required'])
		{
			return $this->user->lang('FIELD_REQUIRED', $field_data['lang_name']);
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value($field_value, $field_data)
	{
		$field_id = $field_data['field_id'];
		$lang_id = $field_data['lang_id'];

		if (!isset($this->profilefields->options_lang[$field_id][$lang_id]))
		{
			$this->profilefields->get_option_lang($field_id, $lang_id, FIELD_BOOL, false);
		}

		if (!$field_value && $field_data['field_show_novalue'])
		{
			$field_value = $field_data['field_default_value'];
		}

		if ($field_data['field_length'] == 1)
		{
			return (isset($this->profilefields->options_lang[$field_id][$lang_id][(int) $field_value])) ? $this->options_lang[$field_id][$lang_id][(int) $field_value] : null;
		}
		else if (!$field_value)
		{
			return null;
		}
		else
		{
			return $this->profilefields->options_lang[$field_id][$lang_id][(int) ($field_value) + 1];
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];
		$default_value = $profile_row['lang_default_value'];

		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);
		// checkbox - set the value to "true" if it has been set to 1
		if ($profile_row['field_length'] == 2)
		{
			$value = ($this->request->is_set($field_ident) && $this->request->variable($field_ident, $default_value) == 1) ? true : ((!isset($this->user->profile_fields[$field_ident]) || $preview) ? $default_value : $this->user->profile_fields[$field_ident]);
		}
		else
		{
			$value = ($this->request->is_set($field_ident)) ? $this->request->variable($field_ident, $default_value) : ((!isset($this->user->profile_fields[$field_ident]) || $preview) ? $default_value : $this->user->profile_fields[$field_ident]);
		}

		$profile_row['field_value'] = (int) $value;
		$this->template->assign_block_vars('bool', array_change_key_case($profile_row, CASE_UPPER));

		if ($profile_row['field_length'] == 1)
		{
			if (!isset($this->profilefields->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || !sizeof($this->profilefields->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
			{
				$this->profilefields->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_BOOL, $preview);
			}

			foreach ($this->profilefields->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
			{
				$this->template->assign_block_vars('bool.options', array(
					'OPTION_ID'	=> $option_id,
					'CHECKED'	=> ($value == $option_id) ? ' checked="checked"' : '',
					'VALUE'		=> $option_value)
				);
			}
		}
	}
}
