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

namespace phpbb\profilefields;

/**
 * Custom Profile Fields (CPF) manager.
 */
class manager
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools */
	protected $db_tools;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\di\service_collection */
	protected $type_collection;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Profile fields table */
	protected $fields_table;

	/** @var string Profile fields data table */
	protected $fields_data_table;

	/** @var string Profile fields data (options) table */
	protected $fields_data_lang_table;

	/** @var string Profile fields language table */
	protected $fields_lang_table;

	/** @var array Users custom profile fields cache */
	protected $profile_cache = [];

	/**
	 * Construct
	 *
	 * @param \phpbb\auth\auth					$auth					Auth object
	 * @param \phpbb\config\db_text				$config_text			Config_text object
	 * @param \phpbb\db\driver\driver_interface	$db						Database object
	 * @param \phpbb\db\tools\tools				$db_tools				Database tools object
	 * @param \phpbb\event\dispatcher_interface	$dispatcher				Event dispatcher object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\log\log					$log					Log object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\di\service_collection		$type_collection		CPF Type collection
	 * @param \phpbb\user						$user					User object
	 * @param string							$fields_table			CPF Table
	 * @param string							$fields_data_table		CPF Data table
	 * @param string							$fields_data_lang_table	CPF Data language table
	 * @param string							$fields_lang_table		CPF Language table
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\db_text $config_text,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\db\tools\tools $db_tools,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\di\service_collection $type_collection,
		\phpbb\user $user,
		$fields_table,
		$fields_data_table,
		$fields_data_lang_table,
		$fields_lang_table
	)
	{
		$this->auth				= $auth;
		$this->config_text		= $config_text;
		$this->db				= $db;
		$this->db_tools			= $db_tools;
		$this->dispatcher		= $dispatcher;
		$this->language			= $language;
		$this->log				= $log;
		$this->request			= $request;
		$this->template			= $template;
		$this->type_collection	= $type_collection;
		$this->user				= $user;

		$this->fields_table				= $fields_table;
		$this->fields_data_table		= $fields_data_table;
		$this->fields_data_lang_table	= $fields_data_lang_table;
		$this->fields_lang_table		= $fields_lang_table;
	}

	/**
	 * Assign editable fields to template.
	 *
	 * Called by ucp_profile and ucp_register.
	 *
	 * @param string	$mode		The mode (profile|register)
	 * @param int		$lang_id	The language identifier
	 * @return void
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
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$has_required = false;

		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_lang_table . ' l,
				' . $this->fields_table . ' f
			WHERE l.field_id = f.field_id
				AND f.field_active = 1
				AND l.lang_id = ' . (int) $lang_id
			. $sql_where . '
			ORDER BY f.field_order ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/** @var \phpbb\profilefields\type\type_interface $profile_field */
			$profile_field = $this->type_collection[$row['field_type']];

			$has_required = $has_required || $row['field_required'];

			$this->template->assign_block_vars('profile_fields', [
				'FIELD'			=> $profile_field->process_field_row('change', $row),
				'FIELD_ID'		=> $profile_field->get_field_ident($row),
				'LANG_NAME'		=> $this->language->lang($row['lang_name']),
				'LANG_EXPLAIN'	=> $this->language->lang($row['lang_explain']),
				'S_REQUIRED'	=> (bool) $row['field_required'],
			]);
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_var('PROFILE_FIELDS_REQUIRED', $has_required);
	}

	/**
	 * Build profile cache, used for display.
	 *
	 * @return void
	 */
	protected function build_cache()
	{
		$this->profile_cache = [];

		// Display hidden/no_view fields for admin/moderator
		$sql_where = !$this->auth->acl_gets('a_', 'm_') && !$this->auth->acl_getf_global('m_') ? ' AND f.field_hide = 0' : '';

		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_lang_table . ' l,
				' . $this->fields_table . ' f
			WHERE l.field_id = f.field_id
				AND f.field_active = 1
				AND f.field_no_view = 0
				AND l.lang_id = ' . $this->user->get_iso_lang_id()
			. $sql_where . '
			ORDER BY f.field_order ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->profile_cache[$row['field_ident']] = $row;
		}
		$this->db->sql_freeresult($result);
	}

	/**
	 * Submit profile field for validation.
	 *
	 * @param string	$mode		The mode (profile|register)
	 * @param int		$lang_id	The language identifier
	 * @param array		$cp_data	Custom profile field data
	 * @param array		$cp_error	Custom profile field errors
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
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		$sql = 'SELECT l.*, f.*
			FROM ' . $this->fields_lang_table . ' l,
				' . $this->fields_table . ' f
			WHERE l.field_id = f.field_id
				AND f.field_active = 1
				AND l.lang_id = ' . (int) $lang_id
			. $sql_where . '
			ORDER BY f.field_order';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/** @var \phpbb\profilefields\type\type_interface $profile_field */
			$profile_field = $this->type_collection[$row['field_type']];
			$cp_data['pf_' . $row['field_ident']] = $profile_field->get_profile_field($row);

			/**
			 * Replace Emoji and other 4bit UTF-8 chars not allowed by MySQL
			 * with their Numeric Character Reference's Hexadecimal notation.
			 */
			if (is_string($cp_data['pf_' . $row['field_ident']]))
			{
				$cp_data['pf_' . $row['field_ident']] = utf8_encode_ucr($cp_data['pf_' . $row['field_ident']]);
			}

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
	 * Update profile field data directly.
	 *
	 * @param int		$user_id		The user identifier
	 * @param array		$cp_data		Custom profile field data
	 */
	public function update_profile_field_data($user_id, $cp_data)
	{
		if (empty($cp_data))
		{
			return;
		}

		$sql = 'UPDATE ' . $this->fields_data_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $cp_data) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$cp_data = $this->build_insert_sql_array($cp_data);
			$cp_data['user_id'] = (int) $user_id;

			$sql = 'INSERT INTO ' . $this->fields_data_table . $this->db->sql_build_array('INSERT', $cp_data);
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Generate the template arrays in order to display the column names.
	 *
	 * @param string	$restrict_option	Restrict the published fields to a certain profile field option
	 * @return array                        Returns an array with the template variables type,
	 * 										name and explain for the fields to display
	 */
	public function generate_profile_fields_template_headlines($restrict_option = '')
	{
		if (empty($this->profile_cache))
		{
			$this->build_cache();
		}

		$tpl_fields = [];

		// Go through the fields in correct order
		foreach ($this->profile_cache as $field_ident => $field_data)
		{
			if ($restrict_option && !$field_data[$restrict_option])
			{
				continue;
			}

			/** @var \phpbb\profilefields\type\type_interface $profile_field */
			$profile_field = $this->type_collection[$field_data['field_type']];

			$tpl_fields[] = [
				'PROFILE_FIELD_IDENT'	=> $field_ident,
				'PROFILE_FIELD_TYPE'	=> $field_data['field_type'],
				'PROFILE_FIELD_NAME'	=> $profile_field->get_field_name($field_data['lang_name']),
				'PROFILE_FIELD_EXPLAIN'	=> $this->language->lang($field_data['lang_explain']),
			];
		}

		$profile_cache = $this->profile_cache;

		/**
		 * Event to modify template headlines of the generated profile fields
		 *
		 * @event core.generate_profile_fields_template_headlines
		 * @var string	restrict_option	Restrict the published fields to a certain profile field option
		 * @var array	tpl_fields		Array with template data fields
		 * @var array	profile_cache	A copy of the profile cache to make additional checks
		 * @since 3.1.6-RC1
		 */
		$vars = ['restrict_option', 'tpl_fields', 'profile_cache'];
		extract($this->dispatcher->trigger_event('core.generate_profile_fields_template_headlines', compact($vars)));
		unset($profile_cache);

		return $tpl_fields;
	}

	/**
	 * Grab the user specific profile fields data.
	 *
	 * @param int|array	$user_ids	Single user id or an array of ids
	 * @return array				Users profile fields data
	 */
	public function grab_profile_fields_data($user_ids = 0)
	{
		if (empty($this->profile_cache))
		{
			$this->build_cache();
		}

		if (empty($user_ids))
		{
			return [];
		}

		$user_ids = (array) $user_ids;

		$sql = 'SELECT *
			FROM ' . $this->fields_data_table . '
			WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids));
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$field_data = array_column($rowset, null, 'user_id');

		/**
		 * Event to modify profile fields data retrieved from the database
		 *
		 * @event core.grab_profile_fields_data
		 * @var array	user_ids		Single user id or an array of ids
		 * @var array	field_data		Array with profile fields data
		 * @since 3.1.0-b3
		 */
		$vars = ['user_ids', 'field_data'];
		extract($this->dispatcher->trigger_event('core.grab_profile_fields_data', compact($vars)));

		$user_fields = [];

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
				if (!isset($user_fields[$user_id][$used_ident]))
				{
					$user_fields[$user_id][$used_ident]['value'] = '';
					$user_fields[$user_id][$used_ident]['data'] = $this->profile_cache[$used_ident];
				}
			}
		}

		return $user_fields;
	}

	/**
	 * Generate the user's profile fields data for the template.
	 *
	 * @param array		$profile_row		Array with users profile field data
	 * @param bool		$use_contact_fields	Should we display contact fields as such?
	 * 										This requires special treatments:
	 * 										(links should not be parsed in the values, and more)
	 * @return array						The user's profile fields data
	 */
	public function generate_profile_fields_template_data($profile_row, $use_contact_fields = true)
	{
		// $profile_row == $user_fields[$row['user_id']];
		$tpl_fields = [
			'row'		=> [],
			'blockrow'	=> [],
		];

		/**
		 * Event to modify data of the generated profile fields, before the template assignment loop
		 *
		 * @event core.generate_profile_fields_template_data_before
		 * @var array	profile_row			Array with users profile field data
		 * @var array	tpl_fields			Array with template data fields
		 * @var bool	use_contact_fields	Should we display contact fields as such?
		 * @since 3.1.0-b3
		 */
		$vars = ['profile_row', 'tpl_fields', 'use_contact_fields'];
		extract($this->dispatcher->trigger_event('core.generate_profile_fields_template_data_before', compact($vars)));

		foreach ($profile_row as $ident => $ident_ary)
		{
			/** @var \phpbb\profilefields\type\type_interface $profile_field */
			$profile_field = $this->type_collection[$ident_ary['data']['field_type']];

			$value = $profile_field->get_profile_value($ident_ary['value'], $ident_ary['data']);
			$value_raw = $profile_field->get_profile_value_raw($ident_ary['value'], $ident_ary['data']);

			if ($value === null)
			{
				continue;
			}

			$field_desc = '';
			$contact_url = '';
			$ident_upper = strtoupper($ident);

			if ($use_contact_fields && $ident_ary['data']['field_is_contact'])
			{
				$value = $profile_field->get_profile_contact_value($ident_ary['value'], $ident_ary['data']);
				$field_desc = $this->language->lang($ident_ary['data']['field_contact_desc']);

				if (strpos($field_desc, '%s') !== false)
				{
					$field_desc = sprintf($field_desc, $value);
				}

				if (strpos($ident_ary['data']['field_contact_url'], '%s') !== false)
				{
					$contact_url = sprintf($ident_ary['data']['field_contact_url'], $value);
				}
			}

			$tpl_fields['row'] += [
				"PROFILE_{$ident_upper}_IDENT"		=> $ident,
				"PROFILE_{$ident_upper}_VALUE"		=> $value,
				"PROFILE_{$ident_upper}_VALUE_RAW"	=> $value_raw,
				"PROFILE_{$ident_upper}_CONTACT"	=> $contact_url,
				"PROFILE_{$ident_upper}_DESC"		=> $field_desc,
				"PROFILE_{$ident_upper}_TYPE"		=> $ident_ary['data']['field_type'],
				"PROFILE_{$ident_upper}_NAME"		=> $this->language->lang($ident_ary['data']['lang_name']),
				"PROFILE_{$ident_upper}_EXPLAIN"	=> $this->language->lang($ident_ary['data']['lang_explain']),

				"S_PROFILE_{$ident_upper}_CONTACT"	=> $ident_ary['data']['field_is_contact'],
				"S_PROFILE_{$ident_upper}"			=> true,
			];

			$tpl_fields['blockrow'][] = [
				'PROFILE_FIELD_IDENT'		=> $ident,
				'PROFILE_FIELD_VALUE'		=> $value,
				'PROFILE_FIELD_VALUE_RAW'	=> $value_raw,
				'PROFILE_FIELD_CONTACT'		=> $contact_url,
				'PROFILE_FIELD_DESC'		=> $field_desc,
				'PROFILE_FIELD_TYPE'		=> $ident_ary['data']['field_type'],
				'PROFILE_FIELD_NAME'		=> $this->language->lang($ident_ary['data']['lang_name']),
				'PROFILE_FIELD_EXPLAIN'		=> $this->language->lang($ident_ary['data']['lang_explain']),

				'S_PROFILE_CONTACT'			=> $ident_ary['data']['field_is_contact'],
				"S_PROFILE_{$ident_upper}"	=> true,
			];
		}

		/**
		 * Event to modify template data of the generated profile fields
		 *
		 * @event core.generate_profile_fields_template_data
		 * @var array	profile_row		Array with users profile field data
		 * @var array	tpl_fields		Array with template data fields
		 * @var bool	use_contact_fields	Should we display contact fields as such?
		 * @since 3.1.0-b3
		 */
		$vars = ['profile_row', 'tpl_fields', 'use_contact_fields'];
		extract($this->dispatcher->trigger_event('core.generate_profile_fields_template_data', compact($vars)));

		return $tpl_fields;
	}

	/**
	 * Build array for the custom profile fields table.
	 *
	 * @param array		$cp_data		Custom profile field data
	 * @return array					Custom profile field data for SQL usage
	 */
	public function build_insert_sql_array($cp_data)
	{
		$prefix = 'pf_';
		$length = strlen($prefix);
		$not_in = [];

		foreach ($cp_data as $key => $null)
		{
			$not_in[] = strncmp($key, $prefix, $length) === 0 ? substr($key, $length) : $key;
		}

		$sql = 'SELECT f.field_type, f.field_ident, f.field_default_value, l.lang_default_value
			FROM ' . $this->fields_lang_table . ' l,
				' . $this->fields_table . ' f
			WHERE l.field_id = f.field_id
				AND l.lang_id = ' . $this->user->get_iso_lang_id() .
				(!empty($not_in) ? ' AND ' . $this->db->sql_in_set('f.field_ident', $not_in, true) : '');
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/** @var \phpbb\profilefields\type\type_interface $profile_field */
			$profile_field = $this->type_collection[$row['field_type']];
			$cp_data[$prefix . $row['field_ident']] = $profile_field->get_default_field_value($row);
		}
		$this->db->sql_freeresult($result);

		return $cp_data;
	}

	/**
	 * Disable all profile fields of a certain type.
	 *
	 * This should be called when an extension which has profile field types is disabled
	 * so that all those profile fields are hidden and do not cause errors.
	 *
	 * @param string	$type_name		Type identifier of the profile fields
	 */
	public function disable_profilefields($type_name)
	{
		// Get the list of active profile fields of this type
		$profile_fields = $this->list_profilefields($type_name, true);

		// If no profile fields affected, then nothing to do
		if (empty($profile_fields))
		{
			return;
		}

		// Update the affected profile fields to "inactive"
		$sql = 'UPDATE ' . $this->fields_table . '
			SET field_active = 0
			WHERE field_active = 1
				AND ' . $this->db->sql_in_set('field_id', array_keys($profile_fields));
		$this->db->sql_query($sql);

		// Save modified information into a config_text field to recover on enable
		$this->config_text->set($type_name . '.saved', json_encode($profile_fields));

		// Log activity
		foreach ($profile_fields as $field_ident)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_DEACTIVATE', time(), [$field_ident]);
		}
	}

	/**
	 * Purge all profile fields of a certain type.
	 *
	 * This should be called when an extension which has profile field types is purged
	 * so that all those profile fields are removed.
	 *
	 * @param string	$type_name		Type identifier of the profile fields
	 */
	public function purge_profilefields($type_name)
	{
		// Remove the information saved on disable in a config_text field, not needed any longer
		$this->config_text->delete($type_name . '.saved');

		// Get the list of all profile fields of this type
		$profile_fields = $this->list_profilefields($type_name);

		// If no profile fields exist, then nothing to do
		if (empty($profile_fields))
		{
			return;
		}

		$this->db->sql_transaction('begin');

		// Delete entries from all profile field definition tables
		$where = $this->db->sql_in_set('field_id', array_keys($profile_fields));
		$this->db->sql_query('DELETE FROM ' . $this->fields_table . ' WHERE ' . $where);
		$this->db->sql_query('DELETE FROM ' . $this->fields_data_lang_table . ' WHERE ' . $where);
		$this->db->sql_query('DELETE FROM ' . $this->fields_lang_table . ' WHERE ' . $where);

		// Drop columns from the Profile Fields data table
		foreach ($profile_fields as $field_ident)
		{
			$this->db_tools->sql_column_remove($this->fields_data_table, 'pf_' . $field_ident);
		}

		// Reset the order of the remaining fields
		$order = 0;

		$sql = 'SELECT *
			FROM ' . $this->fields_table . '
			ORDER BY field_order';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$order++;

			if ($row['field_order'] != $order)
			{
				$sql = 'UPDATE ' . $this->fields_table . "
					SET field_order = $order
					WHERE field_id = {$row['field_id']}";
				$this->db->sql_query($sql);
			}
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_transaction('commit');

		// Log activity
		foreach ($profile_fields as $field_ident)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_REMOVED', time(), [$field_ident]);
		}
	}

	/**
	 * Enable the profile fields of a certain type.
	 *
	 * This should be called when an extension which has profile field types that was disabled is re-enabled
	 * so that all those profile fields that were disabled are enabled again.
	 *
	 * @param string	$type_name		Type identifier of the profile fields
	 */
	public function enable_profilefields($type_name)
	{
		// Read the modified information saved on disable from a config_text field to recover values
		$profile_fields = $this->config_text->get($type_name . '.saved');

		// If nothing saved, then nothing to do
		if (empty($profile_fields))
		{
			return;
		}

		$profile_fields = (array) json_decode($profile_fields, true);

		// Restore the affected profile fields to "active"
		$sql = 'UPDATE ' . $this->fields_table . '
			SET field_active = 1
			WHERE field_active = 0
				AND ' . $this->db->sql_in_set('field_id', array_keys($profile_fields));
		$this->db->sql_query($sql);

		// Remove the information saved in the config_text field, not needed any longer
		$this->config_text->delete($type_name . '.saved');

		// Log activity
		foreach ($profile_fields as $field_ident)
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_ACTIVATE', time(), [$field_ident]);
		}
	}

	/**
	 * Get list of profile fields of a certain type, if any
	 *
	 * @param string	$type_name		Type identifier of the profile fields
	 * @param bool		$active			True to limit output to active profile fields, false for all
	 * @return array					Array with profile field ids as keys and idents as values
	 */
	private function list_profilefields($type_name, $active = false)
	{
		// Get list of profile fields affected by this operation, if any
		$sql = 'SELECT field_id, field_ident
			FROM ' . $this->fields_table . "
			WHERE field_type = '" . $this->db->sql_escape($type_name) . "'" .
			($active ? ' AND field_active = 1' : '');
		$result = $this->db->sql_query($sql);
		$rowset = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return array_column($rowset, 'field_ident', 'field_id');
	}
}
