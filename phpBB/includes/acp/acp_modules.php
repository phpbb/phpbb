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

use phpbb\module\exception\module_exception;

/**
* - Able to check for new module versions (modes changed/adjusted/added/removed)
* Icons for:
* - module enabled and displayed (common)
* - module enabled and not displayed
* - module deactivated
* - category (enabled)
* - category disabled
*/

class acp_modules
{
	var $module_class = '';
	var $parent_id;
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $template, $module, $request, $phpbb_log, $phpbb_container;

		/** @var \phpbb\module\module_manager $module_manager */
		$module_manager = $phpbb_container->get('module.manager');

		// Set a global define for modules we might include (the author is able to prevent execution of code by checking this constant)
		define('MODULE_INCLUDE', true);

		$user->add_lang('acp/modules');
		$this->tpl_name = 'acp_modules';

		$form_key = 'acp_modules';
		add_form_key($form_key);

		// module class
		$this->module_class = $mode;

		if ($this->module_class == 'ucp')
		{
			$user->add_lang('ucp');
		}
		else if ($this->module_class == 'mcp')
		{
			$user->add_lang('mcp');
		}

		if ($module->p_class != $this->module_class)
		{
			$module->add_mod_info($this->module_class);
		}

		$this->page_title = strtoupper($this->module_class);

		$this->parent_id = $request->variable('parent_id', 0);
		$module_id = $request->variable('m', 0);
		$action = $request->variable('action', '');
		$errors = array();

