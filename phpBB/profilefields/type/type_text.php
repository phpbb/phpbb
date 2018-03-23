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

class type_text extends type_string_common
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
		return 'text';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="rows" value="' . $field_data['rows'] . '" /> ' . $this->user->lang['ROWS'] . '</dd><dd><input type="number" min="0" max="99999" name="columns" value="' . $field_data['columns'] . '" /> ' . $this->user->lang['COLUMNS'] . ' <input type="hidden" name="field_length" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_minlen" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_maxlen" value="' . $field_data['field_maxlen'] . '" />'),
			3 => array('TITLE' => $this->user->lang['FIELD_VALIDATION'],	'FIELD' => '<select name="field_validation">' . $this->validate_options($field_data) . '</select>'),
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> '5|80',
			'field_minlen'		=> 0,
			'field_maxlen'		=> 1000,
			'field_validation'	=> '.*',
			'field_novalue'		=> '',
			'field_default_value'	=> '',
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];
		return $this->request->variable($var_name, (string) $profile_row['field_default_value'], true);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		return $this->validate_string_profile_field('text', $field_value, $field_data);
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$field_length = explode('|', $profile_row['field_length']);
		$profile_row['field_rows'] = $field_length[0];
		$profile_row['field_cols'] = $field_length[1];
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];
		$default_value = $profile_row['lang_default_value'];

		$profile_row['field_value'] = ($this->request->is_set($field_ident)) ? $this->request->variable($field_ident, $default_value, true) : ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $default_value : $this->user->profile_fields[$field_ident]);

		$this->template->assign_block_vars('text', array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'MTEXT';
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

		if (strlen($field_data['lang_default_value']))
		{
			$options['lang_default_value'] = 'text';
		}

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_excluded_options($key, $action, $current_value, &$field_data, $step)
	{
		if ($step == 2 && $key == 'field_length')
		{
			if ($this->request->is_set('rows'))
			{
				$field_data['rows'] = $this->request->variable('rows', 0);
				$field_data['columns'] = $this->request->variable('columns', 0);
				$current_value = $field_data['rows'] . '|' . $field_data['columns'];
			}
			else
			{
				$row_col = explode('|', $current_value);
				$field_data['rows'] = $row_col[0];
				$field_data['columns'] = $row_col[1];
			}

			return $current_value;
		}

		return parent::get_excluded_options($key, $action, $current_value, $field_data, $step);
	}

	/**
	* {@inheritDoc}
	*/
	public function prepare_hidden_fields($step, $key, $action, &$field_data)
	{
		if ($key == 'field_length' &&  $this->request->is_set('rows'))
		{
			$field_data['rows'] = $this->request->variable('rows', 0);
			$field_data['columns'] = $this->request->variable('columns', 0);
			return $field_data['rows'] . '|' . $field_data['columns'];
		}

		return parent::prepare_hidden_fields($step, $key, $action, $field_data);
	}

	/**
	* {@inheritDoc}
	*/
	public function display_options(&$template_vars, &$field_data)
	{
		$template_vars = array_merge($template_vars, array(
			'S_TEXT'					=> true,
			'L_DEFAULT_VALUE_EXPLAIN'	=> $this->user->lang['TEXT_DEFAULT_VALUE_EXPLAIN'],
			'LANG_DEFAULT_VALUE'		=> $field_data['lang_default_value'],
		));
	}
}
