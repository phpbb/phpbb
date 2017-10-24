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

class type_bool extends type_base
{
	/**
	* Profile fields language helper
	* @var \phpbb\profilefields\lang_helper
	*/
	protected $lang_helper;

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
	* @param	\phpbb\profilefields\lang_helper		$lang_helper	Profile fields language helper
	* @param	\phpbb\request\request		$request	Request object
	* @param	\phpbb\template\template	$template	Template object
	* @param	\phpbb\user					$user		User object
	*/
	public function __construct(\phpbb\profilefields\lang_helper $lang_helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->lang_helper = $lang_helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_name_short()
	{
		return 'bool';
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
			'field_type'			=> $this->get_service_name(),
			'field_length'			=> $field_data['field_length'],
			'lang_options'			=> $field_data['lang_options'],
		);

		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_TYPE'], 'EXPLAIN' => $this->user->lang['BOOL_TYPE_EXPLAIN'], 'FIELD' => '<label><input type="radio" class="radio" name="field_length" value="1"' . (($field_data['field_length'] == 1) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['RADIO_BUTTONS'] . '</label><label><input type="radio" class="radio" name="field_length" value="2"' . (($field_data['field_length'] == 2) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['CHECKBOX'] . '</label>'),
			1 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'], 'FIELD' => $this->process_field_row('preview', $profile_row)),
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
			return $this->user->lang('FIELD_REQUIRED', $this->get_field_name($field_data['lang_name']));
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

		if (!$this->lang_helper->is_set($field_id, $lang_id))
		{
			$this->lang_helper->load_option_lang($lang_id);
		}

		if (!$field_value && $field_data['field_show_novalue'])
		{
			$field_value = $field_data['field_default_value'];
		}

		if ($field_data['field_length'] == 1)
		{
			return ($this->lang_helper->is_set($field_id, $lang_id, (int) $field_value)) ? $this->lang_helper->get($field_id, $lang_id, (int) $field_value) : null;
		}
		else if (!$field_value)
		{
			return null;
		}
		else
		{
			return $this->lang_helper->is_set($field_id, $lang_id, $field_value + 1) ? $this->lang_helper->get($field_id, $lang_id, $field_value + 1) : null;
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value_raw($field_value, $field_data)
	{
		if ($field_value == $field_data['field_novalue'] && !$field_data['field_show_novalue'])
		{
			return null;
		}

		if (!$field_value && $field_data['field_show_novalue'])
		{
			$field_value = $field_data['field_novalue'];
		}

		return $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];
		$default_value = $profile_row['field_default_value'];

		// checkbox - set the value to "true" if it has been set to 1
		if ($profile_row['field_length'] == 2)
		{
			$value = ($this->request->is_set($field_ident) && $this->request->variable($field_ident, $default_value) == 1) ? true : ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $default_value : $this->user->profile_fields[$field_ident]);
		}
		else
		{
			$value = ($this->request->is_set($field_ident)) ? $this->request->variable($field_ident, $default_value) : ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $default_value : $this->user->profile_fields[$field_ident]);
		}

		$profile_row['field_value'] = (int) $value;
		$this->template->assign_block_vars('bool', array_change_key_case($profile_row, CASE_UPPER));

