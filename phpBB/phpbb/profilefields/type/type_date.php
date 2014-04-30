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

class type_date extends type_base
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
		return 'date';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$profile_row = array(
			'var_name'				=> 'field_default_value',
			'lang_name'				=> $field_data['lang_name'],
			'lang_explain'			=> $field_data['lang_explain'],
			'lang_id'				=> $default_lang_id,
			'field_default_value'	=> $field_data['field_default_value'],
			'field_ident'			=> 'field_default_value',
			'field_type'			=> $this->get_service_name(),
			'field_length'			=> $field_data['field_length'],
			'lang_options'			=> $field_data['lang_options'],
		);

		$always_now = request_var('always_now', -1);
		if ($always_now == -1)
		{
			$s_checked = ($field_data['field_default_value'] == 'now') ? true : false;
		}
		else
		{
			$s_checked = ($always_now) ? true : false;
		}

		$options = array(
			0 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'],	'FIELD' => $this->process_field_row('preview', $profile_row)),
			1 => array('TITLE' => $this->user->lang['ALWAYS_TODAY'],	'FIELD' => '<label><input type="radio" class="radio" name="always_now" value="1"' . (($s_checked) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['YES'] . '</label><label><input type="radio" class="radio" name="always_now" value="0"' . ((!$s_checked) ? ' checked="checked"' : '') . ' onchange="document.getElementById(\'add_profile_field\').submit();" /> ' . $this->user->lang['NO'] . '</label>'),
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_option_values()
	{
		return array(
			'field_length'		=> 10,
			'field_minlen'		=> 10,
			'field_maxlen'		=> 10,
			'field_validation'	=> '',
			'field_novalue'		=> ' 0- 0-   0',
			'field_default_value'	=> ' 0- 0-   0',
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_field_value($field_data)
	{
		if ($field_data['field_default_value'] == 'now')
		{
			$now = getdate();
			$field_data['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
		}

		return $field_data['field_default_value'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];

		if (!$this->request->is_set($var_name . '_day'))
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
			$day = $this->request->variable($var_name . '_day', 0);
			$month = $this->request->variable($var_name . '_month', 0);
			$year = $this->request->variable($var_name . '_year', 0);
		}

		return sprintf('%2d-%2d-%4d', $day, $month, $year);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		$field_validate = explode('-', $field_value);

		$day = (isset($field_validate[0])) ? (int) $field_validate[0] : 0;
		$month = (isset($field_validate[1])) ? (int) $field_validate[1] : 0;
		$year = (isset($field_validate[2])) ? (int) $field_validate[2] : 0;

		if ((!$day || !$month || !$year) && !$field_data['field_required'])
		{
			return false;
		}

		if ((!$day || !$month || !$year) && $field_data['field_required'])
		{
			return $this->user->lang('FIELD_REQUIRED', $this->get_field_name($field_data['lang_name']));
		}

		if ($day < 0 || $day > 31 || $month < 0 || $month > 12 || ($year < 1901 && $year > 0) || $year > gmdate('Y', time()) + 50)
		{
			return $this->user->lang('FIELD_INVALID_DATE', $this->get_field_name($field_data['lang_name']));
		}

		if (checkdate($month, $day, $year) === false)
		{
			return $this->user->lang('FIELD_INVALID_DATE', $this->get_field_name($field_data['lang_name']));
		}

		return false;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_value($field_value, $field_data)
	{
		$date = explode('-', $field_value);
		$day = (isset($date[0])) ? (int) $date[0] : 0;
		$month = (isset($date[1])) ? (int) $date[1] : 0;
		$year = (isset($date[2])) ? (int) $date[2] : 0;

		if (!$day && !$month && !$year && !$field_data['field_show_novalue'])
		{
			return null;
		}
		else if ($day && $month && $year)
		{
			// Date should display as the same date for every user regardless of timezone
			return $this->user->create_datetime()
				->setDate($year, $month, $day)
				->setTime(0, 0, 0)
				->format($this->user->lang['DATE_FORMAT'], true);
		}

		return $field_value;
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

		return $field_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function generate_field($profile_row, $preview_options = false)
	{
		$profile_row['field_ident'] = (isset($profile_row['var_name'])) ? $profile_row['var_name'] : 'pf_' . $profile_row['field_ident'];
		$field_ident = $profile_row['field_ident'];

		$now = getdate();

		if (!$this->request->is_set($profile_row['field_ident'] . '_day'))
		{
			if ($profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
			}
			list($day, $month, $year) = explode('-', ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $profile_row['field_default_value'] : $this->user->profile_fields[$field_ident]));
		}
		else
		{
			if ($preview_options !== false && $profile_row['field_default_value'] == 'now')
			{
				$profile_row['field_default_value'] = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], $now['year']);
				list($day, $month, $year) = explode('-', ((!isset($this->user->profile_fields[$field_ident]) || $preview_options !== false) ? $profile_row['field_default_value'] : $this->user->profile_fields[$field_ident]));
			}
			else
			{
				$day = $this->request->variable($profile_row['field_ident'] . '_day', 0);
				$month = $this->request->variable($profile_row['field_ident'] . '_month', 0);
				$year = $this->request->variable($profile_row['field_ident'] . '_year', 0);
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

		$profile_row['field_value'] = 0;
		$this->template->assign_block_vars('date', array_change_key_case($profile_row, CASE_UPPER));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_field_ident($field_data)
	{
		return '';
	}

	/**
	* {@inheritDoc}
	*/
	public function get_database_column_type()
	{
		return 'VCHAR:10';
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
			$always_now = $this->request->variable('always_now', -1);

			if ($always_now == 1 || ($always_now === -1 && $current_value == 'now'))
			{
				$now = getdate();

				$field_data['field_default_value_day'] = $now['mday'];
				$field_data['field_default_value_month'] = $now['mon'];
				$field_data['field_default_value_year'] = $now['year'];
				$current_value = 'now';
				$this->request->overwrite('field_default_value', $current_value, \phpbb\request\request_interface::POST);
			}
			else
			{
				if ($this->request->is_set('field_default_value_day'))
				{
					$field_data['field_default_value_day'] = $this->request->variable('field_default_value_day', 0);
					$field_data['field_default_value_month'] = $this->request->variable('field_default_value_month', 0);
					$field_data['field_default_value_year'] = $this->request->variable('field_default_value_year', 0);
					$current_value = sprintf('%2d-%2d-%4d', $field_data['field_default_value_day'], $field_data['field_default_value_month'], $field_data['field_default_value_year']);
					$this->request->overwrite('field_default_value', $current_value, \phpbb\request\request_interface::POST);
				}
				else
				{
					list($field_data['field_default_value_day'], $field_data['field_default_value_month'], $field_data['field_default_value_year']) = explode('-', $current_value);
				}
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
		if ($key == 'field_default_value')
		{
			$always_now = $this->request->variable('always_now', 0);

			if ($always_now)
			{
				return 'now';
			}
			else if ($this->request->is_set('field_default_value_day'))
			{
				$field_data['field_default_value_day'] = $this->request->variable('field_default_value_day', 0);
				$field_data['field_default_value_month'] = $this->request->variable('field_default_value_month', 0);
				$field_data['field_default_value_year'] = $this->request->variable('field_default_value_year', 0);
				return sprintf('%2d-%2d-%4d', $field_data['field_default_value_day'], $field_data['field_default_value_month'], $field_data['field_default_value_year']);
			}
		}

		return parent::prepare_hidden_fields($step, $key, $action, $field_data);
	}
}
