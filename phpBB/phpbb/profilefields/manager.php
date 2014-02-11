<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields;

/**
* Custom Profile Fields
* @package phpBB3
*/
class manager
{
	/**
	* Auth object
	* @var \phpbb\auth\auth
	*/
	protected $auth;

	/**
	* Database object
	* @var \phpbb\db\driver\driver
	*/
	protected $db;

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
	* Service Collection object
	* @var \phpbb\di\service_collection
	*/
	protected $type_collection;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	protected $fields_table;

	protected $fields_language_table;

	protected $fields_data_table;

	protected $profile_cache = array();

	/**
	* Construct
	*
	* @param	\phpbb\auth\auth			$auth		Auth object
	* @param	\phpbb\db\driver\driver		$db			Database object
	* @param	\phpbb\request\request		$request	Request object
	* @param	\phpbb\template\template	$template	Template object
	* @param	\phpbb\di\service_collection $type_collection
	* @param	\phpbb\user					$user		User object
	* @param	string				$fields_table
	* @param	string				$fields_language_table
	* @param	string				$fields_data_table
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\di\service_collection $type_collection, \phpbb\user $user, $fields_table, $fields_language_table, $fields_data_table)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->type_collection = $type_collection;
		$this->user = $user;

		$this->fields_table = $fields_table;
		$this->fields_language_table = $fields_language_table;
		$this->fields_data_table = $fields_data_table;
	}

	/**
	* Assign editable fields to template, mode can be profile (for profile change) or register (for registration)
	* Called by ucp_profile and ucp_register
	*/
	public function generate_profile_fields($mode, $lang_id)
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
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . " f
			WHERE f.field_active = 1
				$sql_where
				AND l.lang_id = " . (int) $lang_id . '
				AND l.field_id = f.field_id
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// Return templated field
			$profile_field = $this->type_collection[$row['field_type']];
			$tpl_snippet = $profile_field->process_field_row('change', $row);

			$this->template->assign_block_vars('profile_fields', array(
				'LANG_NAME'		=> $this->user->lang($row['lang_name']),
				'LANG_EXPLAIN'	=> $this->user->lang($row['lang_explain']),
				'FIELD'			=> $tpl_snippet,
				'FIELD_ID'		=> $profile_field->get_field_ident($row),
				'S_REQUIRED'	=> ($row['field_required']) ? true : false,
			));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Build profile cache, used for display
	*/
	protected function build_cache()
	{
		$this->profile_cache = array();

		// Display hidden/no_view fields for admin/moderator
		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . ' f
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
	* Submit profile field for validation
	*/
	public function submit_cp_field($mode, $lang_id, &$cp_data, &$cp_error)
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
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . ' f
			WHERE l.lang_id = ' . (int) $lang_id . "
				AND f.field_active = 1
				$sql_where
				AND l.field_id = f.field_id
			ORDER BY f.field_order";
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$profile_field = $this->type_collection[$row['field_type']];
			$cp_data['pf_' . $row['field_ident']] = $profile_field->get_profile_field($row);
			$check_value = $cp_data['pf_' . $row['field_ident']];

			if (($cp_result = $profile_field->validate_profile_field($check_value, $row)) !== false)
			{
				// If the result is not false, it's an error message
				$cp_error[] = $cp_result;
			}
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Update profile field data directly
	*/
	public function update_profile_field_data($user_id, $cp_data)
	{
		if (!sizeof($cp_data))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->fields_data_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $cp_data) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$cp_data['user_id'] = (int) $user_id;

			$this->db->sql_return_on_error(true);

			$sql = 'INSERT INTO ' . $this->fields_data_table . ' ' . $this->db->sql_build_array('INSERT', $cp_data);
			$this->db->sql_query($sql);

			$this->db->sql_return_on_error(false);
		}
	}

	/**
	* Generate the template arrays in order to display the column names
	*
	* @param string	$restrict_option	Restrict the published fields to a certain profile field option
	* @return array		Returns an array with the template variables type, name and explain for the fields to display
	*/
	public function generate_profile_fields_template_headlines($restrict_option = '')
	{
		if (!sizeof($this->profile_cache))
		{
			$this->build_cache();
		}

		$tpl_fields = array();

		// Go through the fields in correct order
		foreach ($this->profile_cache as $field_ident => $field_data)
		{
			if ($restrict_option && !$field_data[$restrict_option])
			{
				continue;
			}

			$profile_field = $this->type_collection[$field_data['field_type']];

			$tpl_fields[] = array(
				'PROFILE_FIELD_TYPE'	=> $field_data['field_type'],
				'PROFILE_FIELD_NAME'	=> $profile_field->get_field_name($field_data['lang_name']),
				'PROFILE_FIELD_EXPLAIN'	=> $this->user->lang($field_data['lang_explain']),
			);
		}

		return $tpl_fields;
	}

	/**
	* Assign fields to template, used for viewprofile, viewtopic and memberlist (if load setting is enabled)
	* This is directly connected to the user -> mode == grab is to grab the user specific fields, mode == show is for assigning the row to the template
	*/
	public function generate_profile_fields_template($mode, $user_id = 0, $profile_row = false)
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
				FROM ' . $this->fields_data_table . '
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
				$profile_field = $this->type_collection[$ident_ary['data']['field_type']];
				$value = $profile_field->get_profile_value($ident_ary['value'], $ident_ary['data']);

				if ($value === null)
				{
					continue;
				}

				$tpl_fields['row'] += array(
					'PROFILE_' . strtoupper($ident) . '_VALUE'	=> $value,
					'PROFILE_' . strtoupper($ident) . '_TYPE'	=> $ident_ary['data']['field_type'],
					'PROFILE_' . strtoupper($ident) . '_NAME'	=> $this->user->lang($ident_ary['data']['lang_name']),
					'PROFILE_' . strtoupper($ident) . '_EXPLAIN'=> $this->user->lang($ident_ary['data']['lang_explain']),

					'S_PROFILE_' . strtoupper($ident)			=> true,
				);

				$tpl_fields['blockrow'][] = array(
					'PROFILE_FIELD_VALUE'	=> $value,
					'PROFILE_FIELD_TYPE'	=> $ident_ary['data']['field_type'],
					'PROFILE_FIELD_NAME'	=> $this->user->lang($ident_ary['data']['lang_name']),
					'PROFILE_FIELD_EXPLAIN'	=> $this->user->lang($ident_ary['data']['lang_explain']),

					'S_PROFILE_' . strtoupper($ident)		=> true,
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
	* Build Array for user insertion into custom profile fields table
	*/
	public function build_insert_sql_array($cp_data)
	{
		$sql_not_in = array();
		foreach ($cp_data as $key => $null)
		{
			$sql_not_in[] = (strncmp($key, 'pf_', 3) === 0) ? substr($key, 3) : $key;
		}

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM ' . $this->fields_language_table . ' l, ' . $this->fields_table . ' f
			WHERE l.lang_id = ' . $this->user->get_iso_lang_id() . '
				' . ((sizeof($sql_not_in)) ? ' AND ' . $this->db->sql_in_set('f.field_ident', $sql_not_in, true) : '') . '
				AND l.field_id = f.field_id';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$profile_field = $this->type_collection[$row['field_type']];
			$cp_data['pf_' . $row['field_ident']] = $profile_field->get_default_field_value($row);
		}
		$this->db->sql_freeresult($result);

		return $cp_data;
	}
}
