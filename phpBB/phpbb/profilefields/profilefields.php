<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields;

/**
* Custom Profile Fields
* @package phpBB3
*/
class profilefields
{
	var $profile_types = array(FIELD_INT => 'int', FIELD_STRING => 'string', FIELD_TEXT => 'text', FIELD_BOOL => 'bool', FIELD_DROPDOWN => 'dropdown', FIELD_DATE => 'date');
	var $profile_cache = array();
	var $options_lang = array();

	/**
	*
	*/
	public function __construct($auth, $db, /** @todo: */ $phpbb_container, $request, $template, $user)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->container = $phpbb_container;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* Assign editable fields to template, mode can be profile (for profile change) or register (for registration)
	* Called by ucp_profile and ucp_register
	* @access public
	*/
	function generate_profile_fields($mode, $lang_id)
	{
		$sql_where = '';
		switch ($mode)
		{
			case 'register':
				// If the field is required we show it on the registration page
				$sql_where .= ' AND f.field_show_on_reg = 1';
			break;

			case 'profile':
				// Show hidden fields to moderators/admins
				if (!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_'))
				{
					$sql_where .= ' AND f.field_show_profile = 1';
				}
			break;

			default:
				trigger_error('Wrong profile mode specified', E_USER_ERROR);
			break;
		}

		$sql = 'SELECT l.*, f.*
			FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . " f
			WHERE f.field_active = 1
				$sql_where
				AND l.lang_id = $lang_id
				AND l.field_id = f.field_id
			ORDER BY f.field_order";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Return templated field
			$tpl_snippet = $this->process_field_row('change', $row);

			// Some types are multivalue, we can't give them a field_id as we would not know which to pick
			$type = (int) $row['field_type'];

			$this->template->assign_block_vars('profile_fields', array(
				'LANG_NAME'		=> $row['lang_name'],
				'LANG_EXPLAIN'	=> $row['lang_explain'],
				'FIELD'			=> $tpl_snippet,
				'FIELD_ID'		=> ($type == FIELD_DATE || ($type == FIELD_BOOL && $row['field_length'] == '1')) ? '' : 'pf_' . $row['field_ident'],
				'S_REQUIRED'	=> ($row['field_required']) ? true : false)
			);
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Build profile cache, used for display
	* @access private
	*/
	function build_cache()
	{
		$this->profile_cache = array();

		// Display hidden/no_view fields for admin/moderator
		$sql = 'SELECT l.*, f.*
			FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . ' f
			WHERE l.lang_id = ' . $this->user->get_iso_lang_id() . '
				AND f.field_active = 1 ' .
				((!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_')) ? '	AND f.field_hide = 0 ' : '') . '
				AND f.field_no_view = 0
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->profile_cache[$row['field_ident']] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Get language entries for options and store them here for later use
	*/
	function get_option_lang($field_id, $lang_id, $field_type, $preview)
	{
		if ($preview)
		{
			$lang_options = (!is_array($this->vars['lang_options'])) ? explode("\n", $this->vars['lang_options']) : $this->vars['lang_options'];

			foreach ($lang_options as $num => $var)
			{
				$this->options_lang[$field_id][$lang_id][($num + 1)] = $var;
			}
		}
		else
		{
			$sql = 'SELECT option_id, lang_value
				FROM ' . PROFILE_FIELDS_LANG_TABLE . "
					WHERE field_id = $field_id
					AND lang_id = $lang_id
					AND field_type = $field_type
				ORDER BY option_id";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->options_lang[$field_id][$lang_id][($row['option_id'] + 1)] = $row['lang_value'];
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	* Submit profile field for validation
	* @access public
	*/
	function submit_cp_field($mode, $lang_id, &$cp_data, &$cp_error)
	{
		$sql_where = '';
		switch ($mode)
		{
			case 'register':
				// If the field is required we show it on the registration page
				$sql_where .= ' AND f.field_show_on_reg = 1';
			break;

			case 'profile':
				// Show hidden fields to moderators/admins
				if (!$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_'))
				{
					$sql_where .= ' AND f.field_show_profile = 1';
				}
			break;

			default:
				trigger_error('Wrong profile mode specified', E_USER_ERROR);
			break;
		}

		$sql = 'SELECT l.*, f.*
			FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . " f
			WHERE l.lang_id = $lang_id
				AND f.field_active = 1
				$sql_where
				AND l.field_id = f.field_id
			ORDER BY f.field_order";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$profile_field = $this->container->get('profilefields.type.' . $this->profile_types[$row['field_type']]);
			$cp_data['pf_' . $row['field_ident']] = $profile_field->get_profile_field($row);
			$check_value = $cp_data['pf_' . $row['field_ident']];

			if (($cp_result = $profile_field->validate_profile_field($check_value, $row)) !== false)
			{
				// If not and only showing common error messages, use this one
				$error = '';
				switch ($cp_result)
				{
					case 'FIELD_INVALID_DATE':
					case 'FIELD_INVALID_VALUE':
					case 'FIELD_REQUIRED':
						$error = $this->user->lang($cp_result, $row['lang_name']);
					break;

					case 'FIELD_TOO_SHORT':
					case 'FIELD_TOO_SMALL':
						$error = $this->user->lang($cp_result, (int) $row['field_minlen'], $row['lang_name']);
					break;

					case 'FIELD_TOO_LONG':
					case 'FIELD_TOO_LARGE':
						$error = $this->user->lang($cp_result, (int) $row['field_maxlen'], $row['lang_name']);
					break;

					case 'FIELD_INVALID_CHARS':
						switch ($row['field_validation'])
						{
							case '[0-9]+':
								$error = $this->user->lang($cp_result . '_NUMBERS_ONLY', $row['lang_name']);
							break;

							case '[\w]+':
								$error = $this->user->lang($cp_result . '_ALPHA_ONLY', $row['lang_name']);
							break;

							case '[\w_\+\. \-\[\]]+':
								$error = $this->user->lang($cp_result . '_SPACERS_ONLY', $row['lang_name']);
							break;
						}
					break;
				}

				if ($error != '')
				{
					$cp_error[] = $error;
				}
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Update profile field data directly
	*/
	function update_profile_field_data($user_id, &$cp_data)
	{
		if (!sizeof($cp_data))
		{
			return;
		}

		switch ($this->db->sql_layer)
		{
			case 'oracle':
			case 'firebird':
			case 'postgres':
				$right_delim = $left_delim = '"';
			break;

			case 'sqlite':
			case 'mssql':
			case 'mssql_odbc':
			case 'mssqlnative':
				$right_delim = ']';
				$left_delim = '[';
			break;

			case 'mysql':
			case 'mysql4':
			case 'mysqli':
				$right_delim = $left_delim = '`';
			break;
		}

		// use new array for the UPDATE; changes in the key do not affect the original array
		$cp_data_sql = array();
		foreach ($cp_data as $key => $value)
		{
			// Firebird is case sensitive with delimiter
			$cp_data_sql[$left_delim . (($this->db->sql_layer == 'firebird' || $this->db->sql_layer == 'oracle') ? strtoupper($key) : $key) . $right_delim] = $value;
		}

		$sql = 'UPDATE ' . PROFILE_FIELDS_DATA_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $cp_data_sql) . "
			WHERE user_id = $user_id";
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$cp_data_sql['user_id'] = (int) $user_id;

			$this->db->sql_return_on_error(true);

			$sql = 'INSERT INTO ' . PROFILE_FIELDS_DATA_TABLE . ' ' . $this->db->sql_build_array('INSERT', $cp_data_sql);
			$this->db->sql_query($sql);

			$this->db->sql_return_on_error(false);
		}
	}

	/**
	* Assign fields to template, used for viewprofile, viewtopic and memberlist (if load setting is enabled)
	* This is directly connected to the user -> mode == grab is to grab the user specific fields, mode == show is for assigning the row to the template
	* @access public
	*/
	function generate_profile_fields_template($mode, $user_id = 0, $profile_row = false)
	{
		if ($mode == 'grab')
		{
			if (!is_array($user_id))
			{
				$user_id = array($user_id);
			}

			if (!sizeof($this->profile_cache))
			{
				$this->build_cache();
			}

			if (!sizeof($user_id))
			{
				return array();
			}

			$sql = 'SELECT *
				FROM ' . PROFILE_FIELDS_DATA_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_id));
			$result = $this->db->sql_query($sql);

			$field_data = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$field_data[$row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);

			$user_fields = array();

			$user_ids = $user_id;

			// Go through the fields in correct order
			foreach (array_keys($this->profile_cache) as $used_ident)
			{
				foreach ($field_data as $user_id => $row)
				{
					$user_fields[$user_id][$used_ident]['value'] = $row['pf_' . $used_ident];
					$user_fields[$user_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
				}

				foreach ($user_ids as $user_id)
				{
					if (!isset($user_fields[$user_id][$used_ident]) && $this->profile_cache[$used_ident]['field_show_novalue'])
					{
						$user_fields[$user_id][$used_ident]['value'] = '';
						$user_fields[$user_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
					}
				}
			}

			return $user_fields;
		}
		else if ($mode == 'show')
		{
			// $profile_row == $user_fields[$row['user_id']];
			$tpl_fields = array();
			$tpl_fields['row'] = $tpl_fields['blockrow'] = array();

			foreach ($profile_row as $ident => $ident_ary)
			{
				$value = $this->get_profile_value($ident_ary);

				if ($value === NULL)
				{
					continue;
				}

				$tpl_fields['row'] += array(
					'PROFILE_' . strtoupper($ident) . '_VALUE'	=> $value,
					'PROFILE_' . strtoupper($ident) . '_TYPE'	=> $ident_ary['data']['field_type'],
					'PROFILE_' . strtoupper($ident) . '_NAME'	=> $ident_ary['data']['lang_name'],
					'PROFILE_' . strtoupper($ident) . '_EXPLAIN'=> $ident_ary['data']['lang_explain'],

					'S_PROFILE_' . strtoupper($ident)			=> true
				);

				$tpl_fields['blockrow'][] = array(
					'PROFILE_FIELD_VALUE'	=> $value,
					'PROFILE_FIELD_TYPE'	=> $ident_ary['data']['field_type'],
					'PROFILE_FIELD_NAME'	=> $ident_ary['data']['lang_name'],
					'PROFILE_FIELD_EXPLAIN'	=> $ident_ary['data']['lang_explain'],

					'S_PROFILE_' . strtoupper($ident)		=> true
				);
			}

			return $tpl_fields;
		}
		else
		{
			trigger_error('Wrong mode for custom profile', E_USER_ERROR);
		}
	}

	/**
	* Get Profile Value for display
	*/
	function get_profile_value($ident_ary)
	{
		$value = $ident_ary['value'];
		$field_type = $ident_ary['data']['field_type'];

		switch ($this->profile_types[$field_type])
		{
			case 'int':
				if ($value === '' && !$ident_ary['data']['field_show_novalue'])
				{
					return NULL;
				}
				return (int) $value;
			break;

			case 'string':
			case 'text':
				if (!$value && !$ident_ary['data']['field_show_novalue'])
				{
					return NULL;
				}

				$value = make_clickable($value);
				$value = censor_text($value);
				$value = bbcode_nl2br($value);
				return $value;
			break;

			// case 'datetime':
			case 'date':
				$date = explode('-', $value);
				$day = (isset($date[0])) ? (int) $date[0] : 0;
				$month = (isset($date[1])) ? (int) $date[1] : 0;
				$year = (isset($date[2])) ? (int) $date[2] : 0;

				if (!$day && !$month && !$year && !$ident_ary['data']['field_show_novalue'])
				{
					return NULL;
				}
				else if ($day && $month && $year)
				{
					// Date should display as the same date for every user regardless of timezone
					return $this->user->create_datetime()
						->setDate($year, $month, $day)
						->setTime(0, 0, 0)
						->format($user->lang['DATE_FORMAT'], true);
				}

				return $value;
			break;

			case 'dropdown':
				$field_id = $ident_ary['data']['field_id'];
				$lang_id = $ident_ary['data']['lang_id'];
				if (!isset($this->options_lang[$field_id][$lang_id]))
				{
					$this->get_option_lang($field_id, $lang_id, FIELD_DROPDOWN, false);
				}

				if ($value == $ident_ary['data']['field_novalue'] && !$ident_ary['data']['field_show_novalue'])
				{
					return NULL;
				}

				$value = (int) $value;

				// User not having a value assigned
				if (!isset($this->options_lang[$field_id][$lang_id][$value]))
				{
					if ($ident_ary['data']['field_show_novalue'])
					{
						$value = $ident_ary['data']['field_novalue'];
					}
					else
					{
						return NULL;
					}
				}

				return $this->options_lang[$field_id][$lang_id][$value];
			break;

			case 'bool':
				$field_id = $ident_ary['data']['field_id'];
				$lang_id = $ident_ary['data']['lang_id'];
				if (!isset($this->options_lang[$field_id][$lang_id]))
				{
					$this->get_option_lang($field_id, $lang_id, FIELD_BOOL, false);
				}

				if (!$value && $ident_ary['data']['field_show_novalue'])
				{
					$value = $ident_ary['data']['field_default_value'];
				}

				if ($ident_ary['data']['field_length'] == 1)
				{
					return (isset($this->options_lang[$field_id][$lang_id][(int) $value])) ? $this->options_lang[$field_id][$lang_id][(int) $value] : NULL;
				}
				else if (!$value)
				{
					return NULL;
				}
				else
				{
					return $this->options_lang[$field_id][$lang_id][(int) ($value) + 1];
				}
			break;

			default:
				trigger_error('Unknown profile type', E_USER_ERROR);
			break;
		}
	}

	/**
	* Get field value for registration/profile
	* @access private
	*/
	function get_var($field_validation, &$profile_row, $default_value, $preview)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$user_ident = $profile_row['field_ident'];
		// checkbox - set the value to "true" if it has been set to 1
		if ($profile_row['field_type'] == FIELD_BOOL && $profile_row['field_length'] == 2)
		{
			$value = (isset($_REQUEST[$profile_row['field_ident']]) && request_var($profile_row['field_ident'], $default_value) == 1) ? true : ((!isset($this->user->profile_fields[$user_ident]) || $preview) ? $default_value : $this->user->profile_fields[$user_ident]);
		}
		else if ($profile_row['field_type'] == FIELD_INT)
		{
			if (isset($_REQUEST[$profile_row['field_ident']]))
			{
				$value = ($this->request->variable($profile_row['field_ident'], '') === '') ? NULL : $this->request->variable($profile_row['field_ident'], $default_value);
			}
			else
			{
				if (!$preview && array_key_exists($user_ident, $this->user->profile_fields) && is_null($this->user->profile_fields[$user_ident]))
				{
					$value = NULL;
				}
				else if (!isset($this->user->profile_fields[$user_ident]) || $preview)
				{
					$value = $default_value;
				}
				else
				{
					$value = $this->user->profile_fields[$user_ident];
				}
			}

			return (is_null($value) || $value === '') ? '' : (int) $value;
		}
		else
		{
			$value = (isset($_REQUEST[$profile_row['field_ident']])) ? request_var($profile_row['field_ident'], $default_value, true) : ((!isset($this->user->profile_fields[$user_ident]) || $preview) ? $default_value : $this->user->profile_fields[$user_ident]);

			if (gettype($value) == 'string')
			{
				$value = utf8_normalize_nfc($value);
			}
		}

		switch ($field_validation)
		{
			case 'int':
				return (int) $value;
			break;
		}

		return $value;
	}

	/**
	* Process int-type
	* @access private
	*/
	function generate_int($profile_row, $preview = false)
	{
		$profile_row['field_value'] = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);
		$this->template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process date-type
	* @access private
	*/
	function generate_date($profile_row, $preview = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$user_ident = $profile_row['field_ident'];

		$now = getdate();

		if (!isset($_REQUEST[$profile_row['field_ident'] . '_day']))
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', ((!isset($this->user->profile_fields[$user_ident]) || $preview) ? $profile_row['field_default_value'] : $this->user->profile_fields[$user_ident]));
		}
		else
		{
			if ($preview && $profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
				list($day, $month, $year) = explode('-', ((!isset($this->user->profile_fields[$user_ident]) || $preview) ? $profile_row['field_default_value'] : $this->user->profile_fields[$user_ident]));
			}
			else
			{
				$day = request_var($profile_row['field_ident'] . '_day', 0);
				$month = request_var($profile_row['field_ident'] . '_month', 0);
				$year = request_var($profile_row['field_ident'] . '_year', 0);
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
		for ($i = $now['year'] - 100; $i <= $now['year'] + 100; $i++)
		{
			$profile_row['s_year_options'] .= '<option value="' . $i . '"' . (($i == $year) ? ' selected="selected"' : '') . ">$i</option>";
		}
		unset($now);

		$profile_row['field_value'] = 0;
		$this->template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process bool-type
	* @access private
	*/
	function generate_bool($profile_row, $preview = false)
	{
		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);

		$profile_row['field_value'] = $value;
		$this->template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));

		if ($profile_row['field_length'] == 1)
		{
			if (!isset($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || !sizeof($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
			{
				$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_BOOL, $preview);
			}

			foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
			{
				$this->template->assign_block_vars('bool.options', array(
					'OPTION_ID'	=> $option_id,
					'CHECKED'	=> ($value == $option_id) ? ' checked="checked"' : '',
					'VALUE'		=> $option_value)
				);
			}
		}
	}

	/**
	* Process string-type
	* @access private
	*/
	function generate_string($profile_row, $preview = false)
	{
		$profile_row['field_value'] = $this->get_var('string', $profile_row, $profile_row['lang_default_value'], $preview);
		$this->template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process text-type
	* @access private
	*/
	function generate_text($profile_row, $preview = false)
	{
		$field_length = explode('|', $profile_row['field_length']);
		$profile_row['field_rows'] = $field_length[0];
		$profile_row['field_cols'] = $field_length[1];

		$profile_row['field_value'] = $this->get_var('string', $profile_row, $profile_row['lang_default_value'], $preview);
		$this->template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* Process dropdown-type
	* @access private
	*/
	function generate_dropdown($profile_row, $preview = false)
	{
		$value = $this->get_var('int', $profile_row, $profile_row['field_default_value'], $preview);

		if (!isset($this->options_lang[$profile_row['field_id']]) || !isset($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]) || !sizeof($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']]))
		{
			$this->get_option_lang($profile_row['field_id'], $profile_row['lang_id'], FIELD_DROPDOWN, $preview);
		}

		$profile_row['field_value'] = $value;
		$this->template->assign_block_vars($this->profile_types[$profile_row['field_type']], array_change_key_case($profile_row, CASE_UPPER));

		foreach ($this->options_lang[$profile_row['field_id']][$profile_row['lang_id']] as $option_id => $option_value)
		{
			$this->template->assign_block_vars('dropdown.options', array(
				'OPTION_ID'	=> $option_id,
				'SELECTED'	=> ($value == $option_id) ? ' selected="selected"' : '',
				'VALUE'		=> $option_value)
			);
		}
	}

	/**
	* Return Templated value/field. Possible values for $mode are:
	* change == user is able to set/enter profile values; preview == just show the value
	* @access private
	*/
	function process_field_row($mode, $profile_row)
	{
		$preview = ($mode == 'preview') ? true : false;

		// set template filename
		$this->template->set_filenames(array(
			'cp_body'		=> 'custom_profile_fields.html',
		));

		// empty previously filled blockvars
		foreach ($this->profile_types as $field_case => $field_type)
		{
			$this->template->destroy_block_vars($field_type);
		}

		// Assign template variables
		$type_func = 'generate_' . $this->profile_types[$profile_row['field_type']];
		$this->$type_func($profile_row, $preview);

		// Return templated data
		return $this->template->assign_display('cp_body');
	}

	/**
	* Build Array for user insertion into custom profile fields table
	*/
	function build_insert_sql_array($cp_data)
	{
		$sql_not_in = array();
		foreach ($cp_data as $key => $null)
		{
			$sql_not_in[] = (strncmp($key, 'pf_', 3) === 0) ? substr($key, 3) : $key;
		}

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . ' f
			WHERE l.lang_id = ' . $this->user->get_iso_lang_id() . '
				' . ((sizeof($sql_not_in)) ? ' AND ' . $this->db->sql_in_set('f.field_ident', $sql_not_in, true) : '') . '
				AND l.field_id = f.field_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['field_default_value'] == 'now' && $row['field_type'] == FIELD_DATE)
			{
				$now = getdate();
				$row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			else if ($row['field_default_value'] === '' && $row['field_type'] == FIELD_INT)
			{
				// We cannot insert an empty string into an integer column.
				$row['field_default_value'] = NULL;
			}

			$cp_data['pf_' . $row['field_ident']] = (in_array($row['field_type'], array(FIELD_TEXT, FIELD_STRING))) ? $row['lang_default_value'] : $row['field_default_value'];
		}
		$this->db->sql_freeresult($result);

		return $cp_data;
	}
}
