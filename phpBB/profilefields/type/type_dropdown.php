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

class type_dropdown extends type_base
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
		return 'dropdown';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$profile_row[0] = array(
			'var_name'				=> 'field_default_value',
			'field_id'				=> 1,
			'lang_name'				=> $field_data['lang_name'],
			'lang_explain'			=> $field_data['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $field_data['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> $this->get_service_name(),
			'lang_options'			=> $field_data['lang_options'],
		);

		$profile_row[1] = $profile_row[0];
		$profile_row[1]['var_name'] = 'field_novalue';
		$profile_row[1]['field_ident'] = 'field_novalue';
		$profile_row[1]['field_default_value']	= $field_data['field_novalue'];

		$options = array(
			0 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'], 'FIELD' => $this->process_field_row('preview', $profile_row[0])),
			1 => array('TITLE' => $this->user->lang['NO_VALUE_OPTION'], 'EXPLAIN' => $this->user->lang['NO_VALUE_OPTION_EXPLAIN'], 'FIELD' => $this->process_field_row('preview', $profile_row[1])),
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 0,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 5,
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
		return $this->request->variable($var_name, (int) $profile_row['field_default_value']);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		$field_value = (int) $field_value;

		// retrieve option lang data if necessary
		if (!$this->lang_helper->is_set($field_data['field_id'], $field_data['lang_id'], 1))
		{
			$this->lang_helper->load_option_lang($field_data['lang_id']);
		}

		if (!$this->lang_helper->is_set($field_data['field_id'], $field_data['lang_id'], $field_value))
		{
			return $this->user->lang('FIELD_INVALID_VALUE', $this->get_field_name($field_data['lang_name']));
		}

		if ($field_value == $field_data['field_novalue'] && $field_data['field_required'])
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

		if ($field_value == $field_data['field_novalue'] && !$field_data['field_show_novalue'])
		{
			return null;
		}

		$field_value = (int) $field_value;

		// User not having a value assigned
		if (!$this->lang_helper->is_set($field_id, $lang_id, $field_value))
		{
			if ($field_data['field_show_novalue'])
			{
				$field_value = $field_data['field_novalue'];
			}
			else
			{
				return null;
			}
		}

		return $this->lang_helper->get($field_id, $lang_id, $field_value);
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

		$value = ($this->request->is_set($field_ident)) ? $this->request->variable($field_ident, $default_value) : ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $default_value : $this->user->profile_fields[$field_ident]);

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

		$profile_row['field_value'] = (int) $value;
		$this->template->assign_block_vars('dropdown', array_change_key_case($profile_row, CASE_UPPER));

		$options = $this->lang_helper->get($profile_row['field_id'], $profile_row['lang_id']);
		foreach ($options as $option_id => $option_value)
		{
			$this->template->assign_block_vars('dropdown.options', array(
				'OPTION_ID'	=> $option_id,
				'SELECTED'	=> ($value == $option_id) ? ' selected="selected"' : '',
				'VALUE'		=> $option_value,
			));
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'UINT';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_language_options($field_data)
	{
		$options = array(
			'lang_name'		=> 'string',
			'lang_options'	=> 'optionfield',
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
	public function prepare_options_form(&$exclude_options, &$visibility_options)
	{
		$exclude_options[1][] = 'lang_options';

		return $this->request->variable('lang_options', '', true);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_options_on_submit($error, $field_data)
	{
		if (!count($field_data['lang_options']))
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
		if ($step == 2 && $key == 'field_maxlen')
		{
			// Get the number of options if this key is 'field_maxlen'
			return count(explode("\n", $this->request->variable('lang_options', '', true)));
		}

		return parent::get_excluded_options($key, $action, $current_value, $field_data, $step);
	}

	/**
	* {@inheritDoc}
	*/
	public function display_options(&$template_vars, &$field_data)
	{
		// Initialize these array elements if we are creating a new field
		if (!count($field_data['lang_options']))
		{
			// No options have been defined for the dropdown menu
			$field_data['lang_options'] = array();
		}

		$template_vars = array_merge($template_vars, array(
			'S_DROPDOWN'				=> true,
			'L_LANG_OPTIONS_EXPLAIN'	=> $this->user->lang['DROPDOWN_ENTRIES_EXPLAIN'],
			'LANG_OPTIONS'				=> implode("\n", $field_data['lang_options']),
		));
	}
}