		if ($profile_row['field_length'] == 1)
		{
			if (!$this->lang_helper->is_set($profile_row['field_id'], $profile_row['lang_id'], 1))
			{
				if ($preview_options)
				{
					$this->lang_helper->load_preview_options($profile_row['field_id'], $profile_row['lang_id'], $preview_options);
				}
				else
				{
					$this->lang_helper->load_option_lang($profile_row['lang_id']);
				}
			}

			$options = $this->lang_helper->get($profile_row['field_id'], $profile_row['lang_id']);
			foreach ($options as $option_id => $option_value)
			{
				$this->template->assign_block_vars('bool.options', array(
					'OPTION_ID'	=> $option_id,
					'CHECKED'	=> ($value == $option_id) ? ' checked="checked"' : '',
					'VALUE'		=> $option_value,
				));
			}
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function get_field_ident($field_data)
	{
		return ($field_data['field_length'] == '1') ? '' : 'pf_' . $field_data['field_ident'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'TINT:2';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_language_options($field_data)
	{
		$options = array(
			'lang_name'		=> 'string',
			'lang_options'	=> 'two_options',
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
	public function get_language_options_input($field_data)
	{
		$field_data['l_lang_name']			= $this->request->variable('l_lang_name', array(0 => ''), true);
		$field_data['l_lang_explain']		= $this->request->variable('l_lang_explain', array(0 => ''), true);
		$field_data['l_lang_default_value']	= $this->request->variable('l_lang_default_value', array(0 => ''), true);

		/**
		* @todo check if this line is correct...
		$field_data['l_lang_default_value']	= $this->request->variable('l_lang_default_value', array(0 => array('')), true);
		*/
		$field_data['l_lang_options']	= $this->request->variable('l_lang_options', array(0 => array('')), true);

		return $field_data;
	}

	/**
	* {@inheritDoc}
	*/
	public function prepare_options_form(&$exclude_options, &$visibility_options)
	{
		$exclude_options[1][] = 'lang_options';

		return $this->request->variable('lang_options', array(''), true);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_options_on_submit($error, $field_data)
	{
		if (empty($field_data['lang_options'][0]) || empty($field_data['lang_options'][1]))
		{
			$error[] = $this->user->lang['NO_FIELD_ENTRIES'];
		}

		return $error;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_excluded_options($key, $action, $current_value, &$field_data, $step)
	{
		if ($step == 2 && $key == 'field_default_value')
		{
			// 'field_length' == 1 defines radio buttons. Possible values are 1 or 2 only.
			// 'field_length' == 2 defines checkbox. Possible values are 0 or 1 only.
			// If we switch the type on step 2, we have to adjust field value.
			// 1 is a common value for the checkbox and radio buttons.

			// Adjust unchecked checkbox value.
			// If we return or save settings from 2nd/3rd page
			// and the checkbox is unchecked, set the value to 0.
			if ($this->request->is_set('step') && !$this->request->is_set($key))
			{
				return 0;
			}

			// If we switch to the checkbox type but former radio buttons value was 2,
			// which is not the case for the checkbox, set it to 0 (unchecked).
			if ($field_data['field_length'] == 2 && $current_value == 2)
			{
				return 0;
			}
			// If we switch to the radio buttons but the former checkbox value was 0,
			// which is not the case for the radio buttons, set it to 0.
			else if ($field_data['field_length'] == 1 && $current_value == 0)
			{
				return 2;
			}
		}

		if ($key == 'l_lang_options' && $this->request->is_set($key))
		{
			$field_data[$key] = $this->request->variable($key, array(0 => array('')), true);

			return $current_value;
		}

		return parent::get_excluded_options($key, $action, $current_value, $field_data, $step);
	}

	/**
	* {@inheritDoc}
	*/
	public function prepare_hidden_fields($step, $key, $action, &$field_data)
	{
		if ($key == 'field_default_value')
		{
			$field_length = $this->request->variable('field_length', 0);

			// Do a simple is set check if using checkbox.
			if ($field_length == 2)
			{
				return $this->request->is_set($key);
			}
			return $this->request->variable($key, $field_data[$key], true);
		}

		$default_lang_options = array(
			'l_lang_options'	=> array(0 => array('')),
			'lang_options'		=> array(0 => ''),
		);

		if (isset($default_lang_options[$key]) && $this->request->is_set($key))
		{
			return $this->request->variable($key, $default_lang_options[$key], true);
		}

		return parent::prepare_hidden_fields($step, $key, $action, $field_data);
	}

	/**
	* {@inheritDoc}
	*/
	public function display_options(&$template_vars, &$field_data)
	{
		// Initialize these array elements if we are creating a new field
		if (!count($field_data['lang_options']))
		{
			// No options have been defined for a boolean field.
			$field_data['lang_options'][0] = '';
			$field_data['lang_options'][1] = '';
		}

		$template_vars = array_merge($template_vars, array(
			'S_BOOL'					=> true,
			'L_LANG_OPTIONS_EXPLAIN'	=> $this->user->lang['BOOL_ENTRIES_EXPLAIN'],
			'FIRST_LANG_OPTION'			=> $field_data['lang_options'][0],
			'SECOND_LANG_OPTION'		=> $field_data['lang_options'][1],
		));
	}
}
