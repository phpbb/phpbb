<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : functions_profile_fields.php
// STARTED   : Tue Oct 21, 2003
// COPYRIGHT : © 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class custom_profile
{
	var $profile_types = array(1 => 'int', 2 => 'string', 3 => 'text', 4 => 'bool', 5 => 'dropdown', 6 => 'date');
	var $options_lang = array();

	// Functions performing operations on register/profile/profile admin
	function submit_cp_field($mode, &$cp_data, &$cp_error)
	{
		global $auth, $db;

		$sql = 'SELECT *
			FROM phpbb_profile_fields
			WHERE field_active = 1
				' . (($mode == 'register') ? ' AND field_show_on_reg = 1' : '') .
				(($auth->acl_gets('a_', 'm_') && $mode == 'profile') ? '' : ' AND field_hide = 0') . '
			ORDER BY field_order';
		$result = $db->sql_query($sql);
					
		while ($row = $db->sql_fetchrow($result))
		{
			$cp_data[$row['field_ident']] = $this->get_profile_field($row);
			if (($cp_result = $this->validate_profile_field($row['field_type'], $cp_data[$row['field_ident']], $row)) !== false)
			{
				$cp_error[$row['field_ident']] = $cp_result;
			}
		}
		$db->sql_freeresult($result);
	}
	
	function generate_profile_fields($mode, $lang_id, $cp_error)
	{
		global $db, $template, $auth;

		$sql = "SELECT l.*, f.*
			FROM phpbb_profile_lang l, phpbb_profile_fields f 
			WHERE l.lang_id = $lang_id
				AND f.field_active = 1
				" . (($mode == 'register') ? ' AND f.field_show_on_reg = 1' : '') .
				(($auth->acl_gets('a_', 'm_') && $mode == 'profile') ? '' : ' AND f.field_hide = 0') . '
				AND l.field_id = f.field_id 
			GROUP BY f.field_id
			ORDER BY f.field_order';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$template->assign_block_vars('profile_fields', array(
				'LANG_NAME' => $row['lang_name'],
				'LANG_EXPLAIN' => $row['lang_explain'],
				'FIELD' => $this->process_field_row('change', $row),
				'ERROR' => (isset($cp_error[$row['field_ident']])) ? $cp_error[$row['field_ident']] : '')
			);
		}
		$db->sql_freeresult($result);
	}

	// Get language entries for options and store them here for later use
	function get_option_lang($field_id, $lang_id, $field_type, $preview)
	{
		global $db;

		if ($preview)
		{
			$lang_options = (!is_array($this->vars['lang_options'])) ? explode("\n", $this->vars['lang_options']) : $this->vars['lang_options'];
			
			foreach ($lang_options as $num => $var)
			{
				$this->options_lang[$field_id][$lang_id][($num+1)] = $var;
			}
		}
		else
		{
			$sql = "SELECT option_id, value
				FROM phpbb_profile_fields_lang
					WHERE field_id = $field_id
					AND lang_id = $lang_id
					AND field_type = $field_type
				ORDER BY option_id";
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$this->options_lang[$field_id][$lang_id][$row['option_id']] = $row['value'];
			}
			$db->sql_freeresult($result);
		}
	}

	// VALIDATE Function - validate entered data
	function validate_profile_field($field_type, &$field_value, $field_data)
	{
		switch ($field_type)
		{
			case FIELD_INT:
			case FIELD_DROPDOWN:
				$field_value = (int) $field_value;
				break;

			case FIELD_BOOL:
				$field_value = (bool) $field_value;
				break;
		}

		switch ($field_type)
		{
			case FIELD_DATE:
				$field_validate = explode('-', $field_value);
				
				$day = (int) $field_validate[0];
				$month = (int) $field_validate[1];
				$year = (int) $field_validate[2];

				if ((!$day || !$month || !$year) && !$field_data['field_required'])
				{
					return false;
				}

				if ((!$day || !$month || !$year) && $field_data['field_required'])
				{
					return 'FIELD_REQUIRED';
				}

				if ($day < 0 || $day > 31 || $month < 0 || $month > 12 || ($year < 1901 && $year > 0) || $year > gmdate('Y', time()))
				{
					return 'INVALID_DATE';
				}
				break;

			case FIELD_INT:
				if (empty($field_value) && !$field_data['field_required'])
				{
					return false;
				}

				if ($field_value < $field_data['field_minlen'])
				{
					return 'TOO_SMALL';
				}
				else if ($field_value > $field_data['field_maxlen']) 
				{
					return 'TOO_LARGE';
				}
				break;
		
			case FIELD_DROPDOWN:
				if ($field_value == $field_data['field_novalue'] && $field_data['field_required'])
				{
					return 'FIELD_REQUIRED';
				}
				break;
			
			case FIELD_STRING:
			case FIELD_TEXT:
				if (empty($field_value) && !$field_data['field_required'])
				{
					return false;
				}

				if ($field_data['field_minlen'] && strlen($field_value) < $field_data['field_minlen'])
				{
					return 'TOO_SHORT';
				}
				else if ($field_data['field_maxlen'] && strlen($field_value) > $field_data['field_maxlen'])
				{
					return 'TOO_LONG';
				}

				if (!empty($field_data['field_validation']))
				{
					$field_validate = ($field_type == FIELD_STRING) ? $field_value : str_replace("\n", ' ', $field_value);
					if (!preg_match('#^' . str_replace('\\\\', '\\', $field_data['field_validation']) . '$#i', $field_validate))
					{
						return 'INVALID_CHARS';
					}
				}
				break;
		}

		return false;
	}

	function get_profile_value($field_validation, &$profile_row, $default_value, $preview)
	{
		global $user;

		$profile_row['field_name'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];

		$value = (isset($_REQUEST[$profile_row['field_name']])) ? request_var($profile_row['field_name'], 0) : ((!isset($user->profile_fields[$profile_row['field_ident']]) || $preview) ? $default_value : $user->profile_fields[$profile_row['field_ident']]);

		switch ($field_validation)
		{
			case 'int':
				return (int) $value;
				break;
		}

		return $value;
	}
	
	// GENERATE_* Functions - return templated, storable profile fields
	function generate_int($profile_row, $preview = false)
	{
		global $template;

		$value = $this->get_profile_value('int', $profile_row, $profile_row['field_default_value'], $preview);
		$this->set_tpl_vars($profile_row, $value);

		return $this->get_cp_html();
	}

	function generate_date($profile_row, $preview = false)
	{
		global $user;

		$profile_row['field_name'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$now = getdate();

		if (!isset($_REQUEST[$profile_row['field_name'] . '_day']))
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', ((!isset($user->profile_fields[$profile_row['field_ident']]) || $preview) ? $profile_row['field_default_value'] : $user->profile_fields[$profile_row['field_ident']]));
		}
		else
		{
			if ($preview && $profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
				list($day, $month, $year) = explode('-', ((!isset($user->profile_fields[$profile_row['field_ident']]) || $preview) ? $profile_row['field_default_value'] : $user->profile_fields[$profile_row['field_ident']]));
			}
			else
			{
				$day = request_var($profile_row['field_name'] . '_day', 0);
				$month = request_var($profile_row['field_name'] . '_month', 0);
				$year = request_var($profile_row['field_name'] . '_year', 0);
			}
		}

		$profile_row['s_day_options'] = '<option value="0"' . ((!$day) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$profile_row['s_day_options'] .= '<option value="' . $i . '"' . (($i == $day) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['s_month_options'] = '<option value="0"' . ((!$month) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$profile_row['s_month_options'] .= '<option value="' . $i . '"' . (($i == $month) ? ' selected="selected"' : '') . ">$i</option>";
		}

		$profile_row['s_year_options'] = '<option value="0"' . ((!$year) ? ' selected="selected"' : '') . '>--</option>';
		for ($i = $now['year'] - 100; $i <= $now['year']; $i++)
		{
			$profile_row['s_year_options'] .= '<option value="' . $i . '"' . (($i == $year) ? ' selected="selected"' : '') . ">$i</option>";
		}
		unset($now);
		
		$this->set_tpl_vars($profile_row, $value);
		return $this->get_cp_html();
	}

	function generate_bool($profile_row, $preview = false)
	{
		global $template;

		$value = $this->get_profile_value('int', $profile_row, $profile_row['field_default_value'], $preview);
		$this->set_tpl_vars($profile_row, $value);

		if ($profile_row['field_length'] == 1)
		{
			if (!sizeof($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
			{
				$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_BOOL, $preview);
			}

			foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
			{
				$template->assign_block_vars('bool.options', array(
					'OPTION_ID' => $option_id,
					'CHECKED' => ($value == $option_id) ? ' checked="checked"' : '',
					'VALUE' => $option_value)
				);
			}
		}

		return $this->get_cp_html();
	}

	function generate_string($profile_row, $preview = false)
	{
		global $template;

		$value = $this->get_profile_value('', $profile_row, $profile_row['lang_default_value'], $preview);
		$this->set_tpl_vars($profile_row, $value);

		return $this->get_cp_html();
	}

	function generate_text($profile_row, $preview = false)
	{
		global $template;

		$value = $this->get_profile_value('', $profile_row, $profile_row['lang_default_value'], $preview);

		$field_length = explode('|', $profile_row['field_length']);
		$profile_row['field_rows'] = $field_length[0];
		$profile_row['field_cols'] = $field_length[1];

		$this->set_tpl_vars($profile_row, $value);

		return $this->get_cp_html();
	}

	function generate_dropdown($profile_row, $preview = false)
	{
		global $user, $template;

		$value = $this->get_profile_value('int', $profile_row, $profile_row['field_default_value'], $preview);

		if (!sizeof($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
		{
			$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_DROPDOWN, $preview);
		}

		$this->set_tpl_vars($profile_row, $value);

		foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
		{
			$template->assign_block_vars('dropdown.options', array(
				'OPTION_ID' => $option_id,
				'SELECTED' => ($value == $option_id) ? ' selected="selected"' : '',
				'VALUE' => $option_value)
			);
		}

		return $this->get_cp_html();
	}


	// Return Templated value (change == user is able to set/enter profile values; show == just show the value)
	function process_field_row($mode, $profile_row)
	{
		$preview = false;

		switch ($mode)
		{
			case 'preview':
				$preview = true;
			case 'change':
				$type_func = 'generate_' . $this->profile_types[$profile_row['field_type']];
				break;
			default:
				return;
		}

		return $this->$type_func($profile_row, $preview);
	}

	// Build Array for user insertion into custom profile fields table
	function build_insert_sql_array($cp_data)
	{
		global $db, $user, $auth;

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM phpbb_profile_lang l, phpbb_profile_fields f 
			WHERE l.lang_id = ' . $user->get_iso_lang_id() . ' 
				AND f.field_active = 1
				AND f.field_show_on_reg = 0
				' . (($auth->acl_gets('a_', 'm_')) ? '' : ' AND f.field_hide = 0') . '
				AND l.field_id = f.field_id 
			GROUP BY f.field_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['field_default_value'] == 'now' && $row['field_type'] == FIELD_DATE)
			{
				$now = getdate();
				$row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			$cp_data[$row['field_ident']] = (in_array($row['field_type'], array(FIELD_TEXT, FIELD_STRING))) ? $row['lang_default_value'] : $row['field_default_value'];
		}
		$db->sql_freeresult($result);
		
		return $cp_data;
	}

	function get_profile_field($profile_row)
	{
		switch ($profile_row['field_type'])
		{
			case FIELD_DATE:
				$var_name = 'pf_' . $profile_row['field_ident'];	

				if (!isset($_REQUEST[$var_name . '_day']))
				{
					if ($profile_row['field_default_value'] == 'now')
					{
						$now = getdate();
						$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
					}
					list($day, $month, $year) = explode('-', $profile_row['field_default_value']);
				}
				else
				{
					$day = request_var($var_name . '_day', 0);
					$month = request_var($var_name . '_month', 0);
					$year = request_var($var_name . '_year', 0);
				}
				
				$var = sprintf('%2d-%2d-%4d', $day, $month, $year);
				break;

			default:
				$var = request_var('pf_' . $profile_row['field_ident'], $profile_row['field_default_value']);
		}

		return $var;
	}

	function set_tpl_vars($profile_row, $field_value)
	{
		global $template;
		
		$template->set_filenames(array(
			'cp' => 'custom_profile_fields.html')
		);

		foreach ($this->profile_types as $field_case => $field_type)
		{
			unset($template->_tpldata[$field_type]);
		}

		foreach ($profile_row as $key => $value)
		{
			unset($profile_row[$key]);
			$profile_row[strtoupper($key)] = $value;
		}

		$profile_row['FIELD_VALUE'] = $field_value;

		$template->assign_block_vars($this->profile_types[$profile_row['FIELD_TYPE']], $profile_row);
	}

	function get_cp_html()
	{
		global $template;

		ob_start();
		$template->display('cp', false);
		$data = ob_get_contents();
		ob_end_clean();

		return $data;
	}
}

class custom_profile_admin extends custom_profile
{
	var $vars = array();


	// GET_* get admin options for second step
	function get_string_options()
	{
		global $user;

		$options = array();

		$validate_ary = array('CHARS_ANY' => '.*', 'ALPHA_ONLY' => '[\w]+', 'ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');
		$validate_options = '';
		foreach ($validate_ary as $lang => $value)
		{
			$selected = ($this->vars['field_validation'] == $value) ? ' selected="selected"' : '';
			$validate_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		$options = array(
			0 => array( 
				'L_NAME' => 'Length of input box',
				'FIELD' => '<input class="post" type="text" name="field_length" size="5" value="' . $this->vars['field_length'] . '" />'),
			1 => array(
				'L_NAME' => 'Minimum number of characters entered',
				'FIELD' => '<input class="post" type="text" name="field_minlen" size="5" value="' . $this->vars['field_minlen'] . '" />'),
			2 => array(
				'L_NAME' => 'Maximum number of characters entered',
				'FIELD' => '<input class="post" type="text" name="field_maxlen" size="5" value="' . $this->vars['field_maxlen'] . '" />'),
			3 => array(
				'L_NAME' => 'Field Validation',
				'FIELD' => '<select name="field_validation">' . $validate_options . '</select>'),
		);

		return $options;
	}

	function get_text_options()
	{
		global $user;

		$options = array();

		$validate_ary = array('CHARS_ANY' => '.*', 'ALPHA_ONLY' => '[\w]+', 'ALPHA_SPACERS' => '[\w_\+\. \-\[\]]+');
		$validate_options = '';
		foreach ($validate_ary as $lang => $value)
		{
			$selected = ($this->vars['field_validation'] == $value) ? ' selected="selected"' : '';
			$validate_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang[$lang] . '</option>';
		}

		$options = array(
			0 => array(
				'L_NAME' => 'Field Length',
				'FIELD' => '<table border=0><tr><td><input name="rows" size="5" value="' . $this->vars['rows'] . '" class="post" /></td><td>[ ' . $user->lang['ROWS'] . ' ]</td></tr><tr><td><input name="columns" size="5" value="' . $this->vars['columns'] . '" class="post" /></td><td>[ ' . $user->lang['COLUMNS'] . ' ] <input type="hidden" name="field_length" value="' . $this->vars['field_length'] . '" /></td></tr></table>'),
			1 => array(
				'L_NAME' => 'Minimum number of characters entered',
				'FIELD' => '<input class="post" type="text" name="field_minlen" size="10" value="' . $this->vars['field_minlen'] . '" />'),
			2 => array(
				'L_NAME' => 'Maximum number of characters entered',
				'FIELD' => '<input class="post" type="text" name="field_maxlen" size="10" value="' . $this->vars['field_maxlen'] . '" />'),
			3 => array(
				'L_NAME' => 'Field Validation',
				'L_EXPLAIN' => '',
				'FIELD' => '<select name="field_validation">' . $validate_options . '</select>'),
		);

		return $options;
	}

	function get_int_options()
	{
		global $user;

		$options = array(
			0 => array(
				'L_NAME' => 'Length of input box',
				'FIELD' => '<input class="post" type="text" name="field_length" size="5" value="' . $this->vars['field_length'] . '" />'),
			1 => array(
				'L_NAME' => 'Lowest allowed number',
				'FIELD' => '<input class="post" type="text" name="field_minlen" size="5" value="' . $this->vars['field_minlen'] . '" />'),
			2 => array(
				'L_NAME' => 'highest allowed number',
				'FIELD' => '<input class="post" type="text" name="field_maxlen" size="5" value="' . $this->vars['field_maxlen'] . '" />'),
			3 => array(
				'L_NAME' => 'Default Value',
				'FIELD' => '<input class="post" type="post" name="field_default_value" value="' . $this->vars['field_default_value'] . '" />'),
		);

		return $options;
	}

	function get_bool_options()
	{
		global $user, $config, $db;

		$result = $db->sql_query("SELECT lang_id FROM phpbb_lang WHERE lang_iso = '" . $config['default_lang'] . "'");
		$default_lang_id = (int) $db->sql_fetchfield('lang_id', 0, $result);
		$db->sql_freeresult($result);

		$profile_row = array(
			'var_name' => 'field_default_value',
			'lang_name' => $this->vars['lang_name'],
			'lang_explain' => $this->vars['lang_explain'],
			'lang_id' => $default_lang_id,
			'field_default_value' => $this->vars['field_default_value'],
			'field_ident' => 'field_default_value',
			'field_type' => FIELD_BOOL,
			'field_length' => $this->vars['field_length'],
			'lang_options' => $this->vars['lang_options']
		);


		$options = array(
			0 => array(
				'L_NAME' => 'Field Type',
				'L_EXPLAIN' => 'Define the Type, either a checkbox or radio buttons',
				'FIELD' => '<input type="radio" name="field_length" value="1"' . (($this->vars['field_length'] == 1) ? ' checked="checked"' : '') . ' />Radio Buttons&nbsp; &nbsp;<input type="radio" name="field_length" value="2"' . (($this->vars['field_length'] == 2) ? ' checked="checked"' : '') . ' />Checkbox&nbsp; &nbsp;'),
			1 => array(
				'L_NAME' => 'Default Value',
				'FIELD' => $this->generate_bool($profile_row, true)),
		);

		return $options;
	}

	function get_dropdown_options()
	{
		global $user, $config, $db;

		$result = $db->sql_query("SELECT lang_id FROM phpbb_lang WHERE lang_iso = '" . $config['default_lang'] . "'");
		$default_lang_id = (int) $db->sql_fetchfield('lang_id', 0, $result);
		$db->sql_freeresult($result);

		$profile_row[0] = array(
			'var_name' => 'field_default_value',
			'field_id' => 1,
			'lang_name' => $this->vars['lang_name'],
			'lang_explain' => $this->vars['lang_explain'],
			'lang_id' => $default_lang_id,
			'field_default_value' => $this->vars['field_default_value'],
			'field_ident' => 'field_default_value',
			'field_type' => FIELD_DROPDOWN,
			'lang_options' => $this->vars['lang_options']
		);

		$profile_row[1] = $profile_row[0];
		$profile_row[1]['var_name'] = 'field_no_value';
		$profile_row[1]['field_ident'] = 'field_no_value';

		$options = array(
			0 => array(
				'L_NAME' => 'Default Value',
				'FIELD' => $this->generate_dropdown($profile_row[0], true)),
			1 => array(
				'L_NAME' => 'No Value',
				'L_EXPLAIN' => 'Value for a non-entry. If the field is required, the user gets an error if he choose the option selected here',
				'FIELD' => $this->generate_dropdown($profile_row[1], true)),
		);

		return $options;
	}

	function get_date_options()
	{
		global $user, $config, $db;

		$result = $db->sql_query("SELECT lang_id FROM phpbb_lang WHERE lang_iso = '" . $config['default_lang'] . "'");
		$default_lang_id = (int) $db->sql_fetchfield('lang_id', 0, $result);
		$db->sql_freeresult($result);

		$profile_row = array(
			'var_name' => 'field_default_value',
			'lang_name' => $this->vars['lang_name'],
			'lang_explain' => $this->vars['lang_explain'],
			'lang_id' => $default_lang_id,
			'field_default_value' => $this->vars['field_default_value'],
			'field_ident' => 'field_default_value',
			'field_type' => FIELD_DATE,
			'field_length' => $this->vars['field_length']
		);

		$options = array(
			0 => array(
				'L_NAME' => 'Default Value',
				'FIELD' => $this->generate_date($profile_row, true) . '<br /><input type="checkbox" name="always_now"' . ((isset($_REQUEST['always_now']) || $this->vars['field_default_value'] == 'now') ? ' checked="checked"' : '') . ' />&nbsp; Always the current date'),
		);

		return $options;
	}
}

?>