		switch ($action)
		{
			case 'delete':
				if (!$module_id)
				{
					trigger_error($user->lang['NO_MODULE_ID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					// Make sure we are not directly within a module
					if ($module_id == $this->parent_id)
					{
						$sql = 'SELECT parent_id
							FROM ' . MODULES_TABLE . '
							WHERE module_id = ' . $module_id;
						$result = $db->sql_query($sql);
						$this->parent_id = (int) $db->sql_fetchfield('parent_id');
						$db->sql_freeresult($result);
					}

					try
					{
						$row = $module_manager->get_module_row($module_id, $this->module_class);
						$module_manager->delete_module($module_id, $this->module_class);
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_MODULE_REMOVED', false, array($user->lang($row['module_langname'])));
					}
					catch (module_exception $e)
					{
						$msg = $user->lang($e->getMessage());
						trigger_error($msg . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
					}

					$module_manager->remove_cache_file($this->module_class);
					trigger_error($user->lang['MODULE_DELETED'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}
				else
				{
					confirm_box(false, 'DELETE_MODULE', build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'parent_id'	=> $this->parent_id,
						'module_id'	=> $module_id,
						'action'	=> $action,
					)));
				}

			break;

			case 'enable':
			case 'disable':
				if (!$module_id)
				{
					trigger_error($user->lang['NO_MODULE_ID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				if (!check_link_hash($request->variable('hash', ''), 'acp_modules'))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_class = '" . $db->sql_escape($this->module_class) . "'
						AND module_id = $module_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_MODULE'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'UPDATE ' . MODULES_TABLE . '
					SET module_enabled = ' . (($action == 'enable') ? 1 : 0) . "
					WHERE module_class = '" . $db->sql_escape($this->module_class) . "'
						AND module_id = $module_id";
				$db->sql_query($sql);

				$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_MODULE_' . strtoupper($action), false, array($user->lang($row['module_langname'])));
				$module_manager->remove_cache_file($this->module_class);

			break;

			case 'move_up':
			case 'move_down':
				if (!$module_id)
				{
					trigger_error($user->lang['NO_MODULE_ID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				if (!check_link_hash($request->variable('hash', ''), 'acp_modules'))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . MODULES_TABLE . "
					WHERE module_class = '" . $db->sql_escape($this->module_class) . "'
						AND module_id = $module_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_MODULE'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				try
				{
					$move_module_name = $module_manager->move_module_by($row, $this->module_class, $action, 1);

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_MODULE_' . strtoupper($action), false, array($user->lang($row['module_langname']), $move_module_name));
					$module_manager->remove_cache_file($this->module_class);
				}
				catch (module_exception $e)
				{
					// Do nothing
				}

				if ($request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send(array(
						'success'	=> ($move_module_name !== false),
					));
				}

			break;

			case 'quickadd':
				$quick_install = $request->variable('quick_install', '');

				if (confirm_box(true))
				{
					if (!$quick_install || strpos($quick_install, '::') === false)
					{
						break;
					}

					list($module_basename, $module_mode) = explode('::', $quick_install);

					// Check if module name and mode exist...
					$fileinfo = $module_manager->get_module_infos($this->module_class, $module_basename);
					$fileinfo = $fileinfo[$module_basename];

					if (isset($fileinfo['modes'][$module_mode]))
					{
						$module_data = array(
							'module_basename'	=> $module_basename,
							'module_enabled'	=> 0,
							'module_display'	=> (isset($fileinfo['modes'][$module_mode]['display'])) ? $fileinfo['modes'][$module_mode]['display'] : 1,
							'parent_id'			=> $this->parent_id,
							'module_class'		=> $this->module_class,
							'module_langname'	=> $fileinfo['modes'][$module_mode]['title'],
							'module_mode'		=> $module_mode,
							'module_auth'		=> $fileinfo['modes'][$module_mode]['auth'],
						);

						try
						{
							$module_manager->update_module_data($module_data);
							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_MODULE_ADD', false, array($user->lang($module_data['module_langname'])));
						}
						catch (\phpbb\module\exception\module_exception $e)
						{
							$msg = $user->lang($e->getMessage());
							trigger_error($msg . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
						}

						if (!count($errors))
						{
							$module_manager->remove_cache_file($this->module_class);

							trigger_error($user->lang['MODULE_ADDED'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
						}
					}
				}
				else
				{
					confirm_box(false, 'ADD_MODULE', build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'parent_id'	=> $this->parent_id,
						'action'	=> 'quickadd',
						'quick_install'	=> $quick_install,
					)));
				}

			break;

			case 'edit':

				if (!$module_id)
				{
					trigger_error($user->lang['NO_MODULE_ID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

				try
				{
					$module_row = $module_manager->get_module_row($module_id, $this->module_class);
				}
				catch (\phpbb\module\exception\module_not_found_exception $e)
				{
					$msg = $user->lang($e->getMessage());
					trigger_error($msg . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
				}

			// no break

			case 'add':

				if ($action == 'add')
				{
					$module_row = array(
						'module_basename'	=> '',
						'module_enabled'	=> 0,
						'module_display'	=> 1,
						'parent_id'			=> 0,
						'module_langname'	=> $request->variable('module_langname', '', true),
						'module_mode'		=> '',
						'module_auth'		=> '',
					);
				}

				$module_data = array();

				$module_data['module_basename'] = $request->variable('module_basename', (string) $module_row['module_basename']);
				$module_data['module_enabled'] = $request->variable('module_enabled', (int) $module_row['module_enabled']);
				$module_data['module_display'] = $request->variable('module_display', (int) $module_row['module_display']);
				$module_data['parent_id'] = $request->variable('module_parent_id', (int) $module_row['parent_id']);
				$module_data['module_class'] = $this->module_class;
				$module_data['module_langname'] = $request->variable('module_langname', (string) $module_row['module_langname'], true);
				$module_data['module_mode'] = $request->variable('module_mode', (string) $module_row['module_mode']);

				$submit = (isset($_POST['submit'])) ? true : false;

				if ($submit)
				{
					if (!check_form_key($form_key))
					{
						trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
					}

					if (!$module_data['module_langname'])
					{
						trigger_error($user->lang['NO_MODULE_LANGNAME'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
					}

					$module_type = $request->variable('module_type', 'category');

					if ($module_type == 'category')
					{
						$module_data['module_basename'] = $module_data['module_mode'] = $module_data['module_auth'] = '';
						$module_data['module_display'] = 1;
					}

					if ($action == 'edit')
					{
						$module_data['module_id'] = $module_id;
					}

					// Adjust auth row
					if ($module_data['module_basename'] && $module_data['module_mode'])
					{
						$fileinfo = $module_manager->get_module_infos($this->module_class, $module_data['module_basename']);
						$module_data['module_auth'] = $fileinfo[$module_data['module_basename']]['modes'][$module_data['module_mode']]['auth'];
					}

					try
					{
						$module_manager->update_module_data($module_data);
						$phpbb_log->add('admin',
							$user->data['user_id'],
							$user->ip,
							($action === 'edit') ? 'LOG_MODULE_EDIT' : 'LOG_MODULE_ADD',
							false,
							array($user->lang($module_data['module_langname']))
						);					}
					catch (\phpbb\module\exception\module_exception $e)
					{
						$msg = $user->lang($e->getMessage());
						trigger_error($msg . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
					}

					if (!count($errors))
					{
						$module_manager->remove_cache_file($this->module_class);

						trigger_error((($action == 'add') ? $user->lang['MODULE_ADDED'] : $user->lang['MODULE_EDITED']) . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
					}
				}

				// Category/not category?
				$is_cat = (!$module_data['module_basename']) ? true : false;

				// Get module information
				$module_infos = $module_manager->get_module_infos($this->module_class);

				// Build name options
				$s_name_options = $s_mode_options = '';
				foreach ($module_infos as $option => $values)
				{
					if (!$module_data['module_basename'])
					{
						$module_data['module_basename'] = $option;
					}

					// Name options
					$s_name_options .= '<option value="' . $option . '"' . (($option == $module_data['module_basename']) ? ' selected="selected"' : '') . '>' . $user->lang($values['title']) . ' [' . $option . ']</option>';

					$template->assign_block_vars('m_names', array('NAME' => $option, 'A_NAME' => addslashes($option)));

					// Build module modes
					foreach ($values['modes'] as $m_mode => $m_values)
					{
						if ($option == $module_data['module_basename'])
						{
							$s_mode_options .= '<option value="' . $m_mode . '"' . (($m_mode == $module_data['module_mode']) ? ' selected="selected"' : '') . '>' . $user->lang($m_values['title']) . '</option>';
						}

						$template->assign_block_vars('m_names.modes', array(
							'OPTION'		=> $m_mode,
							'VALUE'			=> $user->lang($m_values['title']),
							'A_OPTION'		=> addslashes($m_mode),
							'A_VALUE'		=> addslashes($user->lang($m_values['title'])))
						);
					}
				}

				$s_cat_option = '<option value="0"' . (($module_data['parent_id'] == 0) ? ' selected="selected"' : '') . '>' . $user->lang['NO_PARENT'] . '</option>';

				$template->assign_vars(array_merge(array(
					'S_EDIT_MODULE'		=> true,
					'S_IS_CAT'			=> $is_cat,
					'S_CAT_OPTIONS'		=> $s_cat_option . $this->make_module_select($module_data['parent_id'], ($action == 'edit') ? $module_row['module_id'] : false, false, false, false, true),
					'S_MODULE_NAMES'	=> $s_name_options,
					'S_MODULE_MODES'	=> $s_mode_options,
					'U_BACK'			=> $this->u_action . '&amp;parent_id=' . $this->parent_id,
					'U_EDIT_ACTION'		=> $this->u_action . '&amp;parent_id=' . $this->parent_id,

					'L_TITLE'			=> $user->lang[strtoupper($action) . '_MODULE'],

					'MODULENAME'		=> $user->lang($module_data['module_langname']),
					'ACTION'			=> $action,
					'MODULE_ID'			=> $module_id,

				),
					array_change_key_case($module_data, CASE_UPPER))
				);

				if (count($errors))
				{
					$template->assign_vars(array(
						'S_ERROR'	=> true,
						'ERROR_MSG'	=> implode('<br />', $errors))
					);
				}

				return;

			break;
		}

		// Default management page
		if (count($errors))
		{
			if ($request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'	=> $user->lang('ERROR'),
					'MESSAGE_TEXT'	=> implode('<br />', $errors),
					'SUCCESS'	=> false,
				));
			}

			$template->assign_vars(array(
				'S_ERROR'	=> true,
				'ERROR_MSG'	=> implode('<br />', $errors))
			);
		}

		if (!$this->parent_id)
		{
			$navigation = strtoupper($this->module_class);
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . strtoupper($this->module_class) . '</a>';

			$modules_nav = $module_manager->get_module_branch($this->parent_id, $this->module_class, 'parents');

			foreach ($modules_nav as $row)
			{
				$langname = $user->lang($row['module_langname']);

				if ($row['module_id'] == $this->parent_id)
				{
					$navigation .= ' -&gt; ' . $langname;
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;parent_id=' . $row['module_id'] . '">' . $langname . '</a>';
				}
			}
		}

		// Jumpbox
		$module_box = $this->make_module_select($this->parent_id, false, false, false, false);

		$sql = 'SELECT *
			FROM ' . MODULES_TABLE . "
			WHERE parent_id = {$this->parent_id}
				AND module_class = '" . $db->sql_escape($this->module_class) . "'
			ORDER BY left_id";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$langname = $user->lang($row['module_langname']);

				if (!$row['module_enabled'])
				{
					$module_image = '<img src="images/icon_folder_lock.gif" alt="' . $user->lang['DEACTIVATED_MODULE'] .'" />';
				}
				else
				{
					$module_image = (!$row['module_basename'] || $row['left_id'] + 1 != $row['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['CATEGORY'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['MODULE'] . '" />';
				}

				$url = $this->u_action . '&amp;parent_id=' . $this->parent_id . '&amp;m=' . $row['module_id'];

				$template->assign_block_vars('modules', array(
					'MODULE_IMAGE'		=> $module_image,
					'MODULE_TITLE'		=> $langname,
					'MODULE_ENABLED'	=> ($row['module_enabled']) ? true : false,
					'MODULE_DISPLAYED'	=> ($row['module_display']) ? true : false,

					'S_ACP_CAT_SYSTEM'			=> ($this->module_class == 'acp' && $row['module_langname'] == 'ACP_CAT_SYSTEM') ? true : false,
					'S_ACP_MODULE_MANAGEMENT'	=> ($this->module_class == 'acp' && ($row['module_basename'] == 'modules' || $row['module_langname'] == 'ACP_MODULE_MANAGEMENT')) ? true : false,

					'U_MODULE'			=> $this->u_action . '&amp;parent_id=' . $row['module_id'],
					'U_MOVE_UP'			=> $url . '&amp;action=move_up&amp;hash=' . generate_link_hash('acp_modules'),
					'U_MOVE_DOWN'		=> $url . '&amp;action=move_down&amp;hash=' . generate_link_hash('acp_modules'),
					'U_EDIT'			=> $url . '&amp;action=edit',
					'U_DELETE'			=> $url . '&amp;action=delete',
					'U_ENABLE'			=> $url . '&amp;action=enable&amp;hash=' . generate_link_hash('acp_modules'),
					'U_DISABLE'			=> $url . '&amp;action=disable&amp;hash=' . generate_link_hash('acp_modules'))
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		else if ($this->parent_id)
		{
			try
			{
				$row = $module_manager->get_module_row($this->parent_id, $this->module_class);
			}
			catch (\phpbb\module\exception\module_not_found_exception $e)
			{
				$msg = $user->lang($e->getMessage());
				trigger_error($msg . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id), E_USER_WARNING);
			}

			$url = $this->u_action . '&amp;parent_id=' . $this->parent_id . '&amp;m=' . $row['module_id'];

			$template->assign_vars(array(
				'S_NO_MODULES'		=> true,
				'MODULE_TITLE'		=> $langname,
				'MODULE_ENABLED'	=> ($row['module_enabled']) ? true : false,
				'MODULE_DISPLAYED'	=> ($row['module_display']) ? true : false,

				'U_EDIT'			=> $url . '&amp;action=edit',
				'U_DELETE'			=> $url . '&amp;action=delete',
				'U_ENABLE'			=> $url . '&amp;action=enable&amp;hash=' . generate_link_hash('acp_modules'),
				'U_DISABLE'			=> $url . '&amp;action=disable&amp;hash=' . generate_link_hash('acp_modules'))
			);
		}
		$db->sql_freeresult($result);

		// Quick adding module
		$module_infos = $module_manager->get_module_infos($this->module_class);

		// Build quick options
		$s_install_options = '';
		foreach ($module_infos as $option => $values)
		{
			// Name options
			$s_install_options .= '<optgroup label="' . $user->lang($values['title']) . ' [' . $option . ']">';

			// Build module modes
			foreach ($values['modes'] as $m_mode => $m_values)
			{
				$s_install_options .= '<option value="' . $option . '::' . $m_mode . '">&nbsp; &nbsp;' . $user->lang($m_values['title']) . '</option>';
			}

			$s_install_options .= '</optgroup>';
		}

		$template->assign_vars(array(
			'U_SEL_ACTION'		=> $this->u_action,
			'U_ACTION'			=> $this->u_action . '&amp;parent_id=' . $this->parent_id,
			'NAVIGATION'		=> $navigation,
			'MODULE_BOX'		=> $module_box,
			'PARENT_ID'			=> $this->parent_id,
			'S_INSTALL_OPTIONS'	=> $s_install_options,
			)
		);
	}

	/**
	* Simple version of jumpbox, just lists modules
	*/
	function make_module_select($select_id = false, $ignore_id = false, $ignore_acl = false, $ignore_nonpost = false, $ignore_emptycat = true, $ignore_noncat = false)
	{
		global $db, $user;

		$sql = 'SELECT module_id, module_enabled, module_basename, parent_id, module_langname, left_id, right_id, module_auth
			FROM ' . MODULES_TABLE . "
			WHERE module_class = '" . $db->sql_escape($this->module_class) . "'
			ORDER BY left_id ASC";
		$result = $db->sql_query($sql);

		$right = $iteration = 0;
		$padding_store = array('0' => '');
		$module_list = $padding = '';

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['left_id'] < $right)
			{
				$padding .= '&nbsp; &nbsp;';
				$padding_store[$row['parent_id']] = $padding;
			}
			else if ($row['left_id'] > $right + 1)
			{
				$padding = (isset($padding_store[$row['parent_id']])) ? $padding_store[$row['parent_id']] : '';
			}

			$right = $row['right_id'];

			if (!$ignore_acl && $row['module_auth'])
			{
				// We use zero as the forum id to check - global setting.
				if (!p_master::module_auth($row['module_auth'], 0))
				{
					continue;
				}
			}

			// ignore this module?
			if ((is_array($ignore_id) && in_array($row['module_id'], $ignore_id)) || $row['module_id'] == $ignore_id)
			{
				continue;
			}

			// empty category
			if (!$row['module_basename'] && ($row['left_id'] + 1 == $row['right_id']) && $ignore_emptycat)
			{
				continue;
			}

			// ignore non-category?
			if ($row['module_basename'] && $ignore_noncat)
			{
				continue;
			}

			$selected = (is_array($select_id)) ? ((in_array($row['module_id'], $select_id)) ? ' selected="selected"' : '') : (($row['module_id'] == $select_id) ? ' selected="selected"' : '');

			$langname = $user->lang($row['module_langname']);
			$module_list .= '<option value="' . $row['module_id'] . '"' . $selected . ((!$row['module_enabled']) ? ' class="disabled"' : '') . '>' . $padding . $langname . '</option>';

			$iteration++;
		}
		$db->sql_freeresult($result);

		unset($padding_store);

		return $module_list;
	}
}
