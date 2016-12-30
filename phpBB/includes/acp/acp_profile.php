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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_profile
{
	var $u_action;

	var $edit_lang_id;
	var $lang_defs;
	protected $type_collection;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix;
		global $request, $phpbb_container;

		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$user->add_lang(array('ucp', 'acp/profile'));
		$this->tpl_name = 'acp_profile';
		$this->page_title = 'ACP_CUSTOM_PROFILE_FIELDS';

		$field_id = $request->variable('field_id', 0);
		$action = (isset($_POST['create'])) ? 'create' : request_var('action', '');

		$error = array();
		$s_hidden_fields = '';

		if (!$field_id && in_array($action, array('delete','activate', 'deactivate', 'move_up', 'move_down', 'edit')))
		{
			trigger_error($user->lang['NO_FIELD_ID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$cp = $phpbb_container->get('profilefields.manager');
		$this->type_collection = $phpbb_container->get('profilefields.type_collection');

		// Build Language array
		// Based on this, we decide which elements need to be edited later and which language items are missing
		$this->lang_defs = array();

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . LANG_TABLE . '
			ORDER BY lang_english_name';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Make some arrays with all available languages
			$this->lang_defs['id'][$row['lang_id']] = $row['lang_iso'];
			$this->lang_defs['iso'][$row['lang_iso']] = $row['lang_id'];
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT field_id, lang_id
			FROM ' . PROFILE_LANG_TABLE . '
			ORDER BY lang_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Which languages are available for each item
			$this->lang_defs['entry'][$row['field_id']][] = $row['lang_id'];
		}
		$db->sql_freeresult($result);

		// Have some fields been defined?
		if (isset($this->lang_defs['entry']))
		{
			foreach ($this->lang_defs['entry'] as $field_ident => $field_ary)
			{
				// Fill an array with the languages that are missing for each field
				$this->lang_defs['diff'][$field_ident] = array_diff(array_values($this->lang_defs['iso']), $field_ary);
			}
		}

		switch ($action)
		{
			case 'delete':

				if (confirm_box(true))
				{
					$sql = 'SELECT field_ident
						FROM ' . PROFILE_FIELDS_TABLE . "
						WHERE field_id = $field_id";
					$result = $db->sql_query($sql);
					$field_ident = (string) $db->sql_fetchfield('field_ident');
					$db->sql_freeresult($result);

					$db->sql_transaction('begin');

					$db->sql_query('DELETE FROM ' . PROFILE_FIELDS_TABLE . " WHERE field_id = $field_id");
					$db->sql_query('DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . " WHERE field_id = $field_id");
					$db->sql_query('DELETE FROM ' . PROFILE_LANG_TABLE . " WHERE field_id = $field_id");

					$db_tools = $phpbb_container->get('dbal.tools');
					$db_tools->sql_column_remove(PROFILE_FIELDS_DATA_TABLE, 'pf_' . $field_ident);

					$order = 0;

					$sql = 'SELECT *
						FROM ' . PROFILE_FIELDS_TABLE . '
						ORDER BY field_order';
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$order++;
						if ($row['field_order'] != $order)
						{
							$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
								SET field_order = $order
								WHERE field_id = {$row['field_id']}";
							$db->sql_query($sql);
						}
					}
					$db->sql_freeresult($result);

					$db->sql_transaction('commit');

					add_log('admin', 'LOG_PROFILE_FIELD_REMOVED', $field_ident);
					trigger_error($user->lang['REMOVED_PROFILE_FIELD'] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, 'DELETE_PROFILE_FIELD', build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
						'field_id'	=> $field_id,
					)));
				}

			break;

			case 'activate':

				$sql = 'SELECT lang_id
					FROM ' . LANG_TABLE . "
					WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'";
				$result = $db->sql_query($sql);
				$default_lang_id = (int) $db->sql_fetchfield('lang_id');
				$db->sql_freeresult($result);

				if (!in_array($default_lang_id, $this->lang_defs['entry'][$field_id]))
				{
					trigger_error($user->lang['DEFAULT_LANGUAGE_NOT_FILLED'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
					SET field_active = 1
					WHERE field_id = $field_id";
				$db->sql_query($sql);

				$sql = 'SELECT field_ident
					FROM ' . PROFILE_FIELDS_TABLE . "
					WHERE field_id = $field_id";
				$result = $db->sql_query($sql);
				$field_ident = (string) $db->sql_fetchfield('field_ident');
				$db->sql_freeresult($result);

				add_log('admin', 'LOG_PROFILE_FIELD_ACTIVATE', $field_ident);

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response();
					$json_response->send(array(
						'text'	=> $user->lang('DEACTIVATE'),
					));
				}

				trigger_error($user->lang['PROFILE_FIELD_ACTIVATED'] . adm_back_link($this->u_action));

			break;

			case 'deactivate':

				$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
					SET field_active = 0
					WHERE field_id = $field_id";
				$db->sql_query($sql);

				$sql = 'SELECT field_ident
					FROM ' . PROFILE_FIELDS_TABLE . "
					WHERE field_id = $field_id";
				$result = $db->sql_query($sql);
				$field_ident = (string) $db->sql_fetchfield('field_ident');
				$db->sql_freeresult($result);

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response();
					$json_response->send(array(
						'text'	=> $user->lang('ACTIVATE'),
					));
				}

				add_log('admin', 'LOG_PROFILE_FIELD_DEACTIVATE', $field_ident);

				trigger_error($user->lang['PROFILE_FIELD_DEACTIVATED'] . adm_back_link($this->u_action));

			break;

			case 'move_up':
			case 'move_down':

				$sql = 'SELECT field_order
					FROM ' . PROFILE_FIELDS_TABLE . "
					WHERE field_id = $field_id";
				$result = $db->sql_query($sql);
				$field_order = $db->sql_fetchfield('field_order');
				$db->sql_freeresult($result);

				if ($field_order === false || ($field_order == 0 && $action == 'move_up'))
				{
					break;
				}
				$field_order = (int) $field_order;
				$order_total = $field_order * 2 + (($action == 'move_up') ? -1 : 1);

				$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . "
					SET field_order = $order_total - field_order
					WHERE field_order IN ($field_order, " . (($action == 'move_up') ? $field_order - 1 : $field_order + 1) . ')';
				$db->sql_query($sql);

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send(array(
						'success'	=> (bool) $db->sql_affectedrows(),
					));
				}

			break;

			case 'create':
			case 'edit':

				$step = request_var('step', 1);

				$submit = (isset($_REQUEST['next']) || isset($_REQUEST['prev'])) ? true : false;
				$save = (isset($_REQUEST['save'])) ? true : false;

				// The language id of default language
				$this->edit_lang_id = $this->lang_defs['iso'][$config['default_lang']];

				// We are editing... we need to grab basic things
				if ($action == 'edit')
				{
					$sql = 'SELECT l.*, f.*
						FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . ' f
						WHERE l.lang_id = ' . $this->edit_lang_id . "
							AND f.field_id = $field_id
							AND l.field_id = f.field_id";
					$result = $db->sql_query($sql);
					$field_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$field_row)
					{
						// Some admin changed the default language?
						$sql = 'SELECT l.*, f.*
							FROM ' . PROFILE_LANG_TABLE . ' l, ' . PROFILE_FIELDS_TABLE . ' f
							WHERE l.lang_id <> ' . $this->edit_lang_id . "
							AND f.field_id = $field_id
							AND l.field_id = f.field_id";
						$result = $db->sql_query($sql);
						$field_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if (!$field_row)
						{
							trigger_error($user->lang['FIELD_NOT_FOUND'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$this->edit_lang_id = $field_row['lang_id'];
					}
					$field_type = $field_row['field_type'];
					$profile_field = $this->type_collection[$field_type];

					// Get language entries
					$sql = 'SELECT *
						FROM ' . PROFILE_FIELDS_LANG_TABLE . '
						WHERE lang_id = ' . $this->edit_lang_id . "
							AND field_id = $field_id
						ORDER BY option_id ASC";
					$result = $db->sql_query($sql);

					$lang_options = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$lang_options[$row['option_id']] = $row['lang_value'];
					}
					$db->sql_freeresult($result);

					$s_hidden_fields = '<input type="hidden" name="field_id" value="' . $field_id . '" />';
				}
				else
				{
					// We are adding a new field, define basic params
					$lang_options = $field_row = array();

					$field_type = request_var('field_type', '');

					if (!isset($this->type_collection[$field_type]))
					{
						trigger_error($user->lang['NO_FIELD_TYPE'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$profile_field = $this->type_collection[$field_type];
					$field_row = array_merge($profile_field->get_default_option_values(), array(
						'field_ident'		=> str_replace(' ', '_', utf8_clean_string(request_var('field_ident', '', true))),
						'field_required'	=> 0,
						'field_show_novalue'=> 0,
						'field_hide'		=> 0,
						'field_show_profile'=> 0,
						'field_no_view'		=> 0,
						'field_show_on_reg'	=> 0,
						'field_show_on_pm'	=> 0,
						'field_show_on_vt'	=> 0,
						'field_show_on_ml'	=> 0,
						'field_is_contact'	=> 0,
						'field_contact_desc'=> '',
						'field_contact_url'	=> '',
						'lang_name'			=> utf8_normalize_nfc(request_var('field_ident', '', true)),
						'lang_explain'		=> '',
						'lang_default_value'=> '')
					);

					$s_hidden_fields = '<input type="hidden" name="field_type" value="' . $field_type . '" />';
				}

				// $exclude contains the data we gather in each step
				$exclude = array(
					1	=> array('field_ident', 'lang_name', 'lang_explain', 'field_option_none', 'field_show_on_reg', 'field_show_on_pm', 'field_show_on_vt', 'field_show_on_ml', 'field_required', 'field_show_novalue', 'field_hide', 'field_show_profile', 'field_no_view', 'field_is_contact', 'field_contact_desc', 'field_contact_url'),
					2	=> array('field_length', 'field_maxlen', 'field_minlen', 'field_validation', 'field_novalue', 'field_default_value'),
					3	=> array('l_lang_name', 'l_lang_explain', 'l_lang_default_value', 'l_lang_options')
				);

				// Visibility Options...
				$visibility_ary = array(
					'field_required',
					'field_show_novalue',
					'field_show_on_reg',
					'field_show_on_pm',
					'field_show_on_vt',
					'field_show_on_ml',
					'field_show_profile',
					'field_hide',
					'field_is_contact',
				);

				$options = $profile_field->prepare_options_form($exclude, $visibility_ary);

				$cp->vars['field_ident']		= ($action == 'create' && $step == 1) ? utf8_clean_string(request_var('field_ident', $field_row['field_ident'], true)) : request_var('field_ident', $field_row['field_ident']);
				$cp->vars['lang_name']			= $request->variable('lang_name', $field_row['lang_name'], true);
				$cp->vars['lang_explain']		= $request->variable('lang_explain', $field_row['lang_explain'], true);
				$cp->vars['lang_default_value']	= $request->variable('lang_default_value', $field_row['lang_default_value'], true);
				$cp->vars['field_contact_desc']	= $request->variable('field_contact_desc', $field_row['field_contact_desc'], true);
				$cp->vars['field_contact_url']	= $request->variable('field_contact_url', $field_row['field_contact_url'], true);

				foreach ($visibility_ary as $val)
				{
					$cp->vars[$val] = ($submit || $save) ? $request->variable($val, 0) : $field_row[$val];
				}

				$cp->vars['field_no_view'] = $request->variable('field_no_view', (int) $field_row['field_no_view']);

				// If the user has submitted a form with options (i.e. dropdown field)
				if ($options)
				{
					$exploded_options = (is_array($options)) ? $options : explode("\n", $options);

					if (sizeof($exploded_options) == sizeof($lang_options) || $action == 'create')
					{
						// The number of options in the field is equal to the number of options already in the database
						// Or we are creating a new dropdown list.
						$cp->vars['lang_options'] = $exploded_options;
					}
					else if ($action == 'edit')
					{
						// Changing the number of options? (We remove and re-create the option fields)
						$cp->vars['lang_options'] = $exploded_options;
					}
				}
				else
				{
					$cp->vars['lang_options'] = $lang_options;
				}

				// step 2
				foreach ($exclude[2] as $key)
				{
					$var = utf8_normalize_nfc(request_var($key, $field_row[$key], true));

					$field_data = $cp->vars;
					$var = $profile_field->get_excluded_options($key, $action, $var, $field_data, 2);
					$cp->vars = $field_data;

					$cp->vars[$key] = $var;
				}

				// step 3 - all arrays
				if ($action == 'edit')
				{
					// Get language entries
					$sql = 'SELECT *
						FROM ' . PROFILE_FIELDS_LANG_TABLE . '
						WHERE lang_id <> ' . $this->edit_lang_id . "
							AND field_id = $field_id
						ORDER BY option_id ASC";
					$result = $db->sql_query($sql);

					$l_lang_options = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$l_lang_options[$row['lang_id']][$row['option_id']] = $row['lang_value'];
					}
					$db->sql_freeresult($result);

					$sql = 'SELECT lang_id, lang_name, lang_explain, lang_default_value
						FROM ' . PROFILE_LANG_TABLE . '
						WHERE lang_id <> ' . $this->edit_lang_id . "
							AND field_id = $field_id
						ORDER BY lang_id ASC";
					$result = $db->sql_query($sql);

					$l_lang_name = $l_lang_explain = $l_lang_default_value = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$l_lang_name[$row['lang_id']] = $row['lang_name'];
						$l_lang_explain[$row['lang_id']] = $row['lang_explain'];
						$l_lang_default_value[$row['lang_id']] = $row['lang_default_value'];
					}
					$db->sql_freeresult($result);
				}

				foreach ($exclude[3] as $key)
				{
					$cp->vars[$key] = utf8_normalize_nfc(request_var($key, array(0 => ''), true));

					if (!$cp->vars[$key] && $action == 'edit')
					{
						$cp->vars[$key] = ${$key};
					}

					$field_data = $cp->vars;
					$var = $profile_field->get_excluded_options($key, $action, $var, $field_data, 3);
					$cp->vars = $field_data;
				}

				// Check for general issues in every step
				if ($submit) //  && $step == 1
				{
					// Check values for step 1
					if ($cp->vars['field_ident'] == '')
					{
						$error[] = $user->lang['EMPTY_FIELD_IDENT'];
					}

					if (!preg_match('/^[a-z_]+$/', $cp->vars['field_ident']))
					{
						$error[] = $user->lang['INVALID_CHARS_FIELD_IDENT'];
					}

					if (strlen($cp->vars['field_ident']) > 17)
					{
						$error[] = $user->lang['INVALID_FIELD_IDENT_LEN'];
					}

					if ($cp->vars['lang_name'] == '')
					{
						$error[] = $user->lang['EMPTY_USER_FIELD_NAME'];
					}

					$error = $profile_field->validate_options_on_submit($error, $cp->vars);

					// Check for already existing field ident
					if ($action != 'edit')
					{
						$sql = 'SELECT field_ident
							FROM ' . PROFILE_FIELDS_TABLE . "
							WHERE field_ident = '" . $db->sql_escape($cp->vars['field_ident']) . "'";
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if ($row)
						{
							$error[] = $user->lang['FIELD_IDENT_ALREADY_EXIST'];
						}
					}
				}

				$step = (isset($_REQUEST['next'])) ? $step + 1 : ((isset($_REQUEST['prev'])) ? $step - 1 : $step);

				if (sizeof($error))
				{
					$step--;
					$submit = false;
				}

				// Build up the specific hidden fields
				foreach ($exclude as $num => $key_ary)
				{
					if ($num == $step)
					{
						continue;
					}

					$_new_key_ary = array();

					$field_data = $cp->vars;
					foreach ($key_ary as $key)
					{
						$var = $profile_field->prepare_hidden_fields($step, $key, $action, $field_data);
						if ($var !== null)
						{
							$_new_key_ary[$key] = $profile_field->prepare_hidden_fields($step, $key, $action, $field_data);
						}
					}
					$cp->vars = $field_data;

					$s_hidden_fields .= build_hidden_fields($_new_key_ary);
				}

				if (!sizeof($error))
				{
					if ($step == 3 && (sizeof($this->lang_defs['iso']) == 1 || $save))
					{
						$this->save_profile_field($cp, $field_type, $action);
					}
					else if ($action == 'edit' && $save)
					{
						$this->save_profile_field($cp, $field_type, $action);
					}
				}

				$template->assign_vars(array(
					'S_EDIT'			=> true,
					'S_EDIT_MODE'		=> ($action == 'edit') ? true : false,
					'ERROR_MSG'			=> (sizeof($error)) ? implode('<br />', $error) : '',

					'L_TITLE'			=> $user->lang['STEP_' . $step . '_TITLE_' . strtoupper($action)],
					'L_EXPLAIN'			=> $user->lang['STEP_' . $step . '_EXPLAIN_' . strtoupper($action)],

					'U_ACTION'			=> $this->u_action . "&amp;action=$action&amp;step=$step",
					'U_BACK'			=> $this->u_action)
				);

				// Now go through the steps
				switch ($step)
				{
					// Create basic options - only small differences between field types
					case 1:
						$template_vars = array(
							'S_STEP_ONE'		=> true,
							'S_FIELD_REQUIRED'	=> ($cp->vars['field_required']) ? true : false,
							'S_FIELD_SHOW_NOVALUE'=> ($cp->vars['field_show_novalue']) ? true : false,
							'S_SHOW_ON_REG'		=> ($cp->vars['field_show_on_reg']) ? true : false,
							'S_SHOW_ON_PM'		=> ($cp->vars['field_show_on_pm']) ? true : false,
							'S_SHOW_ON_VT'		=> ($cp->vars['field_show_on_vt']) ? true : false,
							'S_SHOW_ON_MEMBERLIST'=> ($cp->vars['field_show_on_ml']) ? true : false,
							'S_FIELD_HIDE'		=> ($cp->vars['field_hide']) ? true : false,
							'S_SHOW_PROFILE'	=> ($cp->vars['field_show_profile']) ? true : false,
							'S_FIELD_NO_VIEW'	=> ($cp->vars['field_no_view']) ? true : false,
							'S_FIELD_CONTACT'	=> $cp->vars['field_is_contact'],
							'FIELD_CONTACT_DESC'=> $cp->vars['field_contact_desc'],
							'FIELD_CONTACT_URL'	=> $cp->vars['field_contact_url'],

							'L_LANG_SPECIFIC'	=> sprintf($user->lang['LANG_SPECIFIC_OPTIONS'], $config['default_lang']),
							'FIELD_TYPE'		=> $profile_field->get_name(),
							'FIELD_IDENT'		=> $cp->vars['field_ident'],
							'LANG_NAME'			=> $cp->vars['lang_name'],
							'LANG_EXPLAIN'		=> $cp->vars['lang_explain'],
						);

						$field_data = $cp->vars;
						$profile_field->display_options($template_vars, $field_data);
						$cp->vars = $field_data;

						// Build common create options
						$template->assign_vars($template_vars);
					break;

					case 2:

						$template->assign_vars(array(
							'S_STEP_TWO'		=> true,
							'L_NEXT_STEP'			=> (sizeof($this->lang_defs['iso']) == 1) ? $user->lang['SAVE'] : $user->lang['PROFILE_LANG_OPTIONS'])
						);

						// Build options based on profile type
						$options = $profile_field->get_options($this->lang_defs['iso'][$config['default_lang']], $cp->vars);

						foreach ($options as $num => $option_ary)
						{
							$template->assign_block_vars('option', $option_ary);
						}

					break;

					// Define remaining language variables
					case 3:

						$template->assign_var('S_STEP_THREE', true);
						$options = $this->build_language_options($cp, $field_type, $action);

						foreach ($options as $lang_id => $lang_ary)
						{
							$template->assign_block_vars('options', array(
								'LANGUAGE'		=> sprintf($user->lang[(($lang_id == $this->edit_lang_id) ? 'DEFAULT_' : '') . 'ISO_LANGUAGE'], $lang_ary['lang_iso']))
							);

							foreach ($lang_ary['fields'] as $field_ident => $field_ary)
							{
								$template->assign_block_vars('options.field', array(
									'L_TITLE'		=> $field_ary['TITLE'],
									'L_EXPLAIN'		=> (isset($field_ary['EXPLAIN'])) ? $field_ary['EXPLAIN'] : '',
									'FIELD'			=> $field_ary['FIELD'])
								);
							}
						}

					break;
				}

				$template->assign_vars(array(
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields)
				);

				return;

			break;
		}

		$sql = 'SELECT *
			FROM ' . PROFILE_FIELDS_TABLE . '
			ORDER BY field_order';
		$result = $db->sql_query($sql);

		$s_one_need_edit = false;
		while ($row = $db->sql_fetchrow($result))
		{
			$active_lang = (!$row['field_active']) ? 'ACTIVATE' : 'DEACTIVATE';
			$active_value = (!$row['field_active']) ? 'activate' : 'deactivate';
			$id = $row['field_id'];

			$s_need_edit = (sizeof($this->lang_defs['diff'][$row['field_id']])) ? true : false;

			if ($s_need_edit)
			{
				$s_one_need_edit = true;
			}

			$profile_field = $this->type_collection[$row['field_type']];
			$template->assign_block_vars('fields', array(
				'FIELD_IDENT'		=> $row['field_ident'],
				'FIELD_TYPE'		=> $profile_field->get_name(),

				'L_ACTIVATE_DEACTIVATE'		=> $user->lang[$active_lang],
				'U_ACTIVATE_DEACTIVATE'		=> $this->u_action . "&amp;action=$active_value&amp;field_id=$id",
				'U_EDIT'					=> $this->u_action . "&amp;action=edit&amp;field_id=$id",
				'U_TRANSLATE'				=> $this->u_action . "&amp;action=edit&amp;field_id=$id&amp;step=3",
				'U_DELETE'					=> $this->u_action . "&amp;action=delete&amp;field_id=$id",
				'U_MOVE_UP'					=> $this->u_action . "&amp;action=move_up&amp;field_id=$id",
				'U_MOVE_DOWN'				=> $this->u_action . "&amp;action=move_down&amp;field_id=$id",

				'S_NEED_EDIT'				=> $s_need_edit)
			);
		}
		$db->sql_freeresult($result);

		// At least one option field needs editing?
		if ($s_one_need_edit)
		{
			$template->assign_var('S_NEED_EDIT', true);
		}

		$s_select_type = '';
		foreach ($this->type_collection as $key => $profile_field)
		{
			$s_select_type .= '<option value="' . $key . '">' . $profile_field->get_name() . '</option>';
		}

		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_TYPE_OPTIONS'	=> $s_select_type,
		));
	}

	/**
	* Build all Language specific options
	*/
	function build_language_options(&$cp, $field_type, $action = 'create')
	{
		global $user, $config, $db, $phpbb_container;

		$default_lang_id = (!empty($this->edit_lang_id)) ? $this->edit_lang_id : $this->lang_defs['iso'][$config['default_lang']];

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . LANG_TABLE . '
			WHERE lang_id <> ' . (int) $default_lang_id . '
			ORDER BY lang_english_name';
		$result = $db->sql_query($sql);

		$languages = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$languages[$row['lang_id']] = $row['lang_iso'];
		}
		$db->sql_freeresult($result);

		$profile_field = $this->type_collection[$field_type];
		$options = $profile_field->get_language_options($cp->vars);

		$lang_options = array();

		foreach ($options as $field => $field_type)
		{
			$lang_options[1]['lang_iso'] = $this->lang_defs['id'][$default_lang_id];
			$lang_options[1]['fields'][$field] = array(
				'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
				'FIELD'		=> '<dd>' . ((is_array($cp->vars[$field])) ? implode('<br />', $cp->vars[$field]) : bbcode_nl2br($cp->vars[$field])) . '</dd>'
			);

			if (isset($user->lang['CP_' . strtoupper($field) . '_EXPLAIN']))
			{
				$lang_options[1]['fields'][$field]['EXPLAIN'] = $user->lang['CP_' . strtoupper($field) . '_EXPLAIN'];
			}
		}

		foreach ($languages as $lang_id => $lang_iso)
		{
			$lang_options[$lang_id]['lang_iso'] = $lang_iso;
			foreach ($options as $field => $field_type)
			{
				$value = ($action == 'create') ? utf8_normalize_nfc(request_var('l_' . $field, array(0 => ''), true)) : $cp->vars['l_' . $field];
				if ($field == 'lang_options')
				{
					$var = (!isset($cp->vars['l_lang_options'][$lang_id]) || !is_array($cp->vars['l_lang_options'][$lang_id])) ? $cp->vars['lang_options'] : $cp->vars['l_lang_options'][$lang_id];

					switch ($field_type)
					{
						case 'two_options':

							$lang_options[$lang_id]['fields'][$field] = array(
								'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
								'FIELD'		=> '
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][0])) ? $value[$lang_id][0] : $var[0]) . '" /> ' . $user->lang['FIRST_OPTION'] . '</dd>
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][1])) ? $value[$lang_id][1] : $var[1]) . '" /> ' . $user->lang['SECOND_OPTION'] . '</dd>'
							);
						break;

						case 'optionfield':
							$value = ((isset($value[$lang_id])) ? ((is_array($value[$lang_id])) ?  implode("\n", $value[$lang_id]) : $value[$lang_id]) : implode("\n", $var));
							$lang_options[$lang_id]['fields'][$field] = array(
								'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
								'FIELD'		=> '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="7" cols="80">' . $value . '</textarea></dd>'
							);
						break;
					}

					if (isset($user->lang['CP_' . strtoupper($field) . '_EXPLAIN']))
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $user->lang['CP_' . strtoupper($field) . '_EXPLAIN'];
					}
				}
				else
				{
					$var = ($action == 'create' || !is_array($cp->vars[$field])) ? $cp->vars[$field] : $cp->vars[$field][$lang_id];

					$lang_options[$lang_id]['fields'][$field] = array(
						'TITLE'		=> $user->lang['CP_' . strtoupper($field)],
						'FIELD'		=> ($field_type == 'string') ? '<dd><input class="medium" type="text" name="l_' . $field . '[' . $lang_id . ']" value="' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '" /></dd>' : '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="3" cols="80">' . ((isset($value[$lang_id])) ? $value[$lang_id] : $var) . '</textarea></dd>'
					);

					if (isset($user->lang['CP_' . strtoupper($field) . '_EXPLAIN']))
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $user->lang['CP_' . strtoupper($field) . '_EXPLAIN'];
					}
				}
			}
		}

		return $lang_options;
	}

	/**
	* Save Profile Field
	*/
	function save_profile_field(&$cp, $field_type, $action = 'create')
	{
		global $db, $config, $user, $phpbb_container;

		$field_id = request_var('field_id', 0);

		// Collect all information, if something is going wrong, abort the operation
		$profile_sql = $profile_lang = $empty_lang = $profile_lang_fields = array();

		$default_lang_id = (!empty($this->edit_lang_id)) ? $this->edit_lang_id : $this->lang_defs['iso'][$config['default_lang']];

		if ($action == 'create')
		{
			$sql = 'SELECT MAX(field_order) as max_field_order
				FROM ' . PROFILE_FIELDS_TABLE;
			$result = $db->sql_query($sql);
			$new_field_order = (int) $db->sql_fetchfield('max_field_order');
			$db->sql_freeresult($result);

			$field_ident = $cp->vars['field_ident'];
		}

		// Save the field
		$profile_fields = array(
			'field_length'			=> $cp->vars['field_length'],
			'field_minlen'			=> $cp->vars['field_minlen'],
			'field_maxlen'			=> $cp->vars['field_maxlen'],
			'field_novalue'			=> $cp->vars['field_novalue'],
			'field_default_value'	=> $cp->vars['field_default_value'],
			'field_validation'		=> $cp->vars['field_validation'],
			'field_required'		=> $cp->vars['field_required'],
			'field_show_novalue'	=> $cp->vars['field_show_novalue'],
			'field_show_on_reg'		=> $cp->vars['field_show_on_reg'],
			'field_show_on_pm'		=> $cp->vars['field_show_on_pm'],
			'field_show_on_vt'		=> $cp->vars['field_show_on_vt'],
			'field_show_on_ml'		=> $cp->vars['field_show_on_ml'],
			'field_hide'			=> $cp->vars['field_hide'],
			'field_show_profile'	=> $cp->vars['field_show_profile'],
			'field_no_view'			=> $cp->vars['field_no_view'],
			'field_is_contact'		=> $cp->vars['field_is_contact'],
			'field_contact_desc'	=> $cp->vars['field_contact_desc'],
			'field_contact_url'		=> $cp->vars['field_contact_url'],
		);

		if ($action == 'create')
		{
			$profile_fields += array(
				'field_type'		=> $field_type,
				'field_ident'		=> $field_ident,
				'field_name'		=> $field_ident,
				'field_order'		=> $new_field_order + 1,
				'field_active'		=> 1
			);

			$sql = 'INSERT INTO ' . PROFILE_FIELDS_TABLE . ' ' . $db->sql_build_array('INSERT', $profile_fields);
			$db->sql_query($sql);

			$field_id = $db->sql_nextid();
		}
		else
		{
			$sql = 'UPDATE ' . PROFILE_FIELDS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $profile_fields) . "
				WHERE field_id = $field_id";
			$db->sql_query($sql);
		}

		$profile_field = $this->type_collection[$field_type];

		if ($action == 'create')
		{
			$field_ident = 'pf_' . $field_ident;

			$db_tools = $phpbb_container->get('dbal.tools');
			$db_tools->sql_column_add(PROFILE_FIELDS_DATA_TABLE, $field_ident, array($profile_field->get_database_column_type(), null));
		}

		$sql_ary = array(
			'lang_name'				=> $cp->vars['lang_name'],
			'lang_explain'			=> $cp->vars['lang_explain'],
			'lang_default_value'	=> $cp->vars['lang_default_value']
		);

		if ($action == 'create')
		{
			$sql_ary['field_id'] = $field_id;
			$sql_ary['lang_id'] = $default_lang_id;

			$profile_sql[] = 'INSERT INTO ' . PROFILE_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		}
		else
		{
			$this->update_insert(PROFILE_LANG_TABLE, $sql_ary, array('field_id' => $field_id, 'lang_id' => $default_lang_id));
		}

		if (is_array($cp->vars['l_lang_name']) && sizeof($cp->vars['l_lang_name']))
		{
			foreach ($cp->vars['l_lang_name'] as $lang_id => $data)
			{
				if (($cp->vars['lang_name'] != '' && $cp->vars['l_lang_name'][$lang_id] == '')
					|| ($cp->vars['lang_explain'] != '' && $cp->vars['l_lang_explain'][$lang_id] == '')
					|| ($cp->vars['lang_default_value'] != '' && $cp->vars['l_lang_default_value'][$lang_id] == ''))
				{
					$empty_lang[$lang_id] = true;
					break;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					$profile_lang[] = array(
						'field_id'		=> $field_id,
						'lang_id'		=> $lang_id,
						'lang_name'		=> $cp->vars['l_lang_name'][$lang_id],
						'lang_explain'	=> (isset($cp->vars['l_lang_explain'][$lang_id])) ? $cp->vars['l_lang_explain'][$lang_id] : '',
						'lang_default_value'	=> (isset($cp->vars['l_lang_default_value'][$lang_id])) ? $cp->vars['l_lang_default_value'][$lang_id] : ''
					);
				}
			}

			foreach ($empty_lang as $lang_id => $NULL)
			{
				$sql = 'DELETE FROM ' . PROFILE_LANG_TABLE . "
					WHERE field_id = $field_id
					AND lang_id = " . (int) $lang_id;
				$db->sql_query($sql);
			}
		}

		$cp->vars = $profile_field->get_language_options_input($cp->vars);

		if ($cp->vars['lang_options'])
		{
			if (!is_array($cp->vars['lang_options']))
			{
				$cp->vars['lang_options'] = explode("\n", $cp->vars['lang_options']);
			}

			if ($action != 'create')
			{
				$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . "
					WHERE field_id = $field_id
						AND lang_id = " . (int) $default_lang_id;
				$db->sql_query($sql);
			}

			foreach ($cp->vars['lang_options'] as $option_id => $value)
			{
				$sql_ary = array(
					'field_type'	=> $field_type,
					'lang_value'	=> $value
				);

				if ($action == 'create')
				{
					$sql_ary['field_id'] = $field_id;
					$sql_ary['lang_id'] = $default_lang_id;
					$sql_ary['option_id'] = (int) $option_id;

					$profile_sql[] = 'INSERT INTO ' . PROFILE_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
				}
				else
				{
					$this->update_insert(PROFILE_FIELDS_LANG_TABLE, $sql_ary, array(
						'field_id'	=> $field_id,
						'lang_id'	=> (int) $default_lang_id,
						'option_id'	=> (int) $option_id)
					);
				}
			}
		}

		if (is_array($cp->vars['l_lang_options']) && sizeof($cp->vars['l_lang_options']))
		{
			$empty_lang = array();

			foreach ($cp->vars['l_lang_options'] as $lang_id => $lang_ary)
			{
				if (!is_array($lang_ary))
				{
					$lang_ary = explode("\n", $lang_ary);
				}

				if (sizeof($lang_ary) != sizeof($cp->vars['lang_options']))
				{
					$empty_lang[$lang_id] = true;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					if ($action != 'create')
					{
						$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . "
							WHERE field_id = $field_id
							AND lang_id = " . (int) $lang_id;
						$db->sql_query($sql);
					}

					foreach ($lang_ary as $option_id => $value)
					{
						$profile_lang_fields[] = array(
							'field_id'		=> (int) $field_id,
							'lang_id'		=> (int) $lang_id,
							'option_id'		=> (int) $option_id,
							'field_type'	=> $field_type,
							'lang_value'	=> $value
						);
					}
				}
			}

			foreach ($empty_lang as $lang_id => $NULL)
			{
				$sql = 'DELETE FROM ' . PROFILE_FIELDS_LANG_TABLE . "
					WHERE field_id = $field_id
					AND lang_id = " . (int) $lang_id;
				$db->sql_query($sql);
			}
		}

		foreach ($profile_lang as $sql)
		{
			if ($action == 'create')
			{
				$profile_sql[] = 'INSERT INTO ' . PROFILE_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
			}
			else
			{
				$lang_id = $sql['lang_id'];
				unset($sql['lang_id'], $sql['field_id']);

				$this->update_insert(PROFILE_LANG_TABLE, $sql, array('lang_id' => (int) $lang_id, 'field_id' => $field_id));
			}
		}

		if (sizeof($profile_lang_fields))
		{
			foreach ($profile_lang_fields as $sql)
			{
				if ($action == 'create')
				{
					$profile_sql[] = 'INSERT INTO ' . PROFILE_FIELDS_LANG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
				}
				else
				{
					$lang_id = $sql['lang_id'];
					$option_id = $sql['option_id'];
					unset($sql['lang_id'], $sql['field_id'], $sql['option_id']);

					$this->update_insert(PROFILE_FIELDS_LANG_TABLE, $sql, array(
						'lang_id'	=> $lang_id,
						'field_id'	=> $field_id,
						'option_id'	=> $option_id)
					);
				}
			}
		}

		$db->sql_transaction('begin');

		if ($action == 'create')
		{
			foreach ($profile_sql as $sql)
			{
				$db->sql_query($sql);
			}
		}

		$db->sql_transaction('commit');

		if ($action == 'edit')
		{
			add_log('admin', 'LOG_PROFILE_FIELD_EDIT', $cp->vars['field_ident'] . ':' . $cp->vars['lang_name']);
			trigger_error($user->lang['CHANGED_PROFILE_FIELD'] . adm_back_link($this->u_action));
		}
		else
		{
			add_log('admin', 'LOG_PROFILE_FIELD_CREATE', substr($field_ident, 3) . ':' . $cp->vars['lang_name']);
			trigger_error($user->lang['ADDED_PROFILE_FIELD'] . adm_back_link($this->u_action));
		}
	}

	/**
	* Update, then insert if not successfull
	*/
	function update_insert($table, $sql_ary, $where_fields)
	{
		global $db;

		$where_sql = array();
		$check_key = '';

		foreach ($where_fields as $key => $value)
		{
			$check_key = (!$check_key) ? $key : $check_key;
			$where_sql[] = $key . ' = ' . ((is_string($value)) ? "'" . $db->sql_escape($value) . "'" : (int) $value);
		}

		if (!sizeof($where_sql))
		{
			return;
		}

		$sql = "SELECT $check_key
			FROM $table
			WHERE " . implode(' AND ', $where_sql);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			$sql_ary = array_merge($where_fields, $sql_ary);

			if (sizeof($sql_ary))
			{
				$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $sql_ary));
			}
		}
		else
		{
			if (sizeof($sql_ary))
			{
				$sql = "UPDATE $table SET " . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE ' . implode(' AND ', $where_sql);
				$db->sql_query($sql);
			}
		}
	}
}
