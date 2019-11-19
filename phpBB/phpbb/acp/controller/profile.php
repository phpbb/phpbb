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

namespace phpbb\acp\controller;

class profile
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\db\tools\tools_interface */
	protected $db_tools;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\di\service_collection */
	protected $pf_collection;

	/** @var \phpbb\profilefields\manager */
	protected $pf_manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/** @var int */
	protected $edit_lang_id;

	/** @var array */
	protected $lang_defs;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\db\tools\tools_interface	$db_tools		Database tools object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$language		Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\di\service_collection		$pf_collection	Profile field service collection
	 * @param \phpbb\profilefields\manager		$pf_manager		Profile filed manager
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\db\tools\tools_interface $db_tools,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\di\service_collection $pf_collection,
		\phpbb\profilefields\manager $pf_manager,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->config			= $config;
		$this->db				= $db;
		$this->db_tools			= $db_tools;
		$this->dispatcher		= $dispatcher;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->log				= $log;
		$this->pf_collection	= $pf_collection;
		$this->pf_manager		= $pf_manager;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
		$this->tables			= $tables;
	}

	public function main()
	{
		$this->language->add_lang(['ucp', 'acp/profile']);

		if (!function_exists('generate_smilies'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}

		if (!function_exists('user_get_id_name'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$action = $this->request->variable('action', '');
		$action = $this->request->is_set_post('create') ? 'create' : $action;
		$field_id = $this->request->variable('field_id', 0);

		$errors = [];

		$form_key = 'acp_profile';
		add_form_key($form_key);

		if (!$field_id && in_array($action, ['delete', 'edit', 'activate', 'deactivate', 'move_up', 'move_down']))
		{
			return trigger_error($this->language->lang('NO_FIELD_ID') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
		}

		// Build Language array
		// Based on this, we decide which elements need to be edited later and which language items are missing
		$this->lang_defs = [];

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . $this->tables['lang'] . '
			ORDER BY lang_english_name';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Make some arrays with all available languages
			$this->lang_defs['id'][$row['lang_id']] = $row['lang_iso'];
			$this->lang_defs['iso'][$row['lang_iso']] = $row['lang_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT field_id, lang_id
			FROM ' . $this->tables['profile_lang'] . '
			ORDER BY lang_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Which languages are available for each item
			$this->lang_defs['entry'][$row['field_id']][] = $row['lang_id'];
		}
		$this->db->sql_freeresult($result);

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
						FROM ' . $this->tables['profile_fields'] . '
						WHERE field_id = ' . (int) $field_id;
					$result = $this->db->sql_query($sql);
					$field_ident = (string) $this->db->sql_fetchfield('field_ident');
					$this->db->sql_freeresult($result);

					$this->db->sql_transaction('begin');

					$sql = 'DELETE FROM ' . $this->tables['profile_fields'] . ' WHERE field_id = ' . (int) $field_id;
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->tables['profile_fields_lang'] . ' WHERE field_id = ' . (int) $field_id;
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->tables['profile_lang'] . ' WHERE field_id = ' . (int) $field_id;
					$this->db->sql_query($sql);

					$this->db_tools->sql_column_remove($this->tables['profile_fields_data'], 'pf_' . $field_ident);

					$order = 0;

					$sql = 'SELECT *
						FROM ' . $this->tables['profile_fields'] . '
						ORDER BY field_order';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$order++;

						if ($row['field_order'] != $order)
						{
							$sql = 'UPDATE ' . $this->tables['profile_fields'] . "
								SET field_order = $order
								WHERE field_id = {$row['field_id']}";
							$this->db->sql_query($sql);
						}
					}
					$this->db->sql_freeresult($result);

					$this->db->sql_transaction('commit');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_REMOVED', false, [$field_ident]);

					return $this->helper->message_back('REMOVED_PROFILE_FIELD', 'acp_cpf');
				}
				else
				{
					confirm_box(false, 'DELETE_PROFILE_FIELD', build_hidden_fields([
						'action'	=> $action,
						'field_id'	=> $field_id,
					]));

					return redirect($this->helper->route('acp_cpf'));
				}
			break;

			case 'activate':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_profile'))
				{
					return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
				}

				$sql = 'SELECT lang_id
					FROM ' . $this->tables['lang'] . "
					WHERE lang_iso = '" . $this->db->sql_escape($this->config['default_lang']) . "'";
				$result = $this->db->sql_query($sql);
				$default_lang_id = (int) $this->db->sql_fetchfield('lang_id');
				$this->db->sql_freeresult($result);

				if (!in_array($default_lang_id, $this->lang_defs['entry'][$field_id]))
				{
					return trigger_error($this->language->lang('DEFAULT_LANGUAGE_NOT_FILLED') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . $this->tables['profile_fields'] . '
					SET field_active = 1
					WHERE field_id = ' . (int) $field_id;
				$this->db->sql_query($sql);

				$sql = 'SELECT field_ident
					FROM ' . $this->tables['profile_fields'] . '
					WHERE field_id = ' . (int) $field_id;
				$result = $this->db->sql_query($sql);
				$field_ident = (string) $this->db->sql_fetchfield('field_ident');
				$this->db->sql_freeresult($result);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_ACTIVATE', false, [$field_ident]);

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response();
					$json_response->send([
						'text'	=> $this->language->lang('DEACTIVATE'),
					]);
				}

				return $this->helper->message_back('PROFILE_FIELD_ACTIVATED', 'acp_cpf');
			break;

			case 'deactivate':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_profile'))
				{
					return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . $this->tables['profile_fields'] . '
					SET field_active = 0
					WHERE field_id = ' . (int) $field_id;
				$this->db->sql_query($sql);

				$sql = 'SELECT field_ident
					FROM ' . $this->tables['profile_fields'] . '
					WHERE field_id = ' . (int) $field_id;
				$result = $this->db->sql_query($sql);
				$field_ident = (string) $this->db->sql_fetchfield('field_ident');
				$this->db->sql_freeresult($result);

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response();
					$json_response->send([
						'text'	=> $this->language->lang('ACTIVATE'),
					]);
				}

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_DEACTIVATE', false, [$field_ident]);

				return $this->helper->message_back('PROFILE_FIELD_DEACTIVATED', 'acp_cpf');
			break;

			case 'move_up':
			case 'move_down':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_profile'))
				{
					return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
				}

				$sql = 'SELECT field_order
					FROM ' . $this->tables['profile_fields'] . '
					WHERE field_id = ' . (int) $field_id;
				$result = $this->db->sql_query($sql);
				$field_order = $this->db->sql_fetchfield('field_order');
				$this->db->sql_freeresult($result);

				if ($field_order === false || ($field_order == 0 && $action === 'move_up'))
				{
					break;
				}

				$field_order = (int) $field_order;
				$order_total = $field_order * 2 + (($action === 'move_up') ? -1 : 1);

				$sql = 'UPDATE ' . $this->tables['profile_fields'] . "
					SET field_order = $order_total - field_order
					WHERE field_order IN ($field_order, " . ($action === 'move_up' ? $field_order - 1 : $field_order + 1) . ')';
				$this->db->sql_query($sql);

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'	=> (bool) $this->db->sql_affectedrows(),
					]);
				}
			break;

			case 'create':
			case 'edit':
				$submit	= $this->request->is_set('next') || $this->request->is_set('prev');
				$save	= $this->request->is_set('save');
				$step	= $this->request->variable('step', 1);

				// The language id of default language
				$this->edit_lang_id = $this->lang_defs['iso'][$this->config['default_lang']];

				// We are editing... we need to grab basic things
				if ($action === 'edit')
				{
					$sql = 'SELECT l.*, f.*
						FROM ' . $this->tables['profile_lang'] . ' l,
							' . $this->tables['profile_fields'] . ' f
						WHERE l.lang_id = ' . (int) $this->edit_lang_id . '
							AND l.field_id = f.field_id
							AND f.field_id = ' . (int) $field_id;
					$result = $this->db->sql_query($sql);
					$field_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($field_row === false)
					{
						// Some admin changed the default language?
						$sql = 'SELECT l.*, f.*
							FROM ' . $this->tables['profile_lang'] . ' l,
								' . $this->tables['profile_fields'] . ' f
							WHERE l.lang_id <> ' . (int) $this->edit_lang_id . '
								AND l.field_id = f.field_id
								AND f.field_id = ' . (int) $field_id;
						$result = $this->db->sql_query($sql);
						$field_row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($field_row === false)
						{
							return trigger_error($this->language->lang('FIELD_NOT_FOUND') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
						}

						$this->edit_lang_id = (int) $field_row['lang_id'];
					}

					$field_type = $field_row['field_type'];
					$profile_field = $this->pf_collection[$field_type];

					// Get language entries
					$lang_options = [];

					$sql = 'SELECT *
						FROM ' . $this->tables['profile_fields_lang'] . '
						WHERE lang_id = ' . (int) $this->edit_lang_id . '
							AND field_id = ' . (int) $field_id . '
						ORDER BY option_id ASC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$lang_options[$row['option_id']] = $row['lang_value'];
					}
					$this->db->sql_freeresult($result);

					$s_hidden_fields = '<input type="hidden" name="field_id" value="' . $field_id . '" />';
				}
				else
				{
					// We are adding a new field, define basic params
					$lang_options = $field_row = [];

					$field_type = $this->request->variable('field_type', '');

					if (!isset($this->pf_collection[$field_type]))
					{
						return trigger_error($this->language->lang('NO_FIELD_TYPE') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
					}

					$profile_field = $this->pf_collection[$field_type];

					$field_row = array_merge($profile_field->get_default_option_values(), [
						'field_ident'		=> str_replace(' ', '_', utf8_clean_string($this->request->variable('field_ident', '', true))),
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
						'lang_name'			=> $this->request->variable('field_ident', '', true),
						'lang_explain'		=> '',
						'lang_default_value'=> '',
					]);

					$s_hidden_fields = '<input type="hidden" name="field_type" value="' . $field_type . '" />';
				}

				// $exclude contains the data we gather in each step
				$exclude = [
					1	=> ['field_ident', 'lang_name', 'lang_explain', 'field_option_none', 'field_show_on_reg', 'field_show_on_pm', 'field_show_on_vt', 'field_show_on_ml', 'field_required', 'field_show_novalue', 'field_hide', 'field_show_profile', 'field_no_view', 'field_is_contact', 'field_contact_desc', 'field_contact_url'],
					2	=> ['field_length', 'field_maxlen', 'field_minlen', 'field_validation', 'field_novalue', 'field_default_value'],
					3	=> ['l_lang_name', 'l_lang_explain', 'l_lang_default_value', 'l_lang_options'],
				];

				// Visibility Options...
				$visibility_ary = [
					'field_required',
					'field_show_novalue',
					'field_show_on_reg',
					'field_show_on_pm',
					'field_show_on_vt',
					'field_show_on_ml',
					'field_show_profile',
					'field_hide',
					'field_is_contact',
				];

				/**
				 * Event to add initialization for new profile field table fields
				 *
				 * @event core.acp_profile_create_edit_init
				 * @var string	action			create|edit
				 * @var int		step			Configuration step (1|2|3)
				 * @var bool	submit			Form has been submitted
				 * @var bool	save			Configuration should be saved
				 * @var string	field_type		Type of the field we are dealing with
				 * @var array	field_row		Array of data about the field
				 * @var array	exclude			Array of excluded fields by step
				 * @var array	visibility_ary	Array of fields that are visibility related
				 * @since 3.1.6-RC1
				 */
				$vars = [
					'action',
					'step',
					'submit',
					'save',
					'field_type',
					'field_row',
					'exclude',
					'visibility_ary',
				];
				extract($this->dispatcher->trigger_event('core.acp_profile_create_edit_init', compact($vars)));

				$options = $profile_field->prepare_options_form($exclude, $visibility_ary);

				$this->pf_manager->vars['field_ident']			= $action === 'create' && $step == 1 ? utf8_clean_string($this->request->variable('field_ident', $field_row['field_ident'], true)) : $this->request->variable('field_ident', $field_row['field_ident']);
				$this->pf_manager->vars['lang_name']			= $this->request->variable('lang_name', $field_row['lang_name'], true);
				$this->pf_manager->vars['lang_explain']			= $this->request->variable('lang_explain', $field_row['lang_explain'], true);
				$this->pf_manager->vars['lang_default_value']	= $this->request->variable('lang_default_value', $field_row['lang_default_value'], true);
				$this->pf_manager->vars['field_contact_desc']	= $this->request->variable('field_contact_desc', $field_row['field_contact_desc'], true);
				$this->pf_manager->vars['field_contact_url']	= $this->request->variable('field_contact_url', $field_row['field_contact_url'], true);

				foreach ($visibility_ary as $val)
				{
					$this->pf_manager->vars[$val] = $submit || $save ? $this->request->variable($val, 0) : $field_row[$val];
				}

				$this->pf_manager->vars['field_no_view'] = $this->request->variable('field_no_view', (int) $field_row['field_no_view']);

				// If the user has submitted a form with options (i.e. dropdown field)
				if ($options)
				{
					$exploded_options = is_array($options) ? $options : explode("\n", $options);

					if (count($exploded_options) === count($lang_options) || $action === 'create')
					{
						// The number of options in the field is equal to the number of options already in the database
						// Or we are creating a new dropdown list.
						$this->pf_manager->vars['lang_options'] = $exploded_options;
					}
					else if ($action === 'edit')
					{
						// Changing the number of options? (We remove and re-create the option fields)
						$this->pf_manager->vars['lang_options'] = $exploded_options;
					}
				}
				else
				{
					$this->pf_manager->vars['lang_options'] = $lang_options;
				}

				// step 2
				foreach ($exclude[2] as $key)
				{
					$var = $this->request->variable($key, $field_row[$key], true);

					$field_data = $this->pf_manager->vars;
					$var = $profile_field->get_excluded_options($key, $action, $var, $field_data, 2);
					$this->pf_manager->vars = $field_data;

					$this->pf_manager->vars[$key] = $var;
				}

				// step 3 - all arrays
				if ($action === 'edit')
				{
					$l_lang_options = $l_lang_name = [];
					$l_lang_explain = $l_lang_default_value = [];

					// Get language entries
					$sql = 'SELECT *
						FROM ' . $this->tables['profile_fields_lang'] . '
						WHERE lang_id <> ' . (int) $this->edit_lang_id . '
							AND field_id = ' . (int) $field_id . '
						ORDER BY option_id ASC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$l_lang_options[$row['lang_id']][$row['option_id']] = $row['lang_value'];
					}
					$this->db->sql_freeresult($result);

					$sql = 'SELECT lang_id, lang_name, lang_explain, lang_default_value
						FROM ' . $this->tables['profile_lang'] . '
						WHERE lang_id <> ' . (int) $this->edit_lang_id . '
							AND field_id = ' . (int) $field_id . '
						ORDER BY lang_id ASC';
					$result = $this->db->sql_query($sql);
					while ($row = $this->db->sql_fetchrow($result))
					{
						$l_lang_name[$row['lang_id']] = $row['lang_name'];
						$l_lang_explain[$row['lang_id']] = $row['lang_explain'];
						$l_lang_default_value[$row['lang_id']] = $row['lang_default_value'];
					}
					$this->db->sql_freeresult($result);
				}

				foreach ($exclude[3] as $key)
				{
					$this->pf_manager->vars[$key] = $this->request->variable($key, [0 => ''], true);

					if (!$this->pf_manager->vars[$key] && $action === 'edit')
					{
						$this->pf_manager->vars[$key] = ${$key};
					}

					$field_data = $this->pf_manager->vars;
					$var = $profile_field->get_excluded_options($key, $action, $var, $field_data, 3);
					$this->pf_manager->vars = $field_data;
				}

				// Check for general issues in every step
				if ($submit) // && $step == 1
				{
					// Check values for step 1
					if ($this->pf_manager->vars['field_ident'] == '')
					{
						$errors[] = $this->language->lang('EMPTY_FIELD_IDENT');
					}

					if (!preg_match('/^[a-z_]+$/', $this->pf_manager->vars['field_ident']))
					{
						$errors[] = $this->language->lang('INVALID_CHARS_FIELD_IDENT');
					}

					if (strlen($this->pf_manager->vars['field_ident']) > 17)
					{
						$errors[] = $this->language->lang('INVALID_FIELD_IDENT_LEN');
					}

					if ($this->pf_manager->vars['lang_name'] == '')
					{
						$errors[] = $this->language->lang('EMPTY_USER_FIELD_NAME');
					}

					$errors = $profile_field->validate_options_on_submit($errors, $this->pf_manager->vars);

					// Check for already existing field ident
					if ($action !== 'edit')
					{
						$sql = 'SELECT field_ident
							FROM ' . $this->tables['profile_fields'] . "
							WHERE field_ident = '" . $this->db->sql_escape($this->pf_manager->vars['field_ident']) . "'";
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($row)
						{
							$errors[] = $this->language->lang('FIELD_IDENT_ALREADY_EXIST');
						}
					}
				}

				if (!empty($errors))
				{
					$submit = false;
				}
				else
				{
					$step = $this->request->is_set('next') ? $step + 1 : ($this->request->is_set('prev') ? $step - 1 : $step);
				}

				// Build up the specific hidden fields
				foreach ($exclude as $num => $key_ary)
				{
					if ($num == $step)
					{
						continue;
					}

					$_new_key_ary = [];

					$field_data = $this->pf_manager->vars;
					foreach ($key_ary as $key)
					{
						$var = $profile_field->prepare_hidden_fields($step, $key, $action, $field_data);
						if ($var !== null)
						{
							$_new_key_ary[$key] = $var;
						}
					}
					$this->pf_manager->vars = $field_data;

					$s_hidden_fields .= build_hidden_fields($_new_key_ary);
				}

				if (empty($errors))
				{
					if (($step == 3 && (count($this->lang_defs['iso']) === 1 || $save)) || ($action === 'edit' && $save))
					{
						if (!check_form_key($form_key))
						{
							return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_cpf'), E_USER_WARNING);
						}

						return $this->save_profile_field($field_type, $action);
					}
				}

				$this->template->assign_vars([
					'S_EDIT'			=> true,
					'S_EDIT_MODE'		=> $action === 'edit',
					'ERROR_MSG'			=> !empty($errors) ? implode('<br />', $errors) : '',

					'L_TITLE'			=> $this->language->lang('STEP_' . $step . '_TITLE_' . strtoupper($action)),
					'L_EXPLAIN'			=> $this->language->lang('STEP_' . $step . '_EXPLAIN_' . strtoupper($action)),

					'U_ACTION'			=> $this->helper->route('acp_cpf', ['action' => $action, 'step' => $step]),
					'U_BACK'			=> $this->helper->route('acp_cpf'),
				]);

				// Now go through the steps
				switch ($step)
				{
					// Create basic options - only small differences between field types
					case 1:
						$template_vars = [
							'S_STEP_ONE'			=> true,
							'S_FIELD_REQUIRED'		=> (bool) $this->pf_manager->vars['field_required'],
							'S_FIELD_SHOW_NOVALUE'	=> (bool) $this->pf_manager->vars['field_show_novalue'],
							'S_SHOW_ON_REG'			=> (bool) $this->pf_manager->vars['field_show_on_reg'],
							'S_SHOW_ON_PM'			=> (bool) $this->pf_manager->vars['field_show_on_pm'],
							'S_SHOW_ON_VT'			=> (bool) $this->pf_manager->vars['field_show_on_vt'],
							'S_SHOW_ON_MEMBERLIST'	=> (bool) $this->pf_manager->vars['field_show_on_ml'],
							'S_FIELD_HIDE'			=> (bool) $this->pf_manager->vars['field_hide'],
							'S_SHOW_PROFILE'		=> (bool) $this->pf_manager->vars['field_show_profile'],
							'S_FIELD_NO_VIEW'		=> (bool) $this->pf_manager->vars['field_no_view'],
							'S_FIELD_CONTACT'		=> $this->pf_manager->vars['field_is_contact'],
							'FIELD_CONTACT_DESC'	=> $this->pf_manager->vars['field_contact_desc'],
							'FIELD_CONTACT_URL'		=> $this->pf_manager->vars['field_contact_url'],

							'FIELD_TYPE'			=> $profile_field->get_name(),
							'FIELD_IDENT'			=> $this->pf_manager->vars['field_ident'],
							'LANG_NAME'				=> $this->pf_manager->vars['lang_name'],
							'LANG_EXPLAIN'			=> $this->pf_manager->vars['lang_explain'],
							'L_LANG_SPECIFIC'		=> $this->language->lang('LANG_SPECIFIC_OPTIONS', $this->config['default_lang']),
						];

						$field_data = $this->pf_manager->vars;
						$profile_field->display_options($template_vars, $field_data);
						$this->pf_manager->vars = $field_data;

						// Build common create options
						$this->template->assign_vars($template_vars);
					break;

					case 2:
						$this->template->assign_vars([
							'S_STEP_TWO'	=> true,
							'L_NEXT_STEP'	=> count($this->lang_defs['iso']) === 1 ? $this->language->lang('SAVE') : $this->language->lang('PROFILE_LANG_OPTIONS'),
						]);

						// Build options based on profile type
						$options = $profile_field->get_options($this->lang_defs['iso'][$this->config['default_lang']], $this->pf_manager->vars);

						foreach ($options as $num => $option_ary)
						{
							$this->template->assign_block_vars('option', $option_ary);
						}
					break;

					// Define remaining language variables
					case 3:
						$this->template->assign_var('S_STEP_THREE', true);
						$options = $this->build_language_options($field_type, $action);

						foreach ($options as $lang_id => $lang_ary)
						{
							$default = $lang_id == $this->edit_lang_id ? 'DEFAULT_' : '';

							$this->template->assign_block_vars('options', [
								'LANGUAGE'	=> $this->language->lang($default . 'ISO_LANGUAGE', $lang_ary['lang_iso']),
							]);

							foreach ($lang_ary['fields'] as $field_ident => $field_ary)
							{
								$this->template->assign_block_vars('options.field', [
									'FIELD'			=> $field_ary['FIELD'],
									'L_TITLE'		=> $field_ary['TITLE'],
									'L_EXPLAIN'		=> isset($field_ary['EXPLAIN']) ? $field_ary['EXPLAIN'] : '',
								]);
							}
						}
					break;
				}

				$field_data = $this->pf_manager->vars;

				/**
				 * Event to add template variables for new profile field table fields
				 *
				 * @event core.acp_profile_create_edit_after
				 * @var string	action			create|edit
				 * @var int		step			Configuration step (1|2|3)
				 * @var bool	submit			Form has been submitted
				 * @var bool	save			Configuration should be saved
				 * @var string	field_type		Type of the field we are dealing with
				 * @var array	field_data		Array of data about the field
				 * @var string	s_hidden_fields	String of hidden fields in case this needs modification
				 * @var array	options			Array of options specific to this step
				 * @since 3.1.6-RC1
				 */
				$vars = [
					'action',
					'step',
					'submit',
					'save',
					'field_type',
					'field_data',
					's_hidden_fields',
					'options',
				];
				extract($this->dispatcher->trigger_event('core.acp_profile_create_edit_after', compact($vars)));

				unset($field_data);

				$this->template->assign_vars(['S_HIDDEN_FIELDS' => $s_hidden_fields]);

				return $this->helper->render('acp_profile.html', 'ACP_CUSTOM_PROFILE_FIELDS');
			break;
		}

		$tpl_name	= 'acp_profile.html';
		$page_title	= $this->language->lang('ACP_CUSTOM_PROFILE_FIELDS');
		$u_action	= $this->helper->get_current_url();

		/**
		 * Event to handle actions on the ACP profile fields page
		 *
		 * @event core.acp_profile_action
		 * @var string	action		Action that is being performed
		 * @var string	tpl_name	Template file to load
		 * @var string	page_title	Page title
		 * @var string	u_action	The URL we are at, read only
		 * @since 3.2.2-RC1
		 */
		$vars = [
			'action',
			'tpl_name',
			'page_title',
			'u_action',
		];
		extract($this->dispatcher->trigger_event('core.acp_profile_action', compact($vars)));

		unset($u_action);

		$s_one_need_edit = false;

		$sql = 'SELECT *
			FROM ' . $this->tables['profile_fields'] . '
			ORDER BY field_order';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$active_lang = !$row['field_active'] ? 'ACTIVATE' : 'DEACTIVATE';
			$active_value = !$row['field_active'] ? 'activate' : 'deactivate';
			$id = $row['field_id'];

			$s_need_edit = !empty($this->lang_defs['diff'][$row['field_id']]);

			if ($s_need_edit)
			{
				$s_one_need_edit = true;
			}

			if (!isset($this->pf_collection[$row['field_type']]))
			{
				continue;
			}

			$profile_field = $this->pf_collection[$row['field_type']];

			$field_block = [
				'FIELD_IDENT'		=> $row['field_ident'],
				'FIELD_TYPE'		=> $profile_field->get_name(),

				'S_NEED_EDIT'				=> $s_need_edit,

				'L_ACTIVATE_DEACTIVATE'		=> $this->language->lang($active_lang),
				'U_ACTIVATE_DEACTIVATE'		=> $this->helper->route('acp_cpf', ['action' => $active_value, 'field_id' => $id, 'hash' => generate_link_hash('acp_profile')]),
				'U_DELETE'					=> $this->helper->route('acp_cpf', ['action' => 'delete', 'field_id' => $id]),
				'U_EDIT'					=> $this->helper->route('acp_cpf', ['action' => 'edit', 'field_id' => $id]),
				'U_TRANSLATE'				=> $this->helper->route('acp_cpf', ['action' => 'edit', 'field_id' => $id, 'step' => 3]),
				'U_MOVE_DOWN'				=> $this->helper->route('acp_cpf', ['action' => 'move_down', 'field_id' => $id, 'hash' => generate_link_hash('acp_profile')]),
				'U_MOVE_UP'					=> $this->helper->route('acp_cpf', ['action' => 'move_up', 'field_id' => $id, 'hash' => generate_link_hash('acp_profile')]),
			];

			/**
			 * Event to modify profile field data before it is assigned to the template
			 *
			 * @event core.acp_profile_modify_profile_row
			 * @var array	row				Array with data for the current profile field
			 * @var array	field_block		Template data that is being assigned to the 'fields' block
			 * @var object	profile_field	A profile field instance, implements \phpbb\profilefields\type\type_base
			 * @since 3.2.2-RC1
			 */
			$vars = [
				'row',
				'field_block',
				'profile_field',
			];
			extract($this->dispatcher->trigger_event('core.acp_profile_modify_profile_row', compact($vars)));

			$this->template->assign_block_vars('fields', $field_block);
		}
		$this->db->sql_freeresult($result);

		// At least one option field needs editing?
		if ($s_one_need_edit)
		{
			$this->template->assign_var('S_NEED_EDIT', true);
		}

		$s_select_type = '';
		foreach ($this->pf_collection as $key => $profile_field)
		{
			$s_select_type .= '<option value="' . $key . '">' . $profile_field->get_name() . '</option>';
		}

		$this->template->assign_vars([
			'S_TYPE_OPTIONS'	=> $s_select_type,
			'U_ACTION'			=> $this->helper->route('acp_cpf'),
		]);

		return $this->helper->render($tpl_name, $page_title);
	}

	/**
	 * Build all Language specific options.
	 *
	 * @param string	$field_type
	 * @param string	$action
	 * @return array
	 */
	protected function build_language_options($field_type, $action = 'create')
	{
		$default_lang_id = !empty($this->edit_lang_id) ? $this->edit_lang_id : $this->lang_defs['iso'][$this->config['default_lang']];

		$languages = [];

		$sql = 'SELECT lang_id, lang_iso
			FROM ' . $this->tables['lang'] . '
			WHERE lang_id <> ' . (int) $default_lang_id . '
			ORDER BY lang_english_name';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$languages[$row['lang_id']] = $row['lang_iso'];
		}
		$this->db->sql_freeresult($result);

		$profile_field = $this->pf_collection[$field_type];
		$options = $profile_field->get_language_options($this->pf_manager->vars);

		$lang_options = [];

		foreach ($options as $field => $field_type)
		{
			$lang_options[1]['lang_iso'] = $this->lang_defs['id'][$default_lang_id];
			$lang_options[1]['fields'][$field] = [
				'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
				'FIELD'		=> '<dd>' . (is_array($this->pf_manager->vars[$field]) ? implode('<br />', $this->pf_manager->vars[$field]) : bbcode_nl2br($this->pf_manager->vars[$field])) . '</dd>',
			];

			if ($this->language->is_set('CP_' . strtoupper($field) . '_EXPLAIN'))
			{
				$lang_options[1]['fields'][$field]['EXPLAIN'] = $this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN');
			}
		}

		foreach ($languages as $lang_id => $lang_iso)
		{
			$lang_options[$lang_id]['lang_iso'] = $lang_iso;
			foreach ($options as $field => $field_type)
			{
				$value = $action === 'create' ? $this->request->variable('l_' . $field, [0 => ''], true) : $this->pf_manager->vars['l_' . $field];
				if ($field === 'lang_options')
				{
					$var = (!isset($this->pf_manager->vars['l_lang_options'][$lang_id]) || !is_array($this->pf_manager->vars['l_lang_options'][$lang_id])) ? $this->pf_manager->vars['lang_options'] : $this->pf_manager->vars['l_lang_options'][$lang_id];

					switch ($field_type)
					{
						case 'two_options':

							$lang_options[$lang_id]['fields'][$field] = [
								'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
								'FIELD'		=> '
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][0])) ? $value[$lang_id][0] : $var[0]) . '" /> ' . $this->language->lang('FIRST_OPTION') . '</dd>
											<dd><input class="medium" name="l_' . $field . '[' . $lang_id . '][]" value="' . ((isset($value[$lang_id][1])) ? $value[$lang_id][1] : $var[1]) . '" /> ' . $this->language->lang('SECOND_OPTION') . '</dd>',
							];
						break;

						case 'optionfield':
							$value = isset($value[$lang_id]) ? (is_array($value[$lang_id]) ? implode("\n", $value[$lang_id]) : $value[$lang_id]) : implode("\n", $var);
							$lang_options[$lang_id]['fields'][$field] = [
								'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
								'FIELD'		=> '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="7" cols="80">' . $value . '</textarea></dd>',
							];
						break;
					}

					if ($this->language->is_set('CP_' . strtoupper($field) . '_EXPLAIN'))
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN');
					}
				}
				else
				{
					$var = ($action === 'create' || !is_array($this->pf_manager->vars[$field])) ? $this->pf_manager->vars[$field] : $this->pf_manager->vars[$field][$lang_id];

					$lang_options[$lang_id]['fields'][$field] = [
						'TITLE'		=> $this->language->lang('CP_' . strtoupper($field)),
						'FIELD'		=> $field_type === 'string'
							? '<dd><input class="medium" type="text" name="l_' . $field . '[' . $lang_id . ']" value="' . (isset($value[$lang_id]) ? $value[$lang_id] : $var) . '" /></dd>'
							: '<dd><textarea name="l_' . $field . '[' . $lang_id . ']" rows="3" cols="80">' . (isset($value[$lang_id]) ? $value[$lang_id] : $var) . '</textarea></dd>',
					];

					if ($this->language->is_set('CP_' . strtoupper($field) . '_EXPLAIN'))
					{
						$lang_options[$lang_id]['fields'][$field]['EXPLAIN'] = $this->language->lang('CP_' . strtoupper($field) . '_EXPLAIN');
					}
				}
			}
		}

		return $lang_options;
	}

	/**
	 * Save Profile Field
	 *
	 * @param string	$field_type
	 * @param string	$action
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function save_profile_field($field_type, $action = 'create')
	{
		$field_id = $this->request->variable('field_id', 0);

		// Collect all information, if something is going wrong, abort the operation
		$field_ident = '';
		$new_field_order = 0;
		$profile_sql = $profile_lang = [];
		$empty_lang = $profile_lang_fields = [];

		$default_lang_id = !empty($this->edit_lang_id) ? $this->edit_lang_id : $this->lang_defs['iso'][$this->config['default_lang']];

		if ($action === 'create')
		{
			$sql = 'SELECT MAX(field_order) as max_field_order
				FROM ' . $this->tables['profile_fields'];
			$result = $this->db->sql_query($sql);
			$new_field_order = (int) $this->db->sql_fetchfield('max_field_order');
			$this->db->sql_freeresult($result);

			$field_ident = $this->pf_manager->vars['field_ident'];
		}

		// Save the field
		$profile_fields = [
			'field_length'			=> $this->pf_manager->vars['field_length'],
			'field_minlen'			=> $this->pf_manager->vars['field_minlen'],
			'field_maxlen'			=> $this->pf_manager->vars['field_maxlen'],
			'field_novalue'			=> $this->pf_manager->vars['field_novalue'],
			'field_default_value'	=> $this->pf_manager->vars['field_default_value'],
			'field_validation'		=> $this->pf_manager->vars['field_validation'],
			'field_required'		=> $this->pf_manager->vars['field_required'],
			'field_show_novalue'	=> $this->pf_manager->vars['field_show_novalue'],
			'field_show_on_reg'		=> $this->pf_manager->vars['field_show_on_reg'],
			'field_show_on_pm'		=> $this->pf_manager->vars['field_show_on_pm'],
			'field_show_on_vt'		=> $this->pf_manager->vars['field_show_on_vt'],
			'field_show_on_ml'		=> $this->pf_manager->vars['field_show_on_ml'],
			'field_hide'			=> $this->pf_manager->vars['field_hide'],
			'field_show_profile'	=> $this->pf_manager->vars['field_show_profile'],
			'field_no_view'			=> $this->pf_manager->vars['field_no_view'],
			'field_is_contact'		=> $this->pf_manager->vars['field_is_contact'],
			'field_contact_desc'	=> $this->pf_manager->vars['field_contact_desc'],
			'field_contact_url'		=> $this->pf_manager->vars['field_contact_url'],
		];

		$field_data = $this->pf_manager->vars;

		/**
		 * Event to modify profile field configuration data before saving to database
		 *
		 * @event core.acp_profile_create_edit_save_before
		 * @var string	action			create|edit
		 * @var string	field_type		Type of the field we are dealing with
		 * @var array	field_data		Array of data about the field
		 * @var array	profile_fields	Array of fields to be sent to the database
		 * @since 3.1.6-RC1
		 */
		$vars = [
			'action',
			'field_type',
			'field_data',
			'profile_fields',
		];
		extract($this->dispatcher->trigger_event('core.acp_profile_create_edit_save_before', compact($vars)));

		unset($field_data);

		if ($action === 'create')
		{
			$profile_fields += [
				'field_type'		=> $field_type,
				'field_ident'		=> $field_ident,
				'field_name'		=> $field_ident,
				'field_order'		=> $new_field_order + 1,
				'field_active'		=> 1,
			];

			$sql = 'INSERT INTO ' . $this->tables['profile_fields'] . ' ' . $this->db->sql_build_array('INSERT', $profile_fields);
			$this->db->sql_query($sql);

			$field_id = (int) $this->db->sql_nextid();
		}
		else
		{
			$sql = 'UPDATE ' . $this->tables['profile_fields'] . '
				SET ' . $this->db->sql_build_array('UPDATE', $profile_fields) . '
				WHERE field_id = ' . (int) $field_id;
			$this->db->sql_query($sql);
		}

		$profile_field = $this->pf_collection[$field_type];

		if ($action === 'create')
		{
			$field_ident = 'pf_' . $field_ident;
			$this->db_tools->sql_column_add($this->tables['profile_fields_data'], $field_ident, [$profile_field->get_database_column_type(), null]);
		}

		$sql_ary = [
			'lang_name'				=> $this->pf_manager->vars['lang_name'],
			'lang_explain'			=> $this->pf_manager->vars['lang_explain'],
			'lang_default_value'	=> $this->pf_manager->vars['lang_default_value'],
		];

		if ($action === 'create')
		{
			$sql_ary['field_id'] = $field_id;
			$sql_ary['lang_id'] = $default_lang_id;

			$profile_sql[] = 'INSERT INTO ' . $this->tables['profile_lang'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
		}
		else
		{
			$this->update_insert($this->tables['profile_lang'], $sql_ary, ['field_id' => $field_id, 'lang_id' => $default_lang_id]);
		}

		if (is_array($this->pf_manager->vars['l_lang_name']) && !empty($this->pf_manager->vars['l_lang_name']))
		{
			foreach ($this->pf_manager->vars['l_lang_name'] as $lang_id => $data)
			{
				if (($this->pf_manager->vars['lang_name'] != '' && $this->pf_manager->vars['l_lang_name'][$lang_id] == '')
					|| ($this->pf_manager->vars['lang_explain'] != '' && $this->pf_manager->vars['l_lang_explain'][$lang_id] == '')
					|| ($this->pf_manager->vars['lang_default_value'] != '' && $this->pf_manager->vars['l_lang_default_value'][$lang_id] == ''))
				{
					$empty_lang[$lang_id] = true;
					break;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					$profile_lang[] = [
						'field_id'		=> $field_id,
						'lang_id'		=> $lang_id,
						'lang_name'		=> $this->pf_manager->vars['l_lang_name'][$lang_id],
						'lang_explain'	=> isset($this->pf_manager->vars['l_lang_explain'][$lang_id]) ? $this->pf_manager->vars['l_lang_explain'][$lang_id] : '',
						'lang_default_value'	=> isset($this->pf_manager->vars['l_lang_default_value'][$lang_id]) ? $this->pf_manager->vars['l_lang_default_value'][$lang_id] : '',
					];
				}
			}

			foreach ($empty_lang as $lang_id => $NULL)
			{
				$sql = 'DELETE FROM ' . $this->tables['profile_lang'] . '
					WHERE field_id = '. (int) $field_id . '
					AND lang_id = ' . (int) $lang_id;
				$this->db->sql_query($sql);
			}
		}

		$this->pf_manager->vars = $profile_field->get_language_options_input($this->pf_manager->vars);

		if ($this->pf_manager->vars['lang_options'])
		{
			if (!is_array($this->pf_manager->vars['lang_options']))
			{
				$this->pf_manager->vars['lang_options'] = explode("\n", $this->pf_manager->vars['lang_options']);
			}

			if ($action !== 'create')
			{
				$sql = 'DELETE FROM ' . $this->tables['profile_fields_lang'] . '
					WHERE field_id = ' . (int) $field_id . '
						AND lang_id = ' . (int) $default_lang_id;
				$this->db->sql_query($sql);
			}

			foreach ($this->pf_manager->vars['lang_options'] as $option_id => $value)
			{
				$sql_ary = [
					'field_type'	=> $field_type,
					'lang_value'	=> $value,
				];

				if ($action === 'create')
				{
					$sql_ary['field_id'] = $field_id;
					$sql_ary['lang_id'] = $default_lang_id;
					$sql_ary['option_id'] = (int) $option_id;

					$profile_sql[] = 'INSERT INTO ' . $this->tables['profile_fields_lang'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
				}
				else
				{
					$this->update_insert($this->tables['profile_fields_lang'], $sql_ary, [
						'field_id'	=> $field_id,
						'lang_id'	=> (int) $default_lang_id,
						'option_id'	=> (int) $option_id,
					]);
				}
			}
		}

		if (is_array($this->pf_manager->vars['l_lang_options']) && !empty($this->pf_manager->vars['l_lang_options']))
		{
			$empty_lang = [];

			foreach ($this->pf_manager->vars['l_lang_options'] as $lang_id => $lang_ary)
			{
				if (!is_array($lang_ary))
				{
					$lang_ary = explode("\n", $lang_ary);
				}

				if (count($lang_ary) !== count($this->pf_manager->vars['lang_options']))
				{
					$empty_lang[$lang_id] = true;
				}

				if (!isset($empty_lang[$lang_id]))
				{
					if ($action !== 'create')
					{
						$sql = 'DELETE FROM ' . $this->tables['profile_fields_lang'] . '
							WHERE field_id = ' . (int) $field_id . '
							AND lang_id = ' . (int) $lang_id;
						$this->db->sql_query($sql);
					}

					foreach ($lang_ary as $option_id => $value)
					{
						$profile_lang_fields[] = [
							'field_id'		=> (int) $field_id,
							'lang_id'		=> (int) $lang_id,
							'option_id'		=> (int) $option_id,
							'field_type'	=> $field_type,
							'lang_value'	=> $value,
						];
					}
				}
			}

			foreach ($empty_lang as $lang_id => $NULL)
			{
				$sql = 'DELETE FROM ' . $this->tables['profile_fields_lang'] . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id;
				$this->db->sql_query($sql);
			}
		}

		foreach ($profile_lang as $sql)
		{
			if ($action === 'create')
			{
				$profile_sql[] = 'INSERT INTO ' . $this->tables['profile_lang'] . ' ' . $this->db->sql_build_array('INSERT', $sql);
			}
			else
			{
				$lang_id = $sql['lang_id'];
				unset($sql['lang_id'], $sql['field_id']);

				$this->update_insert($this->tables['profile_lang'], $sql, ['lang_id' => (int) $lang_id, 'field_id' => $field_id]);
			}
		}

		if (!empty($profile_lang_fields))
		{
			foreach ($profile_lang_fields as $sql)
			{
				if ($action === 'create')
				{
					$profile_sql[] = 'INSERT INTO ' . $this->tables['profile_fields_lang'] . ' ' . $this->db->sql_build_array('INSERT', $sql);
				}
				else
				{
					$lang_id = $sql['lang_id'];
					$option_id = $sql['option_id'];
					unset($sql['lang_id'], $sql['field_id'], $sql['option_id']);

					$this->update_insert($this->tables['profile_fields_lang'], $sql, [
						'lang_id'	=> $lang_id,
						'field_id'	=> $field_id,
						'option_id'	=> $option_id,
					]);
				}
			}
		}

		$this->db->sql_transaction('begin');

		if ($action === 'create')
		{
			foreach ($profile_sql as $sql)
			{
				$this->db->sql_query($sql);
			}
		}

		$this->db->sql_transaction('commit');

		if ($action === 'edit')
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_EDIT', false, [$this->pf_manager->vars['field_ident'] . ':' . $this->pf_manager->vars['lang_name']]);

			return $this->helper->message_back('CHANGED_PROFILE_FIELD', 'acp_cpf');
		}
		else
		{
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_PROFILE_FIELD_CREATE', false, [substr($field_ident, 3) . ':' . $this->pf_manager->vars['lang_name']]);

			return $this->helper->message_back('ADDED_PROFILE_FIELD', 'acp_cpf');
		}
	}

	/**
	 * Update, then insert if not successful
	 *
	 * @param string	$table
	 * @param array		$sql_ary
	 * @param array		$where_fields
	 * @return void
	 */
	protected function update_insert($table, array $sql_ary, array $where_fields)
	{
		$check_key = '';
		$where_sql = [];

		foreach ($where_fields as $key => $value)
		{
			$check_key = !$check_key ? $key : $check_key;
			$where_sql[] = $key . ' = ' . (is_string($value) ? "'" . $this->db->sql_escape($value) . "'" : (int) $value);
		}

		if (!empty($where_sql))
		{
			$sql = "SELECT $check_key
			FROM $table
			WHERE " . implode(' AND ', $where_sql);
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				$sql_ary = array_merge($where_fields, $sql_ary);

				if (!empty($sql_ary))
				{
					$sql = 'INSERT INTO ' . $table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
				}
			}
			else
			{
				if (!empty($sql_ary))
				{
					$sql = "UPDATE $table SET " . $this->db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE ' . implode(' AND ', $where_sql);
					$this->db->sql_query($sql);
				}
			}
		}
	}
}
